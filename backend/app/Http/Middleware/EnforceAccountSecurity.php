<?php

namespace App\Http\Middleware;

use App\Services\AppSettingsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnforceAccountSecurity
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'login' => 'Votre compte est désactivé. Contactez un administrateur.',
            ]);
        }

        $timeoutMinutes = max(5, app(AppSettingsService::class)->int('session_timeout_minutes', 120));
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity) {
            $inactiveSeconds = now()->diffInSeconds(\Carbon\Carbon::parse($lastActivity));
            if ($inactiveSeconds > ($timeoutMinutes * 60)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'login' => 'Session expirée pour inactivité. Veuillez vous reconnecter.',
                ]);
            }
        }

        $request->session()->put('last_activity_at', now()->toDateTimeString());

        return $next($request);
    }
}
