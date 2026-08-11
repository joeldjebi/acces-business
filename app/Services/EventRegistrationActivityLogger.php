<?php

namespace App\Services;

use App\Models\EventRegistration;
use App\Models\EventRegistrationActivity;
use Illuminate\Http\Request;

class EventRegistrationActivityLogger
{
    public function log(EventRegistration $registration, string $type, string $title, ?string $description = null, array $metadata = [], ?Request $request = null, ?string $actorType = null): EventRegistrationActivity
    {
        $registration->loadMissing('event');

        return EventRegistrationActivity::create([
            'organization_id' => $registration->organization_id,
            'event_id' => $registration->event_id,
            'event_registration_id' => $registration->id,
            'actor_user_id' => auth()->id(),
            'actor_type' => $actorType ?: (auth()->check() ? 'user' : 'system'),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
        ]);
    }
}
