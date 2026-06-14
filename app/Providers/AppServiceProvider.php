<?php

namespace App\Providers;

use App\Services\ActiveAgentService;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
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
        View::composer(['layouts.app', 'layouts.app-header'], function ($view): void {
            if (! Auth::check()) {
                return;
            }

            $service = app(ActiveAgentService::class);

            $view->with([
                'activeAgent' => $service->agent(),
                'requiresAgentSelection' => ! $service->hasAgent(),
            ]);
        });

        Event::listen(Logout::class, function (): void {
            app(ActiveAgentService::class)->clear();
        });
    }
}
