@extends('layouts.dashboard')

@section('page-title', 'Gestion OT/DM (PM-BIO)')

@section('content')
@include('components.module-page-header', [
    'breadcrumb' => 'Modèle / OT/DM (PM-BIO)',
    'addRoute' => 'interventions.create',
    'addLabel' => 'Ajouter une intervention',
    'addIcon' => 'fa-user-doctor',
    'buttonStyle' => 'equipments',
])

@if (session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@include('components.table', [
    'data' => $interventionsData ?? [],
    'showAddButton' => false,
    'buttonStyle' => 'equipments',
    'columns' => [
        ['key' => 'code', 'label' => 'Code Intervention', 'visible' => true, 'type' => 'text'],
        ['key' => 'reclamation', 'label' => 'N° Réclamation', 'visible' => true, 'type' => 'text'],
        ['key' => 'equipement', 'label' => 'Équipement', 'visible' => true, 'type' => 'text'],
        ['key' => 'type', 'label' => 'Type', 'visible' => true, 'type' => 'text'],
        ['key' => 'technicien', 'label' => 'Technicien', 'visible' => true, 'type' => 'text'],
        ['key' => 'statut', 'label' => 'Statut', 'visible' => true, 'type' => 'status'],
    ]
])

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const headerActions = document.querySelector('.module-page-header-actions');
    const toolbarActions = document.querySelector('.table-toolbar-actions');

    if (!headerActions || !toolbarActions) {
        return;
    }

    toolbarActions.classList.remove('justify-center');
    toolbarActions.classList.add('items-center');
    headerActions.prepend(toolbarActions);
});
</script>
@endsection
