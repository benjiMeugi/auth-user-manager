<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        Gate::before(function ($user, $ability) {
            // 1. Si tu as stocké les permissions dans les abilities du token Sanctum lors du login :
            if (method_exists($user, 'tokenCan') && $user->tokenCan($ability)) {
                return true;
            }

            // 2. Sécurité supplémentaire : Vérification directe dans la base de données (au cas où)
            // Vérifie si l'utilisateur a la permission directement ou via ses rôles
            $hasPermission = $user->permissions->contains('label', $ability) ||
                $user->roles->flatMap->permissions->contains('label', $ability);

            return $hasPermission ? true : null;
        });
    }
}
