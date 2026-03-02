<?php

namespace App\Providers;

use App\Services\DataSourceManager;
use Illuminate\Support\ServiceProvider;

class DataSourceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(DataSourceManager::class, function () {
            return new DataSourceManager();
        });
    }

    public function boot(): void
    {
        //
    }
}
