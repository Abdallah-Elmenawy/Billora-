<?php

namespace App\Providers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\View;
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
        View::composer('layouts.main-header', function ($view) {
            try {
                $view->with('recentLogs', ActivityLog::with('user')->latest()->limit(6)->get());
            } catch (\Throwable) {
                $view->with('recentLogs', collect());
            }
        });
    }
}
