<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Blocks all write operations (POST/PUT/PATCH/DELETE) for users with the "major" role.
 * Major users are view-only: they can browse every page but cannot create, edit,
 * delete, or import any data.
 */
class MajorReadOnly
{
    /**
     * Routes that major users are still allowed to POST to
     * (non-destructive account actions).
     */
    protected array $allowedRoutes = [
        'logout',
        'password.update',
        'profile.update',
        'dashboard.notifications.complaints.read-all',
        'dashboard.notifications.complaints.close',
        'reclamations.status.update',
        'push-subscriptions.store',
        'push-subscriptions.destroy',
        'operator.defects.store',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'major' && !$request->isMethod('GET')) {
            $routeName = $request->route()?->getName();

            if ($routeName && in_array($routeName, $this->allowedRoutes, true)) {
                return $next($request);
            }

            abort(403, 'Accès refusé : votre rôle ne permet que la consultation.');
        }

        return $next($request);
    }
}
