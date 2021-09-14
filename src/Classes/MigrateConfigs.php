<?php

namespace HexideDigital\ModelPermissions\Classes;

use Illuminate\Support\Str;

/**
 * Class MigrateConfigs
 * @package HexideDigital\ModelPermissions\Classes
 */
class MigrateConfigs
{

    /**
     * @return array
     */
    public static function getRoles(): array
    {
        $data = [];

        $roles = config('modelPermissions.roles');

        foreach ($roles as $role) {
            $data[] = [
                'title' => Str::title($role),
                'key' => $role,
                'admin_access' => in_array($role, config('modelPermissions.roles_admin_access')),
            ];
        }

        return $data;
    }

}
