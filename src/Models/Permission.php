<?php

namespace HexideDigital\ModelPermissions\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @mixin Eloquent
 *
 * @property int $id
 * @property string|null $title
 * @property string $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
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
    public const ViewAny = 'viewAny';
    public const View = 'view';
    public const Create = 'create';
    public const Update = 'update';
    public const Delete = 'delete';

    public const ViewDeleted = 'viewDeleted';
    public const ForceDelete = 'forceDelete';
    public const Restore = 'restore';

    protected $fillable = [
        'title',
    ];

    public static function key(?string $module, string $permission): string
    {
        return $module . config('model-permissions.divider', '_') . $permission;
    }

    public function getModuleAttribute(): string
    {
        $array = explode(config('model-permissions.divider', '_'), $this->title);
        array_pop($array);

        return implode(config('model-permissions.divider', '_'), $array);
    }
}
