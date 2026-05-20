<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useTailwind();

        View::composer('layouts.partials.header', function ($view) {
            if (auth()->check()) {
                $view->with('unreadNotificationsCount', NotificationService::getUnreadCount(auth()->id()));
            }
        });

        $root = config('app.url');
        if (is_string($root) && $root !== '') {
            URL::forceRootUrl(rtrim($root, '/'));
        }
    }
}
