<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Crypt;

/**
 * Choix du service d'envoi des emails, défini par l'administrateur de la plateforme.
 *
 * - "mailjet" : système actuel (API Mailjet, clés dans le .env)
 * - "smtp"    : serveur SMTP ; les champs laissés vides reprennent les valeurs MAIL_* du .env
 */
class MailSettings
{
    public const KEY = 'mail_delivery';
    public const PROVIDERS = ['mailjet', 'smtp'];

    public static function get(): array
    {
        $stored = PlatformSetting::get(self::KEY, []) ?: [];

        return [
            'provider' => in_array($stored['provider'] ?? null, self::PROVIDERS, true) ? $stored['provider'] : 'mailjet',
            'host' => $stored['host'] ?? null,
            'port' => $stored['port'] ?? null,
            'encryption' => $stored['encryption'] ?? null,
            'username' => $stored['username'] ?? null,
            'has_password' => !empty($stored['password']),
            'from_email' => $stored['from_email'] ?? null,
            'from_name' => $stored['from_name'] ?? null,
        ];
    }

    public static function provider(): string
    {
        return self::get()['provider'];
    }

    /**
     * Configuration SMTP effective : réglages de la plateforme, sinon valeurs du .env.
     */
    public static function smtp(): array
    {
        $stored = PlatformSetting::get(self::KEY, []) ?: [];
        $password = null;
        if (!empty($stored['password'])) {
            try {
                $password = Crypt::decryptString($stored['password']);
            } catch (\Throwable) {
                $password = null;
            }
        }

        $encryption = $stored['encryption'] ?? null;

        return [
            'transport' => 'smtp',
            // "ssl" = connexion chiffrée dès le départ (port 465) ; sinon STARTTLS est négocié automatiquement.
            'scheme' => $encryption === 'ssl' ? 'smtps' : (($encryption === null && config('mail.mailers.smtp.scheme')) ? config('mail.mailers.smtp.scheme') : 'smtp'),
            'host' => $stored['host'] ?: config('mail.mailers.smtp.host'),
            'port' => (int) ($stored['port'] ?: config('mail.mailers.smtp.port')),
            'username' => $stored['username'] ?: config('mail.mailers.smtp.username'),
            'password' => $password ?? config('mail.mailers.smtp.password'),
            'timeout' => 20,
            'local_domain' => config('mail.mailers.smtp.local_domain'),
            'from_email' => $stored['from_email'] ?: config('mail.from.address'),
            'from_name' => $stored['from_name'] ?: config('mail.from.name'),
        ];
    }

    public static function save(array $values): void
    {
        $current = PlatformSetting::get(self::KEY, []) ?: [];

        $data = [
            'provider' => $values['provider'],
            'host' => $values['host'] ?? null,
            'port' => $values['port'] ?? null,
            'encryption' => $values['encryption'] ?? null,
            'username' => $values['username'] ?? null,
            'from_email' => $values['from_email'] ?? null,
            'from_name' => $values['from_name'] ?? null,
            // Mot de passe chiffré ; champ vide = on garde celui déjà enregistré.
            'password' => !empty($values['password']) ? Crypt::encryptString($values['password']) : ($current['password'] ?? null),
        ];

        if (!empty($values['remove_password'])) {
            $data['password'] = null;
        }

        PlatformSetting::put(self::KEY, $data);
    }
}
