<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('login', function (Request $request) {
            $username = (string) $request->input('username');
            return Limit::perMinute(5)->by($username . '|' . $request->ip());
        });

        Gate::before(function ($user, $ability) {
            // Vérification en base de données (avec chargement groupé anti-N+1)
            // pour garantir que tout ajout ou retrait de rôle/permission est effectif immédiatement
            if (method_exists($user, 'hasPermission') && $user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }
}
