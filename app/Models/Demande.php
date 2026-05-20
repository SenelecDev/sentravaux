<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_demande', 'objet', 'observation', 'date', 'date_fin',
        'date_intervention', 'date_debut_intervention', 'date_fin_intervention', 'date_cloture',
        'user_id', 'approbateur_n1_id', 'statut', 'nature', 'unite_code',
        'service_id', 'direction_id', 'departement_id', 'site_id',
        'sad_id', 'seg_id', 'sgb_id', 'umt_id', 'ubt_id', 'unsp_id', 'umr_id', 'utgc_id', 'ual_id', 'ucc_id',
        'chef_equipe_id', 'superviseur_id', 'executant_id',
        'type_prestation', 'team_type', 'prestataire_nom',
        'comment_umt', 'comment_ubt', 'comment_unsp', 'comment_umr', 'comment_utgc',
        'commentaire_equipe',
        'approved_by', 'commentaire_approbation',
        'rejected_by', 'motif', 'rejected_by_n2', 'motif2',
        'validated_by', 'terminated_by',
        'numero_commande', 'commentaire_prestataire', 'cloture_par',
        'commentaire_periode_seg', 'periode_validee_seg', 'periode_validee_umr',
    ];

    protected $casts = [
        'date' => 'date',
        'date_fin' => 'date',
        'date_intervention' => 'datetime',
        'date_debut_intervention' => 'datetime',
        'date_fin_intervention' => 'datetime',
        'date_cloture' => 'datetime',
        'periode_validee_seg' => 'boolean',
        'periode_validee_umr' => 'boolean',
    ];

    // ==================== BOOT ====================

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->numero_demande = $model->generateNumeroDemande();
        });
    }

    public function generateNumeroDemande()
    {
        $lastId = self::max('id') + 1;
        $currentMonth = date('m');
        $currentYear = date('Y');

        return sprintf('%03d', $lastId) . $currentMonth . $currentYear;
    }

    // ==================== RELATIONS ====================

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approbateurN1()
    {
        return $this->belongsTo(User::class, 'approbateur_n1_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function rejectedByN2()
    {
        return $this->belongsTo(User::class, 'rejected_by_n2');
    }

    public function terminatedBy()
    {
        return $this->belongsTo(User::class, 'terminated_by');
    }

    public function cloturedBy()
    {
        return $this->belongsTo(User::class, 'cloture_par');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Affectations service/unité
    public function sad()
    {
        return $this->belongsTo(User::class, 'sad_id');
    }

    public function seg()
    {
        return $this->belongsTo(User::class, 'seg_id');
    }

    public function sgb()
    {
        return $this->belongsTo(User::class, 'sgb_id');
    }

    public function umt()
    {
        return $this->belongsTo(User::class, 'umt_id');
    }

    public function ubt()
    {
        return $this->belongsTo(User::class, 'ubt_id');
    }

    public function unsp()
    {
        return $this->belongsTo(User::class, 'unsp_id');
    }

    public function umr()
    {
        return $this->belongsTo(User::class, 'umr_id');
    }

    public function utgc()
    {
        return $this->belongsTo(User::class, 'utgc_id');
    }

    public function ual()
    {
        return $this->belongsTo(User::class, 'ual_id');
    }

    public function ucc()
    {
        return $this->belongsTo(User::class, 'ucc_id');
    }

    // Équipe d'exécution
    public function chefequipe()
    {
        return $this->belongsTo(User::class, 'chef_equipe_id');
    }

    public function equipe()
    {
        return $this->belongsTo(User::class, 'chef_equipe_id');
    }

    public function superviseur()
    {
        return $this->belongsTo(User::class, 'superviseur_id');
    }

    public function executant()
    {
        return $this->belongsTo(User::class, 'executant_id');
    }

    // Pivot équipes
    public function equipes()
    {
        return $this->belongsToMany(Equipe::class, 'demande_equipe')
                    ->withPivot('duree', 'executant_id')
                    ->withTimestamps();
    }

    // Service, Direction, Departement & Site
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function direction()
    {
        return $this->belongsTo(Direction::class, 'direction_id');
    }

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departement_id');
    }

    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get the selected unite label (direction, departement or service).
     */
    public function getUniteLabelAttribute(): ?string
    {
        if ($this->direction_id && $this->direction) {
            return $this->direction->libelle;
        }
        if ($this->departement_id && $this->departement) {
            return $this->departement->libelle;
        }
        if ($this->service_id && $this->service) {
            return $this->service->libelle;
        }
        return null;
    }

    // Images
    public function images()
    {
        return $this->hasMany(DemandeImage::class);
    }

    public function rejectionHistory()
    {
        return $this->hasMany(DemandeRejection::class)->orderByDesc('rejected_at');
    }
}
