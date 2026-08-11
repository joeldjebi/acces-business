<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message): array
    {
        $to = trim($to);
        if ($to === '') {
            return ['success' => false, 'message' => 'Numéro de téléphone manquant.'];
        }

        $token = config('services.mailjet_sms.token');
        $sender = config('services.mailjet_sms.sender', config('app.name', 'My Signal'));

        if (!$token) {
            Log::warning('SMS non envoyé: MAILJET_SMS_TOKEN manquant', [
                'to' => $to,
                'message_preview' => substr($message, 0, 80),
            ]);

            return ['success' => false, 'message' => 'Configuration SMS manquante: MAILJET_SMS_TOKEN.'];
        }

        try {
            $payload = json_encode([
                'From' => substr((string) $sender, 0, 11),
                'To' => $to,
                'Text' => $message,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $ch = curl_init('https://api.mailjet.com/v4/sms-send');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token,
                ],
                CURLOPT_TIMEOUT => 30,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                return ['success' => false, 'message' => 'Erreur SMS cURL: ' . $curlError];
            }

            $data = json_decode((string) $response, true);
            $success = $httpCode >= 200 && $httpCode < 300;

            Log::info('Réponse SMS Mailjet', [
                'to' => $to,
                'http_code' => $httpCode,
                'success' => $success,
                'response' => $data,
            ]);

            return [
                'success' => $success,
                'message' => $success ? 'SMS envoyé avec succès.' : ($data['ErrorMessage'] ?? $data['message'] ?? 'Erreur lors de l’envoi SMS.'),
                'data' => $data,
                'http_code' => $httpCode,
            ];
        } catch (\Throwable $e) {
            Log::error('Exception SMS', ['to' => $to, 'error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
