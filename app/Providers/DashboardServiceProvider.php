<?php

namespace App\Providers;

use App\Services\PriorityEngine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(PriorityEngine::class, function ($app) {
            return new PriorityEngine($app['auth']->user());
        });
    }

    public function boot()
    {

        view()->composer('dashboard.*', function ($view) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            if ($user) {
                $view->with('timeOfDay', $this->getTimeOfDay());
            }
        });
    }

    private function getTimeOfDay()
    {
        $hour = date('H');

        if ($hour < 10) return 'Pagi';
        if ($hour < 15) return 'Siang';
        if ($hour < 19) return 'Sore';
        return 'Malam';
    }
}
