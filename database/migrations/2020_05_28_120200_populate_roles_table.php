<?php

use HexideDigital\ModelPermissions\Classes\MigrateConfigs;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PopulateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (MigrateConfigs::getRoles() as $role){
            Role::create($role);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->delete();
    }
}
