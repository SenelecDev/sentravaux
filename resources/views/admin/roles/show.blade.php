@extends('layouts.app')

@section('title', 'Rôle : ' . ucfirst($role->name))

@section('content')
<div class="space-y-6">
    <!-- Retour + Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.roles.index') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 capitalize">Rôle : {{ $role->name }}</h1>
            <p class="mt-1 text-gray-500">{{ $users->total() }} utilisateur(s) avec ce rôle</p>
        </div>
    </div>

    <!-- Permissions du rôle -->
    <div class="card-senelec p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Permissions du rôle</h2>
        <form action="{{ route('admin.roles.update', $role) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="flex flex-wrap gap-3 mb-4">
                @foreach($allPermissions as $permission)
                    <label class="flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition-colors
                        {{ $role->hasPermissionTo($permission->name) ? 'bg-purple-50 border-purple-300' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                            {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                            class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                        <span class="text-sm">{{ $permission->name }}</span>
                    </label>
                @endforeach
            </div>
            @if($allPermissions->count())
                <button type="submit" class="btn-senelec">Mettre à jour les permissions</button>
            @else
                <p class="text-gray-500 text-sm">Aucune permission définie dans le système.</p>
            @endif
        </form>

        @if(session('success'))
            <div class="mt-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif
    </div>

    <!-- Utilisateurs -->
    <div class="card-senelec overflow-hidden">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Utilisateurs avec le rôle "{{ $role->name }}"</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-senelec">
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Matricule</th>
                        <th>Email</th>
                        <th>Service</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-xs font-bold text-purple-700">
                                        {{ strtoupper(substr($user->prenom ?? '', 0, 1) . substr($user->nom ?? '', 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $user->prenom }} {{ $user->nom }}</span>
                                </div>
                            </td>
                            <td class="font-mono text-sm text-gray-600">{{ $user->matricule ?? '—' }}</td>
                            <td class="text-sm text-gray-600">{{ $user->email ?? '—' }}</td>
                            <td class="text-sm text-gray-600">{{ $user->service ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors inline-block" title="Voir l'utilisateur">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">
                                Aucun utilisateur avec ce rôle
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
    <div>
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
