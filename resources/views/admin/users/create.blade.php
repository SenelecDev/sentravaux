@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouvel utilisateur</h1>
            <p class="text-sm text-gray-500">Créer un compte utilisateur manuellement</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf

        {{-- ======== INFORMATIONS PERSONNELLES ======== --}}
        <div class="card-senelec p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg bg-purple-100">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Informations personnelles</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="matricule" class="block text-sm font-medium text-gray-700 mb-1">Matricule <span class="text-red-500">*</span></label>
                    <input type="text" id="matricule" name="matricule" value="{{ old('matricule') }}" required
                           class="input-senelec" placeholder="Ex: AGT001">
                    @error('matricule')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                           class="input-senelec" placeholder="utilisateur@senelec.sn">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom') }}" required
                           class="input-senelec">
                    @error('nom')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" id="prenom" name="prenom" value="{{ old('prenom') }}" required
                           class="input-senelec">
                    @error('prenom')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="{{ old('telephone') }}"
                           class="input-senelec" placeholder="Ex: 77 123 45 67">
                </div>
            </div>
        </div>

        {{-- ======== ORGANISATION ======== --}}
        <div class="card-senelec p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg bg-blue-100">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Organisation</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="direction" class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                    <input type="text" id="direction" name="direction" value="{{ old('direction') }}"
                           class="input-senelec" placeholder="Ex: Direction Technique">
                </div>
                <div>
                    <label for="departement" class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                    <input type="text" id="departement" name="departement" value="{{ old('departement') }}"
                           class="input-senelec" placeholder="Ex: Maintenance">
                </div>
                <div>
                    <label for="service" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                    <input type="text" id="service" name="service" value="{{ old('service') }}"
                           class="input-senelec" placeholder="Ex: Électricité">
                </div>
                <div>
                    <label for="poste" class="block text-sm font-medium text-gray-700 mb-1">Poste / Fonction</label>
                    <input type="text" id="poste" name="poste" value="{{ old('poste') }}"
                           class="input-senelec" placeholder="Ex: Technicien Électricien">
                </div>
            </div>
        </div>

        {{-- ======== RÔLES APPLICATIFS ======== --}}
        <div class="card-senelec p-6 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <div class="p-1.5 rounded-lg" style="background-color: #fef3c7;">
                    <svg class="w-5 h-5" style="color: #d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Rôles applicatifs</h2>
            </div>
            <p class="text-sm text-gray-500 mb-5">Sélectionnez un ou plusieurs rôles pour cet utilisateur</p>

            @php
                $roleColors = [
                    'admin'       => ['border' => '#fca5a5', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'desc' => 'Accès complet'],
                    'demandeur'   => ['border' => '#fcd34d', 'bg' => '#fffbeb', 'text' => '#b45309', 'desc' => 'Créer des demandes'],
                    'approbateur' => ['border' => '#93c5fd', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'desc' => 'Approuver les demandes'],
                    'dage'        => ['border' => '#d8b4fe', 'bg' => '#faf5ff', 'text' => '#7e22ce', 'desc' => 'Statistiques DAGE'],
                    'sad'         => ['border' => '#5eead4', 'bg' => '#f0fdfa', 'text' => '#0f766e', 'desc' => 'Service Administratif'],
                    'seg'         => ['border' => '#67e8f9', 'bg' => '#ecfeff', 'text' => '#0e7490', 'desc' => 'Service Entretien Général'],
                    'umt'         => ['border' => '#86efac', 'bg' => '#f0fdf4', 'text' => '#15803d', 'desc' => 'Unité Maintenance'],
                    'ubt'         => ['border' => '#bef264', 'bg' => '#f7fee7', 'text' => '#4d7c0f', 'desc' => 'Unité BT'],
                    'unsp'        => ['border' => '#fdba74', 'bg' => '#fff7ed', 'text' => '#c2410c', 'desc' => 'Unité NSP'],
                    'umr'         => ['border' => '#a5b4fc', 'bg' => '#eef2ff', 'text' => '#4338ca', 'desc' => 'Unité MR'],
                    'utgc'        => ['border' => '#f9a8d4', 'bg' => '#fdf2f8', 'text' => '#be185d', 'desc' => 'Unité TGCC'],
                    'equipe'      => ['border' => '#c4b5fd', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'desc' => "Chef d'équipe"],
                ];
                $defaultColor = ['border' => '#d1d5db', 'bg' => '#f9fafb', 'text' => '#374151', 'desc' => ''];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($roles as $role)
                    @php $c = $roleColors[$role->name] ?? $defaultColor; @endphp
                    <label class="flex flex-col items-center gap-1 py-3 px-4 rounded-xl cursor-pointer transition-all hover:shadow-md"
                           style="border: 2px solid {{ $c['border'] }}; background-color: {{ $c['bg'] }};">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                               class="w-5 h-5 rounded border-gray-300 focus:ring-offset-0 mb-1"
                               {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                        <span class="text-sm font-bold" style="color: {{ $c['text'] }};">{{ ucfirst($role->name) }}</span>
                        @if($c['desc'])
                            <span class="text-xs text-gray-500 text-center">{{ $c['desc'] }}</span>
                        @endif
                    </label>
                @endforeach
            </div>
        </div>

        {{-- ======== AUTHENTIFICATION ======== --}}
        <div class="card-senelec p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg" style="background-color: #e0e7ff;">
                    <svg class="w-5 h-5" style="color: #4f46e5;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Authentification</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe <span class="text-red-500">*</span></label>
                    <input type="password" id="password" name="password" required
                           class="input-senelec" placeholder="Minimum 8 caractères">
                    <p class="text-xs text-gray-400 mt-1">Sera utilisé uniquement si l'authentification LDAP échoue</p>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="input-senelec">
                </div>
            </div>
        </div>

        {{-- ======== ACTIONS ======== --}}
        <div class="flex items-center justify-end gap-4 pb-6">
            <a href="{{ route('admin.users.index') }}" class="px-6 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800 uppercase tracking-wider transition-colors">
                Annuler
            </a>
            <button type="submit" class="btn-senelec inline-flex items-center gap-2 px-6 py-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Créer l'utilisateur
            </button>
        </div>
    </form>
</div>
@endsection
