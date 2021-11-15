<?php

namespace HexideDigital\ModelPermissions\Seeders;

use App\Models\Role;
use App\Models\User;
use Artisan;
use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Artisan::call('model-permissions:init');
    }
}
