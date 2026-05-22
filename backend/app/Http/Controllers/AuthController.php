<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Models\Service;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        $loginOnlyHospitalServiceName = 'Hôpital Universitaire Mère-Enfant Mohammed VI-Tanger';

        try {
            $services = Service::query()
                ->excludeHiddenForUi()
                ->orderBy('name')
                ->get(['id', 'name']);

            $loginOnlyHospitalService = Service::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower(trim($loginOnlyHospitalServiceName))])
                ->first(['id', 'name']);

            if ($loginOnlyHospitalService) {
                $services->push($loginOnlyHospitalService);
            }

            $services = $services
                ->unique(function (Service $service): string {
                    return Str::of($service->name)
                        ->ascii()
                        ->lower()
                        ->replaceMatches('/\s+/', ' ')
                        ->trim()
                        ->value();
                })
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values();
        } catch (QueryException $exception) {
            Log::warning('Unable to load services on login page. Database may be unavailable.', [
                'error' => $exception->getMessage(),
            ]);

            $services = collect();
        }

        return view('login', compact('services'));
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $serviceId = isset($validated['service_id']) ? (int) $validated['service_id'] : null;

        $identifier = $this->normalizeIdentifier((string) ($validated['login'] ?? ''));
        $plainPassword = (string) ($validated['password'] ?? '');

        $user = $this->findUserForAuthentication($identifier);
        $passwordValid = $user ? $this->verifyUserPassword($user, $plainPassword) : false;

        if (!$user || !$passwordValid) {
            Log::warning('Authentication failed for submitted credentials.', [
                'identifier' => $identifier,
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
            ]);

            return back()
                ->withErrors(['login' => 'Identifiants invalides.'])
                ->withInput($request->except('password'));
        }

        if ($user && !$user->is_active) {
            return back()
                ->withErrors(['login' => 'Votre compte est désactivé. Contactez un administrateur.'])
                ->withInput($request->except('password'));
        }

        Auth::login($user, $request->boolean('remember'));

        if ($user && !$user->hasGlobalAccess() && !$serviceId) {
            Auth::logout();

            return back()
                ->withErrors(['service_id' => 'Sélectionnez un service pour vous connecter avec ce profil (non administrateur).'])
                ->withInput($request->except('password'));
        }

        // Verify user has access to the selected service
        if ($serviceId && $user && !$user->hasGlobalAccess()) {
            $allowed = $user->allowedServiceIds();
            if (!in_array((int) $serviceId, $allowed, true)) {
                Auth::logout();
                return back()
                    ->withErrors(['service_id' => 'Le service sélectionné n\'est pas autorisé pour ce compte.'])
                    ->withInput($request->except('password'));
            }
        }

        $request->session()->regenerate();
        $request->session()->put('last_activity_at', now()->toDateTimeString());
        if ($serviceId) {
            $request->session()->put('selected_service_id', $serviceId);
        } else {
            $request->session()->forget('selected_service_id');
        }

        if ($user) {
            $user->forceFill(['last_login_at' => now()])->save();
        }

        return redirect()->intended($this->redirectToByRole($user?->role));
    }

    private function normalizeIdentifier(string $value): string
    {
        $normalized = str_replace(["\u{00A0}", "\u{2007}", "\u{202F}"], ' ', $value);
        $trimmed = preg_replace('/^\s+|\s+$/u', '', $normalized);

        return (string) ($trimmed ?? '');
    }

    private function findUserForAuthentication(string $identifier): ?User
    {
        $normalized = mb_strtolower(trim($identifier));
        if ($normalized === '') {
            return null;
        }

        return User::query()
            ->where(function ($query) use ($normalized): void {
                $query
                    ->whereRaw('LOWER(TRIM(login)) = ?', [$normalized])
                    ->orWhereRaw('LOWER(TRIM(email)) = ?', [$normalized]);
            })
            ->orderBy('id')
            ->first();
    }

    private function verifyUserPassword(User $user, string $plainPassword): bool
    {
        $storedPassword = (string) $user->getAuthPassword();
        if ($storedPassword === '' || $plainPassword === '') {
            return false;
        }

        $valid = Hash::check($plainPassword, $storedPassword);

        // Legacy safeguard: if a plain password was stored historically, accept once and migrate to hash.
        if (!$valid && !Str::startsWith($storedPassword, ['$2y$', '$argon2'])) {
            $valid = hash_equals($storedPassword, $plainPassword);
            if ($valid) {
                $user->forceFill(['password' => Hash::make($plainPassword)])->save();
                return true;
            }
        }

        if ($valid && Hash::needsRehash($storedPassword)) {
            $user->forceFill(['password' => Hash::make($plainPassword)])->save();
        }

        return $valid;
    }

    private function redirectToByRole(?string $role): string
    {
        return match ($role) {
            'operator' => route('dashboard'),
            'technician', 'technicien' => route('dashboard'),
            default => route('dashboard'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Déconnexion réussie.');
    }
}
