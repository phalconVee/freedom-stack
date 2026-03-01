<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ensure asset(), url(), and Vite manifest URLs use HTTPS when APP_URL is HTTPS
        // (e.g. behind Railway or any reverse proxy that terminates TLS).
        $appUrl = config('app.url');
        if ($appUrl && str_starts_with($appUrl, 'https://')) {
            URL::forceScheme('https');
            URL::forceRootUrl($appUrl);
        }
    }
}
