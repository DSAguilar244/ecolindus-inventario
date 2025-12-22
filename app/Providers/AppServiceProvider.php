<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use App\Models\User;

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
        /**
         * -------------------------------------------------------------
         * 🔒 FORZAR HTTPS EN PRODUCCIÓN (Koyeb usa proxy HTTPS)
         * -------------------------------------------------------------
         */
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        /**
         * -------------------------------------------------------------
         * 🔐 AUTHORIZATION GATES (NO TOCAR)
         * -------------------------------------------------------------
         */

        // Permite eliminar facturas solo a administradores
        Gate::define('force-delete-invoice', function (User $user) {
            return (bool) ($user->is_admin ?? false) || ($user->role === 'admin');
        });

        // Permite editar facturas emitidas a admin y editores
        Gate::define('edit-emitted-invoice', function (User $user) {
            return (bool) ($user->is_admin ?? false) || in_array($user->role, ['admin', 'editor']);
        });

        // Permite administrar usuarios solo a admin
        Gate::define('manage-users', function (User $user) {
            return (bool) ($user->is_admin ?? false) || $user->role === 'admin';
        });
    }
}