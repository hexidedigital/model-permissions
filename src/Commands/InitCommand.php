<?php

namespace HexideDigital\ModelPermissions\Commands;

use HexideDigital\ModelPermissions\Classes\MigrateConfigs;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Console\Command;
use PermissionRelation;

class InitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'model-permissions:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate main roles and permissions into database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating roles...');
        $this->withProgressBar(MigrateConfigs::getRoles(), fn($role) => Role::firstOrCreate($role));
        $this->newLine();

        $this->info('Creating startup permissions...');
        $this->withProgressBar(config('modelPermissions.startup_permissions'), fn($table) => PermissionRelation::touch($table)->addCustom()->all());
        $this->newLine();

        Role::firstWhere('key', 'admin')->permissions()->sync(Permission::pluck('id'));

        return self::SUCCESS;
    }
}
