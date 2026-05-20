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
            @if(!empty($teamType))
                · Type : <span class="font-semibold">{{ $teamType === 'interne' ? 'Travaux internes' : 'Travaux externes' }}</span>
            @endif
        </p>
    </div>

    <div class="card-senelec p-4">
        <form method="GET" action="{{ route('sad.demandes') }}" class="flex flex-wrap items-end gap-3">
            <div class="w-64">
                <label for="team_type" class="block text-sm font-semibold text-gray-700 mb-1">Type de travaux</label>
                <select name="team_type" id="team_type" class="input-senelec">
                    <option value="">Tous les types</option>
                    <option value="interne" {{ ($teamType ?? '') === 'interne' ? 'selected' : '' }}>Travaux internes</option>
                    <option value="externe" {{ ($teamType ?? '') === 'externe' ? 'selected' : '' }}>Travaux externes</option>
                </select>
            </div>
            <input type="hidden" name="statut" value="{{ $statut }}">
            <button type="submit" class="btn-senelec">Filtrer</button>
            <a href="{{ route('sad.demandes', ['statut' => $statut]) }}" class="btn-secondary px-4">Réinitialiser</a>
        </form>
    </div>

    {{-- Filtres par statut (raccourcis) --}}
    <div class="card-senelec">
        <div class="flex flex-wrap gap-2">
            @foreach($statutsDisponibles as $code => $label)
                @php
                    $isActive = $statut === $code;
                @endphp
                <a href="{{ route('sad.demandes', array_filter(['statut' => $code, 'team_type' => $teamType])) }}"
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
