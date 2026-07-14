<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
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
