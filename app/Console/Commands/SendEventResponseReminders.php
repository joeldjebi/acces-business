<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Services\EventCommunicationService;
use Illuminate\Console\Command;

class SendEventResponseReminders extends Command
{
    protected $signature = 'events:send-response-reminders {--limit=200} {--force : Ignore l’heure configurée et traite toutes les organisations actives}';

    protected $description = 'Envoie les relances automatiques aux invités qui n’ont pas encore répondu.';

    public function handle(EventCommunicationService $eventCommunicationService): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $force = (bool) $this->option('force');
        $currentTime = now()->format('H:i');

        $organizations = Organization::query()
            ->whereIn('status', ['active', 'trialing'])
            ->orderBy('id')
            ->get()
            ->filter(fn (Organization $organization) => $force || $this->shouldRunForOrganization($organization, $currentTime));

        $sent = 0;
        $eligible = 0;

        foreach ($organizations as $organization) {
            $registrations = $eventCommunicationService->pendingReminderQuery(null, $organization)
                ->limit($limit)
                ->get();

            $eligible += $registrations->count();

            foreach ($registrations as $registration) {
                if ($eventCommunicationService->sendReminder($registration, null, true)) {
                    $sent++;
                }
            }
        }

        $this->info($sent . ' relance(s) envoyée(s) sur ' . $eligible . ' inscription(s) éligible(s), ' . $organizations->count() . ' organisation(s) traitée(s).');

        return self::SUCCESS;
    }

    private function shouldRunForOrganization(Organization $organization, string $currentTime): bool
    {
        $settings = $organization->settings ?? [];
        $reminders = $settings['response_reminders'] ?? [];

        if (($reminders['enabled'] ?? true) === false) {
            return false;
        }

        return ($reminders['time'] ?? '08:00') === $currentTime;
    }
}
