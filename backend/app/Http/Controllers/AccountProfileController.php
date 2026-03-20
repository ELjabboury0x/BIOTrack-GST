<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountProfileController extends Controller
{
    public function edit(Request $request)
    {
        return view('pages.account.profile', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'login' => ['required', 'string', 'max:120', Rule::unique('users', 'login')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profil mis à jour avec succès.');
    }
}
