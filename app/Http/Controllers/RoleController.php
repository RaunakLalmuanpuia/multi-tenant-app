<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    // Roles that cannot be edited or deleted
    private const SYSTEM_ROLES = ['admin', 'owner'];

    private function permissionGroups(): array
    {
        return [
            'Dashboard'       => ['view dashboard'],
            'Invoices'        => ['view invoices', 'create invoice', 'edit invoice', 'delete invoice', 'send invoice'],
            'Bills'           => ['view bills', 'create bill', 'edit bill', 'delete bill'],
            'Payments'        => ['view payments', 'record payment', 'edit payment', 'delete payment'],
            'Clients'         => ['view clients', 'create client', 'edit client', 'delete client'],
            'Vendors'         => ['view vendors', 'create vendor', 'edit vendor', 'delete vendor'],
            'Accounts'        => ['view accounts', 'create account', 'edit account', 'delete account'],
            'Transactions'    => ['view transactions', 'create transaction', 'edit transaction', 'delete transaction'],
            'Reports'         => ['view reports', 'export reports'],
            'Tax'             => ['view tax', 'manage tax'],
            'Settings'        => ['view settings', 'manage settings'],
            'User Management' => ['view users', 'invite user', 'remove user', 'assign roles'],
        ];
    }

    public function index(\App\Models\Business $business)
    {
        $allPermissions = Permission::all()->keyBy('name');

        $permissionGroups = collect($this->permissionGroups())
            ->map(fn($names, $group) => [
                'group'       => $group,
                'permissions' => collect($names)
                    ->map(fn($name) => $allPermissions->get($name))
                    ->filter()
                    ->values(),
            ])
            ->values();

        return inertia('Roles/Index', [
            'roles'            => Role::with('permissions')->get(),
            'permissionGroups' => $permissionGroups,
            'systemRoles'      => self::SYSTEM_ROLES,
        ]);
    }

    public function store(Request $request, \App\Models\Business $business)
    {
        $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return back();
    }

    public function update(Request $request, \App\Models\Business $business, Role $role)
    {
        abort_if(in_array($role->name, self::SYSTEM_ROLES), 403, 'System roles cannot be modified.');

        $request->validate([
            'name'        => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return back();
    }

    public function destroy(\App\Models\Business $business, Role $role)
    {
        abort_if(in_array($role->name, self::SYSTEM_ROLES), 403, 'System roles cannot be deleted.');

        $role->delete();
        return back();
    }
}
