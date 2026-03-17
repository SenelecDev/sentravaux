@extends('layouts.app')

@section('title', 'Imputer la demande')

@section('content')
<div class="space-y-6">
    {{-- En-tête --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('seg.demandes.approuvees') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Imputer la demande {{ $demande->numero_demande }}</h1>
            <p class="mt-1 text-gray-500">Dispatcher vers les unités SEG (UMR, UTGC)</p>
        </div>
    </div>

    {{-- Informations de la demande --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Informations de la demande</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Objet</p>
                    <p class="font-medium text-gray-900">{{ $demande->objet ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Nature</p>
                    <p class="font-medium text-gray-900">{{ $demande->nature ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Site</p>
                    <p class="font-medium text-gray-900">{{ $demande->site->libelle ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Demandeur</p>
                    <p class="font-medium text-gray-900">{{ $demande->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Service</p>
                    <p class="font-medium text-gray-900">{{ $demande->service->nom ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Statut</p>
                    <x-status-badge :statut="$demande->statut" />
                </div>
            </div>
        </div>
    </div>

@if($demande->unite_code === 'UMR' && $demande->date_debut_intervention && $demande->date_fin_intervention)
    {{-- Bloc période d'intervention à valider --}}
    <div class="border border-emerald-300 bg-emerald-50 rounded-xl">
        <div class="p-4 border-b border-emerald-200 flex items-center gap-2 bg-emerald-100/60">
            <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm font-semibold text-emerald-800">Imputer et dispatcher</p>
        </div>
        <div class="p-4 space-y-3">
            <div class="rounded-lg bg-sky-50 border border-sky-100 px-4 py-3 text-sm">
                <p class="text-sky-900"><span class="font-semibold">Nature :</span> {{ $demande->nature ?? '-' }}</p>
                <p class="text-sky-900"><span class="font-semibold">Unité :</span> UMR</p>
                <p class="text-sky-900">
                    <span class="font-semibold">Période proposée :</span>
                    {{ \Carbon\Carbon::parse($demande->date_debut_intervention)->format('d/m/Y') }}
                    -
                    {{ \Carbon\Carbon::parse($demande->date_fin_intervention)->format('d/m/Y') }}
                </p>
            </div>
            <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm flex gap-2">
                <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 3a9 9 0 019 9 9 9 0 01-9 9 9 9 0 01-9-9 9 9 0 019-9z"/>
                </svg>
                <p class="text-amber-800">
                    <span class="font-semibold">Important :</span>
                    Cette demande UMR comporte une période d'intervention proposée par le demandeur.
                    Elle restera <span class="font-semibold">"en attente"</span> tant qu'elle n'aura pas été validée ou rejetée dans l'écran
                    « Périodes en attente ».
                </p>
            </div>
        </div>
    </div>
@endif

    {{-- Formulaire d'imputation --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Imputation aux unités</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('seg.update', $demande) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- UMR --}}
                    <div>
                        <label for="umr_id" class="block text-sm font-medium text-gray-700 mb-1">UMR</label>
                        <select name="umr_id" id="umr_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner UMR --</option>
                            @foreach($umrs as $umr)
                                <option value="{{ $umr->id }}" {{ old('umr_id', $demande->umr_id) == $umr->id ? 'selected' : '' }}>
                                    {{ $umr->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('umr_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- UTGC --}}
                    <div>
                        <label for="utgc_id" class="block text-sm font-medium text-gray-700 mb-1">UTGC</label>
                        <select name="utgc_id" id="utgc_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner UTGC --</option>
                            @foreach($utgcs as $utgc)
                                <option value="{{ $utgc->id }}" {{ old('utgc_id', $demande->utgc_id) == $utgc->id ? 'selected' : '' }}>
                                    {{ $utgc->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('utgc_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn-senelec">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                        Imputer la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
