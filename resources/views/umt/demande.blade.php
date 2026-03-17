@extends('layouts.app')

@section('title', 'Demandes reçues (UMT)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes reçues (UMT)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) reçue(s)</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" edit-route="umt.edit" />
</div>
@endsection
