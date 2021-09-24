<?php

use HexideDigital\ModelPermissions\Facades\PermissionRelation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PopulatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * @throws \Exception
     */
    public function up()
    {
        $tables = config('modelPermissions.startup_permissions');

        foreach ($tables as $table){
            PermissionRelation::touch($table)->all();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('permissions')->delete();
    }
}
