@extends('layouts.dashboard')

@section('page-title', 'Mon Profil')

@section('content')
<div class="max-w-3xl bg-white rounded-xl shadow-md p-6 md:p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Mon Profil</h2>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nom</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Identifiant</label>
            <input type="text" name="login" value="{{ old('login', $user->login) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">E-mail</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
        </div>

        <div class="pt-4 border-t border-gray-200 flex gap-3">
            <a href="{{ route('dashboard') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">Enregistrer</button>
        </div>
    </form>
</div>
@endsection
