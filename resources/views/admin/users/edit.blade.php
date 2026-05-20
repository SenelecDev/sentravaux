@extends('layouts.app')

@section('title', 'Modifier ' . $user->full_name)

@php
    $isLdap = $user->ldap_guid || $user->oracle_person_id;
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users.show', $user) }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Modifier l'utilisateur</h1>
            <p class="text-sm text-gray-500">{{ $user->full_name }} — {{ $user->matricule }}</p>
        </div>
    </div>

    @if($isLdap)
        <div class="flex items-center gap-2 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-sm">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Utilisateur synchronisé (LDAP/Oracle). Seuls le <strong>téléphone</strong>, le <strong>service</strong> et les <strong>rôles</strong> sont modifiables.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

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
                    <input type="text" id="matricule" name="matricule" value="{{ old('matricule', $user->matricule) }}" required
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
                    @error('matricule')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                    <input type="text" id="nom" name="nom" value="{{ old('nom', $user->nom) }}" required
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
                    @error('nom')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                    <input type="text" id="prenom" name="prenom" value="{{ old('prenom', $user->prenom) }}" required
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
                    @error('prenom')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="tel" id="telephone" name="telephone" value="{{ old('telephone', $user->telephone) }}"
                           class="input-senelec">
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
                    <input type="text" id="direction" name="direction" value="{{ old('direction', $user->direction) }}"
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
                </div>
                <div>
                    <label for="departement" class="block text-sm font-medium text-gray-700 mb-1">Département</label>
                    <input type="text" id="departement" name="departement" value="{{ old('departement', $user->departement) }}"
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
                </div>
                <div>
                    <label for="service" class="block text-sm font-medium text-gray-700 mb-1">Service</label>
                    <input type="text" id="service" name="service" value="{{ old('service', $user->service) }}"
                           class="input-senelec">
                </div>
                <div>
                    <label for="poste" class="block text-sm font-medium text-gray-700 mb-1">Poste / Fonction</label>
                    <input type="text" id="poste" name="poste" value="{{ old('poste', $user->poste) }}"
                           class="input-senelec {{ $isLdap ? 'bg-gray-100 text-gray-500' : '' }}" {{ $isLdap ? 'readonly' : '' }}>
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
                $userRoles = old('roles', $user->roles->pluck('name')->toArray());
                $roleColors = [
                    'admin'       => ['border' => '#fca5a5', 'bg' => '#fef2f2', 'text' => '#b91c1c', 'desc' => 'Accès complet'],
                    'demandeur'   => ['border' => '#fcd34d', 'bg' => '#fffbeb', 'text' => '#b45309', 'desc' => 'Créer des demandes'],
                    'approbateur' => ['border' => '#93c5fd', 'bg' => '#eff6ff', 'text' => '#1d4ed8', 'desc' => 'Approuver les demandes'],
                    'dage'        => ['border' => '#d8b4fe', 'bg' => '#faf5ff', 'text' => '#7e22ce', 'desc' => 'Statistiques DAGE'],
                    'sad'         => ['border' => '#5eead4', 'bg' => '#f0fdfa', 'text' => '#0f766e', 'desc' => 'Service Administratif'],
                    'seg'         => ['border' => '#67e8f9', 'bg' => '#ecfeff', 'text' => '#0e7490', 'desc' => 'Service Entretien Général'],
                    'sgb'         => ['border' => '#fde68a', 'bg' => '#fffbeb', 'text' => '#92400e', 'desc' => 'Service Gestions Budget'],
                    'umt'         => ['border' => '#86efac', 'bg' => '#f0fdf4', 'text' => '#15803d', 'desc' => 'Unité Maintenance'],
                    'ubt'         => ['border' => '#bef264', 'bg' => '#f7fee7', 'text' => '#4d7c0f', 'desc' => 'Unité BT'],
                    'unsp'        => ['border' => '#fdba74', 'bg' => '#fff7ed', 'text' => '#c2410c', 'desc' => 'Unité NSP'],
                    'umr'         => ['border' => '#a5b4fc', 'bg' => '#eef2ff', 'text' => '#4338ca', 'desc' => 'Unité MR'],
                    'utgc'        => ['border' => '#f9a8d4', 'bg' => '#fdf2f8', 'text' => '#be185d', 'desc' => 'Unité TGCC'],
                    'ual'         => ['border' => '#fdba74', 'bg' => '#fff7ed', 'text' => '#9a3412', 'desc' => 'Unité Analyse et Liquidation'],
                    'ucc'         => ['border' => '#fbcfe8', 'bg' => '#fdf2f8', 'text' => '#9d174d', 'desc' => 'Unité Contrôle et Conformité'],
                    'equipe'      => ['border' => '#c4b5fd', 'bg' => '#f5f3ff', 'text' => '#6d28d9', 'desc' => "Chef d'équipe"],
                ];
                $defaultColor = ['border' => '#d1d5db', 'bg' => '#f9fafb', 'text' => '#374151', 'desc' => ''];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach($roles as $role)
                    @php
                        $roleName = $role->name;
                        $c = $roleColors[$roleName] ?? $defaultColor;

                        // Modifier uniquement le label affiché (ne pas toucher à la description)
                        $roleLabel = ucfirst($roleName);
                        $roleDesc = $c['desc'] ?? '';

                        if ($roleName === 'sad') {
                            $roleLabel = 'SA';
                        } elseif ($roleName === 'seg') {
                            $roleLabel = 'SEG';
                        } elseif ($roleName === 'umt') {
                            $roleLabel = 'UMT';
                        } elseif ($roleName === 'ubt') {
                            $roleLabel = 'UBT';
                        } elseif ($roleName === 'unsp') {
                            $roleLabel = 'UNSP';
                        } elseif ($roleName === 'sgb') {
                            $roleLabel = 'SGB';
                        } elseif ($roleName === 'ual') {
                            $roleLabel = 'UAL';
                        } elseif ($roleName === 'ucc') {
                            $roleLabel = 'UCC';
                        }
                    @endphp
                    <label class="flex flex-col items-center gap-1 py-3 px-4 rounded-xl cursor-pointer transition-all hover:shadow-md"
                           style="border: 2px solid {{ $c['border'] }}; background-color: {{ $c['bg'] }};">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                               class="w-5 h-5 rounded border-gray-300 focus:ring-offset-0 mb-1"
                               {{ in_array($role->name, $userRoles) ? 'checked' : '' }}>
                        <span class="text-sm font-bold" style="color: {{ $c['text'] }};">{{ $roleLabel }}</span>
                        @if($roleDesc)
                            <span class="text-xs text-gray-500 text-center">{{ $roleDesc }}</span>
                        @endif
                    </label>
                @endforeach
            </div>
        </div>

        {{-- ======== AUTHENTIFICATION ======== --}}
        @if(!$isLdap)
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
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password"
                           class="input-senelec" placeholder="Laisser vide pour ne pas changer">
                    <p class="text-xs text-gray-400 mt-1">Sera utilisé uniquement si l'authentification LDAP échoue</p>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="input-senelec">
                </div>
            </div>
        </div>
        @endif

        {{-- ======== STATUT DU COMPTE ======== --}}
        <div class="card-senelec p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="p-1.5 rounded-lg bg-green-100">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900">Statut du compte</h2>
            </div>

            <label class="relative inline-flex items-center cursor-pointer" x-data="{ active: {{ old('is_active', $user->is_active) ? 'true' : 'false' }} }">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="sr-only peer" x-model="active"
                       {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-100 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-500"></div>
                <span class="ml-3 text-sm font-medium" :class="active ? 'text-green-600' : 'text-gray-500'" x-text="active ? 'Compte actif' : 'Compte désactivé'"></span>
            </label>
        </div>

        {{-- ======== ACTIONS ======== --}}
        <div class="flex items-center justify-end gap-4 pb-6">
            <a href="{{ route('admin.users.show', $user) }}" class="px-6 py-2.5 text-sm font-semibold text-gray-600 hover:text-gray-800 uppercase tracking-wider transition-colors">
                Annuler
            </a>
            <button type="submit" class="btn-senelec inline-flex items-center gap-2 px-6 py-2.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection
