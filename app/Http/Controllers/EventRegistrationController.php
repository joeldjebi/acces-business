<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\InvitationCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventRegistrationController extends Controller
{
    protected $invitationCardService;

    public function __construct(InvitationCardService $invitationCardService)
    {
        $this->invitationCardService = $invitationCardService;
    }

    /**
     * Inscription à un événement public
     */
    public function store(Request $request, Event $event)
    {
        // Vérifier que l'événement est public
        if ($event->visibilite->libelle !== 'Public') {
            return redirect()->back()->with('error', 'Cet événement n\'est pas public.');
        }

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'nullable|string|max:20',
            'entreprise' => 'nullable|string|max:255',
        ]);

        // Vérifier si l'email n'est pas déjà inscrit pour cet événement
        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existingRegistration) {
            return redirect()->back()->with('error', 'Vous êtes déjà inscrit à cet événement avec cet email.');
        }

        // Créer l'inscription
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'email' => $validated['email'],
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'] ?? null,
            'entreprise' => $validated['entreprise'] ?? null,
            'statut_reponse' => 'en_attente',
            'user_id' => auth()->id(),
        ]);

        // Envoyer immédiatement la carte d'invitation
        $this->invitationCardService->sendInvitationCard($registration);

        return redirect()->route('events.show', $event)
            ->with('success', 'Inscription réussie ! Votre carte d\'invitation a été envoyée par email.');
    }

    /**
     * Affiche la page de confirmation après la réponse
     */
    public function showResponseConfirmation(Request $request, Event $event)
    {
        $event->load(['category', 'visibilite']);
        $message = session('success') ?: session('warning');
        $invitationToken = session('invitation_token');
        $email = session('registration_email');

        return view('events.response-confirmation', compact('event', 'message', 'invitationToken', 'email'));
    }

    /**
     * Affiche le formulaire de réponse (après validation OTP pour privé/invitation)
     */
    public function showResponseForm(Request $request, Event $event)
    {
        // Charger la relation visibilite si elle n'est pas déjà chargée
        $event->load('visibilite');

        // Vérifier que l'événement nécessite une réponse (privé ou sur invitation)
        if (!in_array($event->visibilite->libelle, ['Privé', 'Sur invitation'])) {
            // Si l'utilisateur n'est pas authentifié, rediriger vers login
            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'Cet événement nécessite une authentification.');
            }
            return redirect()->route('events.show', $event);
        }

        // Vérifier que l'OTP a été validé (stocké en session)
        $otpVerified = session('otp_verified_' . $event->id);
        $verifiedEmail = session('otp_email_' . $event->id);

        if (!$otpVerified || !$verifiedEmail) {
            // Essayer de trouver un lien d'accès pour rediriger vers la page de vérification
            $accessLink = \App\Models\EventAccessLink::where('event_id', $event->id)
                ->where('email', $request->input('email'))
                ->latest()
                ->first();

            if ($accessLink) {
                return redirect()->route('events.access', ['event' => $event, 'token' => $accessLink->token_unique])
                    ->with('error', 'Vous devez d\'abord vérifier votre email.');
            }

            return redirect()->route('login')
                ->with('error', 'Vous devez d\'abord vérifier votre email via le lien d\'accès.');
        }

        $event->load(['category', 'visibilite']);

        return view('events.respond', compact('event', 'verifiedEmail'));
    }

    /**
     * Traite la réponse de l'utilisateur
     */
    public function submitResponse(Request $request, Event $event)
    {
        $validated = $request->validate([
            'reponse' => 'required|in:present,peut_etre,absent',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'entreprise' => 'nullable|string|max:255',
        ]);

        // Vérifier que l'OTP a été validé
        $otpVerified = session('otp_verified_' . $event->id);
        $verifiedEmail = session('otp_email_' . $event->id);

        if (!$otpVerified || !$verifiedEmail) {
            // Rediriger vers la page d'accès avec le token si disponible
            $accessLink = \App\Models\EventAccessLink::where('event_id', $event->id)
                ->where('email', $request->input('email'))
                ->latest()
                ->first();

            if ($accessLink) {
                return redirect()->route('events.access', ['event' => $event, 'token' => $accessLink->token_unique])
                    ->with('error', 'Session expirée. Veuillez recommencer.');
            }

            return redirect()->route('login')
                ->with('error', 'Session expirée. Veuillez vous reconnecter.');
        }

        // Chercher ou créer l'inscription
        $registration = EventRegistration::firstOrCreate(
            [
                'event_id' => $event->id,
                'email' => $verifiedEmail,
            ],
            [
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'telephone' => $validated['telephone'] ?? null,
                'entreprise' => $validated['entreprise'] ?? null,
                'statut_reponse' => 'en_attente',
                'date_validation_otp' => now(),
                'user_id' => auth()->id(),
            ]
        );

        // Mettre à jour les informations si nécessaire
        $registration->update([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'] ?? null,
            'entreprise' => $validated['entreprise'] ?? null,
            'statut_reponse' => $validated['reponse'],
            'date_reponse' => now(),
        ]);

        // Si présent ou peut-être, envoyer la carte d'invitation
        $emailSent = false;

        \Log::info('Vérification de l\'envoi de la carte d\'invitation', [
            'registration_id' => $registration->id,
            'reponse' => $validated['reponse'] ?? 'non défini',
            'should_send' => in_array($validated['reponse'] ?? '', ['present', 'peut_etre']),
        ]);

        if (in_array($validated['reponse'], ['present', 'peut_etre'])) {
            try {
                // S'assurer que la relation event est chargée
                if (!$registration->relationLoaded('event')) {
                    $registration->load('event');
                }

                \Log::info('Appel de sendInvitationCard', [
                    'registration_id' => $registration->id,
                    'email' => $registration->email,
                    'event_id' => $registration->event_id,
                ]);

                $emailSent = $this->invitationCardService->sendInvitationCard($registration);

                \Log::info('Résultat de sendInvitationCard', [
                    'registration_id' => $registration->id,
                    'email_sent' => $emailSent,
                ]);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'envoi de la carte d\'invitation', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        } else {
            \Log::info('Carte d\'invitation non envoyée (réponse: absent)', [
                'registration_id' => $registration->id,
                'reponse' => $validated['reponse'] ?? 'non défini',
            ]);
        }

        // Nettoyer la session
        session()->forget('otp_verified_' . $event->id);
        session()->forget('otp_email_' . $event->id);

        $message = match($validated['reponse']) {
            'present' => $emailSent
                ? 'Merci de votre confirmation ! Votre carte d\'invitation a été envoyée par email.'
                : 'Merci de votre confirmation ! Une erreur est survenue lors de l\'envoi de la carte d\'invitation. Veuillez contacter l\'administrateur.',
            'peut_etre' => $emailSent
                ? 'Merci ! Votre carte d\'invitation a été envoyée par email. Nous espérons vous voir !'
                : 'Merci ! Une erreur est survenue lors de l\'envoi de la carte d\'invitation. Veuillez contacter l\'administrateur.',
            'absent' => 'Merci de nous avoir informé. Nous espérons vous voir lors d\'un prochain événement.',
        };

        // Rediriger vers une page de confirmation publique
        // Si l'email a été envoyé, stocker le token pour permettre le téléchargement
        if ($emailSent && in_array($validated['reponse'], ['present', 'peut_etre'])) {
            return redirect()->route('events.response-confirmation', $event)
                ->with($emailSent ? 'success' : 'warning', $message)
                ->with('invitation_token', $registration->token_unique)
                ->with('registration_email', $verifiedEmail);
        }

        return redirect()->route('events.response-confirmation', $event)
            ->with($emailSent ? 'success' : 'warning', $message)
            ->with('registration_email', $verifiedEmail);
    }

    /**
     * Liste des inscriptions (pour les admins)
     */
    public function index(Request $request, Event $event)
    {
        $registrations = EventRegistration::where('event_id', $event->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('events.registrations', compact('event', 'registrations'));
    }

    /**
     * Télécharge la carte d'invitation en PDF
     */
    public function downloadInvitationCard($token)
    {
        try {
            $registration = EventRegistration::where('token_unique', $token)
                ->with('event')
                ->first();

            if (!$registration) {
                // Rediriger vers la page d'accueil ou une page d'erreur publique
                return redirect('/')
                    ->with('error', 'Carte d\'invitation introuvable. Le lien peut être expiré ou invalide.');
            }

            // Vérifier si DomPDF est disponible
            if (!class_exists('\Dompdf\Dompdf')) {
                return redirect()->back()
                    ->with('error', 'La génération PDF n\'est pas disponible. Veuillez contacter l\'administrateur.');
            }

            // Générer le PDF
            $pdfPath = $this->invitationCardService->generateInvitationCardPdf($registration);

            if (!file_exists($pdfPath)) {
                return redirect()->back()
                    ->with('error', 'Erreur lors de la génération du PDF.');
            }

            $event = $registration->event;
            $filename = 'Carte_Invitation_' . \Illuminate\Support\Str::slug($event->titre) . '_' . $registration->token_unique . '.pdf';

            // Retourner le PDF en téléchargement
            return response()->download($pdfPath, $filename, [
                'Content-Type' => 'application/pdf',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Erreur lors du téléchargement de la carte d\'invitation', [
                'token' => $token,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors du téléchargement. Veuillez réessayer.');
        }
    }
}
