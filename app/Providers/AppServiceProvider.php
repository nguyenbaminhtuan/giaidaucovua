<?php

namespace App\Providers;

use App\Events\MatchResultConfirmed;
use App\Listeners\UpdateStandingsListener;
use Illuminate\Support\Facades\Event;
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
        if (app()->environment('production') || request()->header('X-Forwarded-Proto') === 'https') {
            URL::forceScheme('https');
        }

        Event::listen(
            MatchResultConfirmed::class,
            UpdateStandingsListener::class
        );
    }
}
