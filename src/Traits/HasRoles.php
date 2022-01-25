<?php

namespace HexideDigital\ModelPermissions\Traits;

use Eloquent;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

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
            ->with('permissions', function (BelongsToMany $builder) {
                return $builder->select('title');
            });
    }

    public function getPermissionsAttribute(): Collection
    {
        return $this->roles
            ->reduce(
                fn(Collection $carry, Role $role) => $carry->merge($role->permissions->pluck('title')),
                collect()
            )
            ->unique();
    }

    public function hasPermissionKey(string $permission, ?string $module = null): bool
    {
        if (isset($module)) {
            $permission = Permission::key($module, $permission);
        }

        return $this->permissions->contains($permission);
    }

    public function hasAdminAccess(): bool
    {
        return $this->roles
                ->filter(fn (Role $role) => in_array($role->id, [Role::Admin, Role::SuperAdmin]) || $role->admin_access)
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
