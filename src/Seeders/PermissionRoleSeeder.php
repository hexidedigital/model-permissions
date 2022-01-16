<?php

namespace HexideDigital\ModelPermissions\Seeders;

use Artisan;
use HexideDigital\ModelPermissions\Facades\PermissionRelation;
use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        Artisan::call('model-permissions:init');

        PermissionRelation::touch('users')->addSet('soft_delete')->populate();
    }
}
