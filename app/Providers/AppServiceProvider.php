<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
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
        if (app()->environment('production')) {
            $caPath = env('MYSQL_ATTR_SSL_CA');

            Log::info('MySQL SSL diagnostic', [
                'ca_path' => $caPath,
                'file_exists' => is_file($caPath ?: ''),
                'file_readable' => is_readable($caPath ?: ''),
            ]);
        }
    }
}
