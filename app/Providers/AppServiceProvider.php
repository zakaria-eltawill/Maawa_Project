<?php

namespace App\Providers;

use App\Listeners\LogEmailSent;
use App\Models\AdminNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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
        // Configure rate limiters
        RateLimiter::for('login', function (Request $request) {
            if (app()->environment('local')) {
                return Limit::perMinute(100)->by($request->ip());
            }

            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer('layouts.admin', function ($view) {
            $count = 0;

            if (auth()->check() && auth()->user()->role === 'admin') {
                $count = AdminNotification::unread()->count();
            }

            $view->with('adminUnreadNotificationsCount', $count);
        });

        // Register mail event listener for logging
        Event::listen(MessageSent::class, LogEmailSent::class);
    }
}
