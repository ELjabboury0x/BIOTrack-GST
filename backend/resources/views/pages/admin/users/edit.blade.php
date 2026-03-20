@extends('layouts.dashboard')

@section('page-title', 'Éditer utilisateur')

@section('content')
<div class="max-w-3xl bg-white rounded-xl shadow-md p-6 md:p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Modifier un utilisateur</h2>

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $userEdit) }}" class="space-y-4">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input name="name" value="{{ old('name', $userEdit->name) }}" placeholder="Nom" class="px-4 py-2 border border-gray-300 rounded-lg">
            <input name="login" value="{{ old('login', $userEdit->login) }}" placeholder="Identifiant" class="px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="email" name="email" value="{{ old('email', $userEdit->email) }}" placeholder="E-mail" class="px-4 py-2 border border-gray-300 rounded-lg">
            <input type="password" name="password" placeholder="Nouveau mot de passe (optionnel)" class="px-4 py-2 border border-gray-300 rounded-lg">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg">
                @foreach($roles as $key => $label)
                    <option value="{{ $key }}" {{ old('role', $userEdit->role) === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="service_id" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">Service principal (optionnel)</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ (string)old('service_id', $userEdit->service_id) === (string)$service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $userEdit->is_active) ? 'checked' : '' }}> <span>Compte actif</span></label>

        <div class="pt-4 flex gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg">Annuler</a>
            <button class="px-5 py-2 bg-blue-600 text-white rounded-lg">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection
