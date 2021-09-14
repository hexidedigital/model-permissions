<?php

namespace HexideDigital\ModelPermissions\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;


/**
 * For User model
 *
 * @package HexideDigital\ModelPermissions\Traits
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
     * @return mixed
     */
    public function permissions(){
        return $this->hasManyThrough(Permission::class, Role::class);
    }

    /**
     * @return bool
     */
    public function hasAdminAccess(): bool
    {
        foreach ($this->roles as $role) {
            if($role->admin_access){
                return true;
            }
        }
        return false;
    }

    /**
     * @param string|null $permission_key
     * @return bool
     */
    public function hasPermission(?string $permission_key): bool
    {
        return $this->permissions()->where('title','=', $permission_key)->get()->isNotEmpty();
    }

}
