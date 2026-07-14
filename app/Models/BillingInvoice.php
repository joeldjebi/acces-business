<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class BillingInvoice extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'reference',
        'description',
        'amount',
        'currency',
        'status',
        'period_start',
        'period_end',
        'due_at',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];
}
