@extends('layouts.dashboard')

@section('page-title', 'Modifier une pièce - Décharge / Retour')

@section('content')
@include('pages.forms._pieces-workflow-form', ['piece' => $piece])
@endsection
