<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Enforces a strict read-only mode for users with the "major" role.
 * Major users can browse data pages, but cannot access create/edit action screens
 * nor execute write operations for business modules.
 */
class MajorReadOnly
{
    /**
     * Write routes still allowed for account/session features.
     */
    protected array $allowedWriteRoutes = [
        'logout',
        'password.update',
        'profile.update',
        'push-subscriptions.store',
        'push-subscriptions.destroy',
    ];

    /**
     * Read routes that are explicitly allowed even if their name matches
     * a blocked pattern (e.g. *.edit for account profile).
     */
    protected array $allowedActionReadRoutes = [
        'profile.edit',
        'password.edit',
    ];

    /**
     * Block action pages in GET for major users.
     */
    protected array $blockedReadRoutePatterns = [
        '/\\.create$/',
        '/\\.edit$/',
        '/\\.close\\.form$/',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->role === 'major') {
            $routeName = $request->route()?->getName();

            if ($request->isMethod('GET') || $request->isMethod('HEAD')) {
                if ($this->isBlockedActionReadRoute($routeName)) {
                    abort(403, 'Accès refusé : votre rôle ne permet que la consultation.');
                }

                return $next($request);
            }

            if ($routeName && in_array($routeName, $this->allowedWriteRoutes, true)) {
                return $next($request);
            }

            abort(403, 'Accès refusé : votre rôle ne permet que la consultation.');
        }

        return $next($request);
    }

    protected function isBlockedActionReadRoute(?string $routeName): bool
    {
        if (!$routeName) {
            return false;
        }

        if (in_array($routeName, $this->allowedActionReadRoutes, true)) {
            return false;
        }

        foreach ($this->blockedReadRoutePatterns as $pattern) {
            if (preg_match($pattern, $routeName) === 1) {
                return true;
            }
        }

        return false;
    }
}
