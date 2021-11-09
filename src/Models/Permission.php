<?php

namespace HexideDigital\ModelPermissions\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Permission
 *
 * @mixin \Eloquent
 * @property int $id
 * @property string|null $title
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Permission whereUpdatedAt($value)
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
