@extends('layouts.app')

@section('title', 'Journal d\'activités')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 font-['Rajdhani']">
            <svg class="w-6 h-6 inline mr-2 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Journal d'activités
        </h1>
    </div>

    <!-- Filtres -->
    <div class="card-senelec p-4">
        <form method="GET" action="{{ route('admin.activity-log.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
                <label class="label text-xs">Type</label>
                <select name="log_name" class="select-senelec w-full text-sm">
                    <option value="">Tous</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}" {{ request('log_name') == $name ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label text-xs">Utilisateur</label>
                <input type="text" name="causer" value="{{ request('causer') }}" class="input-senelec w-full text-sm" placeholder="Nom ou matricule">
            </div>
            <div>
                <label class="label text-xs">Date début</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" class="input-senelec w-full text-sm">
            </div>
            <div>
                <label class="label text-xs">Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" class="input-senelec w-full text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn-senelec text-sm px-4 py-2">Filtrer</button>
                <a href="{{ route('admin.activity-log.index') }}" class="btn-senelec-outline text-sm px-4 py-2">Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Tableau -->
    <div class="card-senelec overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sujet</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modifications</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($activities as $activity)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                            {{ $activity->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @switch($activity->log_name)
                                @case('users')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Utilisateurs</span>
                                    @break
                                @case('travaux')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">Travaux</span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($activity->log_name) }}</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">
                            @if($activity->causer)
                                <span class="font-medium">{{ $activity->causer->name ?? 'N/A' }}</span>
                                <br><span class="text-xs text-gray-500">{{ $activity->causer->matricule ?? '' }}</span>
                            @else
                                <span class="text-gray-400">Système</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm whitespace-nowrap">
                            @switch($activity->event)
                                @case('created')
                                    <span class="text-green-600 font-medium">Création</span>
                                    @break
                                @case('updated')
                                    <span class="text-orange-600 font-medium">Modification</span>
                                    @break
                                @case('deleted')
                                    <span class="text-red-600 font-medium">Suppression</span>
                                    @break
                                @default
                                    <span class="text-gray-600">{{ $activity->event ?? $activity->description }}</span>
                            @endswitch
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            @if($activity->subject)
                                @php
                                    $subjectLabel = '';
                                    if ($activity->subject instanceof \App\Models\User) {
                                        $subjectLabel = $activity->subject->name . ' (' . ($activity->subject->matricule ?? '') . ')';
                                    } else {
                                        $subjectLabel = class_basename($activity->subject) . ' #' . $activity->subject->id;
                                    }
                                @endphp
                                {{ $subjectLabel }}
                            @else
                                <span class="text-gray-400">Supprimé</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs">
                            @if($activity->properties && $activity->properties->has('attributes'))
                                <div class="max-w-xs">
                                    @php $old = $activity->properties->get('old', []); @endphp
                                    @foreach($activity->properties->get('attributes', []) as $key => $value)
                                        @php $oldValue = $old[$key] ?? null; @endphp
                                        <div class="mb-1">
                                            <span class="font-medium text-gray-600">{{ $key }}:</span>
                                            @if($oldValue !== null)
                                                <span class="text-red-500 line-through">{{ is_array($oldValue) ? json_encode($oldValue) : Str::limit((string)$oldValue, 30) }}</span>
                                                <span class="mx-1">&rarr;</span>
                                            @endif
                                            <span class="text-green-600">{{ is_array($value) ? json_encode($value) : Str::limit((string)$value, 30) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            Aucune activité enregistrée
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $activities->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
        @endif
    </div>
</div>
@endsection
