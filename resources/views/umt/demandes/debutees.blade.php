@extends('layouts.app')

@section('title', 'Travaux débutés (UMT)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Travaux débutés (UMT)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) en cours</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" empty-message="Aucun travail débuté" />
</div>
@endsection
