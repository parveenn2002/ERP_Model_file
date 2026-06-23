<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\StockMovement;
use App\Services\PurchaseOrderService;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $purchaseOrderService;
    protected $supplier;
    protected $warehouse;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        $this->user = User::factory()->create();
        $this->user->assignRole('Admin');
        $this->actingAs($this->user);
        
        $this->purchaseOrderService = app(PurchaseOrderService::class);
        
        $this->supplier = Supplier::factory()->create();
        $this->warehouse = Warehouse::factory()->create();
        $this->product = Product::factory()->create([
            'cost_price' => 100.00,
            'reorder_level' => 10
        ]);
    }

    /** @test */
    public function can_create_purchase_order()
    {
        $data = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_date' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ]
            ],
            'notes' => 'Test purchase order'
        ];

        $po = $this->purchaseOrderService->create($data);
        
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'supplier_id' => $this->supplier->id,
            'status' => PurchaseOrderStatus::DRAFT->value,
            'subtotal' => 500.00,
            'tax_amount' => 25.00,
            'grand_total' => 525.00,
        ]);
        
        $this->assertDatabaseHas('purchase_order_items', [
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'quantity' => 5,
            'unit_price' => 100.00,
            'line_total' => 500.00,
        ]);
    }

    /** @test */
    public function can_submit_purchase_order()
    {
        $po = $this->createDraftPurchaseOrder();
        
        $submittedPo = $this->purchaseOrderService->submit($po);
        
        $this->assertEquals(PurchaseOrderStatus::SUBMITTED, $submittedPo->status);
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrderStatus::SUBMITTED->value
        ]);
        
        // Check activity log
        $this->assertDatabaseHas('activity_log', [
            'subject_type' => PurchaseOrder::class,
            'subject_id' => $po->id,
            'description' => 'Purchase order submitted'
        ]);
    }

    /** @test */
    public function only_draft_orders_can_be_submitted()
    {
        $po = $this->createDraftPurchaseOrder();
        $this->purchaseOrderService->submit($po);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only draft purchase orders can be submitted.');
        
        $this->purchaseOrderService->submit($po);
    }

    /** @test */
    public function can_approve_purchase_order()
    {
        $po = $this->createDraftPurchaseOrder();
        $this->purchaseOrderService->submit($po);
        
        $approvedPo = $this->purchaseOrderService->approve($po);
        
        $this->assertEquals(PurchaseOrderStatus::APPROVED, $approvedPo->status);
        $this->assertEquals($this->user->id, $approvedPo->approved_by);
        $this->assertNotNull($approvedPo->approved_at);
        
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrderStatus::APPROVED->value,
            'approved_by' => $this->user->id
        ]);
    }

    /** @test */
    public function only_submitted_orders_can_be_approved()
    {
        $po = $this->createDraftPurchaseOrder();
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only submitted purchase orders can be approved.');
        
        $this->purchaseOrderService->approve($po);
    }

    /** @test */
    public function can_receive_purchase_order_and_update_stock()
    {
        $po = $this->createDraftPurchaseOrder();
        $this->purchaseOrderService->submit($po);
        $this->purchaseOrderService->approve($po);
        
        $receivedPo = $this->purchaseOrderService->receive($po);
        
        $this->assertEquals(PurchaseOrderStatus::RECEIVED, $receivedPo->status);
        $this->assertEquals($this->user->id, $receivedPo->received_by);
        $this->assertNotNull($receivedPo->received_at);
        
        // Check stock was updated
        $this->assertDatabaseHas('warehouse_stock', [
            'warehouse_id' => $this->warehouse->id,
            'product_id' => $this->product->id,
            'quantity' => 5
        ]);
        
        // Check stock movement was created
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $this->product->id,
            'warehouse_id' => $this->warehouse->id,
            'type' => 'in',
            'quantity' => 5,
            'balance_after' => 5,
            'reference_type' => PurchaseOrder::class,
            'reference_id' => $po->id,
        ]);
    }

    /** @test */
    public function only_approved_orders_can_be_received()
    {
        $po = $this->createDraftPurchaseOrder();
        $this->purchaseOrderService->submit($po);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Only approved purchase orders can be received.');
        
        $this->purchaseOrderService->receive($po);
    }

    /** @test */
    public function can_cancel_purchase_order()
    {
        $po = $this->createDraftPurchaseOrder();
        
        $cancelledPo = $this->purchaseOrderService->cancel($po);
        
        $this->assertEquals(PurchaseOrderStatus::CANCELLED, $cancelledPo->status);
        
        $this->assertDatabaseHas('purchase_orders', [
            'id' => $po->id,
            'status' => PurchaseOrderStatus::CANCELLED->value
        ]);
    }

    /** @test */
    public function received_orders_cannot_be_cancelled()
    {
        $po = $this->createDraftPurchaseOrder();
        $this->purchaseOrderService->submit($po);
        $this->purchaseOrderService->approve($po);
        $this->purchaseOrderService->receive($po);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This purchase order cannot be cancelled.');
        
        $this->purchaseOrderService->cancel($po);
    }

    /** @test */
    public function purchase_order_calculations_are_correct()
    {
        $data = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_price' => 100.00,
                ],
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 50.00,
                ]
            ],
            'notes' => 'Test purchase order'
        ];

        $po = $this->purchaseOrderService->create($data);
        
        $expectedSubtotal = (10 * 100) + (5 * 50); // 1000 + 250 = 1250
        $expectedTax = $expectedSubtotal * 0.05; // 62.5
        $expectedGrandTotal = $expectedSubtotal + $expectedTax; // 1312.5
        
        $this->assertEquals($expectedSubtotal, $po->subtotal);
        $this->assertEquals($expectedTax, $po->tax_amount);
        $this->assertEquals($expectedGrandTotal, $po->grand_total);
    }

    /** @test */
    public function po_number_is_auto_generated()
    {
        $po1 = $this->createDraftPurchaseOrder();
        $po2 = $this->createDraftPurchaseOrder();
        
        $this->assertStringStartsWith('PO-' . now()->year, $po1->po_number);
        $this->assertStringStartsWith('PO-' . now()->year, $po2->po_number);
        $this->assertNotEquals($po1->po_number, $po2->po_number);
    }

    /** @test */
    public function receiving_po_creates_stock_movement_audit_trail()
    {
        $po = $this->createDraftPurchaseOrder();
        $this->purchaseOrderService->submit($po);
        $this->purchaseOrderService->approve($po);
        
        $this->purchaseOrderService->receive($po);
        
        $movement = StockMovement::where('reference_type', PurchaseOrder::class)
            ->where('reference_id', $po->id)
            ->first();
        
        $this->assertNotNull($movement);
        $this->assertEquals('in', $movement->type);
        $this->assertEquals($this->product->id, $movement->product_id);
        $this->assertEquals($this->warehouse->id, $movement->warehouse_id);
        $this->assertEquals(5, $movement->quantity);
        $this->assertEquals(5, $movement->balance_after);
        $this->assertEquals($this->user->id, $movement->created_by);
        $this->assertEquals('Received from purchase order ' . $po->po_number, $movement->notes);
    }

    /** @test */
    public function can_edit_draft_purchase_order()
    {
        $po = $this->createDraftPurchaseOrder();
        
        $newData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_date' => now()->addDays(10)->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                    'unit_price' => 150.00,
                ]
            ],
            'notes' => 'Updated notes'
        ];
        
        $updatedPo = $this->purchaseOrderService->update($po, $newData);
        
        $this->assertEquals($newData['notes'], $updatedPo->notes);
        $this->assertEquals(3, $updatedPo->items->first()->quantity);
        $this->assertEquals(150.00, $updatedPo->items->first()->unit_price);
        $this->assertEquals(450.00, $updatedPo->subtotal);
    }

    protected function createDraftPurchaseOrder()
    {
        $data = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'order_date' => now()->format('Y-m-d'),
            'expected_date' => now()->addDays(7)->format('Y-m-d'),
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_price' => 100.00,
                ]
            ],
            'notes' => 'Test purchase order'
        ];

        return $this->purchaseOrderService->create($data);
    }
}