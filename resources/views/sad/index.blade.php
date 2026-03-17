@extends('layouts.app')

@section('title', 'SAD')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">SAD</h1>
        <p class="mt-1 text-gray-500">{{ count($sads) }} utilisateur(s) SAD</p>
    </div>
    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-senelec">
                <thead><tr><th>Utilisateur</th><th>Service</th><th>Email</th></tr></thead>
                <tbody>
                    @forelse($sads as $sad)
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold">
                                    {{ strtoupper(substr($sad->name, 0, 2)) }}
                                </div>
                                <span class="font-medium">{{ $sad->name }}</span>
                            </div>
                        </td>
                        <td>{{ $sad->service ?? '-' }}</td>
                        <td>{{ $sad->email }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center py-8 text-gray-500">Aucun utilisateur SAD trouvé</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
