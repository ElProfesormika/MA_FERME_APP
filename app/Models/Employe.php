<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'poste',
        'date_embauche',
        'salaire',
        'telephone',
        'email',
        'adresse',
        'statut'
    ];

    protected $casts = [
        'date_embauche' => 'date',
        'salaire' => 'decimal:2'
    ];

    public function activites()
    {
        return $this->hasMany(Activite::class);
    }

    public function animaux()
    {
        return $this->hasMany(Animal::class);
    }

    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }
} 