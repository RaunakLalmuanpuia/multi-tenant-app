<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BusinessPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): mixed
    {
        $user     = $request->user();
        $business = $request->route('business');

        abort_if(! $user || ! $business, 403);

        foreach ($permissions as $permission) {
            abort_unless($user->hasPermissionInBusiness($permission, $business), 403);
        }

        return $next($request);
    }
}
