<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Demande;
use App\Models\User;

class NotificationService
{
    public static function create($userId, $type, $title, $message, $demandeId = null, $options = [])
    {
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

        if ($demande->umr_id) {
            self::create(
                $demande->umr_id,
                'demande_recue_umr',
                'Nouvelle demande UMR',
                "Une nouvelle demande #{$demande->numero_demande} vous a été assignée.",
                $demande->id,
                ['url' => url('/umr/demandes/recues'), 'color' => 'info']
            );
        }
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
            ['url' => url('/equipe/demandes/recues'), 'color' => 'warning']
        );
    }

    public static function demandeRetournee(Demande $demande, User $chefEquipe, $commentaire = null)
    {
        $message = "La demande #{$demande->numero_demande} vous a été renvoyée pour correction par " . auth()->user()->name . ".";
        if ($commentaire) {
            $message .= "\n\nCommentaire : " . $commentaire;
        }

        self::create(
            $chefEquipe->id,
            'demande_retour',
            'Demande renvoyée pour correction',
            $message,
            $demande->id,
            ['url' => url('/equipe/' . $demande->id . '/edit'), 'color' => 'warning', 'icon' => 'fas fa-arrow-left']
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
    }

    private static function getDefaultIcon($type)
    {
        return match ($type) {
            'demande_creee' => 'fas fa-plus-circle',
            'demande_a_approuver' => 'fas fa-user-check',
            'demande_approuvee' => 'fas fa-check-circle',
            'demande_rejetee' => 'fas fa-times-circle',
            'demande_imputee' => 'fas fa-file-invoice',
            'demande_recue_umr' => 'fas fa-inbox',
            'periode_validee_seg' => 'fas fa-calendar-check',
            'periode_validee_umr' => 'fas fa-calendar-check',
            'periode_rejetee' => 'fas fa-calendar-times',
            'equipe_affectee' => 'fas fa-users',
            'demande_cloturee' => 'fas fa-flag-checkered',
            default => 'fas fa-bell',
        };
    }

    private static function getDefaultColor($type)
    {
        return match ($type) {
            'demande_creee' => 'primary',
            'demande_a_approuver' => 'warning',
            'demande_approuvee' => 'success',
            'demande_rejetee' => 'danger',
            'demande_imputee' => 'purple',
            'demande_recue_umr' => 'info',
            'periode_validee_seg' => 'success',
            'periode_validee_umr' => 'success',
            'periode_rejetee' => 'danger',
            'equipe_affectee' => 'warning',
            'demande_cloturee' => 'dark',
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
