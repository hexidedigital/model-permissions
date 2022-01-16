<?php

namespace HexideDigital\ModelPermissions\Classes;

use Exception;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Support\Collection;

class PermissionRelation
{
    // config properties
    private bool $withTablePrefix;
    private string $divider;
    private Collection $resourceKeys;

    // property to create
    private string $tableName = '';
    private Collection $permissions;
    private Collection $extraPermissions;

    public function __construct()
    {
        $this->loadConfigs();
        $this->permissions = collect();
        $this->extraPermissions = collect();
    }

    public function loadConfigs(): void
    {
        $resource = config('model-permissions.permission_sets.resource', []);
        $this->resourceKeys = collect(array_combine($resource, $resource));

        $this->divider = config('model-permissions.divider', '_');
        $this->withTablePrefix = true;
    }

    /** Setup table name */
    public function touch(string $table): self
    {
        $ins = new self();
        $ins->tableName = $table;

        return $ins;
    }

    public function disablePrefix(): self
    {
        $this->withTablePrefix = false;

        return $this;
    }

    public function enablePrefix(): self
    {
        $this->withTablePrefix = true;

        return $this;
    }

    /**
     * For creating only extra without table prefix
     *
     * @param array|string $permissions
     */
    public function createOnly($permissions): void
    {
        $ins = new static();
        $ins->disablePrefix();
        $ins->append($permissions)->populate();
    }

    /**
     * Append extra permissions
     *
     * @param array|string $permissions
     *
     * @return self
     */
    public function extra($permissions): self
    {
        return $this->append($permissions);
    }

    /** Append set of permissions from config and execute the population */
    public function addSet(string $name): self
    {
        return $this->extra(config('model-permissions.permission_sets.' . $name, []));
    }

    /** Append custom permission from configs */
    public function addCustomSet(): self
    {
        return $this->addSet('custom');
    }

    /** Append all permissions and execute the population */
    public function addResourceSet(): void
    {
        $this->addSet('resource')->populate();
    }

    /**
     * Append only given permissions and execute the population
     *
     * @param array<string>|string $permissions
     */
    public function only($permissions): void
    {
        $this->append($this->resourceKeys->only($permissions))->populate();
    }

    /**
     * Append all permission except given and execute the population
     *
     * @param array<string>|string $permissions
     */
    public function except($permissions): void
    {
        $this->append($this->resourceKeys->except($permissions))->populate();
    }

    /** Execute the population */
    public function populate(): void
    {
        try {
            if (
                !(
                    (!empty($this->tableName) && $this->withTablePrefix) || (empty($this->tableName) && !$this->withTablePrefix)
                )
                && $this->permissions->isEmpty()
            ) {
                throw new Exception(sprintf(
                    '%s class: Can`t create permissions for table `%s` with %d permissions when table name is %s'
                    , self::class
                    , $this->tableName
                    , $this->permissions->count()
                    , $this->withTablePrefix ? 'required' : 'not required'
                ));
            }

            $permissionsToCreate = collect();
            $titles = $this->permissions->merge($this->extraPermissions);
            foreach ($titles as $title) {
                $permissionsToCreate->push($this->permission($title));
            }

            if (($this->permissions->isNotEmpty() || $this->extraPermissions->isNotEmpty()) && !empty($this->tableName)) {
                $permissionsToCreate->push($this->permission(Permission::ViewAny));
            }

            $permissions = collect();
            foreach ($permissionsToCreate->unique() as $permission) {
                $permissions->push(Permission::firstOrCreate($permission)->id);
            }

            foreach (config('model-permissions.roles_to_assign', [Role::SuperAdmin, Role::Admin]) as $role_id) {
                if ($role = Role::find($role_id)) {
                    $role->permissions()->attach($permissions);
                }
            }
        } catch (\Error $e) {
            echo sprintf(
                'Error in %s class when trying populate. Err code: %s' . PHP_EOL
                . 'Message: %s' . PHP_EOL
                , self::class
                , $e->getCode()
                , $e->getMessage()
            );
        } catch (Exception $e) {
            echo sprintf(
                'Exception in %s class when trying populate. Err code: %s' . PHP_EOL
                . 'Message: %s' . PHP_EOL
                , self::class
                , $e->getCode()
                , $e->getMessage()
            );
            //throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param array|string $permissions
     *
     * @return $this
     */
    private function append($permissions): self
    {
        $permissions = Collection::wrap($permissions);

        foreach ($permissions as $permission) {
            $this->permissions->put($permission, $permission);
        }

        return $this;
    }

    private function permission(string $title): array
    {
        return ['title' => $this->makeTitle($title),];
    }

    private function makeTitle(string $title): string
    {
        if ($this->withTablePrefix) {
            return $this->tableName . $this->divider . $title;
        } else {
            return $title;
        }
    }

    public function __call($name, $arguments)
    {
        return $this;
    }
}
