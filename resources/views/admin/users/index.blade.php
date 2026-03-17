@extends('layouts.app')

@section('title', 'Gestion des utilisateurs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestion des utilisateurs</h1>
            <p class="mt-1 text-gray-500">{{ $users->total() }} utilisateur(s) trouvé(s)</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center gap-3">
            <a href="{{ route('admin.users.sync.index') }}" class="btn-senelec-outline">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Sync Oracle/LDAP
            </a>
            <a href="{{ route('admin.users.create') }}" class="btn-senelec">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvel utilisateur
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card-senelec p-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Rechercher..." class="input-senelec">
            </div>
            <div>
                <select name="role" class="input-senelec">
                    <option value="">Tous les rôles</option>
                    @foreach($roles as $role)
                        @php
                            $name = $role->name;
                            $label = ucfirst($name);
                            if ($name === 'umt') $label = 'UAG';
                            if ($name === 'unsp') $label = 'UPNS';
                            if ($name === 'ubt') $label = 'UGBT';
                        @endphp
                        <option value="{{ $name }}" {{ request('role') === $name ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="service" class="input-senelec">
                    <option value="">Tous les services</option>
                    @foreach($services as $service)
                        <option value="{{ $service }}" {{ request('service') === $service ? 'selected' : '' }}>
                            {{ $service }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="is_active" class="input-senelec">
                    <option value="">Tous les statuts</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actif</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-senelec flex-1">Filtrer</button>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary px-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec min-w-[900px]">
                <thead>
                    <tr>
                        <th class="min-w-[250px]">Utilisateur</th>
                        <th class="min-w-[100px]">Matricule</th>
                        <th class="min-w-[150px]">Service</th>
                        <th class="min-w-[100px]">Rôles</th>
                        <th class="min-w-[80px]">Statut</th>
                        <th class="text-right min-w-[150px]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center space-x-3">
                                    @if($user->photo_url)
                                        <img src="{{ $user->photo_url }}" 
                                             alt="{{ $user->full_name }}" 
                                             class="w-10 h-10 rounded-full object-cover"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="w-10 h-10 rounded-full bg-senelec-purple flex items-center justify-center text-white font-semibold" style="display:none;">
                                            {{ $user->initials }}
                                        </div>
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-senelec-purple flex items-center justify-center text-white font-semibold">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->full_name ?? $user->name }}</p>
                                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-sm">{{ $user->matricule ?? '-' }}</td>
                            <td>
                                <p class="text-sm text-gray-900">{{ $user->service ?? $user->organisation ?? '-' }}</p>
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        @php
                                            $roleColors = [
                                                'admin' => 'bg-red-100 text-red-700',
                                                'user' => 'bg-blue-100 text-blue-700',
                                            ];
                                        @endphp
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $roleColors[$role->name] ?? 'bg-gray-100 text-gray-700' }}">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($user->is_active ?? true)
                                    <span class="badge-success">Actif</span>
                                @else
                                    <span class="badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex items-center justify-end space-x-1">
                                    {{-- Voir (icône œil) --}}
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" title="Voir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    {{-- Simuler --}}
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.impersonate.start', $user) }}" method="POST" class="inline"
                                              >
                                            @csrf
                                            <button type="submit" 
                                                    class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors"
                                                    title="Simuler">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                    {{-- Modifier : tous les utilisateurs (mode view pour LDAP) --}}
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="p-1.5 text-amber-500 hover:text-amber-700 hover:bg-amber-50 rounded-lg transition-colors" title="Modifier">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    {{-- Supprimer : uniquement pour les utilisateurs locaux (non LDAP) --}}
                                    @if(!$user->ldap_guid && !$user->oracle_person_id)
                                        @if($user->id !== auth()->id())
                                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                                                        title="Supprimer">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">
                                Aucun utilisateur trouvé
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div>
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
