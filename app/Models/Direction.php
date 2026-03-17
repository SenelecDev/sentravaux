<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Direction extends Model
{
    use HasFactory;

    protected $fillable = [
        'oracle_org_id',
        'libelle',
        'code',
        'centre_responsabilite',
        'type', // DG, DIRP, DIR
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDg($query)
    {
        return $query->where('type', 'DG');
    }

    public function scopeDirp($query)
    {
        return $query->where('type', 'DIRP');
    }

    public function scopeDir($query)
    {
        return $query->where('type', 'DIR');
    }

    // ==================== HELPERS ====================

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'DG' => 'Direction Générale',
            'DIRP' => 'Direction Principale',
            'DIR' => 'Direction',
            default => $this->type,
        };
    }
}
