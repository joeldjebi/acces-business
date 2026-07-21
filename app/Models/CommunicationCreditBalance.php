<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;

class CommunicationCreditBalance extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'channel',
        'purchased',
        'used',
    ];

    protected $casts = [
        'purchased' => 'integer',
        'used' => 'integer',
    ];

    public function getRemainingAttribute(): int
    {
        return max(0, (int) $this->purchased - (int) $this->used);
    }
}
