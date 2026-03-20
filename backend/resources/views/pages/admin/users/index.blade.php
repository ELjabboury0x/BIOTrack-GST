@extends('layouts.dashboard')

@section('page-title', 'Gestion des utilisateurs')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6 md:p-8">
    @php
        $role = auth()->user()?->role;
        $canCreateDeleteUsers = in_array($role, ['admin', 'ingenieur'], true);
    @endphp
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Utilisateurs</h2>
        @if($canCreateDeleteUsers)
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Créer un utilisateur</a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">{{ session('error') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left border-b border-gray-200">
                    <th class="py-3 pr-4">Login</th>
                    <th class="py-3 pr-4">Nom</th>
                    <th class="py-3 pr-4">Rôle</th>
                    <th class="py-3 pr-4">Statut</th>
                    <th class="py-3 pr-4">Service</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr class="border-b border-gray-100">
                        <td class="py-3 pr-4 font-medium">{{ $user->login }}</td>
                        <td class="py-3 pr-4">{{ $user->name }}</td>
                        <td class="py-3 pr-4">{{ $user->role }}</td>
                        <td class="py-3 pr-4">
                            <span class="px-2 py-1 rounded text-xs {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $user->is_active ? 'Actif' : 'Inactif' }}</span>
                        </td>
                        <td class="py-3 pr-4">{{ $user->primaryService?->name ?? '-' }}</td>
                        <td class="py-3 flex flex-wrap gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="px-3 py-1 border border-blue-500 text-blue-600 rounded">Modifier</a>
                            <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">@csrf @method('PATCH')<button class="px-3 py-1 border border-amber-500 text-amber-600 rounded">{{ $user->is_active ? 'Désactiver' : 'Activer' }}</button></form>
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">@csrf <button class="px-3 py-1 border border-purple-500 text-purple-600 rounded">Réinitialiser le mot de passe</button></form>
                            @if($canCreateDeleteUsers)
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Supprimer cet utilisateur ?')">@csrf @method('DELETE')<button class="px-3 py-1 border border-red-500 text-red-600 rounded">Supprimer</button></form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
