<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activite extends Model
{
    use HasFactory;

    protected $fillable = [
        'titre',
        'description',
        'date',
        'heure_debut',
        'heure_fin',
        'type',
        'statut',
        'employe_id',
        'animal_id'
    ];

    protected $casts = [
        'date' => 'date',
        'heure_debut' => 'datetime',
        'heure_fin' => 'datetime'
    ];

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function scopeAujourdhui($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeCetteSemaine($query)
    {
        return $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]);
    }
} 