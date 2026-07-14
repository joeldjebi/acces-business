<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'country_id',
        'nom',
        'statut',
    ];

    protected $casts = [
        'statut' => 'integer',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function scopeActive($query)
    {
        return $query->where('statut', 1);
    }
}
