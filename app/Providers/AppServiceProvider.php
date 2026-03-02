<?php

namespace App\Providers;

use App\Models\Directorate;
use App\Services\AI\AiProviderManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use SocialiteProviders\Azure\AzureExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiProviderManager::class, function () {
            return new AiProviderManager();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Register Azure AD Socialite driver
        Event::listen(SocialiteWasCalled::class, AzureExtendSocialite::class . '@handle');
        
        // Share directorates with all Inertia views
        Inertia::share([
            'directorates' => fn () => Directorate::active()->ordered()->get(),
        ]);
    }
}
