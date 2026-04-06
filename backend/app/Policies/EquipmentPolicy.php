<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->role === 'admin') {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        // Engineers see all; technicians and majors see only their service (handled in model scope)
        return in_array($user->role, ['ingenieur', 'technicien', 'technician', 'major', 'admin']);
    }

    public function view(User $user, Equipment $equipment)
    {
        if (in_array($user->role, ['ingenieur', 'technicien', 'technician'])) {
            return true;
        }

        if (in_array($user->role, ['major'])) {
            return in_array((int) $equipment->service_id, $user->unitScopedServiceIds(), true);
        }

        return false;
    }

    public function update(User $user, Equipment $equipment)
    {
        // Only engineers and admins can update globally; technicians may be allowed depending on business rules
        if ($user->role === 'ingenieur') return true;
        return false;
    }

    public function delete(User $user, Equipment $equipment)
    {
        return false;
    }
}
