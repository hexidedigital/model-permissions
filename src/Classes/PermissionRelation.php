<?php


namespace HexideDigital\ModelPermissions\Classes;


use Exception;
use HexideDigital\ModelPermissions\Models\Permission;
use HexideDigital\ModelPermissions\Models\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PermissionRelation
{
    // config properties
    /** @var bool */
    private $with_table_prefix;
    /** @var string */
    private $divider;
    /** @var array */
    private $all;

    // property to create
    /** @var string */
    private $table = '';
    /** @var array */
    private $permissions = [];
    /** @var array */
    private $extra = [];

    public function __construct()
    {
        $this->loadConfigs();
    }

    /**
     * Setup table name
     *
     * @param string $table
     * @return self
     */
    public function touch(string $table): self
    {
        $ins = new self();
        $ins->table = $table;
        return $ins;
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
        $ins->with_table_prefix = false;
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

    /**
     * Append custom permission from configs
     *
     * @return self
     */
    public function addCustom(): self
    {
        $custom = config('modelPermissions.custom',);

        return $this->extra($custom);
    }

    /**
     * Append all permissions and execute the population
     */
    public function all(): void
    {
        $this->append($this->all)->_populate();
    }

    /**
     * Append only given permissions and execute the population
     *
     * @param array|string $permissions
     */
    public function only($permissions): void
    {
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        $this->append(Arr::only($this->all, $permissions))->_populate();
    }

    /**
     * Append all permission except given and execute the population
     *
     * @param array|string $permissions
     * @return void
     */
    public function except($permissions): void
    {
        if (!is_array($permissions)) {
            $permissions = array($permissions);
        }

        $this->append(Arr::except($this->all, $permissions))->_populate();
    }

    /**
     * Execute the population
     */
    public function populate(): void
    {
        $this->_populate();
    }

    private function loadConfigs(): void
    {
        foreach (config('modelPermissions.all', []) as $item) {
            $this->all[$item] = $item;
        }
        $this->divider = config('modelPermissions.divider');
        $this->with_table_prefix = config('modelPermissions.with_table_prefix');
    }

    private function append(array $permissions): self
    {
        foreach ($permissions as $key) {
            if ($title = config('modelPermissions.all.' . $key)) {
                $this->permissions[$key] = $title;
            } else {
                $this->permissions[$key] = $key;
            }
        }

        return $this;
    }

    private function _populate(): void
    {
        try {
            if (((!empty($this->table) && $this->with_table_prefix)
                    || (empty($this->table) && !$this->with_table_prefix)
                ) && !empty($this->permissions)) {

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
                    $permission = $this->permission(Permission::access);
                    if (!in_array($permission, $data)) {
                        $data[] = $permission;
                    }
                }

                $permissions = [];

                foreach ($data as $perm) {
                    $permissions[] = Permission::firstOrCreate($perm);
                }

                /** @var Collection $roles */
                $roles = Role::whereIn('key', config('modelPermissions.roles_to_assign'))->get();

                if ($roles->isNotEmpty()) {
                    /** @var Role $role */
                    foreach ($roles as $role) {
                        foreach ($permissions as $permission) {
                            DB::table('permission_role')->insert(
                                array(
                                    'role_id' => $role->id,
                                    'permission_id' => $permission->id
                                )
                            );
                        }
                    }
                }
            } else {
                throw new Exception(
                    sprintf(
                        '%s class: Can`t create permissions for table `%s` with %d permissions when table name is %s'
                        , self::class
                        , $this->table
                        , sizeof($this->permissions)
                        , $this->with_table_prefix ? 'required' : 'not required'
                    )
                );
            }
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

    private function permission(string $title): array
    {
        return array(
            'title' => $this->concat($title)
        );
    }

    private function concat(string $title): string
    {
        if ($this->with_table_prefix) {
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
