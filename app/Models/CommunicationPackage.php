<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationPackage extends Model
{
    protected $fillable = [
        'name',
        'channel',
        'unit_price',
        'minimum_quantity',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'minimum_quantity' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
