<?php

namespace App\Models;

use App\Models\Concerns\BelongsToOrganization;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBroadcast extends Model
{
    use BelongsToOrganization;

    protected $fillable = [
        'organization_id', 'event_id', 'user_id', 'channel', 'audience',
        'subject', 'message', 'attachment_path', 'attachment_name',
        'attachment_mime', 'attachment_token', 'recipient_ids',
        'recipient_count', 'sent_count', 'failed_count', 'status', 'errors', 'sent_at',
    ];

    protected $casts = [
        'recipient_ids' => 'array',
        'errors' => 'array',
        'sent_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
