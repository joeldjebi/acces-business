<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvitationCardService
{
    protected $mailjetService;

    public function __construct(MailjetService $mailjetService)
    {
        $this->mailjetService = $mailjetService;
    }

    /**
     * Génère un QR code pour une inscription
     * Pour l'instant, on génère une URL simple. Plus tard, on pourra utiliser une bibliothèque comme simple-qrcode
     */
    public function generateQrCode(EventRegistration $registration): string
    {
        // Données à encoder dans le QR code
        $data = [
            'event_id' => $registration->event_id,
            'token' => $registration->token_unique,
            'email' => $registration->email,
            'nom' => $registration->nom,
            'prenom' => $registration->prenom,
            'fonction' => $registration->fonction,
        ];

        $qrData = json_encode($data);

        // Pour l'instant, on génère une URL simple
        // Plus tard, on pourra utiliser une bibliothèque comme simple-qrcode/simple-qrcode
        // ou endroid/qr-code pour générer une vraie image QR code

        // URL de vérification du QR code
        $verificationUrl = route('events.verify-qr', [
            'token' => $registration->token_unique,
        ]);

        // Pour l'instant, on retourne juste l'URL
        // TODO: Générer une vraie image QR code avec une bibliothèque
        return $verificationUrl;
    }

    /**
     * Génère et sauvegarde l'image QR code
     */
    public function generateAndSaveQrCodeImage(EventRegistration $registration): ?string
    {
        try {
            // Pour l'instant, on retourne null car on n'a pas de bibliothèque QR code
            // Plus tard, on pourra utiliser:
            // use SimpleSoftwareIO\QrCode\Facades\QrCode;
            // $qrCode = QrCode::format('png')->size(300)->generate($this->generateQrCode($registration));
            // $path = 'qr-codes/' . $registration->token_unique . '.png';
            // Storage::put($path, $qrCode);
            // return $path;

            // Pour l'instant, on retourne null
            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la génération du QR code', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Génère le HTML de la carte d'invitation
     */
    public function generateInvitationCardHtml(EventRegistration $registration): string
    {
        $event = $registration->event;
        $qrCodeUrl = $this->generateQrCode($registration);
        $cardDesign = $this->cardDesignForEvent($event);

        // Générer une URL pour le QR code (on utilisera une API simple pour l'instant)
        $qrCodeImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrCodeUrl);

        $html = view('emails.invitation-card', [
            'registration' => $registration,
            'event' => $event,
            'qrCodeImageUrl' => $qrCodeImageUrl,
            'qrCodeUrl' => $qrCodeUrl,
            'cardDesign' => $cardDesign,
        ])->render();

        return $html;
    }

    /**
     * Génère la version texte de la carte d'invitation
     */
    public function generateInvitationCardText(EventRegistration $registration): string
    {
        $event = $registration->event;
        $cardDesign = $this->cardDesignForEvent($event);

        $text = "INVITATION\n\n";
        $text .= "Bonjour " . $registration->nom_complet . ",\n\n";
        $text .= "Vous êtes invité(e) à l'événement :\n";
        $text .= $event->titre . "\n\n";
        $text .= "Date : " . $event->date_debut->format('d/m/Y') . " - " . $event->date_fin->format('d/m/Y') . "\n";
        $text .= "Heure : " . $event->heure_debut . " - " . $event->heure_fin . "\n";

        if ($event->lieu) {
            $text .= "Lieu : " . $event->lieu . "\n";
        }

        if ($event->ville) {
            $text .= "Ville : " . $event->ville . "\n";
        }

        $text .= "\nCode d'accès : " . $registration->token_unique . "\n";
        if (!empty($cardDesign['signature_text'])) {
            $text .= "\n" . $cardDesign['signature_text'] . "\n";
        }
        $text .= "\nCette carte est personnelle et non transférable.\n";

        return $text;
    }

    /**
     * Génère le PDF de la carte d'invitation
     */
    public function generateInvitationCardPdf(EventRegistration $registration): string
    {
        // Vérifier si DomPDF est installé
        if (!class_exists('\Dompdf\Dompdf')) {
            throw new \Exception('DomPDF n\'est pas installé. Veuillez exécuter: composer require dompdf/dompdf');
        }

        $event = $registration->event;
        $qrCodeUrl = $this->generateQrCode($registration);
        $cardDesign = $this->cardDesignForEvent($event, true);

        // Générer l'URL du QR code image
        $qrCodeImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrCodeUrl);

        // Télécharger l'image QR code pour l'inclure directement dans le PDF
        $qrCodeImageData = null;
        try {
            $qrCodeImageContent = @file_get_contents($qrCodeImageUrl);
            if ($qrCodeImageContent !== false) {
                $qrCodeImageData = 'data:image/png;base64,' . base64_encode($qrCodeImageContent);
            }
        } catch (\Exception $e) {
            \Log::warning('Impossible de télécharger l\'image QR code', [
                'error' => $e->getMessage(),
            ]);
        }

        // Générer le HTML pour le PDF
        $html = view('pdf.invitation-card', [
            'registration' => $registration,
            'event' => $event,
            'qrCodeImageUrl' => $qrCodeImageData ?: $qrCodeImageUrl,
            'qrCodeUrl' => $qrCodeUrl,
            'cardDesign' => $cardDesign,
        ])->render();

        // Configuration DomPDF (utilisation du nom complet de la classe)
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('chroot', base_path());
        $options->set('isPhpEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');

        try {
            $dompdf->render();
        } catch (\Exception $renderException) {
            \Log::error('Erreur lors du rendu PDF', [
                'error' => $renderException->getMessage(),
                'trace' => $renderException->getTraceAsString(),
            ]);
            throw $renderException;
        }

        // Sauvegarder le PDF temporairement
        $filename = 'invitation_' . $registration->token_unique . '.pdf';
        $tempDir = storage_path('app/temp');

        // Créer le dossier temp s'il n'existe pas
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $path = $tempDir . '/' . $filename;
        file_put_contents($path, $dompdf->output());

        return $path;
    }

    public function cardDesignForEvent(Event $event, bool $forPdf = false): array
    {
        $event->loadMissing('organization');

        $organization = $event->organization;
        $settings = $organization?->settings ?? [];
        $branding = $settings['branding'] ?? [];
        $invitationCard = $settings['invitation_card'] ?? [];

        $allowOrganizationBranding = (bool) ($invitationCard['allow_organization_branding'] ?? false);
        $primary = $allowOrganizationBranding ? ($this->validHex($branding['primary_color'] ?? null) ?: '#171713') : '#171713';
        $accent = $allowOrganizationBranding ? ($this->validHex($branding['accent_color'] ?? null) ?: '#b98943') : '#b98943';
        $brandName = $branding['brand_name'] ?? $organization?->name ?? 'Accès Business';
        $allowOrganizationLogo = (bool) ($invitationCard['allow_organization_logo'] ?? false);

        return [
            'brand_name' => $brandName,
            'primary_color' => $primary,
            'accent_color' => $accent,
            'allow_organization_logo' => $allowOrganizationLogo,
            'allow_organization_branding' => $allowOrganizationBranding,
            'organization_logo' => $allowOrganizationLogo ? $this->assetForCard($organization?->logo, $forPdf) : null,
            'organization_logo_blocked' => (bool) ($organization?->logo) && !$allowOrganizationLogo,
            'organization_branding_blocked' => !$allowOrganizationBranding && (!empty($branding['primary_color']) || !empty($branding['accent_color'])),
            'signature_text' => trim((string) ($invitationCard['signature_text'] ?? '')),
            'signature_logo' => $this->assetForCard($invitationCard['signature_logo'] ?? null, $forPdf),
        ];
    }

    private function assetForCard(?string $path, bool $forPdf): ?string
    {
        if (!$path) {
            return null;
        }

        if (!$forPdf) {
            return asset(Storage::disk('public')->url($path));
        }

        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        $absolutePath = Storage::disk('public')->path($path);
        $mimeType = mime_content_type($absolutePath) ?: 'image/png';

        return 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($absolutePath));
    }

    private function validHex(?string $color): ?string
    {
        return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : null;
    }

    /**
     * Envoie la carte d'invitation par email (méthode simplifiée)
     */
    public function sendInvitationCard(EventRegistration $registration): bool
    {
        // Ne pas renvoyer si déjà envoyée (sauf pour forcer le renvoi)
        // if ($registration->carte_envoyee) {
        //     return true;
        // }

        try {
            // S'assurer que la relation event est chargée
            if (!$registration->relationLoaded('event')) {
                $registration->load('event');
            }

            $event = $registration->event;

            if (!$event) {
                \Log::error('Événement introuvable pour la registration', [
                    'registration_id' => $registration->id,
                    'event_id' => $registration->event_id,
                ]);
                return false;
            }

            \Log::info('Début envoi carte d\'invitation', [
                'registration_id' => $registration->id,
                'event_id' => $event->id,
                'email' => $registration->email,
            ]);

            // Générer le contenu HTML et texte
            $html = $this->generateInvitationCardHtml($registration);
            $text = $this->generateInvitationCardText($registration);

            $subject = "Votre carte d'invitation - " . $event->titre;
            $toName = $registration->nom_complet ?: $registration->email;

            \Log::info('Envoi email carte d\'invitation', [
                'registration_id' => $registration->id,
                'email' => $registration->email,
                'subject' => $subject,
            ]);

            // Envoyer l'email directement (méthode simple)
            $result = $this->mailjetService->sendSimpleEmail(
                $registration->email,
                $toName,
                $subject,
                $text,
                $html
            );

            \Log::info('Résultat envoi email', [
                'registration_id' => $registration->id,
                'success' => $result['success'] ?? false,
                'message' => $result['message'] ?? 'N/A',
            ]);

            if ($result['success'] ?? false) {
                $registration->update(['carte_envoyee' => true]);
                \Log::info('Carte d\'invitation envoyée avec succès', [
                    'registration_id' => $registration->id,
                ]);
                return true;
            }

            \Log::error('Échec envoi carte d\'invitation', [
                'registration_id' => $registration->id,
                'result' => $result,
            ]);

            return false;
        } catch (\Exception $e) {
            \Log::error('Exception lors de l\'envoi de la carte d\'invitation', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return false;
        }
    }
}
