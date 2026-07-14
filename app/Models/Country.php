<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'nom',
        'indicatif',
        'currency',
        'flag',
        'statut',
    ];

    protected $casts = [
        'statut' => 'integer',
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    public function scopeActive($query)
    {
        return $query->where('statut', 1);
    }
}
