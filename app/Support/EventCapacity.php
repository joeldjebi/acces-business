<?php

namespace App\Support;

use App\Models\Event;
use App\Models\EventAccessLink;
use App\Models\EventRegistration;

/**
 * Places d'un événement (capacité définie par la structure).
 *
 * Une place est occupée par chaque personne invitée ou inscrite (un email = une place),
 * sauf si elle a répondu « Absent » : sa place est alors libérée.
 * Un invité représenté garde sa place (le représentant vient à sa place).
 */
class EventCapacity
{
    public static function occupied(Event $event): int
    {
        return self::occupiedEmails($event)->count();
    }

    /**
     * Places restantes, ou null si l'événement n'a pas de capacité définie.
     */
    public static function remaining(Event $event): ?int
    {
        if (!$event->capacite_maximale) {
            return null;
        }

        return max(0, (int) $event->capacite_maximale - self::occupied($event));
    }

    public static function canAccept(Event $event, int $places = 1): bool
    {
        $remaining = self::remaining($event);

        return $remaining === null || $remaining >= $places;
    }

    /**
     * Vrai si cet email occupe déjà une place (invité ou inscrit, non absent).
     */
    public static function holdsPlace(Event $event, ?string $email): bool
    {
        return $email !== null && self::occupiedEmails($event)->contains(mb_strtolower(trim($email)));
    }

    public static function summary(Event $event): array
    {
        $occupied = self::occupied($event);
        $capacity = $event->capacite_maximale ? (int) $event->capacite_maximale : null;

        return [
            'capacity' => $capacity,
            'occupied' => $occupied,
            'remaining' => $capacity === null ? null : max(0, $capacity - $occupied),
        ];
    }

    public static function fullMessage(Event $event, int $requested = 1): string
    {
        $remaining = (int) self::remaining($event);

        return $remaining === 0
            ? 'L’événement est complet : les ' . (int) $event->capacite_maximale . ' place(s) sont attribuées.'
            : "Il reste {$remaining} place(s) sur " . (int) $event->capacite_maximale . ", mais {$requested} sont demandées.";
    }

    private static function occupiedEmails(Event $event)
    {
        $normalize = fn ($email) => mb_strtolower(trim((string) $email));

        $registrations = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->get(['email', 'statut_reponse']);

        $absent = $registrations->where('statut_reponse', 'absent')->pluck('email')->map($normalize);
        $registered = $registrations->where('statut_reponse', '!=', 'absent')->pluck('email')->map($normalize);
        $invited = EventAccessLink::forOrganization($event->organization_id)
            ->where('event_id', $event->id)
            ->pluck('email_destinataire')
            ->map($normalize);

        return $invited->diff($absent)->merge($registered)->filter()->unique()->values();
    }
}
