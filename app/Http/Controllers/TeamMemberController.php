<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\BusinessUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class TeamMemberController extends Controller
{
    public function index(Business $business)
    {
        $members = User::whereHas('businesses', fn($q) => $q->where('businesses.id', $business->id))
            ->with(['businessRoles' => fn($q) => $q->where('business_id', $business->id)->with('role')])
            ->get()
            ->map(fn($user) => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->businessRoles->first()?->role
                    ? ['id' => $user->businessRoles->first()->role->id, 'name' => $user->businessRoles->first()->role->name]
                    : null,
            ]);

        return inertia('Settings/Team', [
            'business' => $business,
            'members'  => $members,
            'roles'    => Role::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Business $business, User $user)
    {
        $request->validate(['role_id' => 'required|exists:roles,id']);

        BusinessUserRole::updateOrCreate(
            ['user_id' => $user->id, 'business_id' => $business->id],
            ['role_id' => $request->role_id]
        );

        return back();
    }

    public function destroy(Business $business, User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot remove yourself.');

        BusinessUserRole::where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->delete();

        $user->businesses()->detach($business->id);

        return back();
    }
}
