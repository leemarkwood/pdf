<?php

namespace Leemarkwood\PDF\Facades;

use Illuminate\Support\Facades\Facade;

class PDF extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pdf';
    }
}
