<?php

namespace HexideDigital\ModelPermissions\Commands;

use HexideDigital\ModelPermissions\Classes\MigrateConfigs;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Console\Command;
use PermissionRelation;

class InitCommand extends Command
{
    protected $name = 'model-permissions:init';
    protected $description = 'Populate main roles and permissions into database.';

    public function handle(): int
    {
        $this->info('Creating roles...');

        $this->withProgressBar(MigrateConfigs::getRoles(), fn($role) => Role::firstOrCreate($role));
        $this->newLine();

        $this->info('Creating startup permissions...');
        $this->withProgressBar(
            config('model-permissions.startup_permissions'),
            fn($table) => PermissionRelation::touch($table)->addCustomSet()->addResourceSet()
        );
        $this->newLine();

        Role::firstWhere('key', 'admin')->permissions()->sync(Permission::pluck('id'));

        $this->info('Startup roles with permissions created');

        return self::SUCCESS;
    }
}
