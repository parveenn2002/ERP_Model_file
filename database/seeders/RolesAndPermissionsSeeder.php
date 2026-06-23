<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'products.view', 'products.manage',
            'suppliers.manage',
            'warehouses.manage',
            'purchase_orders.create', 'purchase_orders.submit',
            'purchase_orders.approve', 'purchase_orders.review',
            'purchase_orders.cancel',
            'stock.adjust',
            'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $admin = Role::create(['name' => 'Admin']);
        $admin->givePermissionTo(Permission::all());

        $manager = Role::create(['name' => 'Manager']);
        $manager->givePermissionTo([
            'products.view', 'products.manage',
            'suppliers.manage',
            'warehouses.manage',
            'purchase_orders.create', 'purchase_orders.submit',
            'purchase_orders.approve', 'purchase_orders.review',
            'purchase_orders.cancel',
            'stock.adjust',
            'reports.view',
        ]);

        $procurement = Role::create(['name' => 'Procurement']);
        $procurement->givePermissionTo([
            'products.view',
            'suppliers.manage',
            'purchase_orders.create', 'purchase_orders.submit',
            'reports.view',
        ]);

        $warehouse = Role::create(['name' => 'Warehouse']);
        $warehouse->givePermissionTo([
            'products.view',
            'warehouses.manage',
            'purchase_orders.review',
            'stock.adjust',
            'reports.view',
        ]);

        $viewer = Role::create(['name' => 'Viewer']);
        $viewer->givePermissionTo([
            'products.view',
            'reports.view',
        ]);

        // Create users with explicit email case
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'Admin'
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => 'password',
                'role' => 'Manager'
            ],
            [
                'name' => 'Procurement User',
                'email' => 'procurement@example.com',
                'password' => 'password',
                'role' => 'Procurement'
            ],
            [
                'name' => 'Warehouse User',
                'email' => 'warehouse@example.com',
                'password' => 'password',
                'role' => 'Warehouse'
            ],
            [
                'name' => 'Viewer User',
                'email' => 'viewer@example.com',
                'password' => 'password',
                'role' => 'Viewer'
            ],
        ];

        foreach ($users as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => strtolower($userData['email']), // Ensure lowercase
                'password' => Hash::make($userData['password']),
            ]);
            $user->assignRole($userData['role']);
        }
    }
}