<?php

namespace HexideDigital\ModelPermissions\Facades;

use Illuminate\Support\Facades\Facade;

class PermissionRelation extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'permission_relation';
    }
}
