<?php

namespace HexideDigital\ModelPermissions\Classes;

use Error;
use Exception;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Support\Collection;
use Throwable;

class PermissionRelation
{
    // config properties
    private bool $withTablePrefix = true;
    private Collection $resourceKeys;

    // property to create
    private ?string $tableName = null;
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
        $this->resourceKeys = collect($resource)->combine($resource);
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
            if (!($this->creatingWithTablePrefix() || $this->creatingWithoutTablePrefix()) && $this->permissions->isEmpty()) {
                throw new Exception(sprintf(
                    '%s class: Can`t create permissions for table `%s` with %d permissions when table name is %s'
                    , self::class
                    , $this->tableName
                    , $this->permissions->count()
                    , $this->withTablePrefix ? 'required' : 'not required'
                ));
            }

            $permissions = $this->permissions
                ->merge($this->extraPermissions)
                ->map(fn($title) => $this->permission($title))
                ->when(
                    $this->needsToCreateViewAnyPermission(),
                    fn(Collection $collection) => $collection->push($this->permission(Permission::ViewAny))
                )
                ->unique()
                ->map(fn($permissionTitle) => Permission::firstOrCreate($permissionTitle)->id);

            foreach (config('model-permissions.roles_to_assign', [Role::SuperAdmin, Role::Admin]) as $role_id) {
                if ($role = Role::find($role_id)) {
                    $role->permissions()->attach($permissions);
                }
            }
        } catch (Error|Exception|Throwable $e) {
            report($e);

            echo sprintf(
                '%s in %s class when trying populate.' . PHP_EOL
                . 'Message: %s' . PHP_EOL
                , get_class($e)
                , self::class
                , $e->getMessage()
            );
        }
    }

    /**
     * @param array|string $permissions
     *
     * @return $this
     */
    private function append($permissions): self
    {
        $this->permissions = Collection::wrap($permissions)
            ->reduce(
                fn(Collection $collection, $permission) => $collection->put($permission, $permission),
                $this->permissions
            );

        return $this;
    }

    private function permission(string $title): array
    {
        return ['title' => $this->makeTitle($title),];
    }

    private function makeTitle(string $title): string
    {
        if ($this->withTablePrefix) {
            return $this->tableName . config('model-permissions.divider', '_') . $title;
        }

        return $title;
    }

    private function needsToCreateViewAnyPermission(): bool
    {
        return !$this->tableName && ($this->permissions->isNotEmpty() || $this->extraPermissions->isNotEmpty());
    }

    private function creatingWithTablePrefix(): bool
    {
        return !$this->tableName && !$this->withTablePrefix;
    }

    private function creatingWithoutTablePrefix(): bool
    {
        return $this->tableName && $this->withTablePrefix;
    }

    public function __call($name, $arguments)
    {
        return $this;
    }
}
