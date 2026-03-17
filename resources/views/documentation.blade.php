@extends('layouts.app')

@section('title', 'Documentation SENTRAVAUX')

@section('content')
<div class="max-w-5xl mx-auto space-y-8" x-data="{ activeSection: null }">

    <!-- Hero Banner -->
    <div class="rounded-2xl overflow-hidden text-white p-8 md:p-10" style="background: linear-gradient(135deg, #2B1444 0%, #4a1d6e 50%, #B3006C 100%);">
        <div class="flex items-center gap-5">
            <div class="p-4 rounded-xl bg-white/15">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <h1 class="text-3xl font-bold">Documentation SENTRAVAUX</h1>
                <p class="text-white/70 mt-1">Guide complet pour la gestion des Demandes de Travaux</p>
            </div>
        </div>
    </div>

    <!-- Table des matières -->
    <div class="card-senelec !p-8">
        <div class="flex items-center gap-2 mb-6">
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
            <h2 class="text-xl font-bold text-gray-900">Table des matières</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="#introduction" class="flex items-center gap-4 pl-6 pr-5 py-4 mb-2 rounded-xl text-white font-medium transition-all hover:opacity-90 hover:shadow-lg" style="background-color: #2B1444;">
                <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-sm font-bold" style="background-color: #4f46e5;">1</span>
                Introduction
            </a>
            <a href="#workflow" class="flex items-center gap-4 pl-6 pr-5 py-4 mb-2 rounded-xl text-white font-medium transition-all hover:opacity-90 hover:shadow-lg" style="background-color: #4f46e5;">
                <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-sm font-bold" style="background-color: #7c3aed;">2</span>
                Workflow Général
            </a>
            <a href="#roles" class="flex items-center gap-4 pl-6 pr-5 py-4 mb-2 rounded-xl text-white font-medium transition-all hover:opacity-90 hover:shadow-lg" style="background-color: #B3006C;">
                <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-sm font-bold" style="background-color: #e11d48;">3</span>
                Rôles et Responsabilités
            </a>
            <a href="#creer-demande" class="flex items-center gap-4 pl-6 pr-5 py-4 mb-2 rounded-xl text-white font-medium transition-all hover:opacity-90 hover:shadow-lg" style="background-color: #0f766e;">
                <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-sm font-bold" style="background-color: #059669;">4</span>
                Créer une Demande
            </a>
            <a href="#traitement" class="flex items-center gap-4 pl-6 pr-5 py-4 mb-2 rounded-xl text-white font-medium transition-all hover:opacity-90 hover:shadow-lg" style="background-color: #1d4ed8;">
                <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-sm font-bold" style="background-color: #2563eb;">5</span>
                Traitement des Demandes
            </a>
            <a href="#faq" class="flex items-center gap-4 pl-6 pr-5 py-4 mb-2 rounded-xl text-white font-medium transition-all hover:opacity-90 hover:shadow-lg" style="background-color: #e67e22;">
                <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-sm font-bold" style="background-color: #d97706;">6</span>
                FAQ
            </a>
        </div>
    </div>

    <!-- 1. Introduction -->
    <div id="introduction" class="card-senelec !p-8 scroll-mt-6">
        <div class="flex items-center gap-4 mb-5">
            <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-white font-bold" style="background-color: #4f46e5;">1</span>
            <h2 class="text-2xl font-bold text-gray-900">Introduction</h2>
        </div>
        <div class="prose max-w-none text-gray-700 space-y-4">
            <p>
                <strong style="color: #2B1444;">SENTRAVAUX</strong> est le système de gestion électronique des
                <strong>Demandes de Travaux</strong> de <strong>SENELEC</strong>.
            </p>
            <div class="p-4 rounded-lg border-l-4" style="background-color: #eff6ff; border-color: #3b82f6;">
                <p class="font-medium" style="color: #1d4ed8;">Objectifs de SENTRAVAUX</p>
                <ul class="mt-2 space-y-1 text-sm">
                    <li>Dématérialiser le processus de demande de travaux</li>
                    <li>Assurer la traçabilité complète (de la demande à la clôture)</li>
                    <li>Accélérer les validations grâce à un circuit électronique</li>
                    <li>Permettre le suivi en temps réel de l'avancement des travaux</li>
                    <li>Générer des statistiques et rapports pour le pilotage</li>
                </ul>
            </div>
            <div class="p-4 rounded-lg border-l-4" style="background-color: #f0fdf4; border-color: #22c55e;">
                <p class="font-medium" style="color: #15803d;">Connexion</p>
                <p class="text-sm mt-1">Connectez-vous avec votre <strong>matricule SENELEC</strong> et votre mot de passe. L'authentification utilise le répertoire LDAP de l'entreprise. Si vous êtes un nouvel utilisateur, le rôle <strong>Demandeur</strong> vous est automatiquement attribué.</p>
            </div>
        </div>
    </div>

    <!-- 2. Workflow Général -->
    <div id="workflow" class="card-senelec !p-8 scroll-mt-6">
        <div class="flex items-center gap-4 mb-5">
            <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-white font-bold" style="background-color: #7c3aed;">2</span>
            <h2 class="text-2xl font-bold text-gray-900">Workflow Général</h2>
        </div>
        <div class="space-y-4">
            <p class="text-gray-700">Voici le circuit de traitement d'une demande de travaux dans SENTRAVAUX :</p>

            <!-- Workflow Steps -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background-color: #fffbeb; border: 1px solid #fcd34d;">
                    <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background-color: #f59e0b;">1</span>
                    <div>
                        <p class="font-semibold text-gray-900">Création de la demande</p>
                        <p class="text-sm text-gray-600">Le <strong>Demandeur</strong> soumet sa demande en décrivant les travaux nécessaires, la localisation et l'urgence.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background-color: #eff6ff; border: 1px solid #93c5fd;">
                    <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background-color: #3b82f6;">2</span>
                    <div>
                        <p class="font-semibold text-gray-900">Approbation hiérarchique</p>
                        <p class="text-sm text-gray-600">L'<strong>Approbateur</strong> (responsable hiérarchique) examine et approuve ou rejette la demande.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background-color: #faf5ff; border: 1px solid #d8b4fe;">
                    <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background-color: #8b5cf6;">3</span>
                    <div>
                        <p class="font-semibold text-gray-900">Validation DAGE</p>
                        <p class="text-sm text-gray-600">Le responsable <strong>DAGE</strong> valide la demande après vérification budgétaire et conformité.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background-color: #f0fdfa; border: 1px solid #5eead4;">
                    <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background-color: #14b8a6;">4</span>
                    <div>
                        <p class="font-semibold text-gray-900">Prise en charge SAD</p>
                        <p class="text-sm text-gray-600">Le <strong>Service Administratif (SAD)</strong> réceptionne et affecte la demande au service technique compétent.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background-color: #ecfeff; border: 1px solid #67e8f9;">
                    <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background-color: #06b6d4;">5</span>
                    <div>
                        <p class="font-semibold text-gray-900">Affectation SEG</p>
                        <p class="text-sm text-gray-600">Le <strong>Service Entretien Général (SEG)</strong> planifie et dispatche les travaux aux unités (UMT, UBT, UNSP, UMR, UTGC).</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-4 rounded-xl" style="background-color: #f0fdf4; border: 1px solid #86efac;">
                    <span class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-white text-sm font-bold" style="background-color: #22c55e;">6</span>
                    <div>
                        <p class="font-semibold text-gray-900">Exécution & Clôture</p>
                        <p class="text-sm text-gray-600">L'<strong>équipe technique</strong> exécute les travaux, puis la demande est marquée comme terminée et clôturée.</p>
                    </div>
                </div>
            </div>

            <!-- Workflow diagram -->
            <div class="mt-4 p-4 rounded-lg text-center" style="background-color: #f8fafc;">
                <div class="flex flex-wrap items-center justify-center gap-2 text-sm font-medium">
                    <span class="px-3 py-1.5 rounded-full text-white" style="background-color: #f59e0b;">Demandeur</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-3 py-1.5 rounded-full text-white" style="background-color: #3b82f6;">Approbateur</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-3 py-1.5 rounded-full text-white" style="background-color: #8b5cf6;">DAGE</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-3 py-1.5 rounded-full text-white" style="background-color: #14b8a6;">SAD</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-3 py-1.5 rounded-full text-white" style="background-color: #06b6d4;">SEG</span>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span class="px-3 py-1.5 rounded-full text-white" style="background-color: #22c55e;">Équipe</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Rôles et Responsabilités -->
    <div id="roles" class="card-senelec !p-8 scroll-mt-6">
        <div class="flex items-center gap-4 mb-5">
            <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-white font-bold" style="background-color: #e11d48;">3</span>
            <h2 class="text-2xl font-bold text-gray-900">Rôles et Responsabilités</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @php
                $roles = [
                    ['name' => 'Admin', 'color' => '#b91c1c', 'bg' => '#fef2f2', 'border' => '#fca5a5', 'desc' => 'Accès complet à l\'administration : gestion des utilisateurs, rôles, paramétrage, statistiques globales.'],
                    ['name' => 'Demandeur', 'color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fcd34d', 'desc' => 'Créer des demandes de travaux, suivre leur avancement, recevoir les notifications de statut.'],
                    ['name' => 'Approbateur', 'color' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#93c5fd', 'desc' => 'Approuver ou rejeter les demandes de travaux soumises par les demandeurs de sa direction.'],
                    ['name' => 'DAGE', 'color' => '#7e22ce', 'bg' => '#faf5ff', 'border' => '#d8b4fe', 'desc' => 'Valider les demandes approuvées, consultation des statistiques et rapports.'],
                    ['name' => 'SAD', 'color' => '#0f766e', 'bg' => '#f0fdfa', 'border' => '#5eead4', 'desc' => 'Réceptionner les demandes validées, les affecter au service technique compétent.'],
                    ['name' => 'SEG', 'color' => '#0e7490', 'bg' => '#ecfeff', 'border' => '#67e8f9', 'desc' => 'Planifier les travaux, dispatcher aux unités techniques (UMT, UBT, UNSP, UMR, UTGC).'],
                    ['name' => 'UMT', 'color' => '#15803d', 'bg' => '#f0fdf4', 'border' => '#86efac', 'desc' => 'Unité Maintenance Technique — exécuter les travaux de maintenance assignés.'],
                    ['name' => 'UBT', 'color' => '#4d7c0f', 'bg' => '#f7fee7', 'border' => '#bef264', 'desc' => 'Unité Basse Tension — exécuter les travaux électriques BT.'],
                    ['name' => 'UNSP', 'color' => '#c2410c', 'bg' => '#fff7ed', 'border' => '#fdba74', 'desc' => 'Unité NSP — travaux spécialisés.'],
                    ['name' => 'UMR', 'color' => '#4338ca', 'bg' => '#eef2ff', 'border' => '#a5b4fc', 'desc' => 'Unité Maintenance Réseaux — entretien et réparation des réseaux.'],
                    ['name' => 'UTGC', 'color' => '#be185d', 'bg' => '#fdf2f8', 'border' => '#f9a8d4', 'desc' => 'Unité Travaux Génie Civil — travaux de BTP et génie civil.'],
                    ['name' => 'Équipe', 'color' => '#6d28d9', 'bg' => '#f5f3ff', 'border' => '#c4b5fd', 'desc' => 'Chef d\'équipe — recevoir les demandes affectées, les exécuter et les terminer.'],
                ];
            @endphp

            @foreach($roles as $r)
                <div class="p-4 rounded-xl" style="background-color: {{ $r['bg'] }}; border: 1px solid {{ $r['border'] }};">
                    <p class="font-bold text-sm" style="color: {{ $r['color'] }};">{{ $r['name'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $r['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 4. Créer une Demande -->
    <div id="creer-demande" class="card-senelec !p-8 scroll-mt-6">
        <div class="flex items-center gap-4 mb-5">
            <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-white font-bold" style="background-color: #059669;">4</span>
            <h2 class="text-2xl font-bold text-gray-900">Créer une Demande</h2>
        </div>
        <div class="space-y-5 text-gray-700">
            <p>Pour créer une nouvelle demande de travaux, suivez ces étapes :</p>

            <div class="space-y-4">
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold text-sm" style="background-color: #2B1444;">1</div>
                    <div>
                        <p class="font-semibold text-gray-900">Accéder au formulaire</p>
                        <p class="text-sm">Cliquez sur <strong>"Nouvelle demande"</strong> dans le menu latéral (section Demandeur), ou sur le bouton <strong>"+"</strong> depuis votre tableau de bord.</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold text-sm" style="background-color: #2B1444;">2</div>
                    <div>
                        <p class="font-semibold text-gray-900">Remplir les informations</p>
                        <p class="text-sm">Renseignez les champs obligatoires :</p>
                        <ul class="text-sm mt-1 ml-4 list-disc space-y-0.5">
                            <li><strong>Nature des travaux</strong> — type de travail demandé</li>
                            <li><strong>Description</strong> — détail des travaux à effectuer</li>
                            <li><strong>Localisation / Site</strong> — lieu où les travaux doivent être réalisés</li>
                            <li><strong>Urgence</strong> — niveau de priorité (normale, urgente, très urgente)</li>
                        </ul>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold text-sm" style="background-color: #2B1444;">3</div>
                    <div>
                        <p class="font-semibold text-gray-900">Ajouter des photos (optionnel)</p>
                        <p class="text-sm">Vous pouvez joindre des <strong>photos</strong> pour illustrer le problème ou la zone de travail. Formats acceptés : JPG, PNG (max 5 Mo par image).</p>
                    </div>
                </div>
                <div class="flex gap-4 items-start">
                    <div class="w-10 h-10 rounded-full flex-shrink-0 flex items-center justify-center text-white font-bold text-sm" style="background-color: #2B1444;">4</div>
                    <div>
                        <p class="font-semibold text-gray-900">Soumettre</p>
                        <p class="text-sm">Cliquez sur <strong>"Soumettre la demande"</strong>. Votre demande sera envoyée à votre <strong>Approbateur</strong> pour validation. Vous recevrez une notification à chaque changement de statut.</p>
                    </div>
                </div>
            </div>

            <div class="p-4 rounded-lg border-l-4" style="background-color: #fffbeb; border-color: #f59e0b;">
                <p class="font-medium text-sm" style="color: #b45309;">💡 Astuce</p>
                <p class="text-sm mt-1">Plus votre description est détaillée, plus le traitement sera rapide. N'hésitez pas à préciser les dimensions, matériaux concernés et le contexte.</p>
            </div>
        </div>
    </div>

    <!-- 5. Traitement des Demandes -->
    <div id="traitement" class="card-senelec !p-8 scroll-mt-6">
        <div class="flex items-center gap-4 mb-5">
            <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-white font-bold" style="background-color: #2563eb;">5</span>
            <h2 class="text-2xl font-bold text-gray-900">Traitement des Demandes</h2>
        </div>
        <div class="space-y-5 text-gray-700">
            <p>Chaque demande passe par plusieurs statuts au cours de son cycle de vie :</p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr style="background-color: #f1f5f9;">
                            <th class="text-left p-3 font-semibold text-gray-700 rounded-tl-lg">Statut</th>
                            <th class="text-left p-3 font-semibold text-gray-700">Signification</th>
                            <th class="text-left p-3 font-semibold text-gray-700 rounded-tr-lg">Action requise par</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #f59e0b;">En attente</span></td>
                            <td class="p-3">La demande vient d'être créée</td>
                            <td class="p-3 font-medium">Approbateur</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #3b82f6;">Approuvée</span></td>
                            <td class="p-3">Le supérieur hiérarchique a validé la demande</td>
                            <td class="p-3 font-medium">DAGE</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #8b5cf6;">Validée DAGE</span></td>
                            <td class="p-3">La DAGE a confirmé la demande</td>
                            <td class="p-3 font-medium">SAD</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #14b8a6;">Affectée</span></td>
                            <td class="p-3">La demande est affectée à un service technique</td>
                            <td class="p-3 font-medium">SEG / Unité</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #06b6d4;">En cours</span></td>
                            <td class="p-3">Les travaux sont en cours d'exécution</td>
                            <td class="p-3 font-medium">Équipe technique</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #22c55e;">Terminée</span></td>
                            <td class="p-3">Les travaux sont achevés</td>
                            <td class="p-3 font-medium">SAD (clôture)</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #6b7280;">Clôturée</span></td>
                            <td class="p-3">Dossier finalisé et archivé</td>
                            <td class="p-3 font-medium">—</td>
                        </tr>
                        <tr>
                            <td class="p-3"><span class="px-2.5 py-1 rounded-full text-xs font-medium text-white" style="background-color: #ef4444;">Rejetée</span></td>
                            <td class="p-3">Demande refusée à l'une des étapes</td>
                            <td class="p-3 font-medium">Demandeur (peut modifier)</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 rounded-lg border-l-4" style="background-color: #eff6ff; border-color: #3b82f6;">
                <p class="font-medium text-sm" style="color: #1d4ed8;">🔔 Notifications</p>
                <p class="text-sm mt-1">Vous recevez une notification à chaque changement de statut de vos demandes. Consultez la cloche <strong>🔔</strong> en haut à droite pour voir vos notifications non lues.</p>
            </div>
        </div>
    </div>

    <!-- 6. FAQ -->
    <div id="faq" class="card-senelec !p-8 scroll-mt-6">
        <div class="flex items-center gap-4 mb-5">
            <span class="w-10 h-10 min-w-[2.5rem] rounded-full flex items-center justify-center text-white font-bold" style="background-color: #d97706;">6</span>
            <h2 class="text-2xl font-bold text-gray-900">Questions fréquentes</h2>
        </div>

        <div class="space-y-3">
            @php
                $faqs = [
                    ['q' => 'Comment me connecter ?', 'a' => 'Utilisez votre matricule SENELEC (ex: C00669) et votre mot de passe réseau (LDAP). Si l\'authentification LDAP échoue, le système utilise le mot de passe local.'],
                    ['q' => 'J\'ai oublié mon mot de passe, que faire ?', 'a' => 'Contactez votre administrateur système ou le service informatique pour réinitialiser votre mot de passe LDAP.'],
                    ['q' => 'Puis-je modifier une demande après soumission ?', 'a' => 'Une demande ne peut être modifiée qu\'avant approbation. Si elle a déjà été approuvée, contactez votre approbateur pour la faire rejeter, puis soumettez une nouvelle demande.'],
                    ['q' => 'Pourquoi ma demande met du temps à être traitée ?', 'a' => 'Vérifiez le statut actuel de votre demande dans « Mes demandes ». Chaque demande doit passer par toute la chaîne de validation. En cas de blocage, contactez l\'acteur concerné.'],
                    ['q' => 'Comment ajouter des photos à ma demande ?', 'a' => 'Lors de la création, cliquez sur la zone de téléchargement pour ajouter jusqu\'à 5 photos (JPG ou PNG, max 5 Mo chacune).'],
                    ['q' => 'Je suis approbateur, comment voir les demandes à approuver ?', 'a' => 'Accédez à votre tableau de bord Approbateur via le menu latéral. Les demandes en attente d\'approbation y sont listées.'],
                    ['q' => 'Qui contacter en cas de problème technique ?', 'a' => 'Contactez l\'administrateur SENTRAVAUX via l\'adresse admin@senelec.sn ou le service informatique au poste interne.'],
                ];
            @endphp

            @foreach($faqs as $index => $faq)
                <div class="rounded-xl border border-gray-200 overflow-hidden" x-data="{ open: false }">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 transition-colors">
                        <span class="font-medium text-gray-900">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-collapse class="px-4 pb-4 text-sm text-gray-600">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Back to top -->
    <div class="text-center pb-6">
        <a href="#" class="inline-flex items-center gap-2 text-sm font-medium transition-colors hover:opacity-80" style="color: #B3006C;">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
            </svg>
            Retour en haut
        </a>
    </div>
</div>
@endsection
