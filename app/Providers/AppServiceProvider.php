<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $origin = rtrim((string) config('app.url'), '/');

        // TLS may terminate before the request reaches the container.
        // The configured production origin is authoritative for generated URLs.
        if ($this->app->isProduction()
            && filter_var($origin, FILTER_VALIDATE_URL)
            && parse_url($origin, PHP_URL_SCHEME) === 'https') {
            URL::forceRootUrl($origin);
            URL::forceScheme('https');
            URL::useAssetOrigin(config('app.asset_url') ?: $origin);
        }
    }
}