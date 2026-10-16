<?php

namespace App\Providers;

use App\Models\SystemInfo;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        config(['settings' => SystemInfo::values()]);
        Schema::defaultStringLength(191);

    }
}
