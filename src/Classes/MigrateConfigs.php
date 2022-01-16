<?php

namespace HexideDigital\ModelPermissions\Classes;

use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Support\Str;

class MigrateConfigs
{
    public static function getRoles(): array
    {
        $data = [];

        $roles = config('model-permissions.roles', [
            Role::SuperAdmin,
            Role::Admin,
            Role::User,
        ]);

        foreach ($roles as $role_id) {
            $data[] = [
                'title' => Str::title(Role::Keys[$role_id]),
                'key' => Role::Keys[$role_id],
                'admin_access' => in_array(
                    $role_id,
                    config('model-permissions.roles_admin_access',
                        [Role::SuperAdmin, Role::Admin]
                    )
                ),
            ];
        }

        return $data;
    }
}
