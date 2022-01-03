<?php

namespace HexideDigital\ModelPermissions\Seeders;

use Artisan;
use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    public function run()
    {
        Artisan::call('model-permissions:init');
    }
}
