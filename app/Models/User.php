<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ── Permission map ────────────────────────────────────────────
    protected static array $rolePermissions = [
        'manager' => [
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
        ],
        'cashier' => [
            'process_sale',
            'apply_discount',
            'manage_customers',
            'view_reports',
        ],
    ];

    // ── Role helpers ─────────────────────────────────────────────
    public function isAdmin(): bool   { return $this->role === 'admin'; }
    public function isManager(): bool { return $this->role === 'manager'; }
    public function isCashier(): bool { return $this->role === 'cashier'; }

    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'admin') return true;
        return \in_array($permission, static::$rolePermissions[$this->role] ?? []);
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'admin'   => 'Administrator',
            'manager' => 'Manager',
            'cashier' => 'Cashier',
            default   => ucfirst($this->role),
        };
    }
}
