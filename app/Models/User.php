<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function businesses():BelongsToMany
    {
        return $this->belongsToMany(Business::class, 'business_user');
    }

    public function businessRoles() :HasMany
    {
        return $this->hasMany(BusinessUserRole::class);
    }


    public function hasRoleInBusiness($roleName, ?int $businessId = null): bool
    {
        $businessId ??= request()->route('business')?->id;

        return $this->businessRoles()
            ->where('business_id', $businessId)
            ->whereHas('role', fn($q) => $q->where('name', $roleName))
            ->exists();
    }

    public function hasPermissionInBusiness($permission, ?int $businessId = null): bool
    {
        $businessId ??= request()->route('business')?->id;

        return $this->businessRoles()
            ->where('business_id', $businessId)
            ->whereHas('role.permissions', fn($q) => $q->where('name', $permission))
            ->exists();
    }
}
