<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Demande;
use App\Models\User;

class NotificationService
{
    private const UNITE_FIELDS = [
        'umt_id' => ['route' => 'umt.demandes.recues', 'label' => 'UMT'],
        'ubt_id' => ['route' => 'ubt.demandes.recues', 'label' => 'UBT'],
        'unsp_id' => ['route' => 'unsp.demandes.recues', 'label' => 'UNSP'],
        'umr_id' => ['route' => 'umr.demandes.recues', 'label' => 'UMR'],
        'utgc_id' => ['route' => 'utgc.demandes.recues', 'label' => 'UTGC'],
        'ual_id' => ['route' => 'ual.demandes.recues', 'label' => 'UAL'],
        'ucc_id' => ['route' => 'ucc.demandes.recues', 'label' => 'UCC'],
    ];

    public static function create($userId, $type, $title, $message, $demandeId = null, $options = [])
    {
        if (!$userId) {
            return null;
        }

        return Notification::create([
            'user_id' => $userId,
            'demande_id' => $demandeId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $options['icon'] ?? self::getDefaultIcon($type),
            'color' => $options['color'] ?? self::getDefaultColor($type),
            'url' => $options['url'] ?? null,
            'data' => $options['data'] ?? null,
        ]);
    }

    public static function demandeCreee(Demande $demande)
    {
        self::create(
            $demande->user_id,
            'demande_creee',
            'Demande créée',
            "Votre demande #{$demande->numero_demande} a été créée avec succès.",
            $demande->id,
            ['url' => route('demande.index')]
        );

        if ($demande->approbateur_n1_id) {
            self::create(
                $demande->approbateur_n1_id,
                'demande_a_approuver',
                'Nouvelle demande à approuver',
                "Une nouvelle demande #{$demande->numero_demande} nécessite votre approbation.",
                $demande->id,
                ['url' => url('/demandes/aapprouver')]
            );
        }
    }

    public static function demandeApprouvee(Demande $demande, User $approbateur)
    {
        self::create(
            $demande->user_id,
            'demande_approuvee',
            'Demande approuvée',
            "Votre demande #{$demande->numero_demande} a été approuvée par {$approbateur->name}.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'success']
        );

        self::demandeAValiderParService($demande);
    }

    public static function demandeAValiderParService(Demande $demande)
    {
        $serviceManagers = [
            'sad_id' => ['type' => 'demande_a_imputer_sa', 'title' => 'Demande à imputer (SA)', 'url' => route('sad.demandes.approuvees')],
            'seg_id' => ['type' => 'demande_a_imputer_seg', 'title' => 'Demande à imputer (SEG)', 'url' => route('seg.demandes.approuvees')],
            'sgb_id' => ['type' => 'demande_a_imputer_sgb', 'title' => 'Demande à imputer (SGB)', 'url' => route('sgb.demandes')],
        ];

        foreach ($serviceManagers as $field => $config) {
            if ($demande->$field) {
                self::create(
                    $demande->$field,
                    $config['type'],
                    $config['title'],
                    "La demande #{$demande->numero_demande} est approuvée et attend votre imputation.",
                    $demande->id,
                    ['url' => $config['url'], 'color' => 'warning']
                );
            }
        }
    }

