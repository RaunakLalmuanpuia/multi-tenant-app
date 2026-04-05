<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user     = $request->user();
        $business = $request->route('business'); // resolved via route model binding

        return [
            ...parent::share($request),

            'auth' => [
                'user'     => $user,
                'is_admin' => $user?->hasRole('admin') ?? false,

                // All businesses the user belongs to (for the switcher)
                'businesses' => $user && !$user->hasRole('admin')
                    ? $user->businesses()->select('businesses.id', 'businesses.name')->get()
                    : [],

                // Current business comes from the URL, not session
                'current_business_id' => $business?->id,

                // Admin gets all permissions; others get their role's permissions for this business
                'permissions' => match (true) {
                    $user === null          => [],
                    $user->hasRole('admin') => Permission::pluck('name'),
                    $business === null      => [],
                    default                 => $user->businessRoles()
                        ->where('business_id', $business->id)
                        ->with('role.permissions')
                        ->get()
                        ->pluck('role.permissions')
                        ->flatten()
                        ->pluck('name')
                        ->unique()
                        ->values(),
                },
            ],
        ];
    }
}
