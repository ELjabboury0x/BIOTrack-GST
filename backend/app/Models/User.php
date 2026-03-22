<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PushSubscription;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_OPERATOR = 'operator';
    public const ROLE_TECHNICIAN = 'technician';
    public const ROLE_MAJOR = 'major';
    public const ROLE_TECHNICIEN = 'technicien';
    public const ROLE_INGENIEUR = 'ingenieur';

    protected $fillable = [
        'name',
        'login',
        'email',
        'password',
        'role',
        'is_active',
        'service_id',
        'unit_id',
        'must_change_password',
        'password_changed_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
        'password_changed_at' => 'datetime',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    public function primaryService(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_user')->withTimestamps();
    }

    public function isMajor(): bool
    {
        return $this->role === self::ROLE_MAJOR;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isOperator(): bool
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    public function isTechnician(): bool
    {
        return in_array($this->role, [self::ROLE_TECHNICIAN, self::ROLE_TECHNICIEN], true);
    }

    public function allowedServiceIds(): array
    {
        $serviceIds = [];

        if ($this->service_id) {
            $serviceIds[] = (int) $this->service_id;
        }

        $pivotServiceIds = $this->services()->pluck('services.id')->map(fn ($id) => (int) $id)->all();

        return array_values(array_unique(array_merge($serviceIds, $pivotServiceIds)));
    }

    /**
     * Whether this user has unrestricted global data access.
     */
    public function hasGlobalAccess(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Whether this user's access should be restricted to their unit only (technicien).
     */
    public function isUnitRestricted(): bool
    {
        return in_array($this->role, [self::ROLE_TECHNICIAN, self::ROLE_TECHNICIEN], true);
    }

    /**
     * Whether this user's access is scoped to their service (ingénieur, manager, major).
     */
    public function isServiceRestricted(): bool
    {
        return in_array($this->role, [self::ROLE_INGENIEUR, self::ROLE_MANAGER, self::ROLE_MAJOR], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isIngenieur(): bool
    {
        return $this->role === self::ROLE_INGENIEUR;
    }

    public function maintenanceReports(): HasMany
    {
        return $this->hasMany(MaintenanceReport::class, 'user_id');
    }

    public function validatedMaintenanceReports(): HasMany
    {
        return $this->hasMany(MaintenanceReport::class, 'engineer_user_id');
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }
}
