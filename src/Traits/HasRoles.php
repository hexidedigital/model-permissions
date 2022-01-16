<?php

namespace HexideDigital\ModelPermissions\Traits;

use Eloquent;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * For User model
 *
 * @mixin Model
 * @mixin Eloquent
 */
trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->with('permissions', function (BelongsToMany $builder){
                return $builder->select('title');
            });
    }

    public function getPermissionsAttribute(): \Illuminate\Support\Collection
    {
        $permissions = collect();

        $this->roles->each(function (Role $role) use (&$permissions) {
            $permissions = $permissions->merge($role->permissions->pluck('title'));
        });

        return $permissions->unique();
    }

    public function hasPermission(?string $permission_key): bool
    {
        return $this->permissions->contains($permission_key);
    }

    public function hasAdminAccess(): bool
    {
        return $this->roles
                ->filter(function (Role $role) {
                    return in_array($role->id, [Role::Admin, Role::SuperAdmin])
                        || $role->admin_access;
                })
                ->count() > 0;
    }

    public function scopeIsRole(Builder $builder, $role, string $column = 'id'): Builder
    {
        return $builder->whereHas('roles', fn(Builder $builder) => $builder->where($column, $role));
    }

    public function isRoleSuperAdmin(): bool
    {
        return $this->isRole(Role::SuperAdmin, 'id');
    }

    public function isRoleAdmin(): bool
    {
        return $this->isRole(Role::Admin, 'id');
    }

    public function isRoleUser(): bool
    {
        return $this->isRole(Role::User, 'id');
    }

    public function isRole($value, string $column = 'id'): bool
    {
        return $this->roles->where($column, $value)->count() > 0;
    }

}
