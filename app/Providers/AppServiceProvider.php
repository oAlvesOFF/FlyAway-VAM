<?php

namespace App\Providers;

use App\Models\ActiveFlight;
use App\Models\News;
use App\Models\Pirep;
use App\Models\User;
use App\Observers\ActiveFlightObserver;
use App\Observers\NewsObserver;
use App\Observers\PirepObserver;
use App\Observers\UserObserver;
use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class, function () {
            return new SettingsService();
        });
    }

    public function boot(): void
    {
        ActiveFlight::observe(ActiveFlightObserver::class);
        Pirep::observe(PirepObserver::class);
        User::observe(UserObserver::class);
        News::observe(NewsObserver::class);
    }
}
