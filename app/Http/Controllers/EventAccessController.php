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

        $query = EventAccessLink::forOrganization($event->organization_id)
            ->where('event_id', $event->id);

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
            ->paginate(10)
            ->withQueryString();

        return view('events.send-link', compact('event', 'links'));
    }

    /**
     * Envoie un lien d'accès
     */
    public function sendLink(Request $request, Event $event)
    {
        $validated = $request->validate([
            'email' => 'nullable|required_without:csv_file|email|max:255',
            'nom' => 'nullable|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'entreprise' => 'nullable|string|max:255',
            'fonction' => 'nullable|string|max:255',
            'csv_file' => 'nullable|file|mimes:csv,txt|max:2048',
            'message' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'événement nécessite un lien d'accès
        if (!in_array($event->visibilite->libelle, ['Privé', 'Sur invitation'])) {
            return redirect()->back()->with('error', 'Cet événement ne nécessite pas de lien d\'accès.');
        }

        $contacts = [];

        if ($request->hasFile('csv_file')) {
            $contacts = array_merge($contacts, $this->parseInviteCsv($request->file('csv_file')->getRealPath()));
        }

        if (!empty($validated['email'])) {
            $contacts[] = [
                'email' => $validated['email'],
                'nom' => $validated['nom'] ?? null,
                'prenom' => $validated['prenom'] ?? null,
                'telephone' => $validated['telephone'] ?? null,
                'entreprise' => $validated['entreprise'] ?? null,
                'fonction' => $validated['fonction'] ?? null,
            ];
        }

        $contacts = collect($contacts)
            ->filter(fn ($contact) => filter_var($contact['email'] ?? null, FILTER_VALIDATE_EMAIL))
            ->unique(fn ($contact) => strtolower($contact['email']))
            ->values();

        if ($contacts->isEmpty()) {
            return redirect()->back()->with('error', 'Aucun email valide fourni.');
        }

        $sentCount = 0;
        $errors = [];

        foreach ($contacts as $contact) {
            try {
                // Créer le lien d'accès
                $accessLink = EventAccessLink::create([
                    'organization_id' => $event->organization_id,
                    'event_id' => $event->id,
                    'email_destinataire' => $contact['email'],
                    'nom' => $contact['nom'] ?? null,
                    'prenom' => $contact['prenom'] ?? null,
                    'telephone' => $contact['telephone'] ?? null,
                    'entreprise' => $contact['entreprise'] ?? null,
                    'fonction' => $contact['fonction'] ?? null,
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
                    $contact['email'],
                    $contact['email'],
                    $subject,
                    $text,
                    $html
                );

                if ($result['success']) {
                    $sentCount++;
                } else {
                    $errors[] = $contact['email'] . ': ' . ($result['message'] ?? 'Erreur inconnue');
                }
            } catch (\Exception $e) {
                $errors[] = $contact['email'] . ': ' . $e->getMessage();
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

    public function destroyLink(Event $event, EventAccessLink $accessLink)
    {
        abort_unless((int) $accessLink->event_id === (int) $event->id, 404);
        abort_unless((int) $accessLink->organization_id === (int) $event->organization_id, 404);

        $accessLink->delete();

        return back()->with('success', 'Invitation supprimée.');
    }

    /**
     * Affiche le formulaire de vérification d'accès (avec token)
     */
    public function showAccessForm(Event $event, string $token)
    {
        // Trouver le lien d'accès
        $accessLink = EventAccessLink::forOrganization($event->organization_id)
            ->where('event_id', $event->id)
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
        $filename = 'modele_invitations.csv';

        $csvContent = "email,nom,prenom,telephone,entreprise,fonction\n";
        $csvContent .= "exemple1@email.com,Doe,Jean,+2250700000000,Entreprise A,Directeur\n";
        $csvContent .= "exemple2@email.com,Kouassi,Aya,+2250500000000,Entreprise B,Responsable Marketing\n";

        return response($csvContent, 200)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
            ->header('Pragma', 'public');
    }

    private function parseInviteCsv(string $path): array
    {
        $handle = fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        $contacts = [];
        $headers = null;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $row = array_map(fn ($value) => trim((string) $value), $row);

            if ($headers === null) {
                $headers = array_map(fn ($value) => strtolower(trim($value)), $row);
                if (!in_array('email', $headers, true)) {
                    $contacts[] = $this->contactFromCsvRow(['email'], $row);
                    $headers = ['email'];
                }
                continue;
            }

            $contacts[] = $this->contactFromCsvRow($headers, $row);
        }

        fclose($handle);

        return array_filter($contacts, fn ($contact) => !empty($contact['email']));
    }

    private function contactFromCsvRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = $row[$index] ?? null;
        }

        return [
            'email' => $data['email'] ?? null,
            'nom' => $data['nom'] ?? null,
            'prenom' => $data['prenom'] ?? ($data['prenoms'] ?? null),
            'telephone' => $data['telephone'] ?? null,
            'entreprise' => $data['entreprise'] ?? null,
            'fonction' => $data['fonction'] ?? null,
        ];
    }
}
