<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'message',
        'critique',
        'statut',
        'animal_id',
        'stock_id',
        'employe_id',
        'date_resolution'
    ];

    protected $casts = [
        'critique' => 'boolean',
        'date_resolution' => 'datetime'
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function employe()
    {
        return $this->belongsTo(Employe::class);
    }

    public function scopeCritiques($query)
    {
        return $query->where('critique', true);
    }

    public function scopeNonResolues($query)
    {
        return $query->whereNull('date_resolution');
    }
} 