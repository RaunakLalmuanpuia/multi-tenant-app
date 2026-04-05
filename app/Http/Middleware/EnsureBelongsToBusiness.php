<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBelongsToBusiness
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Platform admin has access to all businesses
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $business = $request->route('business');

        if ($business && !$user->businesses()->where('businesses.id', $business->id)->exists()) {
            abort(403, 'You do not belong to this business.');
        }

        return $next($request);
    }
}
