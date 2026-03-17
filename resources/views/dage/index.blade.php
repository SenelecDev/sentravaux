@extends('layouts.app')

@section('title', 'Utilisateurs DAGE')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Utilisateurs DAGE</h1>
        <p class="mt-1 text-gray-500">{{ count($dages) }} utilisateur(s) avec le rôle DAGE</p>
    </div>

    <div class="card-senelec overflow-hidden">
        <table class="table-senelec">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Matricule</th>
                    <th>Service</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dages as $user)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-senelec-gradient flex items-center justify-center text-white text-sm font-medium">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-900">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email ?? '-' }}</td>
                    <td>{{ $user->matricule ?? '-' }}</td>
                    <td>{{ $user->service ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-gray-500 py-8">Aucun utilisateur DAGE trouvé.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
