<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;

class User extends Authenticatable implements LdapAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, AuthenticatesWithLdap, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'matricule', 'service', 'direction', 'poste', 'is_active'])
            ->logOnlyDirty()
            ->useLogName('users')
            ->setDescriptionForEvent(fn(string $eventName) => "Utilisateur {$eventName}");
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        // Identifiants
        'matricule',
        'ldap_username',
        'ldap_guid',
        // Informations personnelles
        'nom',
        'prenom',
        'poste',
        'telephone',
        'photo',
        // Organisation
        'organisation',
        'entreprise',
        'service',
        'direction',
        'departement',
        // Oracle HR
        'oracle_person_id',
        'oracle_org_id',
        'fonction_oracle',
        'grade_fonction',
        'niveau_remuneration',
        'college',
        'centre_responsabilite',
        'localisation',
        // Hiérarchie Oracle
        'direction_generale',
        'direction_generale_id',
        'direction_principale',
        'direction_principale_id',
        'direction_id',
        'delegation',
        'delegation_id',
        'departement_id',
        'service_id',
        // Signatures
        'signature',
        'stamp',
        // Statut
        'is_active',
        // Timestamps
        'oracle_synced_at',
        'last_sync_at',
        'last_activity_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'oracle_synced_at' => 'datetime',
        'last_sync_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    // ==================== ACCESSEURS ====================

    public function getFullNameAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . ($this->nom ?? '')) ?: $this->name ?? $this->matricule;
    }

    public function getInitialsAttribute(): string
    {
        $prenom = $this->prenom ?? '';
        $nom = $this->nom ?? '';
        
        if ($prenom && $nom) {
            return strtoupper(substr($prenom, 0, 1) . substr($nom, 0, 1));
        }
        
        return strtoupper(substr($this->name ?? $this->matricule ?? 'U', 0, 2));
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature ? asset('storage/' . $this->signature) : null;
    }

    public function getStampUrlAttribute(): ?string
    {
        return $this->stamp ? asset('storage/' . $this->stamp) : null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo) {
            return null;
        }
        
        if (str_starts_with($this->photo, 'data:image')) {
            return $this->photo;
        }
        
        return asset($this->photo);
    }

    // ==================== RELATIONS MÉTIER ====================

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function unreadNotifications()
    {
        return $this->notifications()->where('is_read', false);
    }

    public function personnel()
    {
        return $this->hasOne(Personnel::class, 'user_id');
    }

    // ==================== LDAP ====================

    public function getLdapDomainColumn(): string
    {
        return 'ldap_guid';
    }

    public function getLdapGuidColumn(): string
    {
        return 'ldap_guid';
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $search)
    {
        $terms = preg_split('/\s+/', trim($search));

        return $query->where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                if ($term === '') {
                    continue;
                }

                $like = "%{$term}%";

                $q->where(function ($sub) use ($like) {
                    $sub->where('matricule', 'like', $like)
                        ->orWhere('nom', 'like', $like)
                        ->orWhere('prenom', 'like', $like)
                        ->orWhere('name', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            }
        });
    }
}
