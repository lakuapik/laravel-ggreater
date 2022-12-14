<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/dashboard';

    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            //
            require base_path('routes/api.php');
            require base_path('routes/web.php');
        });
    }

    protected function configureRateLimiting(): void
    {
        // not used right now
        // RateLimiter::for('api', function (Request $request) {
        //     return Limit::perMinute(60)->by(
        //         $request->user()?->id ?: $request->ip()
        //     );
        // });
    }
}
