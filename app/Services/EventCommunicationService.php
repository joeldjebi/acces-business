<?php

namespace App\Services;

use App\Models\CommunicationCreditBalance;
use App\Models\Event;
use App\Models\EventAccessLink;
use App\Models\EventRegistration;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventCommunicationService
{
    public function __construct(
        private MailjetService $mailjetService,
        private SmsService $smsService,
        private EventRegistrationActivityLogger $activityLogger
    ) {
    }

    public function sendReminder(EventRegistration $registration, ?Request $request = null, bool $automatic = false): bool
    {
        $registration->loadMissing('event');
        $event = $registration->event;

        if (!$registration->email || $registration->statut_reponse !== 'en_attente') {
            return false;
        }

        $name = $registration->nom_complet ?: $registration->email;
        $responseUrl = route('events.respond', $event);
        $accessUrl = $this->accessUrlFor($registration) ?: $responseUrl;
        $subject = 'Rappel de réponse - ' . $event->titre;

        $text = "Bonjour {$name},

";
        $text .= "Nous attendons encore votre réponse pour l'événement : {$event->titre}.
";
        $text .= "Répondre à l'invitation : {$accessUrl}

";
        $text .= "Merci.
";

        $html = view('emails.event-reminder', [
            'registration' => $registration,
            'event' => $event,
            'name' => $name,
            'responseUrl' => $accessUrl,
        ])->render();

        $result = $this->mailjetService->sendSimpleEmail($registration->email, $name, $subject, $text, $html);
        $success = (bool) ($result['success'] ?? false);

        if ($success) {
            $registration->forceFill([
                'last_reminder_sent_at' => now(),
                'reminder_count' => ((int) $registration->reminder_count) + 1,
            ])->save();
        }

        $this->activityLogger->log(
            $registration,
            $success ? 'reminder_sent' : 'reminder_failed',
            $success ? 'Relance envoyée' : 'Relance échouée',
            $result['message'] ?? null,
            ['automatic' => $automatic, 'email' => $registration->email],
            $request,
            $automatic ? 'system' : null
        );

        return $success;
    }

    /**
     * Relance un invité qui n'a pas encore ouvert son lien d'invitation (aucune inscription).
     */
    public function sendLinkReminder(EventAccessLink $link, bool $automatic = false): bool
    {
        $link->loadMissing('event');
        $event = $link->event;

        if (!$event || !$link->email_destinataire || $link->est_utilise) {
            return false;
        }

        $name = trim(($link->prenom ?? '') . ' ' . ($link->nom ?? '')) ?: $link->email_destinataire;
        $accessUrl = $link->access_url;
        $subject = 'Rappel de réponse - ' . $event->titre;
        $text = "Bonjour {$name},\n\nNous attendons encore votre réponse pour l'événement : {$event->titre}.\nRépondre à l'invitation : {$accessUrl}\n\nMerci.\n";
        $html = view('emails.event-reminder', [
            'event' => $event,
            'name' => $name,
            'responseUrl' => $accessUrl,
        ])->render();

        $result = $this->mailjetService->sendSimpleEmail($link->email_destinataire, $name, $subject, $text, $html);
        $success = (bool) ($result['success'] ?? false);

        if ($success) {
            $link->forceFill([
                'last_reminder_sent_at' => now(),
                'reminder_count' => ((int) $link->reminder_count) + 1,
            ])->save();
        } else {
            \Illuminate\Support\Facades\Log::warning('Relance d’invitation échouée', [
                'access_link_id' => $link->id,
                'automatic' => $automatic,
                'message' => $result['message'] ?? null,
            ]);
        }

        return $success;
    }

    public function sendThankYouSms(EventRegistration $registration, ?Request $request = null): array
    {
        $registration->loadMissing('event');
        $phone = $registration->checkInContact();

        if (!$phone) {
            $message = 'Téléphone du participant manquant.';
            $this->markThankYou($registration, false, $message, $request);
            return ['success' => false, 'message' => $message];
        }

        $balance = CommunicationCreditBalance::firstOrCreate(
            ['organization_id' => $registration->organization_id, 'channel' => 'sms'],
            ['purchased' => 0, 'used' => 0]
        );

        if ($balance->remaining < 1) {
            $message = 'Crédits SMS insuffisants.';
            $this->markThankYou($registration, false, $message, $request);
            return ['success' => false, 'message' => $message];
        }

        $result = $this->smsService->send($phone, $this->thankYouMessage($registration));
        $success = (bool) ($result['success'] ?? false);

        if ($success) {
            DB::transaction(function () use ($balance, $registration, $request) {
                $freshBalance = CommunicationCreditBalance::whereKey($balance->id)->lockForUpdate()->first();
                if (!$freshBalance || $freshBalance->remaining < 1) {
                    throw new \RuntimeException('Crédits SMS insuffisants.');
                }

                $freshBalance->increment('used');
                $this->markThankYou($registration, true, null, $request);
            });
        } else {
            $this->markThankYou($registration, false, $result['message'] ?? 'Erreur lors de l’envoi SMS.', $request);
        }

        return $result;
    }

    public function pendingReminderQuery(?Event $event = null, ?Organization $organization = null)
    {
        $query = EventRegistration::query()
            ->with('event')
            ->where('statut_reponse', 'en_attente')
            ->whereNotNull('email')
            ->where(function ($q) {
                $q->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<=', now()->subDay());
            })
            ->whereHas('event', function ($eventQuery) {
                $eventQuery->where('statut', 'publie')
                    ->whereDate('date_debut', '>=', now()->toDateString());
            });

        if ($event) {
            $query->where('event_id', $event->id)
                ->where('organization_id', $event->organization_id);
        }

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        return $query;
    }

    /**
     * Invitations envoyées mais jamais ouvertes : pas encore d'inscription pour cet email sur l'événement.
     */
    public function pendingLinkReminderQuery(?Event $event = null, ?Organization $organization = null)
    {
        $query = EventAccessLink::query()
            ->with('event')
            ->where('est_utilise', false)
            ->whereNotNull('email_destinataire')
            ->where(function ($q) {
                $q->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<=', now()->subDay());
            })
            ->whereHas('event', function ($eventQuery) {
                $eventQuery->where('statut', 'publie')
                    ->whereDate('date_debut', '>=', now()->toDateString());
            })
            ->whereNotExists(function ($registration) {
                $registration->selectRaw(1)
                    ->from('event_registrations')
                    ->whereColumn('event_registrations.event_id', 'event_access_links.event_id')
                    ->whereColumn('event_registrations.email', 'event_access_links.email_destinataire');
            });

        if ($event) {
            $query->where('event_id', $event->id)
                ->where('organization_id', $event->organization_id);
        }

        if ($organization) {
            $query->where('organization_id', $organization->id);
        }

        return $query;
    }

    private function accessUrlFor(EventRegistration $registration): ?string
    {
        $link = $registration->event?->accessLinks()
            ->where('organization_id', $registration->organization_id)
            ->where('email_destinataire', $registration->email)
            ->latest()
            ->first();

        return $link?->access_url;
    }

    private function markThankYou(EventRegistration $registration, bool $success, ?string $error, ?Request $request): void
    {
        $registration->forceFill([
            'thank_you_sms_sent_at' => $success ? now() : $registration->thank_you_sms_sent_at,
            'thank_you_sms_status' => $success ? 'sent' : 'failed',
            'thank_you_sms_error' => $success ? null : $error,
        ])->save();

        $this->activityLogger->log(
            $registration,
            $success ? 'thank_you_sms_sent' : 'thank_you_sms_failed',
            $success ? 'SMS de remerciement envoyé' : 'SMS de remerciement échoué',
            $error,
            ['phone' => $registration->checkInContact()],
            $request
        );
    }

    private function thankYouMessage(EventRegistration $registration): string
    {
        $event = $registration->event;
        $name = $registration->checkInDisplayName();

        return "Merci {$name} pour votre présence à {$event->titre}. Nous sommes ravis de vous avoir accueilli(e).";
    }
}
