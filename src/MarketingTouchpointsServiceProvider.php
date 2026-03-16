<?php

namespace MRustamzade\MarketingTouchpoints;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use MRustamzade\MarketingTouchpoints\Http\Middleware\TrackTouchpoints;

class MarketingTouchpointsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/marketing-touchpoints.php', 'marketing-touchpoints');

        $this->app->singleton(MarketingTouchpointsManager::class, function ($app): MarketingTouchpointsManager {
            return new MarketingTouchpointsManager($app['config']);
        });
    }

    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'marketing-touchpoints');

        $router->aliasMiddleware(
            config('marketing-touchpoints.middleware.alias', 'track-touchpoints'),
            TrackTouchpoints::class
        );

        if ((bool) config('marketing-touchpoints.middleware.auto_track_web', false)) {
            $router->pushMiddlewareToGroup('web', TrackTouchpoints::class);
        }

        if ((bool) config('marketing-touchpoints.route.enabled', true)) {
            Route::middleware((array) config('marketing-touchpoints.route.middleware', ['web', 'auth']))
                ->prefix((string) config('marketing-touchpoints.route.prefix', 'marketing'))
                ->as((string) config('marketing-touchpoints.route.name', 'marketing-touchpoints.'))
                ->group(__DIR__ . '/routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/marketing-touchpoints.php' => config_path('marketing-touchpoints.php'),
            ], 'marketing-touchpoints-config');

            $this->publishes([
                __DIR__ . '/database/migrations' => database_path('migrations'),
            ], 'marketing-touchpoints-migrations');

            $this->publishes([
                __DIR__ . '/resources/views' => resource_path('views/vendor/marketing-touchpoints'),
            ], 'marketing-touchpoints-views');
        }
    }
}
