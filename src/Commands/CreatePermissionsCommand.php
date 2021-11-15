<?php

namespace HexideDigital\ModelPermissions\Commands;

use Illuminate\Console\Command;
use PermissionRelation;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreatePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'model-permissions:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all necessary permission for module.';

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
        $this->withProgressBar($this->argument('module'), function ($module) {
            $this->newLine();
            $this->info('Module: '. $module);

            $pm_builder = PermissionRelation::touch($module);

            if ($this->option('no-prefix')) {
                $pm_builder->disablePrefix();
            }

            if ($custom = $this->option('custom')) {
                $custom = array_filter($custom);
                if (empty($custom)) {
                    $pm_builder->addCustom();
                } else {
                    $pm_builder->extra($custom);
                }
            }

            if ($except = $this->option('except')) {
                $pm_builder->except($except);
            } elseif ($only = $this->option('only')) {
                $pm_builder->only($only);
            } else {
                $pm_builder->all();
            }
        });

        $this->newLine();

        return self::SUCCESS;
    }

    protected function getArguments(): array
    {
        return [
            new InputArgument('module', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Module name, also can be as table name.'),
        ];
    }

    protected function getOptions(): array
    {
        return [
            new InputOption('no-prefix', null, InputOption::VALUE_NONE, 'Create permissions without touching module name.'),
            new InputOption('custom', 'c', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'List of custom attributes.'),
            new InputOption('except', 'e', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Skip this permissions.'),
            new InputOption('only', 'o', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Create only this.'),
        ];
    }
}
