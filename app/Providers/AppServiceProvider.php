<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Admin bypasses every gate check automatically
        Gate::before(fn ($user) => $user->isAdmin() ? true : null);

        // Define all permission gates
        $permissions = [
            'process_sale',
            'cancel_sale',
            'apply_discount',
            'manage_products',
            'manage_categories',
            'manage_units',
            'manage_discounts',
            'manage_suppliers',
            'manage_purchases',
            'manage_customers',
            'adjust_stock',
            'view_reports',
            'view_profit_report',
            'manage_users',       // admin-only (bypassed via Gate::before)
        ];

        foreach ($permissions as $permission) {
            Gate::define($permission, fn ($user) => $user->hasPermission($permission));
        }
    }
}
