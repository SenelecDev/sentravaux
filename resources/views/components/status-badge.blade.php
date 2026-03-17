@props(['statut'])

@php
    $raw = strtolower(str_replace(' ', '_', $statut));
    $badges = [
        'brouillon'     => ['class' => 'badge-secondary',  'label' => 'Brouillon',       'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z'],
        'en_attente'    => ['class' => 'badge-warning',    'label' => 'En attente',       'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        'accepte'       => ['class' => 'badge-info',       'label' => 'Acceptée',         'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        'impute'        => ['class' => 'badge-senelec-primary', 'label' => 'Imputée',     'icon' => 'M13 5l7 7-7 7M5 5l7 7-7 7'],
        'valide'        => ['class' => 'badge-success',    'label' => 'Validée',          'icon' => 'M5 13l4 4L19 7'],
        'en_cours'      => ['class' => 'badge-senelec-info','label' => 'En cours',        'icon' => 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z'],
        'rejete'        => ['class' => 'badge-danger',     'label' => 'Rejetée',          'icon' => 'M6 18L18 6M6 6l12 12'],
        'termine'       => ['class' => 'badge badge-teal',       'label' => 'Terminée',         'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        'cloture'       => ['class' => 'badge badge-purple',     'label' => 'Clôturée',         'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
    ];
    $badge = $badges[$raw] ?? ['class' => 'badge-secondary', 'label' => ucfirst($statut), 'icon' => null];
@endphp

<span class="{{ $badge['class'] }} inline-flex items-center gap-1">
    @if($badge['icon'])
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $badge['icon'] }}"/>
        </svg>
    @endif
    {{ $badge['label'] }}
</span>


