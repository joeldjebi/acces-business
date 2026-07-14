<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MailjetService
{
    protected $apiUrl;
    protected $apiKeyPublic;
    protected $apiKeyPrivate;

    public function __construct()
    {
        $this->apiUrl = 'https://api.mailjet.com/v3.1/send';
        $this->apiKeyPublic = config('services.mailjet.api_key_public');
        $this->apiKeyPrivate = config('services.mailjet.api_key_private');
    }

    /**
     * Envoie un email via Mailjet
     *
     * @param string $toEmail Email du destinataire
     * @param string $toName Nom du destinataire
     * @param string $subject Sujet de l'email
     * @param string $textPart Version texte de l'email
     * @param string $htmlPart Version HTML de l'email
     * @param string|null $fromEmail Email de l'expéditeur (optionnel, utilise la config par défaut)
     * @param string|null $fromName Nom de l'expéditeur (optionnel, utilise la config par défaut)
     * @param array|null $attachments Tableau d'attachments [['ContentType' => '...', 'Filename' => '...', 'Base64Content' => '...']]
     * @return array ['success' => bool, 'message' => string, 'data' => array|null, 'http_code' => int|null]
     */
    public function sendEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $textPart,
        string $htmlPart,
        ?string $fromEmail = null,
        ?string $fromName = null,
        ?array $attachments = null
    ): array {
        if (!$this->apiKeyPublic || !$this->apiKeyPrivate) {
            Log::error('Mailjet Service: Clés API non configurées');
            return [
                'success' => false,
                'message' => 'Configuration Mailjet manquante',
            ];
        }

        // Utiliser les valeurs par défaut de la config si non fournies
        $fromEmail = $fromEmail ?? config('services.mailjet.from_email');
        $fromName = $fromName ?? config('services.mailjet.from_name');

        if (!$fromEmail) {
            Log::error('Mailjet Service: Email expéditeur non configuré');
            return [
                'success' => false,
                'message' => 'Email expéditeur non configuré',
            ];
        }

        try {
            // Construire le payload
            $message = [
                'From' => [
                    'Email' => $fromEmail,
                    'Name' => $fromName ?? 'SmartVDH',
                ],
                'To' => [
                    [
                        'Email' => $toEmail,
                        'Name' => $toName,
                    ],
                ],
                'Subject' => $subject,
                'TextPart' => $textPart,
                'HTMLPart' => $htmlPart,
            ];

            // Ajouter les pièces jointes si présentes
            if (!empty($attachments) && is_array($attachments)) {
                $message['Attachments'] = $attachments;
            }

            $payload = [
                'Messages' => [$message],
            ];

            $postData = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            // Log pour débogage (sans le contenu complet pour la sécurité)
            Log::info('Mailjet Service: Tentative d\'envoi', [
                'to_email' => $toEmail,
                'from_email' => $fromEmail,
                'subject' => $subject,
                'has_attachments' => !empty($attachments),
            ]);

            // Initialiser cURL
            $ch = curl_init($this->apiUrl);

            // Configurer les options cURL
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_USERPWD => $this->apiKeyPublic . ':' . $this->apiKeyPrivate,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_TIMEOUT => 30,
            ]);

            // Exécuter la requête
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            // Fermer la session cURL
            curl_close($ch);

            // Vérifier les erreurs cURL
            if ($curlError) {
                Log::error('Mailjet Service: Erreur cURL', [
                    'to_email' => $toEmail,
                    'error' => $curlError,
                ]);

                return [
                    'success' => false,
                    'message' => 'Erreur cURL: ' . $curlError,
                ];
            }

            // Décoder la réponse JSON
            $responseData = json_decode($response, true);

            // Log de la réponse complète pour débogage
            Log::info('Mailjet Service: Réponse API', [
                'to_email' => $toEmail,
                'http_code' => $httpCode,
                'response' => $responseData,
                'raw_response' => $response,
            ]);

            // Vérifier le code HTTP
            if ($httpCode >= 200 && $httpCode < 300) {
                // Mailjet retourne 200 avec une structure Messages
                // Vérifier s'il y a des erreurs dans la réponse
                if (isset($responseData['Messages']) && is_array($responseData['Messages'])) {
                    $messageStatus = $responseData['Messages'][0] ?? null;

                    // Vérifier s'il y a des erreurs
                    if (isset($messageStatus['Errors']) && !empty($messageStatus['Errors'])) {
                        $errorMessage = $messageStatus['Errors'][0]['ErrorMessage'] ?? 'Erreur inconnue de Mailjet';
                        $errorCode = $messageStatus['Errors'][0]['ErrorCode'] ?? null;

                        Log::error('Mailjet Service: Erreur dans la réponse Mailjet', [
                            'to_email' => $toEmail,
                            'http_code' => $httpCode,
                            'error_code' => $errorCode,
                            'error_message' => $errorMessage,
                            'response' => $responseData,
                        ]);

                        return [
                            'success' => false,
                            'message' => 'Erreur Mailjet: ' . $errorMessage . ($errorCode ? ' (Code: ' . $errorCode . ')' : ''),
                            'error' => $responseData,
                            'http_code' => $httpCode,
                        ];
                    }

                    // Pas d'erreurs, considérer comme succès
                    $messageId = $messageStatus['To'][0]['MessageID'] ?? null;
                    Log::info('Mailjet Service: Email envoyé avec succès', [
                        'to_email' => $toEmail,
                        'http_code' => $httpCode,
                        'message_id' => $messageId,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Email envoyé avec succès',
                        'data' => $responseData,
                        'http_code' => $httpCode,
                    ];
                } else {
                    // Réponse HTTP OK mais structure différente (peut être valide)
                    Log::info('Mailjet Service: Email envoyé (structure de réponse différente)', [
                        'to_email' => $toEmail,
                        'http_code' => $httpCode,
                        'response' => $responseData,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Email envoyé avec succès',
                        'data' => $responseData,
                        'http_code' => $httpCode,
                    ];
                }
            } else {
                // Erreur HTTP
                $errorMessage = 'Erreur HTTP ' . $httpCode;
                if (is_array($responseData)) {
                    if (isset($responseData['ErrorMessage'])) {
                        $errorMessage = $responseData['ErrorMessage'];
                    } elseif (isset($responseData['message'])) {
                        $errorMessage = $responseData['message'];
                    } elseif (isset($responseData['error'])) {
                        $errorMessage = is_string($responseData['error']) ? $responseData['error'] : json_encode($responseData['error']);
                    }
                }

                Log::error('Mailjet Service: Erreur HTTP lors de l\'envoi', [
                    'to_email' => $toEmail,
                    'http_code' => $httpCode,
                    'error' => $errorMessage,
                    'response' => $responseData,
                ]);

                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'envoi de l\'email: ' . $errorMessage,
                    'error' => $responseData,
                    'http_code' => $httpCode,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Mailjet Service: Exception lors de l\'envoi', [
                'to_email' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Envoie un email simple sans pièce jointe
     *
     * @param string $toEmail Email du destinataire
     * @param string $toName Nom du destinataire
     * @param string $subject Sujet de l'email
     * @param string $textPart Version texte de l'email
     * @param string $htmlPart Version HTML de l'email
     * @return array
     */
    public function sendSimpleEmail(
        string $toEmail,
        string $toName,
        string $subject,
        string $textPart,
        string $htmlPart
    ): array {
        return $this->sendEmail($toEmail, $toName, $subject, $textPart, $htmlPart);
    }

    /**
     * Envoie un email avec pièce jointe
     *
     * @param string $toEmail Email du destinataire
     * @param string $toName Nom du destinataire
     * @param string $subject Sujet de l'email
     * @param string $textPart Version texte de l'email
     * @param string $htmlPart Version HTML de l'email
     * @param string $attachmentPath Chemin vers le fichier à joindre
     * @param string|null $attachmentName Nom du fichier (optionnel, utilise le nom du fichier si non fourni)
     * @return array
     */
    public function sendEmailWithAttachment(
        string $toEmail,
        string $toName,
        string $subject,
        string $textPart,
        string $htmlPart,
        string $attachmentPath,
        ?string $attachmentName = null
    ): array {
        if (!file_exists($attachmentPath)) {
            Log::error('Mailjet Service: Fichier joint introuvable', [
                'attachment_path' => $attachmentPath,
            ]);

            return [
                'success' => false,
                'message' => 'Fichier joint introuvable',
            ];
        }

        // Lire le fichier et le convertir en base64
        $fileContent = file_get_contents($attachmentPath);
        $base64Content = base64_encode($fileContent);

        // Déterminer le ContentType
        $mimeType = mime_content_type($attachmentPath);
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }

        // Utiliser le nom du fichier si non fourni
        $filename = $attachmentName ?? basename($attachmentPath);

        $attachments = [
            [
                'ContentType' => $mimeType,
                'Filename' => $filename,
                'Base64Content' => $base64Content,
            ],
        ];

        return $this->sendEmail($toEmail, $toName, $subject, $textPart, $htmlPart, null, null, $attachments);
    }
}

