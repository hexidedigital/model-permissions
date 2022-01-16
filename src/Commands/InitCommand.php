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

        $this->withProgressBar(MigrateConfigs::getRoles(), fn(array $role) => Role::firstOrCreate($role));
        $this->newLine();

        $this->info('Creating startup permissions...');
        $this->withProgressBar(
            config('model-permissions.startup_permissions', ['roles', 'permissions', 'users']),
            fn($table) => PermissionRelation::touch($table)->addCustomSet()->addResourceSet()
        );
        $this->newLine();

        $permissions = Permission::pluck('id');
        foreach (config('model-permissions.roles_to_assign', [Role::SuperAdmin, Role::Admin]) as $role_id) {
            optional(Role::find($role_id))->permissions()->sync($permissions);
        }

        $this->info('Startup roles with permissions created');

        return self::SUCCESS;
    }
}
