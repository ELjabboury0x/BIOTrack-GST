<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('logout', 'password.edit', 'password.update')) {
            return $next($request);
        }

        return redirect()
            ->route('password.edit')
            ->with('warning', 'Mot de passe temporaire détecté. Veuillez définir un mot de passe sécurisé.');
    }
}
