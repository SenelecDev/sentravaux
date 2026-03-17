@extends('layouts.app')

@section('title', 'Service : ' . $service)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.services.index') }}" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $service }}</h1>
            <p class="mt-1 text-gray-500">{{ $users->total() }} utilisateur(s) dans ce service</p>
        </div>
    </div>

    <!-- Table -->
    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec">
                <thead>
                    <tr>
                        <th class="min-w-[250px]">Utilisateur</th>
                        <th>Matricule</th>
                        <th>Rôles</th>
                        <th>Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <div class="flex items-center space-x-3">
                                    @if($user->photo_url)
                                        <img src="{{ $user->photo_url }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $user->full_name }}">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-senelec-purple flex items-center justify-center text-white font-semibold">
                                            {{ $user->initials }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->full_name }}</p>
                                        <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="font-mono text-sm">{{ $user->matricule ?? '-' }}</td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($user->roles as $role)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                                            {{ ucfirst($role->name) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge-success">Actif</span>
                                @else
                                    <span class="badge-danger">Inactif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors inline-block" title="Voir">
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
                                Aucun utilisateur dans ce service
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
