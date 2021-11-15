<?php

namespace HexideDigital\ModelPermissions\Traits;

use Eloquent;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;


/**
 * For User model
 *
 * @package HexideDigital\ModelPermissions\Traits
 * @mixin Model
 * @mixin Eloquent
 */
trait WithRoles
{
    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * @return HasManyThrough
     */
    public function permissions(): HasManyThrough
    {
        return $this->hasManyThrough(Permission::class, Role::class);
    }

    /**
     * @return bool
     */
    public function hasAdminAccess(): bool
    {
        return $this->roles()->where('admin_access', TRUE)->count() > 0;
    }

    /**
     * @param string|null $permission_key
     * @return bool
     */
    public function hasPermission(?string $permission_key): bool
    {
        return $this->permissions()->where('title', '=', $permission_key)->get()->isNotEmpty();
    }

    public function scopeAdmins(Builder $builder): Builder
    {
        return $builder->whereHas('roles', fn(Builder $builder) => $builder
            ->where('key', 'admin'));
    }

    public function isAdmin(): BelongsToMany
    {
        return $this->roles()->where('key', 'admin');
    }

    public function scopeUsers(Builder $builder): Builder
    {
        return $builder->whereHas('roles', fn(Builder $builder) => $builder
            ->where('key', 'user'));
    }

    public function isUser()
    {
        return $this->roles()->where('key', 'user');
    }

}
