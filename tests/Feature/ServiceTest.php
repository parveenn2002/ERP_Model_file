<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use App\Services\StockService;
use App\Services\InventoryReportService;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $purchaseOrderService;
    protected $stockService;
    protected $reportService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');
        $this->actingAs($this->user);
        
        $this->purchaseOrderService = app(PurchaseOrderService::class);
        $this->stockService = app(StockService::class);
        $this->reportService = app(InventoryReportService::class);
    }

    /** @test */
    public function purchase_order_service_calculates_totals_correctly()
    {
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product1 = Product::factory()->create(['cost_price' => 100.00]);
        $product2 = Product::factory()->create(['cost_price' => 50.00]);
        
        $data = [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product1->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
                [
                    'product_id' => $product2->id,
                    'quantity' => 5,
                    'unit_price' => 50.00,
                ]
            ],
            'notes' => 'Test PO'
        ];

        $po = $this->purchaseOrderService->create($data);
        
        $expectedSubtotal = 1000 + 250; // 1250
        $expectedTax = 1250 * 0.05; // 62.5
        $expectedGrandTotal = 1250 + 62.5; // 1312.5        
        $this->assertEquals($expectedSubtotal, $po->subtotal);
        $this->assertEquals($expectedTax, $po->tax_amount);
        $this->assertEquals($expectedGrandTotal, $po->grand_total);
    }

    /** @test */
    public function stock_service_tracks_movement_correctly()
    {
        $product = Product::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        // Initial stock in
        $this->stockService->adjust($product, $warehouse, 'in', 20, 'Initial stock');
        
        $this->assertDatabaseHas('warehouse_stock', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 20
        ]);
        
        // Stock out
        $this->stockService->adjust($product, $warehouse, 'out', 5, 'Stock out');
        
        $this->assertDatabaseHas('warehouse_stock', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 15
        ]);
        
        // Adjustment
        $this->stockService->adjust($product, $warehouse, 'adjustment', 10, 'Adjust to 10');
        
        $this->assertDatabaseHas('warehouse_stock', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10
        ]);
        
        // Check all movements were logged
        $movements = StockMovement::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse->id)
            ->get();
        
        $this->assertEquals(3, $movements->count());
        $this->assertEquals('in', $movements[0]->type);
        $this->assertEquals('out', $movements[1]->type);
        $this->assertEquals('adjustment', $movements[2]->type);
    }

    /** @test */
    public function inventory_report_service_returns_correct_stats()
    {
        // Create products with stock
        $product1 = Product::factory()->create(['cost_price' => 100.00, 'reorder_level' => 5]);
        $product2 = Product::factory()->create(['cost_price' => 200.00, 'reorder_level' => 10]);
        $warehouse = Warehouse::factory()->create();
        
        $this->stockService->adjust($product1, $warehouse, 'in', 3, 'Low stock');
        $this->stockService->adjust($product2, $warehouse, 'in', 20, 'Good stock');
        
        // Create purchase orders
        $supplier = Supplier::factory()->create();
        $po1 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::SUBMITTED);
        $po2 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::APPROVED);
        $po3 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::RECEIVED);
        
        $stats = $this->reportService->getDashboardStats();
        
        // Check low stock count (product1 has 3 <= reorder_level 5)
        $this->assertEquals(1, $stats['low_stock_count']);
        
        // Check pending orders (submitted + approved = 2)
        $this->assertEquals(2, $stats['pending_orders_count']);
        
        // Check total stock value (3*100 + 20*200 = 300 + 4000 = 4300)
        $this->assertEquals(4300, $stats['total_stock_value']);
    }

    /** @test */
    public function inventory_report_service_gets_open_purchase_orders()
    {
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        $po1 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::SUBMITTED);
        $po2 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::APPROVED);
        $po3 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::RECEIVED);
        $po4 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::CANCELLED);
        
        $openOrders = $this->reportService->getOpenPurchaseOrders();
        
        $this->assertEquals(2, $openOrders->count());
        $this->assertTrue($openOrders->contains($po1));
        $this->assertTrue($openOrders->contains($po2));
        $this->assertFalse($openOrders->contains($po3));
        $this->assertFalse($openOrders->contains($po4));
    }

    /** @test */
    public function inventory_report_service_gets_warehouse_summary()
    {
        $warehouse = Warehouse::factory()->create();
        $product1 = Product::factory()->create(['cost_price' => 100.00]);
        $product2 = Product::factory()->create(['cost_price' => 50.00]);
        $product3 = Product::factory()->create(['cost_price' => 200.00]);
        
        $this->stockService->adjust($product1, $warehouse, 'in', 10, 'Stock 1');
        $this->stockService->adjust($product2, $warehouse, 'in', 5, 'Stock 2');
        $this->stockService->adjust($product3, $warehouse, 'in', 2, 'Stock 3');
        
        $summary = $this->stockService->getWarehouseSummary($warehouse);
        
        $this->assertEquals(3, $summary['product_count']);
        $this->assertEquals(17, $summary['total_units']);
        
        $expectedValue = (10 * 100) + (5 * 50) + (2 * 200); // 1000 + 250 + 400 = 1650
        $this->assertEquals($expectedValue, $summary['stock_value']);
    }

    /** @test */
    public function purchase_order_service_validates_workflow_transitions()
    {
        $supplier = Supplier::factory()->create();
        $warehouse = Warehouse::factory()->create();
        
        // Test draft -> submitted
        $po = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::DRAFT);
        $this->purchaseOrderService->submit($po);
        $this->assertEquals(PurchaseOrderStatus::SUBMITTED, $po->fresh()->status);
        
        // Test submitted -> approved
        $this->purchaseOrderService->approve($po);
        $this->assertEquals(PurchaseOrderStatus::APPROVED, $po->fresh()->status);
        
        // Test approved -> received
        $this->purchaseOrderService->receive($po);
        $this->assertEquals(PurchaseOrderStatus::RECEIVED, $po->fresh()->status);
        
        // Test cannot transition from received
        $this->expectException(\Exception::class);
        $this->purchaseOrderService->cancel($po);
    }

    /** @test */
    public function stock_service_maintains_data_integrity()
    {
        $product = Product::factory()->create();
        $warehouse1 = Warehouse::factory()->create();
        $warehouse2 = Warehouse::factory()->create();
        
        // Add stock to warehouse 1
        $this->stockService->adjust($product, $warehouse1, 'in', 10, 'Stock WH1');
        
        // Add stock to warehouse 2
        $this->stockService->adjust($product, $warehouse2, 'in', 5, 'Stock WH2');
        
        // Check both warehouses have correct stock
        $stock1 = WarehouseStock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse1->id)
            ->first();
        $stock2 = WarehouseStock::where('product_id', $product->id)
            ->where('warehouse_id', $warehouse2->id)
            ->first();
        
        $this->assertEquals(10, $stock1->quantity);
        $this->assertEquals(5, $stock2->quantity);
        
        // Remove from warehouse 1
        $this->stockService->adjust($product, $warehouse1, 'out', 3, 'Remove 3');
        
        $stock1->refresh();
        $this->assertEquals(7, $stock1->quantity);
        
        // Total stock should be 12 (7 + 5)
        $totalStock = $product->warehouseStocks()->sum('quantity');
        $this->assertEquals(12, $totalStock);
    }

    /** @test */
    public function service_methods_use_eloquent_scopes()
    {
        // Test active scope
        $activeProduct = Product::factory()->create(['is_active' => true]);
        $inactiveProduct = Product::factory()->create(['is_active' => false]);
        
        $activeProducts = Product::active()->get();
        $this->assertTrue($activeProducts->contains($activeProduct));
        $this->assertFalse($activeProducts->contains($inactiveProduct));
        
        // Test low stock scope
        $lowStockProduct = Product::factory()->create(['reorder_level' => 5]);
        $goodStockProduct = Product::factory()->create(['reorder_level' => 5]);
        $warehouse = Warehouse::factory()->create();
        
        $this->stockService->adjust($lowStockProduct, $warehouse, 'in', 3, 'Low stock');
        $this->stockService->adjust($goodStockProduct, $warehouse, 'in', 10, 'Good stock');
        
        $lowStock = Product::lowStock()->get();
        $this->assertTrue($lowStock->contains($lowStockProduct));
        $this->assertFalse($lowStock->contains($goodStockProduct));
        
        // Test pending scope for purchase orders
        $supplier = Supplier::factory()->create();
        $po1 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::SUBMITTED);
        $po2 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::APPROVED);
        $po3 = $this->createPurchaseOrder($supplier, $warehouse, PurchaseOrderStatus::RECEIVED);
        
        $pendingOrders = PurchaseOrder::pending()->get();
        $this->assertTrue($pendingOrders->contains($po1));
        $this->assertTrue($pendingOrders->contains($po2));
        $this->assertFalse($pendingOrders->contains($po3));
    }

    protected function createPurchaseOrder($supplier, $warehouse, $status)
    {
        $product = Product::factory()->create(['cost_price' => 100.00]);
        
        $data = [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ]
            ],
            'notes' => 'Test PO'
        ];
        
        $po = $this->purchaseOrderService->create($data);
        
        if ($status !== PurchaseOrderStatus::DRAFT) {
            $this->purchaseOrderService->submit($po);
            
            if ($status === PurchaseOrderStatus::APPROVED || $status === PurchaseOrderStatus::RECEIVED) {
                $this->purchaseOrderService->approve($po);
                
                if ($status === PurchaseOrderStatus::RECEIVED) {
                    $this->purchaseOrderService->receive($po);
                }
            }
            
            if ($status === PurchaseOrderStatus::CANCELLED) {
                $this->purchaseOrderService->cancel($po);
            }
        }
        
        return $po->fresh();
    }
}