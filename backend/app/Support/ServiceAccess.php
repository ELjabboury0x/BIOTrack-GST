<?php

namespace App\Support;

use App\Models\Equipment;
use App\Models\Intervention;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Centralized service/unit-based data scoping.
 *
 * Role hierarchy:
 *   admin          → global access, no filter
 *   ingénieur/manager/major → filtered by allowed service IDs
 *   technicien     → filtered by service ID + unit ID
 */
class ServiceAccess
{
    /**
     * Scope an Equipment query to the user's allowed data.
     */
    public static function applyEquipmentScope($query, ?User $user)
    {
        if (!$user || $user->hasGlobalAccess()) {
            return $query;
        }

        // Technicien: only equipment in their service AND their unit's rooms
        if ($user->isUnitRestricted()) {
            return static::scopeByUnit($query, $user, 'service_id');
        }

        // Ingénieur / Manager / Major: only equipment in allowed services
        $serviceIds = $user->allowedServiceIds();

        if (empty($serviceIds)) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn('service_id', $serviceIds);
    }

    /**
     * Scope an Intervention query (has no direct service_id — joins through equipment).
     */
    public static function applyInterventionScope(Builder $query, ?User $user): Builder
    {
        if (!$user || $user->hasGlobalAccess()) {
            return $query;
        }

        // Major: OT/DM global read visibility (same scope as admin for interventions only)
        if ($user->isMajor()) {
            return $query;
        }

        if ($user->isUnitRestricted()) {
            return static::scopeInterventionByUnit($query, $user);
        }

        $serviceIds = $user->allowedServiceIds();

        if (empty($serviceIds)) {
            return $query->whereRaw('1=0');
        }

        return $query->whereHas('equipment', function (Builder $eq) use ($serviceIds) {
            $eq->whereIn('service_id', $serviceIds);
        });
    }

    /**
     * Scope a Complaint query (has direct service_id).
     */
    public static function applyComplaintScope($query, ?User $user)
    {
        if (!$user || $user->hasGlobalAccess()) {
            return $query;
        }

        if ($user->isUnitRestricted()) {
            return static::scopeByUnit($query, $user, 'service_id');
        }

        $serviceIds = $user->allowedServiceIds();

        if (empty($serviceIds)) {
            return $query->whereRaw('1=0');
        }

        return $query->whereIn('service_id', $serviceIds);
    }

    /**
     * Scope a MaintenanceReport query (has equipment_id → service).
     */
    public static function applyReportScope(Builder $query, ?User $user): Builder
    {
        if (!$user || $user->hasGlobalAccess()) {
            return $query;
        }

        if ($user->isUnitRestricted()) {
            return static::scopeReportByUnit($query, $user);
        }

        $serviceIds = $user->allowedServiceIds();

        if (empty($serviceIds)) {
            return $query->whereRaw('1=0');
        }

        return $query->whereHas('equipment', function (Builder $eq) use ($serviceIds) {
            $eq->whereIn('service_id', $serviceIds);
        });
    }

    /**
     * Scope a PreventiveMaintenance query (has equipment_id → service).
     */
    public static function applyPreventiveScope(Builder $query, ?User $user): Builder
    {
        if (!$user || $user->hasGlobalAccess()) {
            return $query;
        }

        if ($user->isUnitRestricted()) {
            return static::scopePreventiveByUnit($query, $user);
        }

        $serviceIds = $user->allowedServiceIds();

        if (empty($serviceIds)) {
            return $query->whereRaw('1=0');
        }

        return $query->whereHas('equipment', function (Builder $eq) use ($serviceIds) {
            $eq->whereIn('service_id', $serviceIds);
        });
    }

    // ------------------------------------------------------------------
    // Unit-level filtering helpers (technicien)
    // ------------------------------------------------------------------

    /**
     * Filter a model that has a direct service_id column by the user's
     * service. Technicien only sees records in their own service.
     */
    private static function scopeByUnit($query, User $user, string $serviceColumn = 'service_id')
    {
        $serviceId = $user->service_id;

        if (!$serviceId) {
            return $query->whereRaw('1=0');
        }

        return $query->where($serviceColumn, $serviceId);
    }

    /**
     * For Intervention (no direct service_id): scope through equipment.
     */
    private static function scopeInterventionByUnit(Builder $query, User $user): Builder
    {
        $serviceId = $user->service_id;

        if (!$serviceId) {
            return $query->whereRaw('1=0');
        }

        return $query->whereHas('equipment', function (Builder $eq) use ($serviceId) {
            $eq->where('service_id', $serviceId);
        });
    }

    /**
     * For MaintenanceReport: scope through equipment.
     */
    private static function scopeReportByUnit(Builder $query, User $user): Builder
    {
        $serviceId = $user->service_id;

        if (!$serviceId) {
            return $query->whereRaw('1=0');
        }

        return $query->whereHas('equipment', function (Builder $eq) use ($serviceId) {
            $eq->where('service_id', $serviceId);
        });
    }

    /**
     * For PreventiveMaintenance: scope through equipment.
     */
    private static function scopePreventiveByUnit(Builder $query, User $user): Builder
    {
        $serviceId = $user->service_id;

        if (!$serviceId) {
            return $query->whereRaw('1=0');
        }

        return $query->whereHas('equipment', function (Builder $eq) use ($serviceId) {
            $eq->where('service_id', $serviceId);
        });
    }
}
