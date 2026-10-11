<?php

namespace App\Providers;

use App\Support\CurrencyManager;
use App\Support\MailSettings;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CurrencyManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        MailSettings::register();

        // Email settings live in the database, which may not exist yet (fresh install, build step).
        try {
            MailSettings::apply();
        } catch (Throwable) {
            // Fall back to the MAIL_* values from .env.
        }
    }
}
