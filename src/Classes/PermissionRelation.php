<?php

namespace HexideDigital\ModelPermissions\Classes;

use Exception;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PermissionRelation
{
    // config properties
    private bool $withTablePrefix;
    private string $divider;
    private array $resourceKeys;

    // property to create
    private string $table = '';
    private array $permissions = [];
    private array $extra = [];

    public function __construct()
    {
        $this->loadConfigs();
    }

    /** Setup table name */
    public function touch(string $table): self
    {
        $ins = new self();
        $ins->table = $table;

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
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        $ins = new static();
        $ins->disablePrefix();
        $ins->append($permissions)->_populate();
    }

    /**
     * Append extra permissions
     *
     * @param array|string $permissions
     * @return self
     */
    public function extra($permissions): self
    {
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        return $this->append($permissions);
    }

    /** Append set of permissions from config and execute the population */
    public function addSet(string $name): self
    {
        $custom = config('model-permissions.permission_sets.' . $name, []);

        return $this->extra($custom);
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
        $this->append(Arr::only($this->resourceKeys, array_wrap($permissions)))->_populate();
    }

    /**
     * Append all permission except given and execute the population
     *
     * @param array<string>|string $permissions
     */
    public function except($permissions): void
    {
        $this->append(Arr::except($this->resourceKeys, array_wrap($permissions)))->_populate();
    }

    /** Execute the population */
    public function populate(): void
    {
        try {
            if (!((!empty($this->table) && $this->withTablePrefix) || (empty($this->table) && !$this->withTablePrefix))
                && empty($this->permissions)) {
                throw new Exception(sprintf(
                    '%s class: Can`t create permissions for table `%s` with %d permissions when table name is %s'
                    , self::class
                    , $this->table
                    , sizeof($this->permissions)
                    , $this->withTablePrefix ? 'required' : 'not required'
                ));
            }

            $data = [];

            if (!empty($this->extra)) {
                foreach ($this->extra as $title) {
                    $data[] = $this->permission($title);
                }
            }

            foreach ($this->permissions as $title) {
                $data[] = $this->permission($title);
            }

            if ((!empty($this->permissions) || !empty($this->extra)) && !empty($this->table)) {
                $permission = $this->permission(Permission::Access);
                if (!in_array($permission, $data)) {
                    $data[] = $permission;
                }
            }

            $permissions = [];

            foreach ($data as $perm) {
                $permissions[] = Permission::firstOrCreate($perm);
            }

            Role::whereIn('key', config('model-permissions.roles_to_assign'))
                ->get('id')
                ->each(function (Role $role) use ($permissions) {
                    foreach ($permissions as $permission) {
                        DB::table('permission_role')->insert(['role_id' => $role->id, 'permission_id' => $permission->id]);
                    }
                });
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

    private function loadConfigs(): void
    {
        $resource = config('model-permissions.permission_sets.resource', []);
        $this->resourceKeys = array_combine($resource, $resource);

        $this->divider = config('model-permissions.divider');
        $this->withTablePrefix = true;
    }

    private function append(array $permissions): self
    {
        $this->permissions = array_combine($permissions, $permissions);

        return $this;
    }

    private function permission(string $title): array
    {
        return array(
            'title' => $this->makeTitle($title)
        );
    }

    private function makeTitle(string $title): string
    {
        if ($this->withTablePrefix) {
            return $this->table . $this->divider . $title;
        } else {
            return $title;
        }
    }

    public function __call($name, $arguments)
    {
        return $this;
    }
}
