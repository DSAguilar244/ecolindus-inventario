<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        // Define simple gates based on the is_admin flag for invoice management
        Gate::define('force-delete-invoice', function (User $user) {
            return (bool) ($user->is_admin ?? false) || ($user->role === 'admin');
        });
        Gate::define('edit-emitted-invoice', function (User $user) {
            return (bool) ($user->is_admin ?? false) || in_array($user->role, ['admin', 'editor']);
        });
        Gate::define('manage-users', function (User $user) {
            return (bool) ($user->is_admin ?? false) || $user->role === 'admin';
        });
        //
    }
}
