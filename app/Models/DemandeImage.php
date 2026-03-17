<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeImage extends Model
{
    use HasFactory;

    protected $table = 'demande_images';

    protected $fillable = ['demande_id', 'filename', 'original_name', 'path', 'mime_type', 'size'];

    protected $casts = ['size' => 'integer'];

    public function demande()
    {
        return $this->belongsTo(Demande::class);
    }

    public function getFullPathAttribute()
    {
        return storage_path('app/public/' . $this->path);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}
