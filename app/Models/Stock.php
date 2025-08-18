<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'produit',
        'quantite',
        'unite',
        'date_entree',
        'date_peremption',
        'prix_unitaire',
        'fournisseur',
        'categorie'
    ];

    protected $casts = [
        'date_entree' => 'date',
        'date_peremption' => 'date',
        'prix_unitaire' => 'decimal:2'
    ];

    public function scopeEnRupture($query)
    {
        return $query->where('quantite', '<=', 10);
    }

    public function scopePerime($query)
    {
        return $query->where('date_peremption', '<', now());
    }
} 