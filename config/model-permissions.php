<?php

return [

    'vendor_migrations' => true,
    'with_table_prefix' => true,

    'divider'           => '_',

    // specify model/table names which need to create
    // on first migration
    'startup_permissions' => [
        'roles', 'permissions', 'users',
        // ...
    ],

    // keys
    'roles' => [
        'admin', 'user',
    ],

    'roles_to_assign' => [
        'admin',
    ],

    'roles_admin_access' => [
        'admin',
    ],


    'permission_sets' => [

        // on default create
        'resource'  => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],

        'custom' => [
            'ajax'
            // 'can_export', 'other'
        ],

        // custom sets
        'soft_delete' => [
            'view_deleted',
            'restore',
            'force_delete',
        ],
    ],
];
