<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Business;
use App\Models\BusinessUserRole;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Businesses ───────────────────────────────────────────────
        $alpha = Business::create(['name' => 'Alpha Accounting Firm']);
        $beta  = Business::create(['name' => 'Beta Finance Group']);
        $gamma = Business::create(['name' => 'Gamma Tax Consultants']);

        // ─── Roles ────────────────────────────────────────────────────
        $adminRole = Role::where('name', 'admin')->first();
        $ownerRole = Role::where('name', 'owner')->first();
        $caRole    = Role::where('name', 'ca')->first();
        $userRole  = Role::where('name', 'user')->first();

        // ─── Platform Admin (no business) ─────────────────────────────
        // Admin is a global platform role — not assigned to any business.
        $admin = User::create([
            'name'     => 'Platform Admin',
            'email'    => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole); // Spatie global role — no business context

        // ─── Users with multiple businesses & different roles ──────────
        //
        // alice  → Alpha: owner  | Beta: ca
        // bob    → Beta: owner   | Gamma: ca
        // carol  → Gamma: owner  | Alpha: user
        // david  → Alpha: ca     | Beta: ca      | Gamma: user
        // eve    → Alpha: user   | Beta: owner   | Gamma: ca
        // frank  → Beta: user    | Gamma: user
        // grace  → Alpha: owner  | Gamma: ca

        $users = [
            ['name' => 'Alice Sharma',  'email' => 'alice@example.com',  'assignments' => [[$alpha, $ownerRole], [$beta, $caRole]]],
            ['name' => 'Bob Mehta',     'email' => 'bob@example.com',    'assignments' => [[$beta, $ownerRole], [$gamma, $caRole]]],
            ['name' => 'Carol Singh',   'email' => 'carol@example.com',  'assignments' => [[$gamma, $ownerRole], [$alpha, $userRole]]],
            ['name' => 'David Patel',   'email' => 'david@example.com',  'assignments' => [[$alpha, $caRole], [$beta, $caRole], [$gamma, $userRole]]],
            ['name' => 'Eve Kapoor',    'email' => 'eve@example.com',    'assignments' => [[$alpha, $userRole], [$beta, $ownerRole], [$gamma, $caRole]]],
            ['name' => 'Frank D\'souza','email' => 'frank@example.com',  'assignments' => [[$beta, $userRole], [$gamma, $userRole]]],
            ['name' => 'Grace Nair',    'email' => 'grace@example.com',  'assignments' => [[$alpha, $ownerRole], [$gamma, $caRole]]],
        ];

        foreach ($users as $userData) {
            $firstBusiness = $userData['assignments'][0][0];

            $user = User::create([
                'name'             => $userData['name'],
                'email'            => $userData['email'],
                'password'         => Hash::make('password'),
                'last_business_id' => $firstBusiness->id,
            ]);

            foreach ($userData['assignments'] as [$business, $role]) {
                $user->businesses()->syncWithoutDetaching($business->id);
                BusinessUserRole::create([
                    'user_id'     => $user->id,
                    'business_id' => $business->id,
                    'role_id'     => $role->id,
                ]);
            }
        }
    }
}