    public static function demandeRejetee(Demande $demande, User $rejeteur, $motif = null)
    {
        $message = "Votre demande #{$demande->numero_demande} a été rejetée par {$rejeteur->name}.";
        if ($motif) {
            $message .= " Motif : {$motif}";
        }

        self::create(
            $demande->user_id,
            'demande_rejetee',
            'Demande rejetée',
            $message,
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'danger']
        );
    }

    public static function demandeRejeteeN2(Demande $demande, User $rejeteur, $motif = null)
    {
        self::demandeRejetee($demande, $rejeteur, $motif);
    }

    public static function demandeImputee(Demande $demande, User $gestionnaire, $unite)
    {
        self::create(
            $demande->user_id,
            'demande_imputee',
            'Demande imputée',
            "Votre demande #{$demande->numero_demande} a été assignée à l'unité {$unite} par {$gestionnaire->name}.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'purple']
        );

        self::notifyUniteResponsables($demande, 'demande_recue_unite', 'Nouvelle demande reçue', "La demande #{$demande->numero_demande} vous a été assignée.");
    }

    public static function demandeAssigneeUnite(Demande $demande, User $responsable, string $uniteLabel)
    {
        $url = self::getUniteUrlForDemande($demande) ?? route('demande.index');

        self::create(
            $responsable->id,
            'demande_recue_unite',
            'Demande assignée',
            "La demande #{$demande->numero_demande} vous a été assignée ({$uniteLabel}).",
            $demande->id,
            ['url' => $url, 'color' => 'info']
        );
    }

    public static function periodeValideeSeg(Demande $demande)
    {
        self::create(
            $demande->user_id,
            'periode_validee_seg',
            'Période validée par SEG',
            "La période d'intervention de votre demande #{$demande->numero_demande} a été validée par le SEG.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'success']
        );

        if ($demande->umr_id) {
            self::create(
                $demande->umr_id,
                'periode_a_valider_umr',
                'Période à confirmer (UMR)',
                "La période de la demande #{$demande->numero_demande} a été validée par le SEG. Vous pouvez planifier l'intervention.",
                $demande->id,
                ['url' => route('umr.demandes.recues'), 'color' => 'info']
            );
        }
    }

    public static function periodeValideeUmr(Demande $demande)
    {
        self::create(
            $demande->user_id,
            'periode_validee_umr',
            'Période validée par UMR',
            "La période d'intervention de votre demande #{$demande->numero_demande} a été validée par l'UMR. Les travaux peuvent commencer.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'success']
        );
    }

    public static function periodeModifieeSeg(Demande $demande)
    {
        self::create(
            $demande->user_id,
            'periode_modifiee_seg',
            'Période modifiée par SEG',
            "La période d'intervention de votre demande #{$demande->numero_demande} a été modifiée par le SEG.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'info']
        );
    }

    public static function periodeRejetee(Demande $demande, $motif)
    {
        self::create(
            $demande->user_id,
            'periode_rejetee',
            'Période rejetée',
            "La période d'intervention de votre demande #{$demande->numero_demande} a été rejetée. Motif : {$motif}",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'danger']
        );
    }

    public static function equipeAffectee(Demande $demande, User $chefEquipe)
    {
        self::create(
            $chefEquipe->id,
            'equipe_affectee',
            'Nouvelle affectation',
            "Vous avez été affecté comme chef d'équipe pour la demande #{$demande->numero_demande}.",
            $demande->id,
            ['url' => route('equipe.demandes.recues'), 'color' => 'warning']
        );

        self::create(
            $demande->user_id,
            'equipe_affectee_demandeur',
            'Chef d\'équipe affecté',
            "Un chef d'équipe a été affecté à votre demande #{$demande->numero_demande}.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'info']
        );
    }

    public static function prestationExterneValidee(Demande $demande, User $validateur)
    {
        if ($demande->superviseur_id) {
            self::create(
                $demande->superviseur_id,
                'prestation_externe',
                'Prestation externe à superviser',
                "Vous êtes désigné superviseur pour la demande #{$demande->numero_demande}.",
                $demande->id,
                ['url' => self::getUniteUrlForDemande($demande), 'color' => 'warning']
            );
        }

        self::create(
            $demande->user_id,
            'prestation_externe_demandeur',
            'Prestation externe validée',
            "La demande #{$demande->numero_demande} a été validée pour prestation externe par {$validateur->name}.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'info']
        );
    }

    public static function demandeRetournee(Demande $demande, User $chefEquipe, $commentaire = null)
    {
        $acteur = auth()->user();
        $message = "La demande #{$demande->numero_demande} vous a été renvoyée pour correction";
        if ($acteur) {
            $message .= ' par ' . $acteur->name;
        }
        $message .= '.';
        if ($commentaire) {
            $message .= "\n\nCommentaire : " . $commentaire;
        }

        self::create(
            $chefEquipe->id,
            'demande_retour',
            'Demande renvoyée pour correction',
            $message,
            $demande->id,
            ['url' => route('equipe.demandes.recues'), 'color' => 'warning', 'icon' => 'fas fa-arrow-left']
        );
    }

    public static function travauxDebutes(Demande $demande, ?User $acteur = null)
    {
        $acteurName = $acteur?->name ?? 'l\'équipe';

        self::create(
            $demande->user_id,
            'travaux_debutes',
            'Travaux démarrés',
            "Les travaux de votre demande #{$demande->numero_demande} ont démarré ({$acteurName}).",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'success']
        );

        self::notifyServiceManagers($demande, 'travaux_debutes_service', 'Travaux démarrés', "Les travaux de la demande #{$demande->numero_demande} ont démarré.");
    }

    public static function demandeTerminee(Demande $demande, ?User $acteur = null)
    {
        $acteurName = $acteur?->name ?? 'l\'unité';

        self::create(
            $demande->user_id,
            'demande_terminee',
            'Travaux terminés',
            "Les travaux de votre demande #{$demande->numero_demande} sont terminés ({$acteurName}). En attente de clôture.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'success']
        );

        self::notifyUniteResponsables(
            $demande,
            'demande_terminee_unite',
            'Demande terminée',
            "La demande #{$demande->numero_demande} est terminée et peut être clôturée."
        );
    }

    public static function demandeCloturee(Demande $demande, User $clotureur)
    {
        self::create(
            $demande->user_id,
            'demande_cloturee',
            'Demande clôturée',
            "Votre demande #{$demande->numero_demande} a été clôturée par {$clotureur->name}.",
            $demande->id,
            ['url' => route('demande.index'), 'color' => 'dark']
        );

        if ($demande->approbateur_n1_id) {
            self::create(
                $demande->approbateur_n1_id,
                'demande_cloturee_approbateur',
                'Demande clôturée',
                "La demande #{$demande->numero_demande} que vous avez approuvée a été clôturée.",
                $demande->id,
                ['url' => url('/demandes/aapprouver'), 'color' => 'dark']
            );
        }

        if ($demande->chef_equipe_id) {
            self::create(
                $demande->chef_equipe_id,
                'demande_cloturee_equipe',
                'Demande clôturée',
                "La demande #{$demande->numero_demande} dont vous étiez chef d'équipe a été clôturée.",
                $demande->id,
                ['url' => route('equipe.demandes.cloturees'), 'color' => 'dark']
            );
        }
    }

    /**
     * Point d'entrée unique pour les actions workflow des contrôleurs d'unité.
     */
    public static function handleWorkflowAction(Demande $demande, string $action, ?User $acteur = null): void
    {
        $acteur = $acteur ?? auth()->user();

        match ($action) {
            'debut_travaux' => self::travauxDebutes($demande, $acteur),
            'terminer' => self::demandeTerminee($demande, $acteur),
            'cloturer' => $acteur ? self::demandeCloturee($demande, $acteur) : null,
            'dispatcher_externe' => $acteur ? self::prestationExterneValidee($demande, $acteur) : null,
            default => null,
        };
    }

    private static function notifyUniteResponsables(Demande $demande, string $type, string $title, string $message): void
    {
        foreach (self::UNITE_FIELDS as $field => $config) {
            if ($demande->$field) {
                self::create(
                    $demande->$field,
                    $type,
                    $title,
                    $message,
                    $demande->id,
                    ['url' => route($config['route']), 'color' => 'info']
                );
            }
        }
    }

    private static function notifyServiceManagers(Demande $demande, string $type, string $title, string $message): void
    {
        foreach (['sad_id', 'seg_id', 'sgb_id'] as $field) {
            if ($demande->$field) {
                self::create($demande->$field, $type, $title, $message, $demande->id, ['url' => route('demande.index'), 'color' => 'info']);
            }
        }
    }

    private static function getUniteUrlForDemande(Demande $demande): ?string
    {
        foreach (self::UNITE_FIELDS as $field => $config) {
            if ($demande->$field) {
                return route($config['route']);
            }
        }

        return null;
    }

    private static function getDefaultIcon($type)
    {
        return match ($type) {
            'demande_creee' => 'fas fa-plus-circle',
            'demande_a_approuver' => 'fas fa-user-check',
            'demande_approuvee' => 'fas fa-check-circle',
            'demande_rejetee' => 'fas fa-times-circle',
            'demande_imputee' => 'fas fa-file-invoice',
            'demande_recue_unite', 'demande_a_imputer_sa', 'demande_a_imputer_seg', 'demande_a_imputer_sgb' => 'fas fa-inbox',
            'periode_validee_seg', 'periode_validee_umr', 'periode_modifiee_seg' => 'fas fa-calendar-check',
            'periode_rejetee' => 'fas fa-calendar-times',
            'periode_a_valider_umr' => 'fas fa-calendar-alt',
            'equipe_affectee', 'equipe_affectee_demandeur' => 'fas fa-users',
            'demande_retour' => 'fas fa-arrow-left',
            'travaux_debutes', 'travaux_debutes_service' => 'fas fa-hard-hat',
            'demande_terminee', 'demande_terminee_unite' => 'fas fa-check-double',
            'demande_cloturee', 'demande_cloturee_approbateur', 'demande_cloturee_equipe' => 'fas fa-flag-checkered',
            'prestation_externe', 'prestation_externe_demandeur' => 'fas fa-building',
            default => 'fas fa-bell',
        };
    }

    private static function getDefaultColor($type)
    {
        return match ($type) {
            'demande_creee' => 'primary',
            'demande_a_approuver', 'demande_a_imputer_sa', 'demande_a_imputer_seg', 'demande_a_imputer_sgb' => 'warning',
            'demande_approuvee', 'periode_validee_seg', 'periode_validee_umr', 'travaux_debutes', 'demande_terminee' => 'success',
            'demande_rejetee', 'periode_rejetee' => 'danger',
            'demande_imputee' => 'purple',
            'demande_recue_unite', 'periode_modifiee_seg', 'periode_a_valider_umr', 'equipe_affectee_demandeur' => 'info',
            'equipe_affectee', 'demande_retour', 'prestation_externe' => 'warning',
            'demande_cloturee', 'demande_cloturee_approbateur', 'demande_cloturee_equipe' => 'dark',
            default => 'secondary',
        };
    }

    public static function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    public static function deleteOldNotifications($days = 30)
    {
        return Notification::where('created_at', '<', now()->subDays($days))->delete();
    }

    public static function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)->where('is_read', false)->count();
    }

    public static function getRecentNotifications($userId, $limit = 10)
    {
        return Notification::where('user_id', $userId)
            ->with('demande')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
