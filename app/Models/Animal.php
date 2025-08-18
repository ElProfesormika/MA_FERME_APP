<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'espece',
        'race',
        'date_naissance',
        'historique_sante',
        'poids',
        'sexe',
        'statut',
        'employe_id'
    ];

    protected $casts = [
        'date_naissance' => 'date',
        'poids' => 'decimal:2'
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function activites()
    {
        return $this->hasMany(Activite::class);
    }

    public function alertes()
    {
        return $this->hasMany(Alerte::class);
    }
} 