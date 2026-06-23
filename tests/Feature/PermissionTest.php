<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Warehouse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $manager;
    protected $procurement;
    protected $warehouseStaff;
    protected $viewer;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        
        // Create users
        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('Admin');
        
        $this->manager = User::factory()->create(['email' => 'manager@test.com']);
        $this->manager->assignRole('Manager');
        
        $this->procurement = User::factory()->create(['email' => 'procurement@test.com']);
        $this->procurement->assignRole('Procurement');
        
        $this->warehouseStaff = User::factory()->create(['email' => 'warehouse@test.com']);
        $this->warehouseStaff->assignRole('Warehouse');
        
        $this->viewer = User::factory()->create(['email' => 'viewer@test.com']);
        $this->viewer->assignRole('Viewer');
    }

    /** @test */
    public function admin_can_access_all_routes()
    {
        $this->actingAs($this->admin);
        
        // Admin should have access to all pages
        $response = $this->get(route('admin.dashboard'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.products.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.categories.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.suppliers.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.warehouses.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.purchase-orders.index'));
        $response->assertStatus(200);
        
        $response = $this->get(route('admin.stock.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function viewer_can_only_view_products_and_reports()
    {
        $this->actingAs($this->viewer);
        
        // Viewer can view products
        $response = $this->get(route('admin.products.index'));
        $response->assertStatus(200);
        
        // Viewer cannot manage products
        $response = $this->get(route('admin.products.create'));
        $response->assertStatus(403);
        
        // Viewer cannot access categories
        $response = $this->get(route('admin.categories.index'));
        $response->assertStatus(403);
        
        // Viewer cannot access suppliers
        $response = $this->get(route('admin.suppliers.index'));
        $response->assertStatus(403);
        
        // Viewer cannot access warehouses
        $response = $this->get(route('admin.warehouses.index'));
        $response->assertStatus(403);
        
        // Viewer cannot access purchase orders
        $response = $this->get(route('admin.purchase-orders.index'));
        $response->assertStatus(403);
        
        // Viewer cannot access stock management
        $response = $this->get(route('admin.stock.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function procurement_can_create_and_submit_purchase_orders()
    {
        $this->actingAs($this->procurement);
        
        // Procurement can view products
        $response = $this->get(route('admin.products.index'));
        $response->assertStatus(200);
        
        // Procurement can manage suppliers
        $response = $this->get(route('admin.suppliers.index'));
        $response->assertStatus(200);
        
        // Procurement can create purchase orders
        $response = $this->get(route('admin.purchase-orders.create'));
        $response->assertStatus(200);
        
        // Procurement cannot approve purchase orders
        $purchaseOrder = PurchaseOrder::factory()->create(['status' => 'submitted']);
        $response = $this->post(route('admin.purchase-orders.approve', $purchaseOrder));
        $response->assertStatus(403);
        
        // Procurement cannot adjust stock
        $response = $this->get(route('admin.stock.adjust'));
        $response->assertStatus(403);
    }

    /** @test */
    public function warehouse_staff_can_adjust_stock()
    {
        $this->actingAs($this->warehouseStaff);
        
        // Warehouse staff can view products
        $response = $this->get(route('admin.products.index'));
        $response->assertStatus(200);
        
        // Warehouse staff can manage warehouses
        $response = $this->get(route('admin.warehouses.index'));
        $response->assertStatus(200);
        
        // Warehouse staff can adjust stock
        $response = $this->get(route('admin.stock.adjust'));
        $response->assertStatus(200);
        
        // Warehouse staff cannot create purchase orders
        $response = $this->get(route('admin.purchase-orders.create'));
        $response->assertStatus(403);
        
        // Warehouse staff cannot manage suppliers
        $response = $this->get(route('admin.suppliers.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function manager_can_approve_purchase_orders()
    {
        $this->actingAs($this->manager);
        
        // Manager can approve purchase orders
        $purchaseOrder = PurchaseOrder::factory()->create(['status' => 'submitted']);
        $response = $this->post(route('admin.purchase-orders.approve', $purchaseOrder));
        $response->assertStatus(302);
        
        // Manager can cancel purchase orders
        $purchaseOrder2 = PurchaseOrder::factory()->create(['status' => 'draft']);
        $response = $this->post(route('admin.purchase-orders.cancel', $purchaseOrder2));
        $response->assertStatus(302);
        
        // Manager can adjust stock
        $response = $this->get(route('admin.stock.adjust'));
        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_users_cannot_access_routes_via_direct_url()
    {
        $this->actingAs($this->viewer);
        
        // Try to access admin routes directly
        $response = $this->get('/admin/categories');
        $response->assertStatus(403);
        
        $response = $this->get('/admin/suppliers');
        $response->assertStatus(403);
        
        $response = $this->get('/admin/warehouses');
        $response->assertStatus(403);
        
        $response = $this->get('/admin/purchase-orders/create');
        $response->assertStatus(403);
        
        $response = $this->get('/admin/stock/adjust');
        $response->assertStatus(403);
    }

    /** @test */
    public function users_without_permission_cannot_perform_actions()
    {
        $this->actingAs($this->procurement);
        
        // Create a product first
        $product = Product::factory()->create();
        
        // Procurement cannot delete products
        $response = $this->delete(route('admin.products.destroy', $product));
        $response->assertStatus(403);
        
        // Create a category
        $category = Category::factory()->create();
        
        // Procurement cannot delete categories
        $response = $this->delete(route('admin.categories.destroy', $category));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_manage_all_resources()
    {
        $this->actingAs($this->admin);
        
        // Admin can create products
        $productData = Product::factory()->make()->toArray();
        $response = $this->post(route('admin.products.store'), $productData);
        $response->assertStatus(302);
        $this->assertDatabaseHas('products', ['sku' => $productData['sku']]);
        
        // Admin can create categories
        $categoryData = Category::factory()->make()->toArray();
        $response = $this->post(route('admin.categories.store'), $categoryData);
        $response->assertStatus(302);
        $this->assertDatabaseHas('categories', ['name' => $categoryData['name']]);
        
        // Admin can create suppliers
        $supplierData = Supplier::factory()->make()->toArray();
        $response = $this->post(route('admin.suppliers.store'), $supplierData);
        $response->assertStatus(302);
        $this->assertDatabaseHas('suppliers', ['code' => $supplierData['code']]);
        
        // Admin can create warehouses
        $warehouseData = Warehouse::factory()->make()->toArray();
        $response = $this->post(route('admin.warehouses.store'), $warehouseData);
        $response->assertStatus(302);
        $this->assertDatabaseHas('warehouses', ['code' => $warehouseData['code']]);
    }
}