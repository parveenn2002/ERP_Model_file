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
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'products.view', 'products.manage',
            'suppliers.manage',
            'warehouses.manage',
            'purchase_orders.create', 'purchase_orders.submit',
            'purchase_orders.approve', 'purchase_orders.review',
            'purchase_orders.cancel',
            'stock.adjust',
            'reports.view',
        ]);

        $procurement = Role::firstOrCreate(['name' => 'Procurement', 'guard_name' => 'web']);
        $procurement->syncPermissions([
            'products.view',
            'suppliers.manage',
            'purchase_orders.create', 'purchase_orders.submit',
            'reports.view',
        ]);

        $warehouse = Role::firstOrCreate(['name' => 'Warehouse', 'guard_name' => 'web']);
        $warehouse->syncPermissions([
            'products.view',
            'warehouses.manage',
            'purchase_orders.review',
            'stock.adjust',
            'reports.view',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'products.view',
            'reports.view',
        ]);

        // Create users with exact credentials
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
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles([$userData['role']]);
            
            echo "✅ User created: " . $userData['email'] . " / " . $userData['password'] . " (Role: " . $userData['role'] . ")\n";
        }
    }
}