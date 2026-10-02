<?php

namespace App\Jobs;

use App\Models\CommunicationCreditBalance;
use App\Models\EventBroadcast;
use App\Models\EventRegistration;
use App\Services\MailjetService;
use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SendEventBroadcast implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1200;
    public int $tries = 1;

    public function __construct(public int $broadcastId)
    {
    }

    public function handle(MailjetService $mailjet, SmsService $sms): void
    {
        $broadcast = EventBroadcast::with('event')->find($this->broadcastId);
        if (!$broadcast || $broadcast->status === 'completed') {
            return;
        }

        $broadcast->update(['status' => 'processing']);
        $sent = 0;
        $failed = 0;
        $errors = [];
        $fileUrl = $broadcast->attachment_token
            ? route('event-communications.attachment', $broadcast->attachment_token)
            : null;
        $attachmentPath = $broadcast->attachment_path
            ? Storage::disk('local')->path($broadcast->attachment_path)
            : null;

        EventRegistration::whereIn('id', $broadcast->recipient_ids ?? [])
            ->where('event_id', $broadcast->event_id)
            ->where('organization_id', $broadcast->organization_id)
            ->orderBy('id')
            ->each(function (EventRegistration $registration) use ($broadcast, $mailjet, $sms, $fileUrl, $attachmentPath, &$sent, &$failed, &$errors) {
                $name = $registration->nom_complet ?: $registration->email;

                if ($broadcast->channel === 'email') {
                    $html = view('emails.event-broadcast', compact('broadcast', 'registration', 'name', 'fileUrl'))->render();
                    $text = "Bonjour {$name},\n\n{$broadcast->message}" . ($fileUrl ? "\n\nFichier : {$fileUrl}" : '');
                    $result = $attachmentPath
                        ? $mailjet->sendEmailWithAttachment($registration->email, $name, $broadcast->subject, $text, $html, $attachmentPath, $broadcast->attachment_name)
                        : $mailjet->sendSimpleEmail($registration->email, $name, $broadcast->subject, $text, $html);
                } else {
                    $message = $broadcast->message . ($fileUrl ? "\n{$fileUrl}" : '');
                    $result = $sms->send($registration->telephone, $message);
                }

                if ($result['success'] ?? false) {
                    $sent++;
                } else {
                    $failed++;
                    if (count($errors) < 50) {
                        $errors[] = ($broadcast->channel === 'email' ? $registration->email : $registration->telephone)
                            . ' : ' . ($result['message'] ?? 'Échec inconnu');
                    }
                }
            });

        if ($broadcast->channel === 'sms' && $failed > 0) {
            DB::transaction(function () use ($broadcast, $failed) {
                $balance = CommunicationCreditBalance::where('organization_id', $broadcast->organization_id)
                    ->where('channel', 'sms')
                    ->lockForUpdate()
                    ->first();
                if ($balance) {
                    $balance->update(['used' => max(0, $balance->used - $failed)]);
                }
            });
        }

        $broadcast->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
            'status' => $failed === $broadcast->recipient_count ? 'failed' : 'completed',
            'errors' => $errors ?: null,
            'sent_at' => now(),
        ]);
    }
}
