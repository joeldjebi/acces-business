<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeTarification extends Model
{
    protected $table = 'type_de_tarifications';

    protected $fillable = [
        'libelle',
        'statut',
    ];

    protected $casts = [
        'statut' => 'integer',
    ];

    /**
     * Relation avec les événements
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Scope pour les types de tarification actifs
     */
    public function scopeActive($query)
    {
        return $query->where('statut', 1);
    }
}
