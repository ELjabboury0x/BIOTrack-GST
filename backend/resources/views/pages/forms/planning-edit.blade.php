@extends('layouts.dashboard')

@section('page-title', 'Modifier un planning')

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-8">
    <h2 class="text-xl font-bold text-gray-800 mb-6">Modifier Planning Sociétés Externes</h2>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="grid grid-cols-1 md:grid-cols-2 gap-6" method="POST" action="{{ route('planning.update', $planning) }}">
        @csrf
        @method('PUT')

        <select name="company_id" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="">Choisir une société</option>
            @foreach(($companies ?? collect()) as $company)
                <option value="{{ $company->id }}" {{ (int) old('company_id', $planning->company_id) === (int) $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
            @endforeach
        </select>

        <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date début</label>
            <input type="date" id="planned_date" name="planned_date" value="{{ old('planned_date', optional($planning->planned_date)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
        </div>

        <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-gray-700 mb-2">Date fin</label>
            <input type="date" id="planned_date_end" name="planned_date_end" value="{{ old('planned_date_end', optional($planning->planned_date_end)->format('Y-m-d')) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
        </div>

        <div class="md:col-span-2 p-3 border border-gray-200 rounded-lg bg-gray-50">
            <p class="text-xs font-semibold text-gray-600 mb-2">Date Range Picker (calendrier)</p>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="planning-range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="7">7 jours</button>
                <button type="button" class="planning-range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="14">2 semaines</button>
                <button type="button" class="planning-range-chip px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100" data-range="30">30 jours</button>
                <button type="button" id="planning-copy-end" class="px-3 py-1.5 text-xs rounded-full border border-gray-300 bg-white hover:bg-gray-100">Date fin = Date début</button>
            </div>
        </div>

        <input name="contact_person" value="{{ old('contact_person', $planning->contact_person) }}" placeholder="Intervenant" class="px-4 py-2 border border-gray-300 rounded-lg">

        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg" required>
            <option value="en_attente" {{ old('status', $planning->status) === 'en_attente' ? 'selected' : '' }}>En attente</option>
            <option value="en_cours" {{ old('status', $planning->status) === 'en_cours' ? 'selected' : '' }}>En cours</option>
            <option value="termine" {{ old('status', $planning->status) === 'termine' ? 'selected' : '' }}>Terminé</option>
        </select>

        <textarea name="description" placeholder="Description" class="md:col-span-2 px-4 py-2 border border-gray-300 rounded-lg" rows="3">{{ old('description', $planning->description) }}</textarea>

        <div class="md:col-span-2 flex justify-end gap-3">
            <a href="{{ route('planning.index') }}" class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700">Annuler</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Enregistrer</button>
        </div>
    </form>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const startInput = document.getElementById('planned_date');
    const endInput = document.getElementById('planned_date_end');
    const chips = Array.from(document.querySelectorAll('.planning-range-chip'));
    const copyButton = document.getElementById('planning-copy-end');

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            const days = parseInt(chip.dataset.range || '0', 10);
            if (!days || !startInput || !endInput) {
                return;
            }

            const start = startInput.value ? new Date(startInput.value) : new Date();
            const end = new Date(start);
            end.setDate(end.getDate() + days);
            endInput.value = formatDate(end);
        });
    });

    if (copyButton) {
        copyButton.addEventListener('click', function () {
            if (startInput && endInput && startInput.value) {
                endInput.value = startInput.value;
            }
        });
    }
});
</script>
@endsection
@endsection
