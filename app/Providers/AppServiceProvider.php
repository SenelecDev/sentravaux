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

        // Utiliser l'URL réelle de la requête (IP, port, proxy Apache)
        // Évite ERR_NAME_NOT_RESOLVED si APP_URL=http://sentravaux ou placeholder .env
        if (!$this->app->runningInConsole()) {
            $request = $this->app->make('request');
            if ($request && $request->getHttpHost()) {
                URL::forceRootUrl($request->getSchemeAndHttpHost());
                return;
            }
        }

        $root = config('app.url');
        if (is_string($root) && $root !== '' && !str_contains($root, 'VOTRE_SERVEUR')) {
            URL::forceRootUrl(rtrim($root, '/'));
        }
    }
}
