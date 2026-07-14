<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventOtpVerification;
use App\Services\MailjetService;
use Illuminate\Http\Request;

class EventOtpController extends Controller
{
    protected $mailjetService;

    public function __construct(MailjetService $mailjetService)
    {
        $this->mailjetService = $mailjetService;
    }

    /**
     * Demande un code OTP
     */
    public function requestOtp(Request $request, Event $event)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        }

        // Charger la relation visibilite si elle n'est pas déjà chargée
        $event->load('visibilite');

        // Vérifier que l'événement nécessite un OTP (privé ou sur invitation)
        if (!in_array($event->visibilite->libelle, ['Privé', 'Sur invitation'])) {
            return response()->json([
                'success' => false,
                'message' => 'Cet événement ne nécessite pas de vérification OTP.',
            ], 400);
        }

        // Générer un nouveau code OTP
        $otp = EventOtpVerification::generateOtp(
            $event->id,
            $validated['email'],
            $request->ip()
        );

        // Envoyer l'email avec le code OTP
        $subject = "Code de vérification - " . $event->titre;
        $html = view('emails.otp', [
            'event' => $event,
            'otpCode' => $otp->otp_code,
        ])->render();

        $text = "Bonjour,\n\n";
        $text .= "Vous avez demandé à accéder à l'événement \"" . $event->titre . "\".\n\n";
        $text .= "Votre code de vérification est : " . $otp->otp_code . "\n\n";
        $text .= "Ce code est valide pendant 15 minutes.\n\n";
        $text .= "Si vous n'avez pas demandé cet accès, ignorez cet email.\n";

        $result = $this->mailjetService->sendSimpleEmail(
            $validated['email'],
            $validated['email'],
            $subject,
            $text,
            $html
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Un code de vérification a été envoyé à votre email.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi du code. Veuillez réessayer.',
        ], 500);
    }

    /**
     * Vérifie le code OTP
     */
    public function verifyOtp(Request $request, Event $event)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email|max:255',
                'otp_code' => 'required|string|size:6',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Les données fournies sont invalides.',
                'errors' => $e->errors(),
            ], 422);
        }

        // Trouver le code OTP le plus récent et non vérifié
        $otp = EventOtpVerification::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otp) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP introuvable ou déjà utilisé.',
            ], 400);
        }

        // Vérifier le code
        if (!$otp->verify($validated['otp_code'])) {
            return response()->json([
                'success' => false,
                'message' => 'Code OTP incorrect ou expiré.',
            ], 400);
        }

        // Stocker en session que l'OTP est vérifié
        session([
            'otp_verified_' . $event->id => true,
            'otp_email_' . $event->id => $validated['email'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Code vérifié avec succès.',
            'redirect' => route('events.respond', $event),
        ]);
    }
}
