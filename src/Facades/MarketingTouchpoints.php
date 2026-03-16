<?php

namespace MRustamzade\MarketingTouchpoints\Facades;

use Illuminate\Support\Facades\Facade;
use MRustamzade\MarketingTouchpoints\MarketingTouchpointsManager;

class MarketingTouchpoints extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MarketingTouchpointsManager::class;
    }
}
