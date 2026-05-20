@props([
    'totalDemandes' => 0,
    'demandesBrouillon' => 0,
    'demandesEnAttente' => 0,
    'demandesAcceptees' => 0,
    'demandesImputees' => 0,
    'demandesValides' => 0,
    'demandesEnCours' => 0,
    'demandesRejetees' => 0,
    'demandesTerminees' => 0,
    'demandesCloturees' => 0,
])

@php
    $currentRoute = \Illuminate\Support\Facades\Route::currentRouteName();
    $prefix = explode('.', $currentRoute)[0] ?? null;
    $baseRoute = null;
    $routesByStatut = [];
    // Pour SAD, SEG et toutes les unités on ne montre pas les compteurs Brouillon / En attente
    $hideDraftWaiting = in_array($prefix, ['sad', 'seg', 'sgb', 'umt', 'ubt', 'unsp', 'umr', 'utgc']);

    // Layout spécifique par rôle (nombre de colonnes)
    // UMT : 3 par ligne, autres rôles : jusqu'à 5 par ligne sur grand écran
    $gridCols = $prefix === 'umt'
        ? 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-4'
        : 'grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4';

    if (str_starts_with($currentRoute, 'demandeur.')) {
        // Demandeur : chaque compteur filtre la liste /demande par statut
        $baseRoute = route('demande.index');
        $routesByStatut = [
            'brouillon'  => route('demande.index', ['statut' => 'brouillon']),
            'en_attente' => route('demande.index', ['statut' => 'en_attente']),
            'accepte'    => route('demande.index', ['statut' => 'accepte']),
            'impute'     => route('demande.index', ['statut' => 'impute']),
            'valide'     => route('demande.index', ['statut' => 'valide']),
            'en_cours'   => route('demande.index', ['statut' => 'en_cours']),
            'rejete'     => route('demande.index', ['statut' => 'rejete']),
            'termine'    => route('demande.index', ['statut' => 'termine']),
            'cloture'    => route('demande.index', ['statut' => 'cloture']),
        ];
    } elseif (str_starts_with($currentRoute, 'approbateur.')) {
        // Approbateur : compteurs cliquables vers les listes filtrées
        $baseRoute = route('demande.demandes_approuvees');
        $routesByStatut = [
            'brouillon'  => route('demande.demandes_approuvees', ['statut' => 'brouillon']),
            'en_attente' => route('demandes.aapprouver'),
            'accepte'    => route('demande.demandes_approuvees', ['statut' => 'accepte']),
            'impute'     => route('demande.demandes_approuvees', ['statut' => 'impute']),
            'valide'     => route('demande.demandes_approuvees', ['statut' => 'valide']),
            'en_cours'   => route('demande.demandes_approuvees', ['statut' => 'en_cours']),
            'termine'    => route('demande.demandes_approuvees', ['statut' => 'termine']),
            'cloture'    => route('demande.demandes_approuvees', ['statut' => 'cloture']),
            'rejete'     => route('demande.demandes_rejetes'),
        ];
    } else {
        // SAD, SEG, UMT, UBT, UNSP, UMR, UTGC, Equipe : base sur <prefix>.demandes
        if ($prefix === 'sad') {
            // SAD : tous les compteurs renvoient vers sad.demandes avec le statut correspondant
            if (\Illuminate\Support\Facades\Route::has('sad.demandes')) {
                $baseRoute = route('sad.demandes');
                $routesByStatut = [
                    'accepte'  => route('sad.demandes', ['statut' => 'accepte']),
                    'impute'   => route('sad.demandes', ['statut' => 'impute']),
                    'valide'   => route('sad.demandes', ['statut' => 'valide']),
                    'en_cours' => route('sad.demandes', ['statut' => 'en_cours']),
                    'termine'  => route('sad.demandes', ['statut' => 'termine']),
                    'cloture'  => route('sad.demandes', ['statut' => 'cloture']),
                    'rejete'   => route('sad.demandes', ['statut' => 'rejete']),
                ];
            }
        } elseif ($prefix === 'seg') {
            // SEG : compteurs pointent vers la liste globale filtrée (comme SAD)
            $baseRoute = \Illuminate\Support\Facades\Route::has('seg.demandes') ? route('seg.demandes') : null;
            $routesByStatut = [
                'accepte'  => route('seg.demandes', ['statut' => 'accepte']),
                'impute'   => route('seg.demandes', ['statut' => 'impute']),
                'valide'   => route('seg.demandes', ['statut' => 'valide']),
                'en_cours' => route('seg.demandes', ['statut' => 'en_cours']),
                'termine'  => route('seg.demandes', ['statut' => 'termine']),
                'cloture'  => route('seg.demandes', ['statut' => 'cloture']),
                'rejete'   => route('seg.demandes', ['statut' => 'rejete']),
            ];
        } elseif ($prefix === 'sgb') {
            // SGB : même logique que SAD/SEG
            $baseRoute = \Illuminate\Support\Facades\Route::has('sgb.demandes') ? route('sgb.demandes') : null;
            $routesByStatut = [
                'accepte'  => route('sgb.demandes', ['statut' => 'accepte']),
                'impute'   => route('sgb.demandes', ['statut' => 'impute']),
                'valide'   => route('sgb.demandes', ['statut' => 'valide']),
                'en_cours' => route('sgb.demandes', ['statut' => 'en_cours']),
                'termine'  => route('sgb.demandes', ['statut' => 'termine']),
                'cloture'  => route('sgb.demandes', ['statut' => 'cloture']),
                'rejete'   => route('sgb.demandes', ['statut' => 'rejete']),
            ];
        } elseif ($prefix === 'umt') {
            // UMT : règles spécifiques
            // - Total & Imputées ➜ demandes reçues (statut impute)
            // - Validées        ➜ umt.demandes.validees
            // - En cours        ➜ umt.demandes.debutees
            // - Rejetées        ➜ umt.demandes.rejetees
            // - Terminées       ➜ umt.demandes.terminees
            // - Clôturées       ➜ umt.demandes.cloturees
            $baseRoute = \Illuminate\Support\Facades\Route::has('umt.demandes.recues')
                ? route('umt.demandes.recues')
                : null;
            $routesByStatut = [
                'impute'   => route('umt.demandes.recues'),
                'valide'   => route('umt.demandes.validees'),
                'en_cours' => route('umt.demandes.debutees'),
                'rejete'   => route('umt.demandes.rejetees'),
                'termine'  => route('umt.demandes.terminees'),
                'cloture'  => route('umt.demandes.cloturees'),
            ];
        } elseif (in_array($prefix, ['ubt', 'unsp', 'umr', 'utgc'])) {
            // UBT / UNSP / UMR / UTGC : même logique que UMT pour les redirections
            $baseRoute = \Illuminate\Support\Facades\Route::has($prefix . '.demandes.recues')
                ? route($prefix . '.demandes.recues')
                : null;
            $routesByStatut = [
                'impute'   => route($prefix . '.demandes.recues'),
                'valide'   => route($prefix . '.demandes.validees'),
                'en_cours' => route($prefix . '.demandes.debutees'),
                'termine'  => route($prefix . '.demandes.terminees'),
                'cloture'  => route($prefix . '.demandes.cloturees'),
            ];
        } elseif (in_array($prefix, ['seg', 'equipe'])) {
            $routeName = $prefix . '.demandes';
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                $baseRoute = route($routeName);
            }
        }
    }

    $linkFor = function (?string $statutKey = null) use ($routesByStatut, $baseRoute, $prefix) {
        // Pour les unités (UMT, UBT, UNSP, UMR, UTGC), on ne rend plus la carte "Rejetées"
        if ($statutKey === 'rejete' && in_array($prefix, ['umt', 'ubt', 'unsp', 'umr', 'utgc'])) {
            return null;
        }
        if ($statutKey && isset($routesByStatut[$statutKey])) {
            return $routesByStatut[$statutKey];
        }
        return $baseRoute;
    };
