<div class="flex grow flex-col gap-y-5 overflow-y-auto px-6 pb-4 scrollbar-sidebar" style="background-color: #2B1444;">
    @php
        $user = auth()->user();
    @endphp

    <!-- Logo -->
    <div class="flex h-20 shrink-0 items-center justify-center border-b border-white/10">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <img src="{{ asset('img/logo.png') }}" alt="SENTRAVAUX" class="w-12 h-12 object-contain">
            <div class="text-white">
                <span class="text-lg font-bold font-['Rajdhani'] tracking-wide">SENTRAVAUX</span>
                <p class="text-xs text-white/60">Gestion des Travaux</p>
            </div>
        </a>
    </div>
    
    <!-- Navigation -->
    <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">

            <!-- Administration -->
            @if($user->hasRole('admin'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">Administration</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="{{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                            </svg>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.demandes.index') }}" 
                           class="{{ request()->routeIs('admin.demandes.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Demandes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.sites.index') }}" 
                           class="{{ request()->routeIs('admin.sites.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Sites
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.services.index') }}" 
                           class="{{ request()->routeIs('admin.services.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Services
                        </a>
                    </li>   
                    <li>
                        <a href="{{ route('admin.users.index') }}" 
                           class="{{ request()->routeIs('admin.users.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Utilisateurs
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('equipe.index') }}" 
                           class="{{ request()->routeIs('equipe.index') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M9 20H4v-2a3 3 0 015.356-1.857M12 7a4 4 0 110-8 4 4 0 010 8zM4 22h16"/>
                            </svg>
                            Équipes
                        </a>
                    </li>
                                     
                    {{-- <li>
                        <a href="{{ route('admin.roles.index') }}" 
                           class="{{ request()->routeIs('admin.roles.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            Rôles & Permissions
                        </a>
                    </li> --}}
                    <li>
                        <a href="{{ route('admin.activity-log.index') }}" 
                           class="{{ request()->routeIs('admin.activity-log.*') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Journal d'activités
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ============================================
                 DEMANDEUR
                 ============================================ --}}
            @if($user->hasRole('demandeur'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">Demandeur</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('demandeur.dashboard') }}" class="{{ request()->routeIs('demandeur.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('demande.index') }}" class="{{ request()->routeIs('demande.index') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Mes demandes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('demande.create') }}" class="{{ request()->routeIs('demande.create') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Nouvelle demande
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ============================================
                 APPROBATEUR
                 ============================================ --}}
            @if($user->hasRole('approbateur'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">Approbateur</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('approbateur.dashboard') }}" class="{{ request()->routeIs('approbateur.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('demandes.aapprouver') }}" class="{{ request()->routeIs('demandes.aapprouver') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            À approuver
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('demande.demandes_approuvees') }}" class="{{ request()->routeIs('demande.demandes_approuvees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Approuvées
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('demande.demandes_rejetes') }}" class="{{ request()->routeIs('demande.demandes_rejetes') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Rejetées
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ============================================
                 DAGE
                 ============================================ --}}
            @if($user->hasRole('dage'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">DAGE</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('dage.dashboard') }}" class="{{ request()->routeIs('dage.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            Statistiques
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ============================================
                 SAD (Service Administratif)
                 ============================================ --}}
            @if($user->hasRole('sad'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">SAD</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('sad.dashboard') }}" class="{{ request()->routeIs('sad.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Tableau de bord
                        </a>
                    </li>
                    
                    
                    <li>
                        <a href="{{ route('sad.demandes') }}" class="{{ request()->routeIs('sad.demandes') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Toutes les demandes
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ============================================
                 SEG (Service Entretien Général)
                 ============================================ --}}
            @if($user->hasRole('seg'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">SEG</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('seg.dashboard') }}" class="{{ request()->routeIs('seg.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('seg.demandes') }}" class="{{ request()->routeIs('seg.demandes') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Toutes les demandes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('seg.periodes_attente') }}" class="{{ request()->routeIs('seg.periodes_attente') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Périodes en attente
                        </a>
                    </li>
                    
                </ul>
            </li>
            @endif

            {{-- ============================================
                 UNITÉS (UMT, UBT, UNSP, UMR, UTGC)
                 ============================================ --}}
            @foreach(['umt' => 'UMT', 'ubt' => 'UBT', 'unsp' => 'UNSP', 'umr' => 'UMR', 'utgc' => 'UTGC'] as $role => $label)
            @if($user->hasRole($role))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">{{ $label }}</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route($role . '.dashboard') }}" class="{{ request()->routeIs($role . '.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route($role . '.demandes.recues') }}" class="{{ request()->routeIs($role . '.demandes.recues') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            Demandes reçues
                        </a>
                    </li>
                    <li>
                        <a href="{{ route($role . '.demandes.validees') }}" class="{{ request()->routeIs($role . '.demandes.validees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Validées
                        </a>
                    </li>
                    <li>
                        <a href="{{ route($role . '.demandes.debutees') }}" class="{{ request()->routeIs($role . '.demandes.debutees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Travaux débutés
                        </a>
                    </li>
                    <li>
                        <a href="{{ route($role . '.demandes.terminees') }}" class="{{ request()->routeIs($role . '.demandes.terminees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Terminées
                        </a>
                    </li>
                    <li>
                        <a href="{{ route($role . '.demandes.cloturees') }}" class="{{ request()->routeIs($role . '.demandes.cloturees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            Clôturées
                        </a>
                    </li>
                    {{-- Lien "Rejetées" retiré pour toutes les unités --}}
                </ul>
            </li>
            @endif
            @endforeach

            {{-- ============================================
                 CHEF D'EQUIPE
                 ============================================ --}}
            @if($user->hasRole('equipe'))
            <li>
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">Chef d'équipe</div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('equipe.dashboard') }}" class="{{ request()->routeIs('equipe.dashboard') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Tableau de bord
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('equipe.demandes.recues') }}" class="{{ request()->routeIs('equipe.demandes','equipe.demandes.recues') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            Demandes reçues
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('equipe.demandes.a_traiter') }}" class="{{ request()->routeIs('equipe.demandes.a_traiter') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Demandes à traiter
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('equipe.demandes.debutees') }}" class="{{ request()->routeIs('equipe.demandes.debutees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Travaux débutés
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('equipe.demandes.terminees') }}" class="{{ request()->routeIs('equipe.demandes.terminees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Terminées
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('equipe.demandes.cloturees') }}" class="{{ request()->routeIs('equipe.demandes.cloturees') ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            Clôturées
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            {{-- ============================================
                 SECTION UTILISATEUR (tous les rôles)
                 ============================================ --}}
            <li class="mt-auto pt-4 border-t border-white/10 space-y-3">
                <div class="text-xs font-semibold leading-6 text-white/40 uppercase tracking-wider mb-2">
                    Navigation
                </div>
                <ul role="list" class="-mx-2 space-y-1">
                    <li>
                        <a href="{{ route('documentation') }}" 
                           class="flex items-center justify-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white transition-all duration-200 hover:opacity-90"
                           style="background-color: #e67e22;">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Documentation
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Déconnexion -->
            <li class="space-y-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-3 px-3 py-2.5 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 hover:bg-red-500/20 hover:border-red-500/50 transition-all duration-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="font-medium">Déconnexion</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>
</div>
