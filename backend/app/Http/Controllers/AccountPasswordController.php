<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOwnPasswordRequest;
use App\Services\AppSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class AccountPasswordController extends Controller
{
    public function edit()
    {
        $settings = app(AppSettingsService::class);

        return view('auth.change-password', [
            'passwordPolicy' => [
                'min' => max(8, min(32, $settings->int('password_min_length', 12))),
                'uppercase' => $settings->bool('require_uppercase', true),
                'numbers' => $settings->bool('require_numbers', true),
                'symbols' => $settings->bool('require_symbols', true),
            ],
        ]);
    }

    public function update(UpdateOwnPasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->string('password')->toString()),
            'must_change_password' => false,
            'password_changed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', 'Mot de passe mis à jour avec succès.');
    }
}
