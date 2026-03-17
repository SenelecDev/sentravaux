@extends('layouts.app')

@section('title', 'Demandes terminées (UNSP)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes terminées (UNSP)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) terminée(s)</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" empty-message="Aucune demande terminée" />
</div>
@endsection
