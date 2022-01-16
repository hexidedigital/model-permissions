<?php

use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;

return [

    'divider'           => '_',

    // specify model/table names which need to create
    // on first migration and been secured for edit and delete
    'startup_permissions' => [
        'roles', 'permissions', 'users',
        // ...
    ],

    // keys
    'roles' => [
        Role::SuperAdmin,
        Role::Admin,
        Role::User,
    ],

    'roles_to_assign' => [
        Role::SuperAdmin,
        Role::Admin,
    ],

    'roles_admin_access' => [
        Role::SuperAdmin,
        Role::Admin,
    ],


    'permission_sets' => [

        // on default create
        'resource'  => [
            Permission::ViewAny,
            Permission::View,
            Permission::Update,
            Permission::Create,
            Permission::Delete,
        ],

        'custom' => [
            'ajax'
            // 'can_export', 'other'
        ],

        // custom sets
        'soft_delete' => [
            Permission::ViewDeleted,
            Permission::Restore,
            Permission::ForceDelete,
        ],
    ],
];
