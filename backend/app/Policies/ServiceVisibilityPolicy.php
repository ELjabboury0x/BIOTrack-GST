<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\Equipment;
use App\Models\Intervention;
use App\Models\User;

/**
 * Gate-based authorisation for individual record access.
 *
 * Role hierarchy:
 *   admin          → always true
 *   ingénieur/manager/major → record must belong to an allowed service
 *   technicien     → record must belong to the user's own service
 */
class ServiceVisibilityPolicy
{
    public function accessService(User $user, int $serviceId): bool
    {
        if ($user->hasGlobalAccess()) {
            return true;
        }

        if ($user->isUnitRestricted()) {
            return (int) $user->service_id === $serviceId;
        }

        return in_array($serviceId, $user->allowedServiceIds(), true);
    }

    public function viewEquipment(User $user, Equipment $equipment): bool
    {
        if ($user->hasGlobalAccess()) {
            return true;
        }

        $sid = (int) $equipment->service_id;

        if ($user->isUnitRestricted()) {
            return (int) $user->service_id === $sid;
        }

        return in_array($sid, $user->allowedServiceIds(), true);
    }

    public function viewIntervention(User $user, Intervention $intervention): bool
    {
        if ($user->hasGlobalAccess()) {
            return true;
        }

        if ($user->isMajor()) {
            return true;
        }

        $sid = (int) ($intervention->equipment?->service_id ?? 0);

        if ($sid === 0) {
            return false;
        }

        if ($user->isUnitRestricted()) {
            return (int) $user->service_id === $sid;
        }

        return in_array($sid, $user->allowedServiceIds(), true);
    }

    public function viewComplaint(User $user, Complaint $complaint): bool
    {
        if ($user->hasGlobalAccess()) {
            return true;
        }

        $sid = (int) $complaint->service_id;

        if ($user->isUnitRestricted()) {
            return (int) $user->service_id === $sid;
        }

        return in_array($sid, $user->allowedServiceIds(), true);
    }
}
