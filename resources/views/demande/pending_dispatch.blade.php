@extends('layouts.app')

@section('title', 'Demandes en attente de dispatch')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Demandes en attente de dispatch</h1>
        <p class="mt-1 text-gray-500">Demandes validées en attente d'affectation aux unités</p>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" />
</div>
@endsection
