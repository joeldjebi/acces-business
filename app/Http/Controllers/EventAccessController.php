<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAccessLink;
use App\Services\MailjetService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EventAccessController extends Controller
{
    protected $mailjetService;

    public function __construct(MailjetService $mailjetService)
    {
        $this->mailjetService = $mailjetService;
    }

    /**
     * Affiche le formulaire d'envoi de lien d'accès (admin)
     */
    public function showSendLinkForm(Request $request, Event $event)
    {
        $event->load(['visibilite']);
        
        // Vérifier que l'événement nécessite un lien d'accès
        if (!in_array($event->visibilite->libelle, ['Privé', 'Sur invitation'])) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Cet événement ne nécessite pas de lien d\'accès.');
        }

        $query = EventAccessLink::where('event_id', $event->id);

        // Filtre par statut
        if ($request->filled('statut')) {
            if ($request->statut === 'utilise') {
                $query->whereNotNull('utilise_le');
            } elseif ($request->statut === 'envoye') {
                $query->whereNull('utilise_le');
            }
        }

        // Filtre par recherche email
        if ($request->filled('search')) {
            $query->where('email_destinataire', 'like', '%' . $request->search . '%');
        }

        // Filtre par date d'envoi
        if ($request->filled('date_from')) {
            $query->whereDate('envoye_le', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('envoye_le', '<=', $request->date_to);
        }

        $links = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('events.send-link', compact('event', 'links'));
    }

    /**
     * Envoie un lien d'accès
     */
    public function sendLink(Request $request, Event $event)
    {
        $validated = $request->validate([
            'emails' => 'nullable|required_without:csv_file|string',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:2048',
            'message' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'événement nécessite un lien d'accès
        if (!in_array($event->visibilite->libelle, ['Privé', 'Sur invitation'])) {
            return redirect()->back()->with('error', 'Cet événement ne nécessite pas de lien d\'accès.');
        }

        $emailSource = $validated['emails'] ?? '';

        if ($request->hasFile('csv_file')) {
            $emailSource .= "\n" . file_get_contents($request->file('csv_file')->getRealPath());
        }

        // Séparer les emails (séparés par virgule, point-virgule ou retour à la ligne)
        $emails = preg_split('/[,\s;]+/', $emailSource);
        $emails = array_filter(array_map('trim', $emails));
        $emails = array_filter($emails, function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($emails)) {
            return redirect()->back()->with('error', 'Aucun email valide fourni.');
        }

        $sentCount = 0;
        $errors = [];

        foreach ($emails as $email) {
            try {
                // Créer le lien d'accès
                $accessLink = EventAccessLink::create([
                    'event_id' => $event->id,
                    'email_destinataire' => $email,
                    'envoye_par' => auth()->id(),
                ]);

                // Envoyer l'email avec le lien
                $subject = "Invitation à l'événement : " . $event->titre;

                $html = view('emails.access-link', [
                    'event' => $event,
                    'accessLink' => $accessLink,
                    'message' => $validated['message'] ?? null,
                    'senderName' => auth()->user()->name,
                ])->render();

                $text = "Bonjour,\n\n";
                $text .= "Vous êtes invité(e) à participer à l'événement \"" . $event->titre . "\".\n\n";
                $text .= "Cliquez sur le lien ci-dessous pour accéder à l'événement :\n";
                $text .= $accessLink->access_url . "\n\n";
                if ($validated['message']) {
                    $text .= $validated['message'] . "\n\n";
                }
                $text .= "Cordialement,\n" . auth()->user()->name;

                $result = $this->mailjetService->sendSimpleEmail(
                    $email,
                    $email,
                    $subject,
                    $text,
                    $html
                );

                if ($result['success']) {
                    $sentCount++;
                } else {
                    $errors[] = $email . ': ' . ($result['message'] ?? 'Erreur inconnue');
                }
            } catch (\Exception $e) {
                $errors[] = $email . ': ' . $e->getMessage();
            }
        }

        $message = $sentCount . ' lien(s) envoyé(s) avec succès.';
        if (!empty($errors)) {
            $message .= ' Erreurs : ' . implode(', ', $errors);
        }

        return redirect()->back()->with(
            empty($errors) ? 'success' : 'warning',
            $message
        );
    }

    /**
     * Affiche le formulaire de vérification d'accès (avec token)
     */
    public function showAccessForm(Event $event, string $token)
    {
        // Trouver le lien d'accès
        $accessLink = EventAccessLink::where('event_id', $event->id)
            ->where('token_unique', $token)
            ->first();

        if (!$accessLink) {
            return redirect()->route('events.show', $event)
                ->with('error', 'Lien d\'accès invalide.');
        }

        // Marquer le lien comme utilisé
        if (!$accessLink->est_utilise) {
            $accessLink->markAsUsed();
        }

        $event->load(['visibilite']);

        return view('events.verify-access', compact('event', 'accessLink'));
    }

    /**
     * Télécharge le modèle CSV pour l'import d'emails
     */
    public function downloadCsvTemplate()
    {
        $filename = 'modele_emails.csv';

        $csvContent = "email\n";
        $csvContent .= "exemple1@email.com\n";
        $csvContent .= "exemple2@email.com\n";
        $csvContent .= "exemple3@email.com\n";

        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Pragma', 'public');
    }
}
