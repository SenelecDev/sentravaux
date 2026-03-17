@extends('layouts.app')

@section('title', 'Demandes validées (UNSP)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes validées (UNSP)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) validée(s)</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" empty-message="Aucune demande validée" />
</div>
@endsection
