<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Equipment;

class EnforceEquipmentAccess
{
    /**
     * For index requests we inject a `service_filter` query param so controllers can apply it automatically.
     * For show/edit/update/destroy we validate the target equipment belongs to allowed scope.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $role = $user->role;

        if ($user->hasGlobalAccess()) {
            return $next($request);
        }

        // For index/list routes: add filtering param for controllers that read it
        if ($request->isMethod('GET') && ($request->routeIs('equipements') || $request->routeIs('equipements.index') || str_starts_with($request->path(), 'dashboard'))) {
            if (!in_array($role, ['admin', 'ingenieur', 'manager', 'major', 'technicien', 'technician'], true)) {
                // restrict to user's service
                $request->merge(['service_filter' => $user->service_id]);
            }
        }

        // For routes that include {equipment} parameter we enforce ownership
        $equipmentId = $request->route('equipment') ?? $request->route('equipement') ?? null;

        if ($equipmentId) {
            $equipment = Equipment::find($equipmentId);
            if (!$equipment) {
                abort(404);
            }

            // Roles with global data access can access everything
            if (in_array($role, ['admin', 'ingenieur', 'manager', 'major', 'technicien', 'technician'], true)) {
                return $next($request);
            }

            // technicien/major can access only equipment from own service
            if (in_array($role, ['technicien', 'technician', 'major'])) {
                if ($equipment->service_id != $user->service_id) {
                    abort(403);
                }
            } else {
                // other roles denied
                abort(403);
            }
        }

        return $next($request);
    }
}
