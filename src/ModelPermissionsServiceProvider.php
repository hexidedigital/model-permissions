<?php

namespace HexideDigital\ModelPermissions;

use HexideDigital\ModelPermissions\Classes\PermissionRelation;
use HexideDigital\ModelPermissions\Commands\CreatePermissionsCommand;
use HexideDigital\ModelPermissions\Commands\InitCommand;
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

        if(config('modelPermissions.vendor_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->commands([
            InitCommand::class,
            CreatePermissionsCommand::class,
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('permission_relation', PermissionRelation::class);
    }

}
