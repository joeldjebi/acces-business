<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'tagline',
        'monthly_price',
        'yearly_price',
        'currency',
        'events_limit',
        'users_limit',
        'invitations_limit',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'monthly_price' => 'integer',
        'yearly_price' => 'integer',
        'events_limit' => 'integer',
        'users_limit' => 'integer',
        'invitations_limit' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
