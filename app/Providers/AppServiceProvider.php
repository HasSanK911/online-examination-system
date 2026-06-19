<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services
        $this->app->singleton(\App\Services\GradingService::class);
        $this->app->singleton(\App\Services\RankingService::class);
        $this->app->singleton(\App\Services\OlapService::class);
        $this->app->singleton(\App\Services\AuditService::class);
        $this->app->singleton(\App\Services\ReportService::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Super admin bypasses all policies
        Gate::before(function ($user) {
            if ($user->hasRole('super_admin')) return true;
        });
    }
}
