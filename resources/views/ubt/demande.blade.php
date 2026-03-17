@extends('layouts.app')

@section('title', 'Demandes reçues (UBT)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes reçues (UBT)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) reçue(s)</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" edit-route="ubt.edit" />
</div>
@endsection
