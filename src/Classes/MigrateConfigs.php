<?php

namespace HexideDigital\ModelPermissions\Classes;

use Illuminate\Support\Str;

class MigrateConfigs
{
    public static function getRoles(): array
    {
        $data = [];

        $roles = config('model-permissions.roles');

        foreach ($roles as $role) {
            $data[] = [
                'title' => Str::title($role),
                'key' => $role,
                'admin_access' => in_array($role, config('model-permissions.roles_admin_access')),
            ];
        }

        return $data;
    }
}
