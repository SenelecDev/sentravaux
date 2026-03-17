@extends('layouts.app')

@section('title', 'Toutes les demandes SAD')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Toutes les demandes</h1>
        <p class="mt-1 text-gray-500">
            {{ count($demandes) }} demande(s) -
            Vue : <span class="font-semibold">
                {{ $statutsDisponibles[$statut] ?? ucfirst(str_replace('_', ' ', $statut)) }}
            </span>
        </p>
    </div>

    {{-- Filtres par statut (raccourcis) --}}
    <div class="card-senelec">
        <div class="flex flex-wrap gap-2">
            @foreach($statutsDisponibles as $code => $label)
                @php
                    $isActive = $statut === $code;
                @endphp
                <a href="{{ route('sad.demandes', ['statut' => $code]) }}"
                   class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border transition-all
                        {{ $isActive
                            ? 'bg-senelec-purple text-white border-senelec-purple shadow-sm'
                            : 'bg-white text-gray-700 border-gray-200 hover:border-senelec-purple/60 hover:text-senelec-purple' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <x-demandes-table :demandes="$demandes" :show-user="true" empty-message="Aucune demande trouvée" />
</div>
@endsection
