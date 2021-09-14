<?php

namespace HexideDigital\ModelPermissions;

use Illuminate\Support\ServiceProvider;

class ModelPermissionsServiceProvider extends ServiceProvider
{
    /**
     * Boot the instance.
     *
     * @return void
     */
    public function boot(){

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'model-permissions-migrations');

        $this->publishes([
            __DIR__.'/../config/modelPermissions.php' => config_path('modelPermissions.php'),
        ], 'model-permissions-configs');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

}
