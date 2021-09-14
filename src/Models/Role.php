<?php

namespace HexideDigital\ModelPermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string title
 * @property string|null key
 * @property bool admin_access
 */
class Role extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'key',
        'admin_access',
    ];

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
