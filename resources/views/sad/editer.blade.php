@extends('layouts.app')

@section('title', 'Imputer la demande')

@section('content')
<div class="space-y-6">
    {{-- En-tête --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('sad.demandes.approuvees') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Imputer la demande {{ $demande->numero_demande }}</h1>
            <p class="mt-1 text-gray-500">Dispatcher vers les unités SA (UMT, UBT, UNSP)</p>
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

    {{-- Formulaire d'imputation --}}
    <div class="card-senelec">
        <div class="p-6 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Imputation aux unités</h2>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('sad.update', $demande) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- UMT --}}
                    <div>
                        <label for="umt_id" class="block text-sm font-medium text-gray-700 mb-1">UMT</label>
                        <select name="umt_id" id="umt_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner UMT --</option>
                            @foreach($umts as $umt)
                                <option value="{{ $umt->id }}" {{ old('umt_id', $demande->umt_id) == $umt->id ? 'selected' : '' }}>
                                    {{ $umt->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('umt_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- UBT --}}
                    <div>
                        <label for="ubt_id" class="block text-sm font-medium text-gray-700 mb-1">UBT</label>
                        <select name="ubt_id" id="ubt_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner UBT --</option>
                            @foreach($ubts as $ubt)
                                <option value="{{ $ubt->id }}" {{ old('ubt_id', $demande->ubt_id) == $ubt->id ? 'selected' : '' }}>
                                    {{ $ubt->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('ubt_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- UNSP --}}
                    <div>
                        <label for="unsp_id" class="block text-sm font-medium text-gray-700 mb-1">UNSP</label>
                        <select name="unsp_id" id="unsp_id" class="select-senelec w-full">
                            <option value="">-- Sélectionner UNSP --</option>
                            @foreach($unsps as $unsp)
                                <option value="{{ $unsp->id }}" {{ old('unsp_id', $demande->unsp_id) == $unsp->id ? 'selected' : '' }}>
                                    {{ $unsp->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unsp_id')
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
