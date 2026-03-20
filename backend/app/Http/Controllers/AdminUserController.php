<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ComplaintCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['primaryService:id,name'])
            ->orderByDesc('id')
            ->paginate(20);

        return view('pages.admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        return view('pages.admin.users.create', [
            'services' => Service::query()->excludeHiddenForUi()->orderBy('name')->get(['id', 'name']),
            'roles' => $this->roles(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'login' => ['required', 'string', 'max:120', 'unique:users,login'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'service_id' => $validated['service_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'password' => Hash::make($validated['password']),
            'must_change_password' => true,
        ]);

        $this->syncComplaintNotificationsForEligibleUser($user);

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $user)
    {
        return view('pages.admin.users.edit', [
            'userEdit' => $user,
            'services' => Service::query()->excludeHiddenForUi()->orderBy('name')->get(['id', 'name']),
            'roles' => $this->roles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'login' => ['required', 'string', 'max:120', Rule::unique('users', 'login')->ignore($user->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys($this->roles()))],
            'service_id' => ['nullable', 'integer', 'exists:services,id'],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'name' => $validated['name'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'service_id' => $validated['service_id'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
            $payload['must_change_password'] = true;
            $payload['password_changed_at'] = null;
        }

        $user->update($payload);

        $this->syncComplaintNotificationsForEligibleUser($user->fresh());

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Impossible de supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }

    public function toggleActive(User $user)
    {
        if ((int) $user->id === (int) auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Impossible de désactiver votre propre compte.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $this->syncComplaintNotificationsForEligibleUser($user->fresh());

        return redirect()->route('admin.users.index')->with('success', 'Statut du compte mis à jour.');
    }

    public function resetPassword(User $user)
    {
        $temporaryPassword = 'Tmp!' . Str::upper(Str::random(8));

        $user->update([
            'password' => Hash::make($temporaryPassword),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        return redirect()->route('admin.users.index')->with('success', "Mot de passe réinitialisé pour {$user->login}: {$temporaryPassword}");
    }

    private function roles(): array
    {
        return [
            'admin' => 'Admin',
            'manager' => 'Manager',
            'operator' => 'Operator',
            'technician' => 'Technician',
            'major' => 'Major',
            'technicien' => 'Technicien',
            'ingenieur' => 'Ingénieur',
        ];
    }

    private function syncComplaintNotificationsForEligibleUser(?User $user): void
    {
        if (!$user || !$user->is_active) {
            return;
        }

        if (!in_array($user->role, ['admin', 'ingenieur', 'major', 'technicien', 'technician'], true)) {
            return;
        }

        $existingComplaintIds = $user->notifications()
            ->where('type', ComplaintCreatedNotification::class)
            ->get(['data'])
            ->pluck('data.complaint_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->all();

        $complaintsQuery = Complaint::query()
            ->with([
                'service:id,name',
                'equipment:id,inventory_number_current,designation',
            ])
            ->whereIn('status', ['open', 'in_progress'])
            ->latest('id');

        if (!empty($existingComplaintIds)) {
            $complaintsQuery->whereNotIn('id', $existingComplaintIds);
        }

        $complaintsQuery->get()->each(function (Complaint $complaint) use ($user) {
            $user->notify(new ComplaintCreatedNotification($complaint));
        });
    }
}