@endphp

<div class="{{ $gridCols }}">
    {{-- Carte Total : pas affichée pour les unités (UMT, UBT, UNSP, UMR, UTGC) --}}
    @if(!in_array($prefix, ['umt', 'ubt', 'unsp', 'umr', 'utgc']))
        @php $link = $linkFor(); @endphp
        @if($link)
            <a href="{{ $link }}" class="block">
                <div class="stat-card-purple">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="stat-label">Total</p>
                            <p class="stat-value">{{ $totalDemandes }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-senelec-purple/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                    </div>
                </div>
            </a>
        @else
        <div class="stat-card-purple">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Total</p>
                    <p class="stat-value">{{ $totalDemandes }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-senelec-purple/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
            </div>
        </div>
        @endif
    @endif

    @if(!$hideDraftWaiting)
        @php $link = $linkFor('brouillon'); @endphp
        @if($link)
            <a href="{{ $link }}" class="block">
                <div class="stat-card-gray">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="stat-label">Brouillon</p>
                            <p class="stat-value">{{ $demandesBrouillon }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                    </div>
                </div>
            </a>
        @else
        <div class="stat-card-gray">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Brouillon</p>
                    <p class="stat-value">{{ $demandesBrouillon }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
        </div>
        @endif

        @php $link = $linkFor('en_attente'); @endphp
        @if($link)
            <a href="{{ $link }}" class="block">
                <div class="stat-card-orange">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="stat-label">En attente</p>
                            <p class="stat-value">{{ $demandesEnAttente }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-senelec-orange/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-senelec-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </a>
        @else
        <div class="stat-card-orange">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">En attente</p>
                    <p class="stat-value">{{ $demandesEnAttente }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-senelec-orange/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-senelec-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- Carte "Acceptées" : affichée partout sauf pour les unités où elle n'est pas utile --}}
    @if(!in_array($prefix, ['umt', 'ubt', 'unsp', 'umr', 'utgc']))
        @php $link = $linkFor('accepte'); @endphp
        @if($link)
            <a href="{{ $link }}" class="block">
                <div class="stat-card-teal">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="stat-label">Acceptées</p>
                            <p class="stat-value">{{ $demandesAcceptees }}</p>
                        </div>
                        <div class="w-10 h-10 rounded-lg bg-senelec-teal/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-senelec-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>
            </a>
        @else
        <div class="stat-card-teal">
            <div class="flex items-center justify-between">
                <div>
                    <p class="stat-label">Acceptées</p>
                    <p class="stat-value">{{ $demandesAcceptees }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-senelec-teal/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-senelec-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
        @endif
    @endif

    @php $link = $linkFor('impute'); @endphp
    @if($link)
        <a href="{{ $link }}" class="block">
            <div class="stat-card-magenta">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Imputées</p>
                        <p class="stat-value">{{ $demandesImputees }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-senelec-magenta/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-senelec-magenta" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </div>
        </a>
    @else
    <div class="stat-card-magenta">
        <div class="flex items-center justify-between">
            <div>
                <p class="stat-label">Imputées</p>
                <p class="stat-value">{{ $demandesImputees }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-senelec-magenta/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-senelec-magenta" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/></svg>
            </div>
        </div>
    </div>
    @endif

    @php $link = $linkFor('valide'); @endphp
    @if($link)
        <a href="{{ $link }}" class="block">
            <div class="stat-card-green">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Validées</p>
                        <p class="stat-value">{{ $demandesValides }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                </div>
            </div>
        </a>
    @else
    <div class="stat-card-green">
        <div class="flex items-center justify-between">
            <div>
                <p class="stat-label">Validées</p>
                <p class="stat-value">{{ $demandesValides }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
        </div>
    </div>
    @endif

    @php $link = $linkFor('en_cours'); @endphp
    @if($link)
        <a href="{{ $link }}" class="block">
            <div class="stat-card-blue">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">En cours</p>
                        <p class="stat-value">{{ $demandesEnCours }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-senelec-blue/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-senelec-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                    </div>
                </div>
            </div>
        </a>
    @else
    <div class="stat-card-blue">
        <div class="flex items-center justify-between">
            <div>
                <p class="stat-label">En cours</p>
                <p class="stat-value">{{ $demandesEnCours }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-senelec-blue/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-senelec-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
            </div>
        </div>
    </div>
    @endif

    @php $link = $linkFor('rejete'); @endphp
    @if($link)
        <a href="{{ $link }}" class="block">
            <div class="stat-card-red">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Rejetées</p>
                        <p class="stat-value">{{ $demandesRejetees }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </div>
            </div>
        </a>
    @endif

    @php $link = $linkFor('termine'); @endphp
    @if($link)
        <a href="{{ $link }}" class="block">
            <div class="stat-card-emerald">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Terminées</p>
                        <p class="stat-value">{{ $demandesTerminees }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                </div>
            </div>
        </a>
    @else
    <div class="stat-card-emerald">
        <div class="flex items-center justify-between">
            <div>
                <p class="stat-label">Terminées</p>
                <p class="stat-value">{{ $demandesTerminees }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
            </div>
        </div>
    </div>
    @endif

    @php $link = $linkFor('cloture'); @endphp
    @if($link)
        <a href="{{ $link }}" class="block">
            <div class="stat-card-purple">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-label">Clôturées</p>
                        <p class="stat-value">{{ $demandesCloturees }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-senelec-purple/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                </div>
            </div>
        </a>
    @else
    <div class="stat-card-purple">
        <div class="flex items-center justify-between">
            <div>
                <p class="stat-label">Clôturées</p>
                <p class="stat-value">{{ $demandesCloturees }}</p>
            </div>
            <div class="w-10 h-10 rounded-lg bg-senelec-purple/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-senelec-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>
    </div>
    @endif
</div>
