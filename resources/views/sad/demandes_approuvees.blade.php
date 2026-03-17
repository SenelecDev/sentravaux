@extends('layouts.app')

@section('title', 'Demandes approuvées (SA)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes approuvées (SA)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) approuvée(s)</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" edit-route="sad.edit" empty-message="Aucune demande approuvée" />
</div>
@endsection
