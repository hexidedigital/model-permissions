<?php

namespace HexideDigital\ModelPermissions\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Permission
 *
 * @mixin Eloquent
 * @property int $id
 * @property string|null $title
 * @property string $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Permission newModelQuery()
 * @method static Builder|Permission newQuery()
 * @method static Builder|Permission query()
 * @method static Builder|Permission whereCreatedAt($value)
 * @method static Builder|Permission whereId($value)
 * @method static Builder|Permission whereTitle($value)
 * @method static Builder|Permission whereUpdatedAt($value)
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

    public function getModuleAttribute(): string
    {
        $array = explode(config('modelPermissions.divider'), $this->title);
        array_pop($array);

        return implode(config('modelPermissions.divider'), $array);
    }
}
