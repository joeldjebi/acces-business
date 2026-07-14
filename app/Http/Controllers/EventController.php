<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Devise;
use App\Models\EventRegistration;
use App\Models\TypeTarification;
use App\Models\User;
use App\Models\Visibilite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{
    /**
     * Affiche la liste des événements
     */
    public function index(Request $request)
    {
        $query = Event::with(['category', 'user', 'visibilite', 'typeTarification', 'devise'])
            ->forOrganization();

        // Filtre par recherche (titre)
        if ($request->filled('search')) {
            $query->where('titre', 'like', '%' . $request->search . '%');
        }

        // Filtre par statut
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        // Filtre par catégorie
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filtre par visibilité
        if ($request->filled('visibilite_id')) {
            $query->where('visibilite_id', $request->visibilite_id);
        }

        // Filtre par date de début
        if ($request->filled('date_from')) {
            $query->whereDate('date_debut', '>=', $request->date_from);
        }

        // Filtre par date de fin
        if ($request->filled('date_to')) {
            $query->whereDate('date_fin', '<=', $request->date_to);
        }

        // Filtre par créateur (si admin)
        if ($request->filled('user_id') && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())) {
            $query->where('user_id', $request->user_id);
        }

        $events = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Récupérer les données pour les filtres
        $categories = Category::forOrganization()->active()->get();
        $visibilites = Visibilite::active()->get();
        $users = User::where('organization_id', auth()->user()->organization_id)->get();

        return view('events.index', compact('events', 'categories', 'visibilites', 'users'));
    }

    /**
     * Affiche le formulaire de création
     */
    public function create()
    {
        $categories = Category::forOrganization()->active()->get();
        $devises = Devise::forOrganization()->active()->get();
        $typeTarifications = TypeTarification::active()->get();
        $visibilites = Visibilite::active()->get();
        $countries = Country::forOrganization()->active()->orderBy('nom')->get();
        $cities = City::forOrganization()->active()->with('country')->orderBy('nom')->get();

        return view('events.create', compact('categories', 'devises', 'typeTarifications', 'visibilites', 'countries', 'cities'));
    }

    /**
     * Stocke un nouvel événement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'draft_event_id' => 'nullable|exists:events,id',
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'date_debut' => 'required|date',
            'heure_debut' => 'required',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_fin' => 'required',
            'fuseau_horaire' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'adresse_complete' => 'nullable|string',
            'ville' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'lien_google_map' => 'nullable|url',
            'organisateur' => 'nullable|string|max:255',
            'email_contact' => 'nullable|email',
            'telephone' => 'nullable|string|max:20',
            'site_web' => 'nullable|url',
            'statut' => ['required', Rule::in(['brouillon', 'publie', 'annule', 'termine', 'reporte'])],
            'visibilite_id' => 'required|exists:visibilites,id',
            'date_publication' => 'nullable|date',
            'capacite_maximale' => 'nullable|integer|min:1',
            'inscription_requise' => 'boolean',
            'date_limite_inscription' => 'nullable|date',
            'type_tarification_id' => 'required|exists:type_de_tarifications,id',
            'prix' => 'nullable|numeric|min:0',
            'devise_id' => 'nullable|exists:devises,id',
            'tags' => 'nullable|string|max:255',
            'notes_internes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $draft = null;
        if ($request->filled('draft_event_id')) {
            $draft = Event::where('id', $request->draft_event_id)
                ->forOrganization()
                ->where('user_id', auth()->id())
                ->where('statut', 'brouillon')
                ->first();
        }

        unset($validated['draft_event_id']);

        // Génération du slug
        $validated['slug'] = Str::slug($validated['titre'] . '-' . time());

        // Gestion de l'image
        if ($request->hasFile('image')) {
            if ($draft && $draft->image) {
                Storage::disk('public')->delete($draft->image);
            }

            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        // Ajout de l'utilisateur créateur
        $validated['organization_id'] = auth()->user()->organization_id;
        $validated['user_id'] = auth()->id();

        if ($draft) {
            $draft->update($validated);
        } else {
            Event::create($validated);
        }

        return redirect()->route('events.index')
            ->with('success', 'Événement créé avec succès.');
    }

    public function saveDraft(Request $request)
    {
        $validated = $request->validate([
            'draft_event_id' => 'nullable|exists:events,id',
            'titre' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'date_debut' => 'nullable|date',
            'heure_debut' => 'nullable',
            'date_fin' => 'nullable|date',
            'heure_fin' => 'nullable',
            'fuseau_horaire' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'adresse_complete' => 'nullable|string',
            'ville' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'lien_google_map' => 'nullable|url',
            'organisateur' => 'nullable|string|max:255',
            'email_contact' => 'nullable|email',
            'telephone' => 'nullable|string|max:20',
            'site_web' => 'nullable|url',
            'statut' => ['nullable', Rule::in(['brouillon', 'publie', 'annule', 'termine', 'reporte'])],
            'visibilite_id' => 'nullable|exists:visibilites,id',
            'date_publication' => 'nullable|date',
            'capacite_maximale' => 'nullable|integer|min:1',
            'inscription_requise' => 'nullable|boolean',
            'date_limite_inscription' => 'nullable|date',
            'type_tarification_id' => 'nullable|exists:type_de_tarifications,id',
            'prix' => 'nullable|numeric|min:0',
            'devise_id' => 'nullable|exists:devises,id',
            'tags' => 'nullable|string|max:255',
            'notes_internes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $draft = null;
        if ($request->filled('draft_event_id')) {
            $draft = Event::where('id', $request->draft_event_id)
                ->forOrganization()
                ->where('user_id', auth()->id())
                ->where('statut', 'brouillon')
                ->first();
        }

        $data = $this->draftPayload($validated, $draft);

        if ($request->hasFile('image')) {
            if ($draft && $draft->image) {
                Storage::disk('public')->delete($draft->image);
            }

            $data['image'] = $request->file('image')->store('events', 'public');
        }

        if ($draft) {
            $draft->update($data);
        } else {
            $draft = Event::create($data);
        }

        return response()->json([
            'id' => $draft->id,
            'saved_at' => now()->format('H:i'),
        ]);
    }

    private function draftPayload(array $validated, ?Event $draft = null): array
    {
        unset($validated['draft_event_id'], $validated['image']);

        $value = fn (string $key, mixed $fallback = null) => array_key_exists($key, $validated)
            ? $validated[$key]
            : $fallback;

        $categoryId = $value('category_id', $draft?->category_id) ?? Category::forOrganization()->active()->value('id');
        $visibiliteId = $value('visibilite_id', $draft?->visibilite_id) ?? Visibilite::active()->value('id');
        $typeTarificationId = $value('type_tarification_id', $draft?->type_tarification_id) ?? TypeTarification::active()->value('id');

        if (!$categoryId || !$visibiliteId || !$typeTarificationId) {
            throw ValidationException::withMessages([
                'draft' => 'Ajoutez au moins une catégorie, une visibilité et un type de tarification avant de sauvegarder un brouillon.',
            ]);
        }

        $title = trim((string) ($validated['titre'] ?? '')) ?: ($draft?->titre ?: 'Brouillon sans titre');
        $dateDebut = $validated['date_debut'] ?? $draft?->date_debut?->toDateString() ?? now()->toDateString();
        $dateFin = $validated['date_fin'] ?? $draft?->date_fin?->toDateString() ?? $dateDebut;

        $payload = [
            'organization_id' => auth()->user()->organization_id,
            'titre' => $title,
            'description' => $value('description', $draft?->description),
            'category_id' => $categoryId,
            'date_debut' => $dateDebut,
            'heure_debut' => $value('heure_debut', $draft?->heure_debut) ?? '00:00',
            'date_fin' => $dateFin,
            'heure_fin' => $value('heure_fin', $draft?->heure_fin) ?? '00:00',
            'fuseau_horaire' => $value('fuseau_horaire', $draft?->fuseau_horaire),
            'lieu' => $value('lieu', $draft?->lieu),
            'adresse_complete' => $value('adresse_complete', $draft?->adresse_complete),
            'ville' => $value('ville', $draft?->ville),
            'code_postal' => $value('code_postal', $draft?->code_postal),
            'pays' => $value('pays', $draft?->pays),
            'latitude' => $value('latitude', $draft?->latitude),
            'longitude' => $value('longitude', $draft?->longitude),
            'lien_google_map' => $value('lien_google_map', $draft?->lien_google_map),
            'organisateur' => $value('organisateur', $draft?->organisateur),
            'email_contact' => $value('email_contact', $draft?->email_contact),
            'telephone' => $value('telephone', $draft?->telephone),
            'site_web' => $value('site_web', $draft?->site_web),
            'statut' => 'brouillon',
            'visibilite_id' => $visibiliteId,
            'date_publication' => $value('date_publication', $draft?->date_publication),
            'capacite_maximale' => $value('capacite_maximale', $draft?->capacite_maximale),
            'inscription_requise' => array_key_exists('inscription_requise', $validated)
                ? (bool) $validated['inscription_requise']
                : (bool) ($draft?->inscription_requise ?? false),
            'date_limite_inscription' => $value('date_limite_inscription', $draft?->date_limite_inscription),
            'type_tarification_id' => $typeTarificationId,
            'prix' => $value('prix', $draft?->prix),
            'devise_id' => $value('devise_id', $draft?->devise_id),
            'tags' => $value('tags', $draft?->tags),
            'notes_internes' => $value('notes_internes', $draft?->notes_internes),
            'user_id' => auth()->id(),
        ];

        if (!$draft || $draft->titre !== $title) {
            $payload['slug'] = Str::slug($title . '-' . ($draft?->id ?? time()));
        }

        return $payload;
    }

    /**
     * Affiche un événement
     */
    public function show(Event $event)
    {
        $event->load(['category', 'user', 'visibilite', 'typeTarification', 'devise']);

        // Vérifier l'accès selon la visibilité
        $visibilite = $event->visibilite->libelle;

        if ($visibilite === 'Public') {
            // Public : accessible à tous
            $event->incrementViews();
            return view('events.show', compact('event'));
        } elseif ($visibilite === 'Privé') {
            // Privé : nécessite authentification
            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cet événement.');
            }
            $event->incrementViews();
            return view('events.show', compact('event'));
        } elseif ($visibilite === 'Sur invitation') {
            // Sur invitation : nécessite un lien d'accès ou authentification admin
            if (!auth()->check()) {
                return redirect()->route('login')
                    ->with('error', 'Cet événement est sur invitation uniquement.');
            }

            // Les admins peuvent toujours voir
            $user = auth()->user();
            if ($user->isSuperAdmin() || $user->isAdmin() || $user->id === $event->user_id) {
                $event->incrementViews();
                return view('events.show', compact('event'));
            }

            // Vérifier si l'utilisateur a un lien d'accès valide
            $hasAccess = \App\Models\EventAccessLink::where('event_id', $event->id)
                ->forOrganization($event->organization_id)
                ->where('email_destinataire', $user->email)
                ->exists();

            if (!$hasAccess) {
                return redirect()->route('dashboard')
                    ->with('error', 'Vous n\'avez pas accès à cet événement.');
            }
        }

        $event->incrementViews();
        return view('events.show', compact('event'));
    }

    /**
     * Affiche le formulaire d'édition
     */
    public function edit(Event $event)
    {
        $categories = Category::forOrganization()->active()->get();
        $devises = Devise::forOrganization()->active()->get();
        $typeTarifications = TypeTarification::active()->get();
        $visibilites = Visibilite::active()->get();
        $countries = Country::forOrganization()->active()->orderBy('nom')->get();
        $cities = City::forOrganization()->active()->with('country')->orderBy('nom')->get();

        return view('events.edit', compact('event', 'categories', 'devises', 'typeTarifications', 'visibilites', 'countries', 'cities'));
    }

    /**
     * Met à jour un événement
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'date_debut' => 'required|date',
            'heure_debut' => 'required',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'heure_fin' => 'required',
            'fuseau_horaire' => 'nullable|string',
            'lieu' => 'nullable|string|max:255',
            'adresse_complete' => 'nullable|string',
            'ville' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:20',
            'pays' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'lien_google_map' => 'nullable|url',
            'organisateur' => 'nullable|string|max:255',
            'email_contact' => 'nullable|email',
            'telephone' => 'nullable|string|max:20',
            'site_web' => 'nullable|url',
            'statut' => ['required', Rule::in(['brouillon', 'publie', 'annule', 'termine', 'reporte'])],
            'visibilite_id' => 'required|exists:visibilites,id',
            'date_publication' => 'nullable|date',
            'capacite_maximale' => 'nullable|integer|min:1',
            'inscription_requise' => 'boolean',
            'date_limite_inscription' => 'nullable|date',
            'type_tarification_id' => 'required|exists:type_de_tarifications,id',
            'prix' => 'nullable|numeric|min:0',
            'devise_id' => 'nullable|exists:devises,id',
            'tags' => 'nullable|string|max:255',
            'notes_internes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Génération du slug si le titre a changé
        if ($event->titre !== $validated['titre']) {
            $validated['slug'] = Str::slug($validated['titre'] . '-' . time());
        }

        // Gestion de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('events.index')
            ->with('success', 'Événement mis à jour avec succès.');
    }

    /**
     * Supprime un événement
     */
    public function destroy(Event $event)
    {
        // Supprimer l'image si elle existe
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Événement supprimé avec succès.');
    }

    public function destroyAll()
    {
        abort_unless(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin(), 403);

        $eventIds = Event::withTrashed()->forOrganization()->pluck('id');

        if ($eventIds->isEmpty()) {
            return redirect()->route('events.index')
                ->with('success', 'Aucun événement à supprimer.');
        }

        $imagePaths = Event::withTrashed()
            ->forOrganization()
            ->whereNotNull('image')
            ->pluck('image')
            ->filter()
            ->values();

        $qrCodePaths = EventRegistration::whereIn('event_id', $eventIds)
            ->whereNotNull('qr_code_path')
            ->pluck('qr_code_path')
            ->filter()
            ->values();

        DB::transaction(function () use ($eventIds) {
            Event::withTrashed()
                ->forOrganization()
                ->whereIn('id', $eventIds)
                ->forceDelete();
        });

        $filesToDelete = $imagePaths->merge($qrCodePaths)->unique()->values();

        if ($filesToDelete->isNotEmpty()) {
            Storage::disk('public')->delete($filesToDelete->all());
        }

        return redirect()->route('events.index')
            ->with('success', $eventIds->count() . ' événement(s) et leurs éléments liés ont été supprimés.');
    }
}
