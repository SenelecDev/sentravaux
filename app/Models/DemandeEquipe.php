<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandeEquipe extends Model
{
    use HasFactory;

    protected $table = 'demande_equipe';

    protected $fillable = ['demande_id', 'equipe_id', 'duree', 'executant_id'];
}
