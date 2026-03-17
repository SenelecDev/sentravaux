@extends('layouts.app')

@section('title', 'Demandes reçues (UMR)')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes reçues (UMR)</h1>
        <p class="mt-1 text-gray-500">{{ count($demandes) }} demande(s) reçue(s)</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" edit-route="umr.edit" />
</div>
@endsection
