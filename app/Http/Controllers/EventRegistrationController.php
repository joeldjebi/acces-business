<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAccessLink;
use App\Models\EventRegistration;
use App\Services\EventCommunicationService;
use App\Services\EventRegistrationActivityLogger;
use App\Services\InvitationCardService;
use App\Services\MailjetService;
use App\Services\WalletPassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EventRegistrationController extends Controller
{
    protected $invitationCardService;
    protected $mailjetService;
    protected $walletPassService;
    protected $activityLogger;
    protected $eventCommunicationService;

    public function __construct(
        InvitationCardService $invitationCardService,
        MailjetService $mailjetService,
        WalletPassService $walletPassService,
        EventRegistrationActivityLogger $activityLogger,
        EventCommunicationService $eventCommunicationService
    ) {
        $this->invitationCardService = $invitationCardService;
        $this->mailjetService = $mailjetService;
        $this->walletPassService = $walletPassService;
        $this->activityLogger = $activityLogger;
        $this->eventCommunicationService = $eventCommunicationService;
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
            'fonction' => 'nullable|string|max:255',
        ]);

        // Vérifier si l'email n'est pas déjà inscrit pour cet événement
        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->where('email', $validated['email'])
            ->first();

        if ($existingRegistration) {
            return redirect()->back()->with('error', 'Vous êtes déjà inscrit à cet événement avec cet email.');
        }

        // Créer l'inscription
        $registration = EventRegistration::create([
            'organization_id' => $event->organization_id,
            'event_id' => $event->id,
            'email' => $validated['email'],
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'telephone' => $validated['telephone'] ?? null,
            'entreprise' => $validated['entreprise'] ?? null,
            'fonction' => $validated['fonction'] ?? null,
            'statut_reponse' => 'en_attente',
            'user_id' => auth()->id(),
        ]);

        $this->activityLogger->log(
            $registration,
            'registration_created',
            'Inscription publique créée',
            'L’invité s’est inscrit depuis la page publique de l’événement.',
            ['email' => $registration->email],
            $request
        );

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
            $accessLink = EventAccessLink::where('event_id', $event->id)
                ->forOrganization($event->organization_id)
                ->where('email_destinataire', $request->input('email'))
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
        $inviteData = $this->inviteDataFor($event, $verifiedEmail);

        return view('events.respond', compact('event', 'verifiedEmail', 'inviteData'));
    }

    /**
     * Traite la réponse de l'utilisateur
     */
    public function submitResponse(Request $request, Event $event)
    {
        $validated = $request->validate([
            'reponse' => 'required|in:present,peut_etre,absent,represente',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'entreprise' => 'nullable|string|max:255',
            'fonction' => 'nullable|string|max:255',
            'representant_nom' => 'nullable|required_if:reponse,represente|string|max:255',
            'representant_prenoms' => 'nullable|required_if:reponse,represente|string|max:255',
            'representant_fonction' => 'nullable|required_if:reponse,represente|string|max:255',
            'representant_contact' => 'nullable|required_if:reponse,represente|string|max:30',
            'representant_email' => 'nullable|required_if:reponse,represente|email|max:255',
        ]);

        // Vérifier que l'OTP a été validé
        $otpVerified = session('otp_verified_' . $event->id);
        $verifiedEmail = session('otp_email_' . $event->id);

        if (!$otpVerified || !$verifiedEmail) {
            // Rediriger vers la page d'accès avec le token si disponible
            $accessLink = EventAccessLink::where('event_id', $event->id)
                ->forOrganization($event->organization_id)
                ->where('email_destinataire', $request->input('email'))
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
                'organization_id' => $event->organization_id,
                'event_id' => $event->id,
                'email' => $verifiedEmail,
            ],
            [
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'telephone' => $validated['telephone'] ?? null,
                'entreprise' => $validated['entreprise'] ?? null,
                'fonction' => $validated['fonction'] ?? null,
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
            'fonction' => $validated['fonction'] ?? null,
            'statut_reponse' => $validated['reponse'],
            'date_reponse' => now(),
            'representant_nom' => $validated['reponse'] === 'represente' ? $validated['representant_nom'] : null,
            'representant_prenoms' => $validated['reponse'] === 'represente' ? $validated['representant_prenoms'] : null,
            'representant_fonction' => $validated['reponse'] === 'represente' ? $validated['representant_fonction'] : null,
            'representant_contact' => $validated['reponse'] === 'represente' ? $validated['representant_contact'] : null,
            'representant_email' => $validated['reponse'] === 'represente' ? $validated['representant_email'] : null,
            'representant_statut' => $validated['reponse'] === 'represente' ? 'en_attente' : null,
        ]);

        $this->activityLogger->log(
            $registration,
            'response_submitted',
            'Réponse enregistrée',
            'L’invité a répondu: ' . $validated['reponse'],
            [
                'response' => $validated['reponse'],
                'representative_email' => $validated['reponse'] === 'represente' ? $registration->representant_email : null,
            ],
            $request,
            'guest'
        );

        // Si présent ou peut-être, envoyer la carte d'invitation
        $emailSent = false;
        $representativeEmailSent = false;

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
        } elseif ($validated['reponse'] === 'represente') {
            $representativeEmailSent = $this->sendRepresentativeConfirmationLink($registration);
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
            'represente' => $representativeEmailSent
                ? 'Votre demande de représentation a bien été enregistrée. Un email de confirmation a été envoyé à votre représentant.'
                : 'Votre demande de représentation a bien été enregistrée, mais l\'email au représentant n\'a pas pu être envoyé. L\'organisateur verra cette trace.',
        };

        // Rediriger vers une page de confirmation publique
        // Si l'email a été envoyé, stocker le token pour permettre le téléchargement
        if ($emailSent && in_array($validated['reponse'], ['present', 'peut_etre'])) {
            return redirect()->route('events.response-confirmation', $event)
                ->with($emailSent ? 'success' : 'warning', $message)
                ->with('invitation_token', $registration->token_unique)
                ->with('registration_email', $verifiedEmail);
        }

        $flashKey = match ($validated['reponse']) {
            'present', 'peut_etre' => $emailSent ? 'success' : 'warning',
            'represente' => $representativeEmailSent ? 'success' : 'warning',
            default => 'success',
        };

        return redirect()->route('events.response-confirmation', $event)
            ->with($flashKey, $message)
            ->with('registration_email', $verifiedEmail);
    }

    /**
     * Liste les invités dont la présence est confirmée.
     */
    public function confirmedAttendees(Request $request, Event $event)
    {
        $baseQuery = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->where(function ($query) {
                $query->where('statut_reponse', 'present')
                    ->orWhere(function ($represented) {
                        $represented->where('statut_reponse', 'represente')
                            ->where('representant_statut', 'confirme');
                    });
            });

        $statsRegistrations = (clone $baseQuery)->get();
        $stats = [
            'total' => $statsRegistrations->count(),
            'directs' => $statsRegistrations->where('statut_reponse', 'present')->count(),
            'representes' => $statsRegistrations->where('statut_reponse', 'represente')->count(),
            'cartes_envoyees' => $statsRegistrations->filter(function ($registration) {
                return $registration->statut_reponse === 'present'
                    ? (bool) $registration->carte_envoyee
                    : (bool) $registration->representant_carte_envoyee;
            })->count(),
        ];

        $query = clone $baseQuery;

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhere('entreprise', 'like', "%{$search}%")
                    ->orWhere('fonction', 'like', "%{$search}%")
                    ->orWhere('representant_nom', 'like', "%{$search}%")
                    ->orWhere('representant_prenoms', 'like', "%{$search}%")
                    ->orWhere('representant_email', 'like', "%{$search}%")
                    ->orWhere('representant_contact', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            if ($request->type === 'direct') {
                $query->where('statut_reponse', 'present');
            } elseif ($request->type === 'represente') {
                $query->where('statut_reponse', 'represente');
            }
        }

        if ($request->filled('card')) {
            if ($request->card === 'sent') {
                $query->where(function ($q) {
                    $q->where(function ($direct) {
                        $direct->where('statut_reponse', 'present')->where('carte_envoyee', true);
                    })->orWhere(function ($represented) {
                        $represented->where('statut_reponse', 'represente')->where('representant_carte_envoyee', true);
                    });
                });
            } elseif ($request->card === 'not_sent') {
                $query->where(function ($q) {
                    $q->where(function ($direct) {
                        $direct->where('statut_reponse', 'present')->where('carte_envoyee', false);
                    })->orWhere(function ($represented) {
                        $represented->where('statut_reponse', 'represente')->where('representant_carte_envoyee', false);
                    });
                });
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_reponse', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_reponse', '<=', $request->date_to);
        }

        $attendees = $query->orderByDesc('date_reponse')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('events.confirmed-attendees', compact('event', 'attendees', 'stats'));
    }

    /**
     * Affiche le détail d'une présence confirmée.
     */
    public function confirmedAttendeeDetail(Event $event, EventRegistration $registration)
    {
        abort_unless((int) $registration->event_id === (int) $event->id, 404);
        abort_unless((int) $registration->organization_id === (int) $event->organization_id, 404);

        $isConfirmed = $registration->statut_reponse === 'present'
            || ($registration->statut_reponse === 'represente' && $registration->representant_statut === 'confirme');

        abort_unless($isConfirmed, 404);

        return view('events.confirmed-attendee-detail', compact('event', 'registration'));
    }

    /**
     * Liste des inscriptions (pour les admins)
     */
    public function index(Request $request, Event $event)
    {
        $baseQuery = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id);

        $statsRegistrations = (clone $baseQuery)->get();
        $stats = [
            'total' => $statsRegistrations->count(),
            'present' => $statsRegistrations->where('statut_reponse', 'present')->count(),
            'peut_etre' => $statsRegistrations->where('statut_reponse', 'peut_etre')->count(),
            'absent' => $statsRegistrations->where('statut_reponse', 'absent')->count(),
            'represente' => $statsRegistrations->where('statut_reponse', 'represente')->count(),
            'en_attente' => $statsRegistrations->where('statut_reponse', 'en_attente')->count(),
        ];

        $query = clone $baseQuery;

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhere('entreprise', 'like', "%{$search}%")
                    ->orWhere('fonction', 'like', "%{$search}%")
                    ->orWhere('representant_nom', 'like', "%{$search}%")
                    ->orWhere('representant_prenoms', 'like', "%{$search}%")
                    ->orWhere('representant_email', 'like', "%{$search}%")
                    ->orWhere('representant_contact', 'like', "%{$search}%");
            });
        }

        if ($request->filled('response')) {
            $query->where('statut_reponse', $request->response);
        }

        if ($request->filled('representative_status')) {
            $query->where('representant_statut', $request->representative_status);
        }

        if ($request->filled('card')) {
            if ($request->card === 'sent') {
                $query->where(function ($q) {
                    $q->where('carte_envoyee', true)
                        ->orWhere('representant_carte_envoyee', true);
                });
            } elseif ($request->card === 'not_sent') {
                $query->where(function ($q) {
                    $q->where('carte_envoyee', false)
                        ->where(function ($inner) {
                            $inner->whereNull('representant_carte_envoyee')
                                ->orWhere('representant_carte_envoyee', false);
                        });
                });
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date_inscription', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date_inscription', '<=', $request->date_to);
        }

        $perPage = in_array((int) $request->input('per_page'), [10, 20, 50, 100], true)
            ? (int) $request->input('per_page')
            : 20;

        $registrations = $query->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('events.registrations', compact('event', 'registrations', 'stats'));
    }

    /**
     * Affiche le détail complet d'une inscription.
     */
    public function registrationDetail(Event $event, EventRegistration $registration)
    {
        abort_unless((int) $registration->event_id === (int) $event->id, 404);
        abort_unless((int) $registration->organization_id === (int) $event->organization_id, 404);

        $registration->load(['activities.actor', 'checkedInBy']);

        return view('events.registration-detail', compact('event', 'registration'));
    }

    /**
     * Renvoie le lien de confirmation au représentant depuis l'espace organisateur.
     */
    public function resendRepresentativeConfirmation(Event $event, EventRegistration $registration)
    {
        abort_unless((int) $registration->event_id === (int) $event->id, 404);
        abort_unless((int) $registration->organization_id === (int) $event->organization_id, 404);
        abort_unless($registration->statut_reponse === 'represente', 404);

        $sent = $this->sendRepresentativeConfirmationLink($registration);

        return redirect()->route('events.registrations', $event)
            ->with($sent ? 'success' : 'warning', $sent
                ? 'Le lien de confirmation a été renvoyé au représentant.'
                : 'Le lien n’a pas pu être envoyé. La trace de l’erreur est visible dans la liste.');
    }

    /**
     * Interface de check-in en temps réel pour un événement.
     */
    public function checkInDashboard(Request $request, Event $event)
    {
        $baseQuery = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->where(function ($query) {
                $query->whereIn('statut_reponse', ['present', 'peut_etre'])
                    ->orWhere(function ($represented) {
                        $represented->where('statut_reponse', 'represente')
                            ->where('representant_statut', 'confirme');
                    });
            });

        $statsRegistrations = (clone $baseQuery)->get();
        $stats = [
            'eligible' => $statsRegistrations->count(),
            'checked_in' => $statsRegistrations->whereNotNull('checked_in_at')->count(),
            'waiting' => $statsRegistrations->whereNull('checked_in_at')->count(),
            'represented' => $statsRegistrations->where('statut_reponse', 'represente')->count(),
        ];

        $query = clone $baseQuery;

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('prenom', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('telephone', 'like', "%{$search}%")
                    ->orWhere('representant_nom', 'like', "%{$search}%")
                    ->orWhere('representant_prenoms', 'like', "%{$search}%")
                    ->orWhere('representant_email', 'like', "%{$search}%")
                    ->orWhere('token_unique', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($request->status === 'waiting') {
                $query->whereNull('checked_in_at');
            }
        }

        $registrations = $query->orderByRaw('checked_in_at is null desc')
            ->orderByDesc('checked_in_at')
            ->orderBy('nom')
            ->paginate(20)
            ->withQueryString();

        $recentActivities = $event->registrationActivities()
            ->whereIn('type', ['checked_in', 'duplicate_checkin'])
            ->with(['registration', 'actor'])
            ->latest()
            ->take(12)
            ->get();

        return view('events.check-in', compact('event', 'registrations', 'stats', 'recentActivities'));
    }

    public function checkInByToken(Request $request, Event $event)
    {
        $validated = $request->validate([
            'token' => 'required|string|max:120',
        ]);

        $registration = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->where('token_unique', trim($validated['token']))
            ->first();

        if (!$registration) {
            return back()->with('error', 'QR code ou token introuvable pour cet événement.');
        }

        return $this->performCheckIn($request, $event, $registration);
    }

    public function checkIn(Request $request, Event $event, EventRegistration $registration)
    {
        abort_unless((int) $registration->event_id === (int) $event->id, 404);
        abort_unless((int) $registration->organization_id === (int) $event->organization_id, 404);

        return $this->performCheckIn($request, $event, $registration);
    }

    public function sendReminders(Request $request, Event $event)
    {
        $pending = $this->eventCommunicationService->pendingReminderQuery($event)
            ->limit(200)
            ->get();

        $sent = 0;
        foreach ($pending as $registration) {
            if ($this->eventCommunicationService->sendReminder($registration, $request)) {
                $sent++;
            }
        }

        return back()->with($sent > 0 ? 'success' : 'warning', $sent > 0
            ? "{$sent} relance(s) envoyée(s)."
            : 'Aucune relance envoyée: aucun invité en attente éligible ou erreur d’envoi.');
    }

    public function sendThankYouSms(Request $request, Event $event)
    {
        $registrations = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->whereNotNull('checked_in_at')
            ->where(function ($q) {
                $q->whereNull('thank_you_sms_status')
                    ->orWhere('thank_you_sms_status', 'failed');
            })
            ->get();

        $sent = 0;
        $failed = 0;
        foreach ($registrations as $registration) {
            $result = $this->eventCommunicationService->sendThankYouSms($registration, $request);
            ((bool) ($result['success'] ?? false)) ? $sent++ : $failed++;
        }

        return back()->with($sent > 0 ? 'success' : 'warning', "{$sent} SMS envoyé(s), {$failed} échec(s). Les crédits sont débités uniquement pour les SMS envoyés.");
    }

    public function verifyQr(Request $request, string $token)
    {
        $registration = EventRegistration::where('token_unique', $token)
            ->with(['event', 'checkedInBy'])
            ->first();

        if (!$registration) {
            return view('events.qr-invalid');
        }

        $canCheckIn = auth()->check()
            && auth()->user()->organization_id === $registration->organization_id
            && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin());

        $this->activityLogger->log(
            $registration,
            'qr_scanned',
            'QR code scanné',
            $canCheckIn ? 'QR scanné depuis une session organisateur.' : 'QR scanné hors session organisateur.',
            ['can_check_in' => $canCheckIn],
            $request,
            $canCheckIn ? null : 'public'
        );

        return view('events.qr-verified', compact('registration', 'canCheckIn'));
    }

    public function checkInFromQr(Request $request, string $token)
    {
        $registration = EventRegistration::where('token_unique', $token)->with('event')->firstOrFail();
        abort_unless(auth()->check(), 403);
        abort_unless((int) auth()->user()->organization_id === (int) $registration->organization_id, 403);
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin(), 403);

        return $this->performCheckIn($request, $registration->event, $registration, true);
    }

    private function performCheckIn(Request $request, Event $event, EventRegistration $registration, bool $fromQr = false)
    {
        if (!$this->isCheckInEligible($registration)) {
            $this->activityLogger->log(
                $registration,
                'checkin_rejected',
                'Check-in refusé',
                'Le statut de réponse ne permet pas le check-in.',
                ['status' => $registration->statut_reponse, 'representative_status' => $registration->representant_statut],
                $request
            );

            return back()->with('error', 'Ce participant n’est pas éligible au check-in.');
        }

        if ($registration->checked_in_at) {
            $registration->increment('checkin_count');
            $this->activityLogger->log(
                $registration,
                'duplicate_checkin',
                'Double scan détecté',
                'Le participant avait déjà été validé à l’entrée.',
                ['checked_in_at' => $registration->checked_in_at?->toDateTimeString()],
                $request
            );

            return back()->with('warning', 'Participant déjà checké le ' . $registration->checked_in_at->format('d/m/Y à H:i') . '.');
        }

        $registration->forceFill([
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
            'checkin_ip' => $request->ip(),
            'checkin_user_agent' => substr((string) $request->userAgent(), 0, 500),
            'checkin_count' => 1,
        ])->save();

        $this->activityLogger->log(
            $registration,
            'checked_in',
            'Check-in validé',
            $registration->checkInDisplayName() . ' a été validé à l’entrée.',
            ['from_qr' => $fromQr, 'attendee' => $registration->checkInDisplayName()],
            $request
        );

        return back()->with('success', 'Check-in validé pour ' . $registration->checkInDisplayName() . '.');
    }

    private function isCheckInEligible(EventRegistration $registration): bool
    {
        return in_array($registration->statut_reponse, ['present', 'peut_etre'], true)
            || ($registration->statut_reponse === 'represente' && $registration->representant_statut === 'confirme');
    }

    /**
     * Télécharge la carte Apple Wallet (.pkpass).
     */
    public function downloadAppleWalletPass(string $token)
    {
        $registration = EventRegistration::where('token_unique', $token)
            ->with('event.organization')
            ->firstOrFail();

        $path = $this->walletPassService->generateApplePass($registration);
        $filename = 'Invitation_' . \Illuminate\Support\Str::slug($registration->event->titre) . '.pkpass';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.apple.pkpass',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Télécharge la carte d'invitation en PDF
     */
    public function downloadInvitationCard($token)
    {
        try {
            $registration = EventRegistration::where('token_unique', $token)
                ->with('event.organization')
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

    /**
     * Affiche la page publique de confirmation du représentant.
     */
    public function showRepresentativeConfirmation(string $token)
    {
        $registration = EventRegistration::where('representant_token', $token)
            ->where('statut_reponse', 'represente')
            ->with('event')
            ->firstOrFail();

        return view('representations.confirm', compact('registration'));
    }

    /**
     * Confirme la présence du représentant et garde la trace pour l'organisateur.
     */
    public function confirmRepresentative(Request $request, string $token)
    {
        $registration = EventRegistration::where('representant_token', $token)
            ->where('statut_reponse', 'represente')
            ->with('event')
            ->firstOrFail();

        $registration->update([
            'representant_statut' => 'confirme',
            'representant_confirme_le' => now(),
            'representant_confirmation_ip' => $request->ip(),
            'representant_confirmation_user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $this->activityLogger->log(
            $registration,
            'representative_confirmed',
            'Représentant confirmé',
            'Le représentant a confirmé sa présence.',
            ['representative_email' => $registration->representant_email],
            $request,
            'representative'
        );

        $cardSent = $this->invitationCardService->sendRepresentativeInvitationCard($registration->fresh('event'));
        $representedNotified = $this->notifyRepresentedGuest($registration->fresh('event'), $cardSent);

        \Log::info('Présence du représentant confirmée', [
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'invite_email' => $registration->email,
            'representant_email' => $registration->representant_email,
        ]);

        $message = $cardSent
            ? 'Votre présence a bien été confirmée. Votre carte d’invitation vous a été envoyée par email et vous pouvez aussi la télécharger ici.'
            : 'Votre présence a bien été confirmée, mais l’envoi de votre carte par email a échoué. Vous pouvez la télécharger ici.';

        return redirect()->route('representations.confirm', $token)
            ->with($cardSent ? 'success' : 'warning', $message);
    }

    private function notifyRepresentedGuest(EventRegistration $registration, bool $cardSent): bool
    {
        $registration->loadMissing('event');
        $event = $registration->event;

        if (!$registration->email) {
            $registration->update([
                'represente_notification_envoyee' => false,
                'represente_notification_erreur' => 'Email de l’invité représenté manquant.',
            ]);
            return false;
        }

        $representativeName = trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? '')) ?: $registration->representant_email;
        $inviteName = $registration->nom_complet ?: $registration->email;

        $html = view('emails.represented-confirmed', [
            'registration' => $registration,
            'event' => $event,
            'representativeName' => $representativeName,
            'inviteName' => $inviteName,
            'cardSent' => $cardSent,
        ])->render();

        $plainText = "Bonjour {$inviteName},\n\n";
        $plainText .= "Votre représentant {$representativeName} a confirmé sa présence à l'événement : {$event->titre}.\n";
        $plainText .= $cardSent
            ? "Sa carte d'invitation lui a été envoyée par email.\n"
            : "Sa présence est confirmée, mais l'envoi de sa carte par email a échoué.\n";

        $result = $this->mailjetService->sendSimpleEmail(
            $registration->email,
            $inviteName,
            'Votre représentant a confirmé sa présence - ' . $event->titre,
            $plainText,
            $html
        );

        $success = (bool) ($result['success'] ?? false);
        $registration->update([
            'represente_notification_envoyee' => $success,
            'represente_notification_envoyee_le' => $success ? now() : null,
            'represente_notification_erreur' => $success ? null : ($result['message'] ?? 'Erreur inconnue lors de la notification.'),
        ]);

        \Log::info('Notification envoyée à l’invité représenté', [
            'registration_id' => $registration->id,
            'invite_email' => $registration->email,
            'success' => $success,
            'message' => $result['message'] ?? null,
        ]);

        return $success;
    }

    private function sendRepresentativeConfirmationLink(EventRegistration $registration): bool
    {
        if (!$registration->representant_email) {
            $registration->update([
                'representant_statut' => 'mail_echec',
                'representant_mail_erreur' => 'Email du représentant manquant.',
            ]);
            return false;
        }

        if (!$registration->representant_token) {
            $registration->forceFill(['representant_token' => Str::random(48)])->save();
            $registration->refresh();
        }

        $registration->loadMissing('event');
        $event = $registration->event;
        $confirmationUrl = route('representations.confirm', $registration->representant_token);
        $representativeName = trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? '')) ?: $registration->representant_email;
        $inviteName = $registration->nom_complet ?: $registration->email;

        $html = view('emails.representative-confirmation', [
            'registration' => $registration,
            'event' => $event,
            'confirmationUrl' => $confirmationUrl,
            'representativeName' => $representativeName,
            'inviteName' => $inviteName,
        ])->render();

        $text = "Bonjour {$representativeName},\n\n";
        $text .= "{$inviteName} vous a désigné(e) pour le représenter à l'événement : {$event->titre}.\n";
        $text .= "Confirmez votre présence ici : {$confirmationUrl}\n\n";
        $text .= "Ce lien est personnel.\n";

        $result = $this->mailjetService->sendSimpleEmail(
            $registration->representant_email,
            $representativeName,
            'Confirmation de représentation - ' . $event->titre,
            $text,
            $html
        );

        $success = (bool) ($result['success'] ?? false);
        $registration->update([
            'representant_statut' => $success ? 'mail_envoye' : 'mail_echec',
            'representant_mail_envoye' => $success,
            'representant_mail_envoye_le' => $success ? now() : null,
            'representant_mail_erreur' => $success ? null : ($result['message'] ?? 'Erreur inconnue lors de l’envoi.'),
        ]);

        $this->activityLogger->log(
            $registration,
            $success ? 'representative_link_sent' : 'representative_link_failed',
            $success ? 'Lien représentant envoyé' : 'Lien représentant échoué',
            $result['message'] ?? null,
            ['representative_email' => $registration->representant_email],
            request(),
            'system'
        );

        \Log::info('Envoi du lien de confirmation représentant', [
            'registration_id' => $registration->id,
            'success' => $success,
            'representant_email' => $registration->representant_email,
            'message' => $result['message'] ?? null,
        ]);

        return $success;
    }

    private function inviteDataFor(Event $event, string $email): array
    {
        $registration = EventRegistration::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->where('email', $email)
            ->latest()
            ->first();

        if ($registration) {
            return [
                'nom' => $registration->nom,
                'prenom' => $registration->prenom,
                'telephone' => $registration->telephone,
                'entreprise' => $registration->entreprise,
                'fonction' => $registration->fonction,
            ];
        }

        $accessLink = EventAccessLink::where('event_id', $event->id)
            ->forOrganization($event->organization_id)
            ->where('email_destinataire', $email)
            ->latest()
            ->first();

        return [
            'nom' => $accessLink?->nom,
            'prenom' => $accessLink?->prenom,
            'telephone' => $accessLink?->telephone,
            'entreprise' => $accessLink?->entreprise,
            'fonction' => $accessLink?->fonction,
        ];
    }
}
