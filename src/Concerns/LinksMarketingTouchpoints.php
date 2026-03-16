<?php

namespace MRustamzade\MarketingTouchpoints\Concerns;

use Illuminate\Database\Eloquent\Model;
use MRustamzade\MarketingTouchpoints\MarketingTouchpointsManager;

trait LinksMarketingTouchpoints
{
    public static function bootLinksMarketingTouchpoints(): void
    {
        static::created(function (Model $model): void {
            if (!app()->bound(MarketingTouchpointsManager::class)) {
                return;
            }

            app(MarketingTouchpointsManager::class)->linkOrder($model);
        });
    }
}
