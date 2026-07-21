@extends('layouts.app')

@section('title', $event->titre)

@php
    $statusLabels = [
        'publie' => 'Publié',
        'brouillon' => 'Brouillon',
        'annule' => 'Annulé',
        'termine' => 'Terminé',
        'reporte' => 'Reporté',
        'archive' => 'Archivé',
    ];

    $canManageEvent = auth()->check();
    $canManageAccess = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin());
    $showInternalNotes = $event->notes_internes && $canManageAccess;
    $isPublicRegistrationOpen = optional($event->visibilite)->libelle === 'Public' && $event->statut === 'publie';
@endphp

@push('styles')
<style>
    .event-detail {
        --ink: #171713;
        --text: #2c2a25;
        --muted: #746f65;
        --line: #dfd7cb;
        --panel: #fffefa;
        --panel-soft: #f8f4ec;
        --gold: #b98943;
        --green: #2e7b65;
        --blue: #315f83;
        --red: #a4514a;
        --shadow: 0 18px 45px rgba(39, 33, 25, 0.08);
        color: var(--text);
        max-width: 1420px;
        margin: 0 auto;
    }

    .event-detail *,
    .event-detail *::before,
    .event-detail *::after {
        min-width: 0;
    }

    .event-hero {
        background:
            linear-gradient(115deg, rgba(255, 254, 250, 0.98), rgba(248, 244, 236, 0.92)),
            radial-gradient(circle at 12% 10%, rgba(185, 137, 67, 0.16), transparent 28%);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        display: grid;
        gap: 24px;
        grid-template-columns: minmax(0, 1fr) minmax(300px, 0.36fr);
        margin-bottom: 18px;
        overflow: hidden;
        padding: 28px;
    }

    .event-kicker {
        color: var(--gold);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .event-title {
        color: var(--ink);
        font-size: clamp(2rem, 4vw, 4rem);
        font-weight: 600;
        letter-spacing: 0;
        line-height: 0.98;
        margin: 10px 0 16px;
        overflow-wrap: anywhere;
    }

    .event-lead {
        color: var(--muted);
        font-size: 1rem;
        line-height: 1.7;
        max-width: 780px;
    }

    .hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .meta-chip,
    .status-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        gap: 8px;
        min-height: 34px;
        padding: 7px 12px;
        width: fit-content;
    }

    .meta-chip {
        background: rgba(255, 255, 255, 0.72);
        border: 1px solid var(--line);
        color: var(--text);
        font-size: 0.86rem;
    }

    .status-pill {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .status-pill.publie { background: rgba(46, 123, 101, 0.12); color: var(--green); }
    .status-pill.brouillon { background: rgba(185, 137, 67, 0.15); color: #8a6128; }
    .status-pill.annule { background: rgba(164, 81, 74, 0.13); color: var(--red); }
    .status-pill.termine { background: rgba(49, 95, 131, 0.13); color: var(--blue); }
    .status-pill.reporte { background: rgba(185, 137, 67, 0.15); color: #8a6128; }

    .hero-aside {
        align-self: stretch;
        background: #171713;
        border-radius: 8px;
        color: #fffaf1;
        display: grid;
        gap: 14px;
        padding: 20px;
    }

    .hero-date {
        border-bottom: 1px solid rgba(255, 250, 241, 0.16);
        padding-bottom: 16px;
    }

    .hero-date span {
        color: rgba(255, 250, 241, 0.58);
        display: block;
        font-size: 0.78rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .hero-date strong {
        color: #fffaf1;
        display: block;
        font-size: 1.35rem;
        font-weight: 600;
        line-height: 1.25;
        margin-top: 7px;
    }

    .hero-fact {
        display: grid;
        gap: 4px;
    }

    .hero-fact span {
        color: rgba(255, 250, 241, 0.58);
        font-size: 0.78rem;
    }

    .hero-fact strong {
        color: #fffaf1;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .event-actions {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
        padding: 14px;
    }

    .event-btn {
        align-items: center;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--ink);
        display: inline-flex;
        gap: 8px;
        justify-content: center;
        min-height: 42px;
        padding: 0 14px;
        text-decoration: none;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
    }

    .event-btn:hover {
        background: #fff;
        border-color: rgba(185, 137, 67, 0.55);
        color: var(--ink);
        transform: translateY(-1px);
    }

    .event-btn.primary {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }

    .event-btn.primary:hover {
        background: #000;
        color: #fff;
    }

    .event-layout {
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(0, 1.58fr) minmax(340px, 0.78fr);
        align-items: start;
    }

    .detail-panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .detail-panel + .detail-panel {
        margin-top: 18px;
    }

    .panel-head {
        border-bottom: 1px solid var(--line);
        padding: 17px 20px;
    }

    .panel-head h2 {
        color: var(--ink);
        font-size: 1.02rem;
        font-weight: 600;
        margin: 0;
    }

    .panel-head p {
        color: var(--muted);
        font-size: 0.82rem;
        margin: 4px 0 0;
    }

    .panel-body {
        padding: 20px;
    }

    .event-image {
        aspect-ratio: 16 / 9;
        background: var(--panel-soft);
        border-bottom: 1px solid var(--line);
        object-fit: cover;
        width: 100%;
    }

    .description-text {
        color: var(--text);
        font-size: 1rem;
        line-height: 1.85;
        overflow-wrap: anywhere;
    }

    .tag-list {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .tag {
        background: var(--panel-soft);
        border: 1px solid var(--line);
        border-radius: 999px;
        color: var(--text);
        font-size: 0.82rem;
        padding: 7px 11px;
    }

    .info-list {
        display: grid;
    }

    .info-row {
        align-items: start;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 12px;
        grid-template-columns: 38px minmax(0, 1fr);
        padding: 15px 20px;
    }

    .info-row:last-child {
        border-bottom: 0;
    }

    .info-icon {
        align-items: center;
        background: var(--panel-soft);
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--gold);
        display: flex;
        height: 38px;
        justify-content: center;
        width: 38px;
    }

    .info-label {
        color: var(--muted);
        font-size: 0.76rem;
        letter-spacing: 0.08em;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .info-value {
        color: var(--ink);
        font-weight: 500;
        line-height: 1.45;
        overflow-wrap: anywhere;
    }

    .info-value a {
        color: var(--ink);
        text-decoration-color: rgba(185, 137, 67, 0.45);
        text-underline-offset: 4px;
    }

    .map-link {
        align-items: center;
        background: rgba(185, 137, 67, 0.1);
        border: 1px solid rgba(185, 137, 67, 0.25);
        border-radius: 8px;
        color: #8a6128 !important;
        display: inline-flex;
        gap: 8px;
        min-height: 38px;
        padding: 0 12px;
        text-decoration: none;
    }

    .side-stack {
        display: grid;
        gap: 18px;
    }

    .internal-note {
        background: #fff6df;
        border: 1px solid rgba(185, 137, 67, 0.26);
        border-radius: 8px;
        color: #6b5126;
        line-height: 1.75;
        padding: 14px;
    }

    .registration-form .form-control {
        border-color: var(--line);
        border-radius: 8px;
        min-height: 42px;
    }

    .registration-form .form-label {
        color: var(--text);
        font-weight: 500;
    }

    .event-hero {
        border-color: rgba(223, 215, 203, 0.7);
        border-radius: 22px;
        box-shadow: 0 22px 52px rgba(39, 33, 25, 0.06);
    }

    .hero-aside {
        border-radius: 18px;
    }

    .event-actions {
        background: transparent;
        border: 0;
        box-shadow: none;
        padding: 0;
    }

    .event-btn {
        background: rgba(255, 254, 250, 0.82);
        border-color: rgba(223, 215, 203, 0.84);
        border-radius: 14px;
    }

    .detail-panel {
        border-color: rgba(223, 215, 203, 0.7);
        border-radius: 20px;
        box-shadow: 0 18px 42px rgba(39, 33, 25, 0.055);
    }

    .panel-head {
        border-bottom-color: rgba(223, 215, 203, 0.58);
        padding: 20px 22px 16px;
    }

    .panel-body {
        padding: 22px;
    }

    .event-image {
        border-bottom-color: rgba(223, 215, 203, 0.58);
    }

    .info-row {
        border-bottom-color: rgba(223, 215, 203, 0.54);
        padding: 16px 22px;
    }

    .info-icon,
    .map-link,
    .internal-note,
    .registration-form .form-control {
        border-radius: 12px;
    }

    @media (max-width: 1100px) {
        .event-hero,
        .event-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .event-hero {
            padding: 20px;
        }

        .event-actions {
            display: grid;
        }

        .event-btn {
            width: 100%;
        }

        .info-row {
            grid-template-columns: 1fr;
        }

        .info-icon {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="event-detail">
    <section class="event-hero">
        <div>
            <div class="event-kicker">Détail événement</div>
            <h1 class="event-title">{{ $event->titre }}</h1>
            <p class="event-lead">
                {{ $event->description ? \Illuminate\Support\Str::limit(strip_tags($event->description), 190) : 'Vue complète du dossier événement, de ses paramètres de publication et de ses informations opérationnelles.' }}
            </p>
            <div class="hero-meta">
                <span class="status-pill {{ $event->statut }}">{{ $statusLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                <span class="meta-chip"><i class="bi bi-tag"></i>{{ optional($event->category)->libelle ?? 'Sans catégorie' }}</span>
                <span class="meta-chip"><i class="bi bi-eye"></i>{{ number_format($event->vues, 0, ',', ' ') }} vues</span>
                <span class="meta-chip"><i class="bi bi-person"></i>{{ optional($event->user)->name ?? 'Non attribué' }}</span>
            </div>
        </div>

        <aside class="hero-aside">
            <div class="hero-date">
                <span>Début</span>
                <strong>{{ optional($event->date_debut)->translatedFormat('d M Y') ?? 'Date à confirmer' }} · {{ $event->heure_debut ?: '--:--' }}</strong>
            </div>
            <div class="hero-fact">
                <span>Fin</span>
                <strong>{{ optional($event->date_fin)->translatedFormat('d M Y') ?? 'Date à confirmer' }} · {{ $event->heure_fin ?: '--:--' }}</strong>
            </div>
            <div class="hero-fact">
                <span>Lieu</span>
                <strong>{{ $event->lieu ?: ($event->ville ?: 'Lieu à confirmer') }}</strong>
            </div>
            <div class="hero-fact">
                <span>Visibilité</span>
                <strong>{{ optional($event->visibilite)->libelle ?? 'Non définie' }}</strong>
            </div>
        </aside>
    </section>

    <nav class="event-actions" aria-label="Actions événement">
        @if($canManageEvent)
            <a href="{{ route('events.edit', $event) }}" class="event-btn primary">
                <i class="bi bi-pencil"></i>
                Modifier
            </a>
            @if($canManageAccess)
                <a href="{{ route('events.send-link', $event) }}" class="event-btn">
                    <i class="bi bi-send"></i>
                    Envoyer des liens
                </a>
                <a href="{{ route('events.registrations', $event) }}" class="event-btn">
                    <i class="bi bi-people"></i>
                    Inscriptions
                </a>
            @endif
        @endif
        <a href="{{ route('events.index') }}" class="event-btn">
            <i class="bi bi-arrow-left"></i>
            Retour à la liste
        </a>
    </nav>

    <div class="event-layout">
        <main>
            <section class="detail-panel">
                @php($eventVideoEmbed = \App\Support\EventMedia::videoEmbedUrl($event->video_url))
                @if($event->image)
                    <img src="{{ \App\Support\EventMedia::storageUrl($event->image) }}" alt="{{ $event->titre }}" class="event-image">
                @endif
                @if($eventVideoEmbed)
                    <div class="ratio ratio-16x9">
                        <iframe src="{{ $eventVideoEmbed }}" title="Vidéo de l'événement" allowfullscreen></iframe>
                    </div>
                @elseif($event->video_url)
                    <div class="p-3">
                        <a href="{{ $event->video_url }}" target="_blank" class="event-btn">
                            <i class="bi bi-play-circle"></i> Voir la vidéo
                        </a>
                    </div>
                @endif
                <div class="panel-head">
                    <h2>Description</h2>
                    <p>Présentation et contexte de l'événement.</p>
                </div>
                <div class="panel-body">
                    <div class="description-text">
                        {!! nl2br(e($event->description ?: 'Aucune description renseignée.')) !!}
                    </div>
                </div>
            </section>

            @if($event->tags)
                <section class="detail-panel">
                    <div class="panel-head">
                        <h2>Tags et mots-clés</h2>
                    </div>
                    <div class="panel-body">
                        <div class="tag-list">
                            @foreach(explode(',', $event->tags) as $tag)
                                <span class="tag">{{ trim($tag) }}</span>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            @if($showInternalNotes)
                <section class="detail-panel">
                    <div class="panel-head">
                        <h2>Notes internes</h2>
                        <p>Informations réservées à l'administration.</p>
                    </div>
                    <div class="panel-body">
                        <div class="internal-note">{!! nl2br(e($event->notes_internes)) !!}</div>
                    </div>
                </section>
            @endif

            @if($isPublicRegistrationOpen)
                <section class="detail-panel">
                    <div class="panel-head">
                        <h2>S'inscrire à cet événement</h2>
                        <p>Une carte d'invitation sera envoyée après validation.</p>
                    </div>
                    <div class="panel-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('events.register', $event) }}" class="registration-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" required>
                                    @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                                    @error('prenom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}">
                                    @error('telephone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="entreprise" class="form-label">Entreprise</label>
                                    <input type="text" class="form-control @error('entreprise') is-invalid @enderror" id="entreprise" name="entreprise" value="{{ old('entreprise') }}">
                                    @error('entreprise')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="fonction" class="form-label">Fonction</label>
                                    <input type="text" class="form-control @error('fonction') is-invalid @enderror" id="fonction" name="fonction" value="{{ old('fonction') }}">
                                    @error('fonction')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="event-btn primary">
                                <i class="bi bi-check-circle"></i>
                                Valider l'inscription
                            </button>
                        </form>
                    </div>
                </section>
            @endif
        </main>

        <aside class="side-stack">
            <section class="detail-panel">
                <div class="panel-head">
                    <h2>Planning</h2>
                </div>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-icon"><i class="bi bi-calendar3"></i></span>
                        <div>
                            <div class="info-label">Date de début</div>
                            <div class="info-value">{{ optional($event->date_debut)->translatedFormat('d F Y') ?? 'Non définie' }} à {{ $event->heure_debut ?: '--:--' }}</div>
                        </div>
                    </div>
                    <div class="info-row">
                        <span class="info-icon"><i class="bi bi-calendar-check"></i></span>
                        <div>
                            <div class="info-label">Date de fin</div>
                            <div class="info-value">{{ optional($event->date_fin)->translatedFormat('d F Y') ?? 'Non définie' }} à {{ $event->heure_fin ?: '--:--' }}</div>
                        </div>
                    </div>
                    @if($event->fuseau_horaire)
                        <div class="info-row">
                            <span class="info-icon"><i class="bi bi-clock-history"></i></span>
                            <div>
                                <div class="info-label">Fuseau horaire</div>
                                <div class="info-value">{{ $event->fuseau_horaire }}</div>
                            </div>
                        </div>
                    @endif
                    @if($event->date_publication)
                        <div class="info-row">
                            <span class="info-icon"><i class="bi bi-broadcast"></i></span>
                            <div>
                                <div class="info-label">Publication</div>
                                <div class="info-value">{{ $event->date_publication->format('d/m/Y à H:i') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            @if($event->lieu || $event->adresse_complete || $event->ville || $event->lien_google_map || $event->pays)
                <section class="detail-panel">
                    <div class="panel-head">
                        <h2>Localisation</h2>
                    </div>
                    <div class="info-list">
                        @if($event->lieu)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-geo-alt-fill"></i></span>
                                <div>
                                    <div class="info-label">Lieu</div>
                                    <div class="info-value">{{ $event->lieu }}</div>
                                </div>
                            </div>
                        @endif
                        @if($event->adresse_complete)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-house"></i></span>
                                <div>
                                    <div class="info-label">Adresse</div>
                                    <div class="info-value">{{ $event->adresse_complete }}</div>
                                </div>
                            </div>
                        @endif
                        @if($event->ville || $event->code_postal)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-building"></i></span>
                                <div>
                                    <div class="info-label">Ville</div>
                                    <div class="info-value">@if($event->code_postal){{ $event->code_postal }} @endif{{ $event->ville }}</div>
                                </div>
                            </div>
                        @endif
                        @if($event->pays)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-globe"></i></span>
                                <div>
                                    <div class="info-label">Pays</div>
                                    <div class="info-value">{{ $event->pays }}</div>
                                </div>
                            </div>
                        @endif
                        @if($event->latitude && $event->longitude)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-crosshair"></i></span>
                                <div>
                                    <div class="info-label">Coordonnées</div>
                                    <div class="info-value">{{ $event->latitude }}, {{ $event->longitude }}</div>
                                </div>
                            </div>
                        @endif
                        @if($event->lien_google_map)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-map"></i></span>
                                <div>
                                    <div class="info-label">Carte</div>
                                    <a href="{{ $event->lien_google_map }}" target="_blank" rel="noopener noreferrer" class="map-link">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                        Ouvrir Google Maps
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            <section class="detail-panel">
                <div class="panel-head">
                    <h2>Tarification et capacité</h2>
                </div>
                <div class="info-list">
                    <div class="info-row">
                        <span class="info-icon"><i class="bi bi-currency-exchange"></i></span>
                        <div>
                            <div class="info-label">Type</div>
                            <div class="info-value">{{ optional($event->typeTarification)->libelle ?? 'Non défini' }}</div>
                        </div>
                    </div>
                    @if(!$event->isFree())
                        <div class="info-row">
                            <span class="info-icon"><i class="bi bi-cash-coin"></i></span>
                            <div>
                                <div class="info-label">Prix</div>
                                <div class="info-value">{{ number_format($event->prix, 2, ',', ' ') }} {{ optional($event->devise)->libelle }}</div>
                            </div>
                        </div>
                    @endif
                    @if($event->capacite_maximale)
                        <div class="info-row">
                            <span class="info-icon"><i class="bi bi-people"></i></span>
                            <div>
                                <div class="info-label">Capacité</div>
                                <div class="info-value">{{ number_format($event->capacite_maximale, 0, ',', ' ') }} personnes</div>
                            </div>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-icon"><i class="bi bi-clipboard-check"></i></span>
                        <div>
                            <div class="info-label">Inscription</div>
                            <div class="info-value">{{ $event->inscription_requise ? 'Requise' : 'Non requise' }}</div>
                        </div>
                    </div>
                    @if($event->inscription_requise && $event->date_limite_inscription)
                        <div class="info-row">
                            <span class="info-icon"><i class="bi bi-calendar-x"></i></span>
                            <div>
                                <div class="info-label">Limite</div>
                                <div class="info-value">{{ $event->date_limite_inscription->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            @if($event->organisateur || $event->email_contact || $event->telephone || $event->site_web)
                <section class="detail-panel">
                    <div class="panel-head">
                        <h2>Organisation</h2>
                    </div>
                    <div class="info-list">
                        @if($event->organisateur)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-person-badge"></i></span>
                                <div>
                                    <div class="info-label">Organisateur</div>
                                    <div class="info-value">{{ $event->organisateur }}</div>
                                </div>
                            </div>
                        @endif
                        @if($event->email_contact)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-envelope"></i></span>
                                <div>
                                    <div class="info-label">Email</div>
                                    <div class="info-value"><a href="mailto:{{ $event->email_contact }}">{{ $event->email_contact }}</a></div>
                                </div>
                            </div>
                        @endif
                        @if($event->telephone)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-telephone"></i></span>
                                <div>
                                    <div class="info-label">Téléphone</div>
                                    <div class="info-value"><a href="tel:{{ $event->telephone }}">{{ $event->telephone }}</a></div>
                                </div>
                            </div>
                        @endif
                        @if($event->site_web)
                            <div class="info-row">
                                <span class="info-icon"><i class="bi bi-link-45deg"></i></span>
                                <div>
                                    <div class="info-label">Site web</div>
                                    <div class="info-value"><a href="{{ $event->site_web }}" target="_blank" rel="noopener noreferrer">{{ $event->site_web }}</a></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </section>
            @endif
        </aside>
    </div>
</div>
@endsection
