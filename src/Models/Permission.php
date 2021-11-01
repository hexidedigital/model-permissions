<?php

namespace HexideDigital\ModelPermissions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string title
 * @mixin \Eloquent
 */
class Permission extends Model
{
    public const access = 'access';
    public const view = 'view';
    public const create = 'create';
    public const edit = 'edit';
    public const delete = 'delete';
    public const read = 'read';

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
    ];

    /**
     * @param string|null $module
     * @param string $permission
     * @return string
     */
    public static function key(?string $module, string $permission): string
    {
        return $module . config('modelPermissions.divider') . $permission;
    }
}
