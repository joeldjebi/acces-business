<?php

namespace App\Services;

use App\Models\EventRegistration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use ZipArchive;

class WalletPassService
{
    public function linksFor(EventRegistration $registration): array
    {
        return [
            'apple' => $this->canGenerateApplePass() ? route('wallet.apple', $registration->token_unique) : null,
            'google' => $this->googleSaveUrl($registration),
        ];
    }

    public function generateApplePass(EventRegistration $registration): string
    {
        if (!$this->canGenerateApplePass()) {
            throw new \RuntimeException('Configuration Apple Wallet incomplète.');
        }

        $registration->loadMissing('event');

        $workDir = storage_path('app/temp/wallet/apple/' . $registration->token_unique . '-' . Str::random(8));
        File::ensureDirectoryExists($workDir);

        $files = [];
        $passJson = $this->applePassPayload($registration);
        file_put_contents($workDir . '/pass.json', json_encode($passJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $files[] = 'pass.json';

        foreach ($this->appleAssetFiles() as $filename => $source) {
            copy($source, $workDir . '/' . $filename);
            $files[] = $filename;
        }

        $manifest = [];
        foreach ($files as $file) {
            $manifest[$file] = sha1_file($workDir . '/' . $file);
        }
        file_put_contents($workDir . '/manifest.json', json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $this->signManifest($workDir . '/manifest.json', $workDir . '/signature');

        $passPath = storage_path('app/temp/wallet/apple/' . $registration->token_unique . '.pkpass');
        File::ensureDirectoryExists(dirname($passPath));
        if (file_exists($passPath)) {
            unlink($passPath);
        }

        $zip = new ZipArchive();
        if ($zip->open($passPath, ZipArchive::CREATE) !== true) {
            throw new \RuntimeException('Impossible de créer le fichier .pkpass.');
        }

        foreach (array_merge($files, ['manifest.json', 'signature']) as $file) {
            $zip->addFile($workDir . '/' . $file, $file);
        }
        $zip->close();

        File::deleteDirectory($workDir);

        return $passPath;
    }

    public function googleSaveUrl(EventRegistration $registration): ?string
    {
        try {
            $config = config('services.google_wallet');
            if (empty($config['issuer_id']) || empty($config['class_id']) || empty($config['service_account_path'])) {
                return null;
            }

            $serviceAccountPath = base_path($config['service_account_path']);
            if (!file_exists($serviceAccountPath)) {
                return null;
            }

            $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
            if (empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
                return null;
            }

            $registration->loadMissing('event');
            $object = $this->googleGenericObject($registration);
            $claims = [
                'iss' => $serviceAccount['client_email'],
                'aud' => 'google',
                'typ' => 'savetowallet',
                'iat' => time(),
                'payload' => [
                    'genericObjects' => [$object],
                ],
            ];

            return 'https://pay.google.com/gp/v/save/' . $this->jwt($claims, $serviceAccount['private_key']);
        } catch (\Throwable $e) {
            Log::warning('Impossible de générer le lien Google Wallet', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function canGenerateApplePass(): bool
    {
        $config = config('services.apple_wallet');
        foreach (['pass_type_identifier', 'team_identifier'] as $key) {
            if (empty($config[$key])) {
                return false;
            }
        }

        foreach (['certificate_path', 'key_path', 'wwdr_path'] as $key) {
            if (empty($config[$key]) || !file_exists(base_path($config[$key]))) {
                return false;
            }
        }

        return class_exists(ZipArchive::class) && function_exists('openssl_pkcs7_sign');
    }

    private function applePassPayload(EventRegistration $registration): array
    {
        $event = $registration->event;
        $holderName = $this->holderName($registration);
        $verificationUrl = route('events.verify-qr', $registration->token_unique);
        $config = config('services.apple_wallet');

        return [
            'formatVersion' => 1,
            'passTypeIdentifier' => $config['pass_type_identifier'],
            'serialNumber' => $registration->token_unique,
            'teamIdentifier' => $config['team_identifier'],
            'organizationName' => $config['organization_name'],
            'description' => $config['description'],
            'logoText' => $config['logo_text'],
            'foregroundColor' => $config['foreground_color'],
            'backgroundColor' => $config['background_color'],
            'labelColor' => $config['label_color'],
            'eventTicket' => [
                'primaryFields' => [
                    ['key' => 'event', 'label' => 'ÉVÉNEMENT', 'value' => $event->titre],
                ],
                'secondaryFields' => [
                    ['key' => 'guest', 'label' => 'INVITÉ', 'value' => $holderName],
                    ['key' => 'date', 'label' => 'DATE', 'value' => optional($event->date_debut)->format('d/m/Y') ?: 'À confirmer'],
                ],
                'auxiliaryFields' => [
                    ['key' => 'time', 'label' => 'HEURE', 'value' => trim(($event->heure_debut ?: '') . ($event->heure_fin ? ' - ' . $event->heure_fin : '')) ?: 'À confirmer'],
                    ['key' => 'place', 'label' => 'LIEU', 'value' => $event->lieu ?: ($event->ville ?: 'À confirmer')],
                ],
                'backFields' => [
                    ['key' => 'code', 'label' => 'Code d’accès', 'value' => $registration->token_unique],
                    ['key' => 'email', 'label' => 'Email', 'value' => $this->holderEmail($registration)],
                    ['key' => 'verify', 'label' => 'Vérification', 'value' => $verificationUrl],
                ],
            ],
            'barcodes' => [[
                'format' => 'PKBarcodeFormatQR',
                'message' => $verificationUrl,
                'messageEncoding' => 'iso-8859-1',
            ]],
            'barcode' => [
                'format' => 'PKBarcodeFormatQR',
                'message' => $verificationUrl,
                'messageEncoding' => 'iso-8859-1',
            ],
        ];
    }

    private function googleGenericObject(EventRegistration $registration): array
    {
        $event = $registration->event;
        $config = config('services.google_wallet');
        $objectId = $config['issuer_id'] . '.invitation_' . preg_replace('/[^A-Za-z0-9_]/', '_', $registration->token_unique);
        $verificationUrl = route('events.verify-qr', $registration->token_unique);
        $holderName = $this->holderName($registration);

        $object = [
            'id' => $objectId,
            'classId' => $config['class_id'],
            'state' => 'ACTIVE',
            'hexBackgroundColor' => $config['background_color'] ?: '#C9A227',
            'cardTitle' => [
                'defaultValue' => ['language' => 'fr-FR', 'value' => $event->titre],
            ],
            'subheader' => [
                'defaultValue' => ['language' => 'fr-FR', 'value' => 'Carte d’invitation'],
            ],
            'header' => [
                'defaultValue' => ['language' => 'fr-FR', 'value' => $holderName],
            ],
            'barcode' => [
                'type' => 'QR_CODE',
                'value' => $verificationUrl,
                'alternateText' => $registration->token_unique,
            ],
            'textModulesData' => [
                ['id' => 'date', 'header' => 'Date', 'body' => optional($event->date_debut)->format('d/m/Y') ?: 'À confirmer'],
                ['id' => 'time', 'header' => 'Heure', 'body' => trim(($event->heure_debut ?: '') . ($event->heure_fin ? ' - ' . $event->heure_fin : '')) ?: 'À confirmer'],
                ['id' => 'place', 'header' => 'Lieu', 'body' => $event->lieu ?: ($event->ville ?: 'À confirmer')],
            ],
            'linksModuleData' => [
                'uris' => [[
                    'id' => 'verify',
                    'uri' => $verificationUrl,
                    'description' => 'Vérifier le QR code',
                ]],
            ],
        ];

        if (!empty($config['logo_url'])) {
            $object['logo'] = [
                'sourceUri' => ['uri' => $config['logo_url']],
                'contentDescription' => ['defaultValue' => ['language' => 'fr-FR', 'value' => 'Logo']],
            ];
        }

        if (!empty($config['watermark_url'])) {
            $object['heroImage'] = [
                'sourceUri' => ['uri' => $config['watermark_url']],
                'contentDescription' => ['defaultValue' => ['language' => 'fr-FR', 'value' => 'My Signal']],
            ];
        }

        return $object;
    }

    private function appleAssetFiles(): array
    {
        $assetPath = base_path(config('services.apple_wallet.asset_path'));
        $files = [];
        foreach (['icon.png', 'icon@2x.png', 'logo.png', 'logo@2x.png'] as $file) {
            $path = $assetPath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($path)) {
                $files[$file] = $path;
            }
        }
        return $files;
    }

    private function signManifest(string $manifestPath, string $signaturePath): void
    {
        $config = config('services.apple_wallet');
        $command = [
            'openssl',
            'smime',
            '-binary',
            '-sign',
            '-certfile',
            base_path($config['wwdr_path']),
            '-signer',
            base_path($config['certificate_path']),
            '-inkey',
            base_path($config['key_path']),
            '-in',
            $manifestPath,
            '-out',
            $signaturePath,
            '-outform',
            'DER',
        ];

        if (!empty($config['key_password'])) {
            $command[] = '-passin';
            $command[] = 'pass:' . $config['key_password'];
        }

        $process = proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('OpenSSL est indisponible pour signer le pass Apple Wallet.');
        }

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if ($exitCode !== 0 || !is_file($signaturePath) || filesize($signaturePath) === 0) {
            Log::error('Signature Apple Wallet impossible.', [
                'exit_code' => $exitCode,
                'output' => trim($output),
                'error' => trim($error),
            ]);

            throw new \RuntimeException('Signature Apple Wallet impossible. Vérifiez les certificats Apple, la clé privée et le mot de passe.');
        }
    }

    private function jwt(array $claims, string $privateKey): string
    {
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $segments = [
            $this->base64Url(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->base64Url(json_encode($claims, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)),
        ];
        $signingInput = implode('.', $segments);

        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $segments[] = $this->base64Url($signature);

        return implode('.', $segments);
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function holderName(EventRegistration $registration): string
    {
        if ($registration->statut_reponse === 'represente' && $registration->representant_statut === 'confirme') {
            return trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? '')) ?: $registration->representant_email;
        }

        return $registration->nom_complet ?: $registration->email;
    }

    private function holderEmail(EventRegistration $registration): string
    {
        if ($registration->statut_reponse === 'represente' && $registration->representant_statut === 'confirme') {
            return $registration->representant_email ?: $registration->email;
        }

        return $registration->email;
    }
}
