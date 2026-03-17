<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Demande N° {{ $demande->numero_demande }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #333; line-height: 1.4; }
        .page { padding: 15px 20px; }

        /* Header */
        .header { display: table; width: 100%; margin-bottom: 10px; border-bottom: 2px solid #e30613; padding-bottom: 8px; }
        .header-left { display: table-cell; width: 30%; vertical-align: middle; }
        .header-center { display: table-cell; width: 40%; text-align: center; vertical-align: middle; }
        .header-right { display: table-cell; width: 30%; text-align: right; vertical-align: middle; }
        .logo { max-height: 40px; }
        .titre { font-size: 16px; font-weight: bold; color: #e30613; }
        .sous-titre { font-size: 9px; color: #666; margin-top: 2px; }
        .numero { font-size: 12px; font-weight: bold; color: #333; }

        /* Info table */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 4px 8px; vertical-align: top; border: 1px solid #ddd; }
        .info-table .label { background-color: #f8f8f8; font-weight: bold; color: #555; width: 22%; font-size: 9px; text-transform: uppercase; }
        .info-table .value { font-size: 10px; }

        /* Section */
        .section-title { background-color: #e30613; color: white; padding: 4px 10px; font-size: 10px; font-weight: bold; margin: 8px 0 4px 0; text-transform: uppercase; }

        /* Badge statut */
        .badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 9px; font-weight: bold; text-transform: uppercase; }
        .badge-cloture { background-color: #22c55e; color: white; }
        .badge-termine { background-color: #10b981; color: white; }
        .badge-en_cours { background-color: #a855f7; color: white; }
        .badge-valide { background-color: #3b82f6; color: white; }
        .badge-impute { background-color: #6366f1; color: white; }
        .badge-accepte { background-color: #14b8a6; color: white; }
        .badge-en_attente { background-color: #f97316; color: white; }
        .badge-rejete { background-color: #ef4444; color: white; }
        .badge-brouillon { background-color: #9ca3af; color: white; }

        /* Equipe table */
        .equipe-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .equipe-table th { background-color: #f3f4f6; padding: 4px 6px; font-size: 9px; text-align: left; border: 1px solid #ddd; color: #555; text-transform: uppercase; }
        .equipe-table td { padding: 4px 6px; font-size: 10px; border: 1px solid #ddd; }

        /* Signatures */
        .signatures { display: table; width: 100%; margin-top: 15px; }
        .signature-box { display: table-cell; width: 33.33%; padding: 5px 10px; text-align: center; vertical-align: top; border: 1px solid #ddd; }
        .signature-title { font-size: 9px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 3px; }
        .signature-name { font-size: 10px; font-weight: bold; color: #333; margin-bottom: 6px; }
        .signature-img { max-height: 50px; max-width: 100px; margin: 3px auto; display: block; }
        .stamp-img { max-height: 45px; max-width: 80px; margin: 3px auto; display: block; opacity: 0.7; }
        .signature-date { font-size: 8px; color: #888; margin-top: 4px; }

        /* Footer */
        .footer { position: fixed; bottom: 10px; left: 20px; right: 20px; border-top: 1px solid #ddd; padding-top: 5px; font-size: 8px; color: #999; display: table; width: 100%; }
        .footer-left { display: table-cell; width: 50%; text-align: left; }
        .footer-right { display: table-cell; width: 50%; text-align: right; }

        /* Two-col layout */
        .two-col { display: table; width: 100%; }
        .col-left { display: table-cell; width: 50%; padding-right: 5px; vertical-align: top; }
        .col-right { display: table-cell; width: 50%; padding-left: 5px; vertical-align: top; }
    </style>
</head>
<body>
    <div class="page">
        {{-- HEADER --}}
        <div class="header">
            <div class="header-left">
                @if(file_exists(public_path('img/logo-senelec.png')))
                    <img src="{{ public_path('img/logo-senelec.png') }}" alt="SENELEC" class="logo">
                @else
                    <span style="font-size: 14px; font-weight: bold; color: #e30613;">SENELEC</span>
                @endif
            </div>
            <div class="header-center">
                <div class="titre">DEMANDE DE TRAVAUX</div>
                <div class="sous-titre">Direction Administrative et Gestion des Équipements</div>
            </div>
            <div class="header-right">
                <div class="numero">N° {{ $demande->numero_demande }}</div>
                <div>
                    <span class="badge badge-{{ $demande->statut }}">{{ str_replace('_', ' ', strtoupper($demande->statut)) }}</span>
                </div>
            </div>
        </div>

        {{-- INFORMATIONS GÉNÉRALES --}}
        <div class="section-title">Informations Générales</div>
        <table class="info-table">
            <tr>
                <td class="label">Demandeur</td>
                <td class="value">{{ $demande->user->name ?? '-' }}</td>
                <td class="label">Date de création</td>
                <td class="value">{{ $demande->created_at ? $demande->created_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Service</td>
                <td class="value">{{ $demande->service->libelle ?? '-' }}</td>
                <td class="label">Site</td>
                <td class="value">{{ $demande->site->libelle ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Nature</td>
                <td class="value">{{ $demande->nature ?? '-' }}</td>
                <td class="label">Type prestation</td>
                <td class="value">{{ $demande->type_prestation == 'interne' ? 'Interne (Régie)' : ($demande->type_prestation == 'externe' ? 'Externe (Prestataire)' : ($demande->type_prestation ?? '-')) }}</td>
            </tr>
            <tr>
                <td class="label">Objet</td>
                <td class="value" colspan="3">{{ $demande->objet }}</td>
            </tr>
            @if($demande->observation)
            <tr>
                <td class="label">Observation</td>
                <td class="value" colspan="3">{{ $demande->observation }}</td>
            </tr>
            @endif
        </table>

        {{-- DATES --}}
        <div class="section-title">Dates</div>
        <table class="info-table">
            <tr>
                <td class="label">Date souhaitée</td>
                <td class="value">{{ $demande->date ? $demande->date->format('d/m/Y') : '-' }}</td>
                <td class="label">Date fin souhaitée</td>
                <td class="value">{{ $demande->date_fin ? $demande->date_fin->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Début intervention</td>
                <td class="value">{{ $demande->date_debut_intervention ? $demande->date_debut_intervention->format('d/m/Y') : '-' }}</td>
                <td class="label">Fin intervention</td>
                <td class="value">{{ $demande->date_fin_intervention ? $demande->date_fin_intervention->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td class="label">Date intervention</td>
                <td class="value">{{ $demande->date_intervention ? $demande->date_intervention->format('d/m/Y') : '-' }}</td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
        </table>

        {{-- AFFECTATIONS --}}
        <div class="section-title">Affectations & Équipe</div>
        <div class="two-col">
            <div class="col-left">
                <table class="info-table">
                    <tr>
                        <td class="label">Approbateur N1</td>
                        <td class="value">{{ $demande->approbateurN1->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Approuvé par</td>
                        <td class="value">{{ $demande->approvedBy->name ?? '-' }}</td>
                    </tr>
                    @if($demande->sad)
                    <tr><td class="label">SAD</td><td class="value">{{ $demande->sad->name }}</td></tr>
                    @endif
                    @if($demande->seg)
                    <tr><td class="label">SEG</td><td class="value">{{ $demande->seg->name }}</td></tr>
                    @endif
                    @foreach(['umt', 'ubt', 'unsp', 'umr', 'utgc'] as $unite)
                        @if($demande->$unite)
                        <tr><td class="label">{{ strtoupper($unite) }}</td><td class="value">{{ $demande->$unite->name }}</td></tr>
                        @endif
                    @endforeach
                </table>
            </div>
            <div class="col-right">
                <table class="info-table">
                    @if($demande->team_type)
                    <tr><td class="label">Type d'équipe</td><td class="value">{{ $demande->team_type == 'interne' ? 'Équipe interne' : 'Prestataire' }}</td></tr>
                    @endif
                    @if($demande->chefequipe)
                    <tr><td class="label">Chef équipe</td><td class="value">{{ $demande->chefequipe->name }}</td></tr>
                    @endif
                    @if($demande->superviseur)
                    <tr><td class="label">Superviseur</td><td class="value">{{ $demande->superviseur->name }}</td></tr>
                    @endif
                    @if($demande->executant)
                    <tr><td class="label">Exécutant</td><td class="value">{{ $demande->executant->name }}</td></tr>
                    @endif
                    @if($demande->prestataire_nom)
                    <tr><td class="label">Prestataire</td><td class="value">{{ $demande->prestataire_nom }}</td></tr>
                    @endif
                    @if($demande->numero_commande)
                    <tr><td class="label">N° Commande</td><td class="value">{{ $demande->numero_commande }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        {{-- Équipes affectées --}}
        @if($demande->equipes && $demande->equipes->count() > 0)
        <div style="margin-top: 6px;">
            <table class="equipe-table">
                <thead>
                    <tr>
                        <th>Équipe</th>
                        <th>Durée</th>
                        <th>Exécutant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demande->equipes as $equipe)
                    <tr>
                        <td>{{ $equipe->nom ?? $equipe->name ?? '-' }}</td>
                        <td>{{ $equipe->pivot->duree ?? '-' }}</td>
                        <td>
                            @php $exec = $equipe->pivot->executant_id ? \App\Models\User::find($equipe->pivot->executant_id) : null; @endphp
                            {{ $exec ? $exec->name : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Commentaires --}}
        @php
            $commentaires = [];
            foreach (['comment_umt' => 'UMT', 'comment_ubt' => 'UBT', 'comment_unsp' => 'UNSP', 'comment_umr' => 'UMR', 'comment_utgc' => 'UTGC', 'commentaire_equipe' => 'Équipe', 'commentaire_approbation' => 'Approbation', 'commentaire_prestataire' => 'Prestataire', 'commentaire_periode_seg' => 'Période SEG'] as $field => $label) {
                if (!empty($demande->$field)) $commentaires[$label] = $demande->$field;
            }
        @endphp
        @if(count($commentaires) > 0)
        <div class="section-title">Commentaires</div>
        <table class="info-table">
            @foreach($commentaires as $label => $comment)
            <tr>
                <td class="label" style="width: 20%;">{{ $label }}</td>
                <td class="value">{{ $comment }}</td>
            </tr>
            @endforeach
        </table>
        @endif

        {{-- Motifs de rejet --}}
        @if($demande->motif || $demande->motif2)
        <div class="section-title">Motifs de rejet</div>
        <table class="info-table">
            @if($demande->motif)
            <tr>
                <td class="label" style="width: 20%;">Rejet N1</td>
                <td class="value">{{ $demande->motif }} <span style="font-size: 8px; color: #999;">(par {{ $demande->rejectedBy->name ?? '-' }})</span></td>
            </tr>
            @endif
            @if($demande->motif2)
            <tr>
                <td class="label" style="width: 20%;">Rejet N2</td>
                <td class="value">{{ $demande->motif2 }} <span style="font-size: 8px; color: #999;">(par {{ $demande->rejectedByN2->name ?? '-' }})</span></td>
            </tr>
            @endif
        </table>
        @endif

        {{-- SIGNATURES --}}
        <div class="section-title">Signatures</div>
        <div class="signatures">
            {{-- N1 - Approbateur --}}
            <div class="signature-box">
                <div class="signature-title">Approbateur</div>
                <div class="signature-name">{{ $demande->approvedBy->name ?? ($demande->approbateurN1->name ?? '-') }}</div>
                @if($signatureN1)
                    <img src="{{ $signatureN1 }}" alt="Signature N1" class="signature-img">
                @else
                    <div style="height: 50px; border-bottom: 1px dotted #ccc; margin: 5px 20px;"></div>
                @endif
                @if($stampN1)
                    <img src="{{ $stampN1 }}" alt="Cachet N1" class="stamp-img">
                @endif
                <div class="signature-date"></div>
            </div>

            {{-- N2 - Chef de service --}}
            <div class="signature-box">
                <div class="signature-title">Chef de service</div>
                <div class="signature-name">
                    {{ $demande->sad->name ?? ($demande->seg->name ?? '-') }}
                </div>
                @if($signatureN2)
                    <img src="{{ $signatureN2 }}" alt="Signature N2" class="signature-img">
                @else
                    <div style="height: 50px; border-bottom: 1px dotted #ccc; margin: 5px 20px;"></div>
                @endif
                @if($stampN2)
                    <img src="{{ $stampN2 }}" alt="Cachet N2" class="stamp-img">
                @endif
                <div class="signature-date"></div>
            </div>

            {{-- N3 - Chef d'unité --}}
            <div class="signature-box">
                <div class="signature-title">Chef d'unité</div>
                <div class="signature-name">{{ $n3Name ?? '-' }}</div>
                @if($signatureN3)
                    <img src="{{ $signatureN3 }}" alt="Signature N3" class="signature-img">
                @else
                    <div style="height: 50px; border-bottom: 1px dotted #ccc; margin: 5px 20px;"></div>
                @endif
                @if($stampN3)
                    <img src="{{ $stampN3 }}" alt="Cachet N3" class="stamp-img">
                @endif
                <div class="signature-date">
                    @if($demande->statut === 'cloture') Date : {{ now()->format('d/m/Y') }} @endif
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="footer-left">SENELEC — SENTRAVAUX — Document généré le {{ now()->format('d/m/Y à H:i') }}</div>
            <div class="footer-right">Demande N° {{ $demande->numero_demande }}</div>
        </div>
    </div>
</body>
</html>
