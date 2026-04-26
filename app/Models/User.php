<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $connection = 'mysql'; // always master DB — used for auth

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone',
        'is_active', 'tenant_id', 'default_module', 'department',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Departments list for UI
    public const DEPARTMENTS = [
        'production'   => 'Production',
        'maintenance'  => 'Maintenance',
        'logistique'   => 'Logistique',
        'commercial'   => 'Commercial',
        'comptabilite' => 'Comptabilité',
        'rh'           => 'Ressources Humaines',
        'qualite'      => 'Qualité',
        'direction'    => 'Direction',
    ];

    // Modules accessible per department (for operators)
    public const DEPT_MODULES = [
        'production'   => ['stock', 'production', 'mrp', 'quality'],
        'maintenance'  => ['maintenance', 'energy'],
        'logistique'   => ['logistics', 'purchases', 'stock'],
        'commercial'   => ['sales', 'crm'],
        'comptabilite' => ['accounting'],
        'rh'           => ['hr'],
        'qualite'      => ['quality'],
        'direction'    => [], // empty = all modules
    ];

    // First route to land on after login, per department
    public const DEPT_HOME = [
        'production'   => 'production.index',
        'maintenance'  => 'maintenance.index',
        'logistique'   => 'logistics.index',
        'commercial'   => 'clients.index',
        'comptabilite' => 'accounting.index',
        'rh'           => 'hr.index',
        'qualite'      => 'quality.index',
        'direction'    => 'dashboard',
    ];

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['admin', 'manager', 'super_admin']);
    }

    // True if this user (based on role + department) may access the given module key
    public function canAccessModule(string $module): bool
    {
        // Super admin and admins and managers see everything
        if ($this->role !== 'operateur') return true;

        // Operator with no department → falls back to default_module behaviour, no extra restriction
        if (!$this->department) return true;

        $allowed = self::DEPT_MODULES[$this->department] ?? [];
        return empty($allowed) || in_array($module, $allowed);
    }

    // Home route for this user's department (used after login)
    public function deptHomeRoute(): string
    {
        if ($this->isSuperAdmin()) return route('superadmin.index');

        if ($this->default_module) {
            $routes = [
                'stock' => 'products.index', 'production' => 'production.index',
                'mrp' => 'mrp.index', 'quality' => 'quality.index',
                'maintenance' => 'machines.index', 'logistics' => 'logistics.index',
                'purchases' => 'purchases.index', 'sales' => 'clients.index',
                'crm' => 'crm.index', 'accounting' => 'accounting.index',
                'hr' => 'hr.index', 'energy' => 'energy.index', 'users' => 'users.index',
            ];
            if (isset($routes[$this->default_module])) {
                return route($routes[$this->default_module]);
            }
        }
        if ($this->role === 'operateur' && $this->department && isset(self::DEPT_HOME[$this->department])) {
            return route(self::DEPT_HOME[$this->department]);
        }
        return route('dashboard');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function maintenancesAssigned(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'technician_id');
    }
}
