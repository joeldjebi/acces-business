<?php

namespace App\Services;

use App\Support\MailSettings;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envoi des emails par SMTP (réglages définis dans la console plateforme).
 * Même format de retour que MailjetService.
 */
class SmtpMailService
{
    private const MAILER = 'platform_smtp';

    /**
     * @param array|null $attachments [['ContentType' => '...', 'Filename' => '...', 'Base64Content' => '...']]
     */
    public function send(
        string $toEmail,
        string $toName,
        string $subject,
        string $textPart,
        string $htmlPart,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?array $attachments = null,
        ?array $smtp = null
    ): array {
        $smtp ??= MailSettings::smtp();
        $fromEmail ??= $smtp['from_email'];
        $fromName ??= $smtp['from_name'];

        if (!$smtp['host'] || !$fromEmail) {
            Log::error('SMTP : hôte ou adresse d’expédition non configuré');

            return ['success' => false, 'message' => 'L’email n’a pas pu être envoyé. Réessayez plus tard.'];
        }

        // Mailer construit à la volée avec les réglages courants.
        config(['mail.mailers.' . self::MAILER => collect($smtp)->except(['from_email', 'from_name'])->all()]);
        app('mail.manager')->purge(self::MAILER);

        try {
            Mail::mailer(self::MAILER)->send([], [], function (Message $message) use ($toEmail, $toName, $subject, $textPart, $htmlPart, $fromEmail, $fromName, $attachments) {
                $message->to($toEmail, $toName)
                    ->from($fromEmail, $fromName)
                    ->subject($subject)
                    ->html($htmlPart)
                    ->text($textPart);

                foreach ($attachments ?? [] as $attachment) {
                    $message->attachData(base64_decode($attachment['Base64Content']), $attachment['Filename'], ['mime' => $attachment['ContentType'] ?? 'application/octet-stream']);
                }
            });

            return ['success' => true, 'message' => 'Email envoyé avec succès'];
        } catch (\Throwable $e) {
            Log::error('SMTP : échec de l’envoi', ['to_email' => $toEmail, 'host' => $smtp['host'], 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'L’email n’a pas pu être envoyé. Réessayez plus tard.', 'error' => $e->getMessage()];
        }
    }
}
