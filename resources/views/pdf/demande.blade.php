<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Demande N° {{ $demande->numero_demande }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { margin: 10px !important; }
        @page { margin: 10px !important; }

        body { font-family: 'DejaVu Sans', Verdana, sans-serif; font-size: 10px; color: #333; line-height: 1.4; padding-bottom: 70px; }
        table { border: 1px solid #eee; width: 100%; border-collapse: collapse; border-spacing: 0; margin: 0; padding: 0; }

        .logo { text-align: center; width: 140px; margin: auto; margin-bottom: 5px; }
        .mission-title { text-align: center; font-weight: 800; margin-bottom: 20px; }
        .direction { margin-bottom: 30px; }

        .table-mission { width: 100%; margin-bottom: 50px; }
        .table-mission table thead { background: #af016e; color: #fff; }
        .table-mission table tbody tr td { padding: 4px 20px; text-align: center; }
        .table-spacing td { padding: 10px; }

        .signataire-table thead tr th { padding: 5px; border: 1px solid #ccc; font-size: 12px; }
        .signataire-table .signRow { height: 170px; }
        .signataire-table tbody td { text-align: center; padding: 10px; border: 1px solid #ccc; }
        .sign-name {
            margin-top: 10px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            color: #222;
        }

        .mission-foo {
            position: fixed;
            bottom: 10px;
            left: 0;
            right: 0;
            text-align: center;
        }
        .mission-foo p { max-width: 550px; margin: 0 auto; display: inline-block; font-size: 12px; }

        .signValid { position: relative; }
        .signValid .cachet { position: absolute; top: -10px; left: 0; right: 0; margin: auto; width: 180px; }
    </style>
</head>
<body>
@php
    // Logo (Dompdf): utiliser une image locale encodée en base64
    $logoPath = null;
    foreach (['img/logo.png', 'img/logo-senelec.png'] as $candidate) {
        $p = public_path($candidate);
        if (is_string($p) && file_exists($p)) { $logoPath = $p; break; }
    }
    $logoBase64 = null;
    if (is_string($logoPath) && file_exists($logoPath)) {
        try { $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)); } catch (\Throwable $e) { $logoBase64 = null; }
    }

    $serviceLabel = '-';
    if ($demande->service) $serviceLabel = $demande->service->libelle;
    elseif ($demande->departement) $serviceLabel = $demande->departement->libelle;
    elseif ($demande->direction) $serviceLabel = $demande->direction->libelle;

    $siteLabel = $demande->site->libelle ?? '-';

    $dateDemande = $demande->date
        ? \Carbon\Carbon::parse($demande->date)->format('d/m/Y')
        : ($demande->created_at ? $demande->created_at->format('d/m/Y') : '-');

    // UMR: afficher début/fin intervention (périodes). Sinon: date_intervention + date_fin.
    $isUmr = ($demande->unite_code ?? null) === 'UMR' || !empty($demande->umr_id) || !empty($demande->umr);

    $dateIntervention = $demande->date_intervention
        ? \Carbon\Carbon::parse($demande->date_intervention)->format('d/m/Y H:i')
        : '-';

    $dateDebutIntervention = $demande->date_debut_intervention
        ? \Carbon\Carbon::parse($demande->date_debut_intervention)->format('d/m/Y H:i')
        : '-';

    $dateFinIntervention = $demande->date_fin_intervention
        ? \Carbon\Carbon::parse($demande->date_fin_intervention)->format('d/m/Y H:i')
        : '-';

    // Date de clôture (nouvelle source principale)
    if ($demande->date_cloture) {
        $dateCloturePdf = \Carbon\Carbon::parse($demande->date_cloture)->format('d/m/Y H:i');
    } elseif ($demande->date_fin_intervention) {
        $dateCloturePdf = \Carbon\Carbon::parse($demande->date_fin_intervention)->format('d/m/Y H:i');
    } elseif ($demande->date_fin) {
        // Fallback legacy
        $datePart = \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y');
        $timePart = $demande->updated_at ? $demande->updated_at->format('H:i') : '00:00';
        $dateCloturePdf = $datePart . ' ' . $timePart;
    } else {
        $dateCloturePdf = $demande->updated_at ? $demande->updated_at->format('d/m/Y H:i') : '-';
    }

    $approbateurName = $demande->approvedBy?->name ?? ($demande->approbateurN1?->name ?? 'Non renseigné');
    $chefServiceName = ($demande->sad?->name ?? $demande->seg?->name) ?? 'Non renseigné';
    $chefUniteName = ($demande->umt?->name ?? $demande->ubt?->name ?? $demande->unsp?->name ?? $demande->umr?->name ?? $demande->utgc?->name) ?? ($n3Name ?? 'Non renseigné');
@endphp

<div class="box-missionView">
    <div class="missionContent" id="missionContent">
        <div class="logo" style="text-align: center; width: 100%;">
            @if($logoBase64)
                <img style="display: inline-block;" src="{{ $logoBase64 }}" width="100" alt="Logo Senelec">
            @else
                <span style="font-size: 18px; font-weight: 800;">SENELEC</span>
            @endif
        </div>
        <br>
        <div class="mission-title">
            <h1 style="font-size: 1.2rem; margin-bottom: 5px;">DEMANDE DE TRAVAUX N° {{ $demande->numero_demande }}</h1>
        </div>
        <br>
        <div class="direction">
            <table class="table-spacing" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td colspan="2" style="border: 1px solid; text-align: center;">
                        <strong>
                            @if($demande->sad_id)
                                Service Administratif
                            @elseif($demande->seg_id)
                                Service Entretien Général
                            @else
                                Non spécifié
                            @endif
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td style="border: 1px solid;">
                        <strong>Service : {{ $serviceLabel }}</strong><br><br>
                        <strong>Site : {{ $siteLabel }}</strong>
                    </td>
                    <td style="text-align:right; border: 1px solid;">
                        <strong>Nature : {{ $demande->nature ?? '-' }}</strong><br><br>
                        <strong>Travaux demandés le : {{ $dateDemande }}</strong>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" style="text-align: center; border: 1px solid;">
                        <strong>Objet : {{ $demande->objet ?? '-' }}</strong><br><br>
                        <strong>Observation : {{ $demande->observation ?? '-' }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <br>
        @if($isUmr)
            <p style="text-align: left">Début intervention : <b>{{ $dateDebutIntervention }}</b></p>
            <p style="text-align: left; margin-top: 4px;">Fin intervention : <b>{{ $dateFinIntervention }}</b></p>
        @else
            <p style="text-align: left">Date d'intervention : <b>{{ $dateIntervention }}</b></p>
        @endif

        <div class="table-mission table-participants">
            <table>
                <thead>
                    <tr>
                        <th>Equipe</th>
                        <th>Durée H</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($demande->equipes as $e)
                        <tr>
                            <td>{{ $e->nom ?? '-' }}</td>
                            <td>{{ $e->pivot->duree ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">-</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(!$isUmr)
            <p style="text-align: left">Demande clôturée le : <b>{{ $dateCloturePdf }}</b></p>
        @endif

        <div class="mission-signataires">
            <table class="signataire-table">
                <thead>
                    <tr>
                        <th style="border-collapse: collapse;"><strong>Approbateur unité demandeur</strong></th>
                        <th style="border-collapse: collapse;"><strong>Chef de service</strong></th>
                        <th style="border-collapse: collapse;"><strong>Chef d'unité</strong></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="signRow">
                        <td>
                            <div class="signValid">
                                <div class="signature">
                                    @if($signatureN1)
                                        <img src="{{ $signatureN1 }}" alt="Signature de {{ $approbateurName }}" width="120" height="120" style="margin: auto; object-fit: contain;">
                                    @endif
                                </div>
                                <div class="cachet">
                                    @if($stampN1)
                                        <img src="{{ $stampN1 }}" alt="Cachet de {{ $approbateurName }}" height="120" width="120" style="margin: auto; object-fit: contain !important; position: relative; top: 20px;">
                                    @endif
                                </div>
                                <p class="sign-name">{{ $approbateurName }}</p>
                            </div>
                        </td>
                        <td>
                            <div class="signValid">
                                <div class="signature">
                                    @if($signatureN2)
                                        <img src="{{ $signatureN2 }}" alt="Signature de {{ $chefServiceName }}" width="120" height="120" style="margin: auto; object-fit: contain;">
                                    @endif
                                </div>
                                <div class="cachet">
                                    @if($stampN2)
                                        <img src="{{ $stampN2 }}" alt="Cachet de {{ $chefServiceName }}" height="120" width="120" style="margin: auto; object-fit: contain !important; position: relative; top: 20px;">
                                    @endif
                                </div>
                                <p class="sign-name">{{ $chefServiceName }}</p>
                            </div>
                        </td>
                        <td>
                            <div class="signValid">
                                <div class="signature">
                                    @if($signatureN3)
                                        <img src="{{ $signatureN3 }}" alt="Signature de {{ $chefUniteName }}" width="120" height="120" style="margin: auto; object-fit: contain;">
                                    @endif
                                </div>
                                <div class="cachet">
                                    @if($stampN3)
                                        <img src="{{ $stampN3 }}" alt="Cachet de {{ $chefUniteName }}" height="120" width="120" style="margin: auto; object-fit: contain !important; position: relative; top: 20px;">
                                    @endif
                                </div>
                                <p class="sign-name">{{ $chefUniteName }}</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <br />
        <div class="mission-foo">
            <p style="font-size: 12px;">
                Société Anonyme au Capital de 175 236 340 000 Francs CFA 28, rue Vincens—BP 93 Dakar (Sénégal)—N°RC : SN-DK-84-B-30—NINEA : 00140012G3<br>
                Tél. : (221) 33 839 30 30 <br>
                Fax : (221) 33 823 12 67 – <strong>www.senelec.sn</strong>
            </p>
        </div>
    </div>
</div>
</body>
</html>
