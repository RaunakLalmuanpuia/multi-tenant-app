<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Seeds global roles and their DEFAULT permissions via Spatie.
 *
 * These serve as fallback when a business has not customised a role.
 * Business-specific overrides are stored in business_role_permissions
 * and seeded in DummyDataSeeder.
 */

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles & permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── Permissions ─────────────────────────────────────────────

        $permissions = [
            // Dashboard
            'view dashboard',

            // Invoices
            'view invoices',
            'create invoice',
            'edit invoice',
            'delete invoice',
            'send invoice',

            // Bills & Expenses
            'view bills',
            'create bill',
            'edit bill',
            'delete bill',

            // Payments
            'view payments',
            'record payment',
            'edit payment',
            'delete payment',

            // Clients / Customers
            'view clients',
            'create client',
            'edit client',
            'delete client',

            // Vendors / Suppliers
            'view vendors',
            'create vendor',
            'edit vendor',
            'delete vendor',

            // Chart of Accounts
            'view accounts',
            'create account',
            'edit account',
            'delete account',

            // Journal Entries / Transactions
            'view transactions',
            'create transaction',
            'edit transaction',
            'delete transaction',

            // Reports
            'view reports',
            'export reports',

            // Tax
            'view tax',
            'manage tax',

            // Business Settings
            'view settings',
            'manage settings',

            // User Management (within a business)
            'view users',
            'invite user',
            'remove user',
            'assign roles',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ─── Roles & their permissions ────────────────────────────────

        /**
         * Admin — Platform-level super admin.
         * Full access across the system.
         */
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        /**
         * Owner — Business owner.
         * Full control within their own business, including user management.
         */
        $owner = Role::firstOrCreate(['name' => 'owner']);
        $owner->syncPermissions([
            'view dashboard',

            'view invoices', 'create invoice', 'edit invoice', 'delete invoice', 'send invoice',

            'view bills', 'create bill', 'edit bill', 'delete bill',

            'view payments', 'record payment', 'edit payment', 'delete payment',

            'view clients', 'create client', 'edit client', 'delete client',

            'view vendors', 'create vendor', 'edit vendor', 'delete vendor',

            'view accounts', 'create account', 'edit account', 'delete account',

            'view transactions', 'create transaction', 'edit transaction', 'delete transaction',

            'view reports', 'export reports',

            'view tax', 'manage tax',

            'view settings', 'manage settings',

            'view users', 'invite user', 'remove user', 'assign roles',
        ]);

        /**
         * CA (Chartered Accountant) — Senior accountant.
         * Full financial access and reporting. Cannot manage users or business settings.
         */
        $ca = Role::firstOrCreate(['name' => 'ca']);
        $ca->syncPermissions([
            'view dashboard',

            'view invoices', 'create invoice', 'edit invoice', 'delete invoice', 'send invoice',

            'view bills', 'create bill', 'edit bill', 'delete bill',

            'view payments', 'record payment', 'edit payment', 'delete payment',

            'view clients', 'create client', 'edit client', 'delete client',

            'view vendors', 'create vendor', 'edit vendor', 'delete vendor',

            'view accounts', 'create account', 'edit account', 'delete account',

            'view transactions', 'create transaction', 'edit transaction', 'delete transaction',

            'view reports', 'export reports',

            'view tax', 'manage tax',
        ]);

        /**
         * User — Basic staff user.
         * Can create and view day-to-day entries. Cannot delete, manage tax, settings, or users.
         */
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'view dashboard',

            'view invoices', 'create invoice',

            'view bills', 'create bill',

            'view payments', 'record payment',

            'view clients', 'create client',

            'view vendors', 'create vendor',

            'view accounts',

            'view transactions', 'create transaction',

            'view reports',
        ]);
    }
}
