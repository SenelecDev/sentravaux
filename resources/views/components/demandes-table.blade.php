@props([
    'demandes',
    'showUser' => true,
    'showApprobateur' => false,
    'showActions' => true,
    'editRoute' => null,
    'showRoute' => 'demande.show',
    'emptyMessage' => 'Aucune demande trouvée',
    'showNature' => true,
    'showSite' => true,
    'actionsSlot' => null,
])

@php
    $currentUser = auth()->user();
    $isUnitRole = $currentUser && $currentUser->hasAnyRole(['umt', 'ubt', 'unsp', 'umr', 'utgc', 'equipe']);
@endphp

<div class="card-senelec overflow-hidden">
    <div class="overflow-x-auto">
        <table class="table-senelec min-w-[900px]">
            <thead>
                <tr>
                    <th class="min-w-[120px]">N° Demande</th>
                    @if($showUser)
                    <th class="min-w-[180px]">Demandeur</th>
                    @endif
                    <th class="min-w-[200px]">Objet</th>
                    @if($showNature)
                    <th class="min-w-[130px]">Nature</th>
                    @endif
                    @if($showSite)
                    <th class="min-w-[100px]">Site</th>
                    @endif
                    <th class="min-w-[100px]">Date</th>
                    <th class="min-w-[100px] text-right">Statut</th>
                    @if($showActions)
                    <th class="text-right min-w-[100px]">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                    <tr class="cursor-pointer hover:bg-gray-50 transition-colors" onclick="window.location='{{ route($showRoute, $demande) }}'">
                        <td>
                            <span class="font-mono text-sm font-semibold text-senelec-purple">
                                {{ $demande->numero_demande }}
                            </span>
                        </td>
                        @if($showUser)
                        <td>
                            <div class="flex items-center gap-2">
                                @if($demande->user && ($demande->user->photo_url ?? $demande->user->photo ?? false))
                                    @php
                                        $photoSrc = $demande->user->photo_url ?? asset('storage/' . $demande->user->photo);
                                    @endphp
                                    <img src="{{ $photoSrc }}" alt="{{ $demande->user->name }}" class="w-8 h-8 rounded-full object-cover border border-gray-300 shrink-0">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-senelec-purple flex items-center justify-center text-white text-xs font-semibold shrink-0">
                                        {{ $demande->user ? strtoupper(substr($demande->user->name ?? '', 0, 2)) : '?' }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $demande->user->name ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $demande->user->service ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        @endif
                        <td>
                            <p class="text-sm text-gray-900 line-clamp-2">{{ $demande->objet ?? '-' }}</p>
                        </td>
                        @if($showNature)
                        <td>
                            <span class="text-sm text-gray-700">{{ $demande->nature ?? '-' }}</span>
                        </td>
                        @endif
                        @if($showSite)
                        <td>
                            <span class="text-sm text-gray-700">{{ $demande->site->libelle ?? '-' }}</span>
                        </td>
                        @endif
                        <td>
                            <span class="text-sm text-gray-500">{{ $demande->created_at?->format('d/m/Y') }}</span>
                        </td>
                        <td class="text-right">
                            <x-status-badge :statut="$demande->statut" />
                        </td>
                        @if($showActions)
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                @if($showRoute)
                                <a href="{{ route($showRoute, $demande) }}" onclick="event.stopPropagation()" 
                                   class="p-1.5 text-purple-500 hover:text-purple-700 hover:bg-purple-50 rounded-lg transition-colors" title="Voir">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                @endif

                                {{-- Icône PDF pour les demandes clôturées --}}
                                @if(strtolower($demande->statut) === 'cloture')
                                    <a href="{{ route('demande.pdf', $demande) }}" onclick="event.stopPropagation()"
                                       class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors"
                                       title="Ouvrir le PDF" target="_blank">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M7 2h8l5 5v11a2 2 0 01-2 2H7a2 2 0 01-2-2V4a2 2 0 012-2z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 11v6m0 0l-2-2m2 2l2-2M9 7h6"/>
                                        </svg>
                                    </a>
                                @endif

                                @if($editRoute)
                                    @php
                                        $statutLower = strtolower($demande->statut ?? '');
                                        $isEquipeUser = $currentUser && $currentUser->hasRole('equipe');
                                    @endphp
                                    @if($isUnitRole && $statutLower !== 'cloture' && !($isEquipeUser && $statutLower === 'termine'))
                                        {{-- Icône de traitement pour les unités (UMT, UBT, UNSP, UMR, UTGC, Équipe) --}}
                                        <a href="{{ route($editRoute, $demande) }}" onclick="event.stopPropagation()"
                                           class="p-1.5 text-white bg-senelec-purple hover:bg-senelec-purple/90 rounded-lg transition-colors"
                                           title="Traiter la demande">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M3 10l6 6-2 5 5-2 6-6-7-7L3 10z"/>
                                            </svg>
                                        </a>
                                    @elseif(in_array(strtolower($demande->statut), ['brouillon', 'rejete']))
                                        {{-- Boutons classiques Modifier / Supprimer pour le demandeur --}}
                                        <a href="{{ route($editRoute, $demande) }}" onclick="event.stopPropagation()" 
                                           class="p-1.5 text-blue-500 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors" title="Modifier">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('demande.destroy', $demande) }}" method="POST" class="inline" onsubmit="event.stopPropagation(); return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                {{ $actionsSlot ?? '' }}
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
