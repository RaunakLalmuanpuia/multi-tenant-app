# Multi-Tenant Business App

A multi-tenant B2B SaaS foundation. One user can belong to several businesses and hold a different role in each. Every tenant's data is isolated automatically.

Built with **Laravel 13, Vue 3 and Inertia.js**.

## Features

- **Businesses as tenants.** Routes are scoped under `/b/{business}/…`.
- **Automatic data isolation.** Models using the `BelongsToBusiness` trait get a global scope, so queries on business routes only return that business's records.
- **Per-business roles and permissions.** The `BusinessPermission` middleware checks permissions for the current business, not globally. `EnsureBelongsToBusiness` blocks access to businesses the user isn't a member of.
- **Team management.** Invite members by email, accept invitations, and assign and manage roles.
- **Role-based dashboards.** There are separate dashboards for Admin, Owner, CA (accountant) and User.
- **Domain modules.** Clients and vendors, both tenant-scoped.

## How tenancy works

```php
class Client extends Model
{
    use BelongsToBusiness;   // auto-filters by the {business} in the URL
}

Route::middleware(['auth', 'verified', 'member'])   // must belong to the business
    ->prefix('b/{business}')
    ->group(function () {
        Route::get('/settings/team', [TeamMemberController::class, 'index'])
            ->middleware('biz.permission:view users');  // permission checked per business
    });
```

## Stack

Laravel 13 · PHP 8.3 · Vue 3 · Inertia.js · Tailwind · spatie/laravel-permission

## Running locally

```bash
git clone https://github.com/RaunakLalmuanpuia/multi-tenant-app.git && cd multi-tenant-app
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
composer run dev
```
