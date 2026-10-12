@extends('layouts.app')

@section('title', $event->titre)

@php
    $canManageEvent = auth()->check();
    $canManageAccess = auth()->check() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin());
    $showInternalNotes = $event->notes_internes && $canManageAccess;
    $isPublicRegistrationOpen = optional($event->visibilite)->libelle === 'Public' && $event->statut === 'publie';
    $eventVideoEmbed = \App\Support\EventMedia::videoEmbedUrl($event->video_url);
    $image = \App\Support\EventMedia::storageUrl($event->image);
    $time = fn ($value) => $value ? \Illuminate\Support\Str::substr($value, 0, 5) : '--:--';
    $start = $event->date_debut?->locale('fr');
    $end = $event->date_fin?->locale('fr');

    if ($canManageAccess) {
        $responses = $event->registrations()->selectRaw('statut_reponse, count(*) as total')->groupBy('statut_reponse')->pluck('total', 'statut_reponse');
        $registered = (int) $responses->sum();
        $confirmed = (int) ($responses['present'] ?? 0) + (int) ($responses['peut_etre'] ?? 0) + (int) ($responses['represente'] ?? 0);
        $checkedIn = $event->registrations()->whereNotNull('checked_in_at')->count();
        $invitations = $event->accessLinks()->count();
        $capacity = (int) $event->capacite_maximale;
    }
@endphp

@push('styles')
<style>
    .event-detail { margin: 0 auto; max-width: 1320px; }
    .event-detail *, .event-detail *::before, .event-detail *::after { min-width: 0; }
    .event-layout { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.55fr) minmax(320px, 1fr); }
    .stack { display: grid; gap: 20px; }

    .media { border-radius: var(--app-radius); overflow: hidden; position: relative; }
    .media img { aspect-ratio: 16 / 9; display: block; object-fit: cover; width: 100%; }
    .media-placeholder { align-items: center; aspect-ratio: 16 / 7; background: radial-gradient(90% 120% at 0% 0%, rgba(185, 137, 67, .45), transparent 60%), #171713; color: rgba(216, 185, 120, .4); display: flex; font-size: 3.5rem; justify-content: center; }
    .description { color: var(--app-text); font-size: .98rem; line-height: 1.8; overflow-wrap: anywhere; }
    .description.is-empty { color: var(--app-muted); font-style: italic; }
    .link-chips { display: flex; flex-wrap: wrap; gap: 8px; }
    .link-chip { align-items: center; background: rgba(23, 23, 19, .05); border-radius: 999px; color: var(--app-ink); display: inline-flex; font-size: .84rem; font-weight: 500; gap: 7px; padding: 8px 14px; text-decoration: none; transition: background .2s; }
    .link-chip:hover { background: rgba(23, 23, 19, .09); color: var(--app-ink); }
    .link-chip i { color: var(--app-gold-deep); }
    .internal-note { background: #fbf4e8; border: 1px solid rgba(185, 137, 67, .25); border-radius: 14px; color: #6b5126; font-size: .92rem; line-height: 1.7; padding: 14px 16px; }

    .facts { display: grid; }
    .fact { align-items: flex-start; border-top: 1px solid var(--app-line); display: grid; gap: 14px; grid-template-columns: 34px minmax(0, 1fr); padding: 13px 22px; }
    .fact:first-child { border-top: 0; }
    .fact-icon { align-items: center; background: var(--app-gold-soft); border-radius: 10px; color: var(--app-gold-deep); display: flex; height: 34px; justify-content: center; width: 34px; }
    .fact-label { color: var(--app-muted); font-size: .74rem; font-weight: 500; }
    .fact-value { color: var(--app-ink); font-size: .9rem; font-weight: 600; line-height: 1.4; margin-top: 1px; overflow-wrap: anywhere; }
    .fact-value small { color: var(--app-muted); display: block; font-weight: 400; }
    .fact-value a { color: var(--app-ink); text-decoration-color: rgba(185, 137, 67, .5); text-underline-offset: 3px; }
    .timeline-dates { align-items: stretch; display: grid; gap: 10px; grid-template-columns: 1fr auto 1fr; padding: 0 22px 18px; }
    .tl-date { background: var(--app-bg); border-radius: 14px; padding: 12px 14px; }
    .tl-date span { color: var(--app-muted); display: block; font-size: .72rem; font-weight: 600; letter-spacing: .04em; text-transform: uppercase; }
    .tl-date strong { color: var(--app-ink); display: block; font-size: .95rem; font-weight: 650; margin-top: 4px; }
    .tl-date small { color: var(--app-muted); display: block; font-size: .8rem; }
    .tl-arrow { align-self: center; color: var(--app-muted); }


    @media (max-width: 1099.98px) { .event-layout { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="event-detail">
    <x-app.event-header :event="$event" active="show">
        @if($canManageEvent)
            <x-slot:actions>
                @if($canManageAccess)
                    <div class="dropdown">
                        <button class="ui-btn ui-btn-ghost" type="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-lightning-charge"></i>Actions</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('events.send-link', $event) }}"><i class="bi bi-send"></i>Envoyer des invitations</a></li>
                            <li>
                                <form method="POST" action="{{ route('events.reminders.send', $event) }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-bell"></i>Relancer les invités</button>
                                </form>
                            </li>
                            <li>
                                <form method="POST" action="{{ route('events.thank-you-sms.send', $event) }}" onsubmit="return confirm('Envoyer un SMS de remerciement aux participants déjà checkés ?');">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="bi bi-chat-heart"></i>SMS de remerciement</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endif
                <a href="{{ route('events.edit', $event) }}" class="ui-btn ui-btn-primary"><i class="bi bi-pencil"></i>Modifier</a>
            </x-slot:actions>
        @endif
    </x-app.event-header>

    @if($canManageAccess)
        <section class="ui-stats">
            <article class="ui-stat" style="--accent: var(--app-blue)">
                <div class="ui-stat-top">Invitations envoyées<i class="bi bi-send"></i></div>
                <strong>{{ number_format($invitations, 0, ',', ' ') }}</strong>
                <small>Liens d’accès personnels</small>
            </article>
            <article class="ui-stat" style="--accent: var(--app-gold)">
                <div class="ui-stat-top">Inscrits<i class="bi bi-people"></i></div>
                <strong>{{ number_format($registered, 0, ',', ' ') }}</strong>
                <small>{{ $capacity ? 'sur ' . number_format($capacity, 0, ',', ' ') . ' places' : 'Capacité libre' }}</small>
                @if($capacity)<div class="ui-meter"><span style="width: {{ min(100, round($registered / $capacity * 100)) }}%"></span></div>@endif
            </article>
            <article class="ui-stat" style="--accent: var(--app-green)">
                <div class="ui-stat-top">Confirmés<i class="bi bi-person-check"></i></div>
                <strong>{{ number_format($confirmed, 0, ',', ' ') }}</strong>
                <small>{{ $registered ? round($confirmed / $registered * 100) : 0 }}% des réponses</small>
                <div class="ui-meter"><span style="width: {{ $registered ? round($confirmed / $registered * 100) : 0 }}%"></span></div>
            </article>
            <article class="ui-stat" style="--accent: #7a5cc2">
                <div class="ui-stat-top">Check-in<i class="bi bi-qr-code-scan"></i></div>
                <strong>{{ number_format($checkedIn, 0, ',', ' ') }}</strong>
                <small>{{ $confirmed ? round($checkedIn / max(1, $confirmed) * 100) : 0 }}% des confirmés</small>
                <div class="ui-meter"><span style="width: {{ $confirmed ? min(100, round($checkedIn / $confirmed * 100)) : 0 }}%"></span></div>
            </article>
        </section>
    @endif

    <div class="event-layout">
        <main class="stack">
            <section class="ui-card" style="overflow: hidden">
                <div class="media">
                    @if($image)
                        <img src="{{ $image }}" alt="{{ $event->titre }}">
                    @elseif(!$eventVideoEmbed)
                        <div class="media-placeholder"><i class="bi bi-stars"></i></div>
                    @endif
                    @if($eventVideoEmbed)
                        <div class="ratio ratio-16x9"><iframe src="{{ $eventVideoEmbed }}" title="Vidéo de l'événement" allowfullscreen></iframe></div>
                    @endif
                </div>
                <div class="ui-card-head"><div><h2>À propos de l’événement</h2></div></div>
                <div class="ui-card-body pt-0">
                    <div @class(['description', 'is-empty' => !$event->description])>{!! nl2br(e($event->description ?: 'Aucune description renseignée.')) !!}</div>
                    @if($event->programme_pdf || ($event->video_url && !$eventVideoEmbed))
                        <div class="link-chips mt-4">
                            @if($event->programme_pdf)
                                <a href="{{ \App\Support\EventMedia::storageUrl($event->programme_pdf) }}" target="_blank" rel="noopener" class="link-chip"><i class="bi bi-file-earmark-pdf"></i>Déroulé de l’événement</a>
                            @endif
                            @if($event->video_url && !$eventVideoEmbed)
                                <a href="{{ $event->video_url }}" target="_blank" rel="noopener" class="link-chip"><i class="bi bi-play-circle"></i>Voir la vidéo</a>
                            @endif
                        </div>
                    @endif
                </div>
            </section>

            @if(!empty($event->social_links) || $event->tags)
                <section class="ui-card">
                    @if(!empty($event->social_links))
                        <div class="ui-card-head"><div><h2>Réseaux sociaux</h2><p>Retrouvez l’événement sur ses différentes plateformes.</p></div></div>
                        <div class="ui-card-body pt-0">
                            <div class="link-chips">
                                @foreach($event->social_links as $socialLink)
                                    <a href="{{ $socialLink['url'] }}" target="_blank" rel="noopener noreferrer" class="link-chip"><i class="bi bi-box-arrow-up-right"></i>{{ $socialLink['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($event->tags)
                        <div class="ui-card-head"><div><h2>Mots-clés</h2></div></div>
                        <div class="ui-card-body pt-0">
                            <div class="link-chips">
                                @foreach(explode(',', $event->tags) as $tag)
                                    @if(trim($tag) !== '')<span class="ui-pill">#{{ trim($tag) }}</span>@endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            @if($showInternalNotes)
                <section class="ui-card">
                    <div class="ui-card-head"><div><h2>Notes internes</h2><p>Visibles uniquement par les administrateurs.</p></div><i class="bi bi-lock text-muted"></i></div>
                    <div class="ui-card-body pt-0"><div class="internal-note">{!! nl2br(e($event->notes_internes)) !!}</div></div>
                </section>
            @endif

            @if($isPublicRegistrationOpen)
                <section class="ui-card">
                    <div class="ui-card-head has-border"><div><h2>S'inscrire à cet événement</h2><p>Une carte d'invitation sera envoyée après validation.</p></div></div>
                    <div class="ui-card-body">
                        <form method="POST" action="{{ route('events.register', $event) }}" class="registration-form">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" required>
                                    @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input type="tel" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}">
                                    @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="entreprise" class="form-label">Entreprise</label>
                                    <input type="text" class="form-control @error('entreprise') is-invalid @enderror" id="entreprise" name="entreprise" value="{{ old('entreprise') }}">
                                    @error('entreprise')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="fonction" class="form-label">Fonction</label>
                                    <input type="text" class="form-control @error('fonction') is-invalid @enderror" id="fonction" name="fonction" value="{{ old('fonction') }}">
                                    @error('fonction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <button type="submit" class="ui-btn ui-btn-primary mt-4"><i class="bi bi-check2-circle"></i>Valider l'inscription</button>
                        </form>
                    </div>
                </section>
            @endif
        </main>

        <aside class="stack">
            <section class="ui-card">
                <div class="ui-card-head"><div><h2>Planning</h2></div>@if($event->fuseau_horaire)<span class="ui-pill">{{ $event->fuseau_horaire }}</span>@endif</div>
                <div class="timeline-dates">
                    <div class="tl-date"><span>Début</span><strong>{{ $start ? $start->translatedFormat('d M Y') : 'À définir' }}</strong><small>{{ $time($event->heure_debut) }}</small></div>
                    <i class="bi bi-arrow-right tl-arrow"></i>
                    <div class="tl-date"><span>Fin</span><strong>{{ $end ? $end->translatedFormat('d M Y') : 'À définir' }}</strong><small>{{ $time($event->heure_fin) }}</small></div>
                </div>
                @if($event->date_publication)
                    <div class="facts"><div class="fact"><span class="fact-icon"><i class="bi bi-broadcast"></i></span><div><div class="fact-label">Publié le</div><div class="fact-value">{{ $event->date_publication->format('d/m/Y à H:i') }}</div></div></div></div>
                @endif
            </section>

            @if($event->lieu || $event->adresse_complete || $event->ville || $event->lien_google_map || $event->pays)
                <section class="ui-card">
                    <div class="ui-card-head"><div><h2>Lieu</h2></div>
                        @if($event->lien_google_map)<a href="{{ $event->lien_google_map }}" target="_blank" rel="noopener noreferrer" class="ui-btn ui-btn-soft ui-btn-sm"><i class="bi bi-map"></i>Itinéraire</a>@endif
                    </div>
                    <div class="facts">
                        <div class="fact">
                            <span class="fact-icon"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <div class="fact-value">{{ $event->lieu ?: 'Lieu à confirmer' }}
                                    @if($event->adresse_complete)<small>{{ $event->adresse_complete }}</small>@endif
                                    <small>{{ collect([trim(($event->code_postal ? $event->code_postal . ' ' : '') . $event->ville), $event->pays])->filter()->implode(', ') }}</small>
                                </div>
                            </div>
                        </div>
                        @if($event->latitude && $event->longitude)
                            <div class="fact"><span class="fact-icon"><i class="bi bi-crosshair"></i></span><div><div class="fact-label">Coordonnées</div><div class="fact-value">{{ $event->latitude }}, {{ $event->longitude }}</div></div></div>
                        @endif
                    </div>
                </section>
            @endif

            <section class="ui-card">
                <div class="ui-card-head"><div><h2>Accès et tarification</h2></div></div>
                <div class="facts">
                    <div class="fact"><span class="fact-icon"><i class="bi bi-ticket-perforated"></i></span><div><div class="fact-label">Tarif</div><div class="fact-value">{{ $event->isFree() ? 'Gratuit' : number_format($event->prix, 0, ',', ' ') . ' ' . optional($event->devise)->libelle }}<small>{{ optional($event->typeTarification)->libelle ?? 'Type non défini' }}</small></div></div></div>
                    <div class="fact"><span class="fact-icon"><i class="bi bi-people"></i></span><div><div class="fact-label">Capacité</div><div class="fact-value">{{ $event->capacite_maximale ? number_format($event->capacite_maximale, 0, ',', ' ') . ' personnes' : 'Illimitée' }}</div></div></div>
                    <div class="fact"><span class="fact-icon"><i class="bi bi-clipboard-check"></i></span><div><div class="fact-label">Inscription</div><div class="fact-value">{{ $event->inscription_requise ? 'Requise' : 'Non requise' }}@if($event->inscription_requise && $event->date_limite_inscription)<small>Jusqu’au {{ $event->date_limite_inscription->format('d/m/Y') }}</small>@endif</div></div></div>
                    <div class="fact"><span class="fact-icon"><i class="bi bi-eye"></i></span><div><div class="fact-label">Visibilité</div><div class="fact-value">{{ optional($event->visibilite)->libelle ?? 'Non définie' }}<small>{{ number_format($event->vues, 0, ',', ' ') }} vue(s)</small></div></div></div>
                </div>
            </section>

            @if($event->organisateur || $event->email_contact || $event->telephone || $event->site_web)
                <section class="ui-card">
                    <div class="ui-card-head"><div><h2>Contact</h2></div></div>
                    <div class="facts">
                        @if($event->organisateur)<div class="fact"><span class="fact-icon"><i class="bi bi-person-badge"></i></span><div><div class="fact-label">Organisateur</div><div class="fact-value">{{ $event->organisateur }}</div></div></div>@endif
                        @if($event->email_contact)<div class="fact"><span class="fact-icon"><i class="bi bi-envelope"></i></span><div><div class="fact-label">Email</div><div class="fact-value"><a href="mailto:{{ $event->email_contact }}">{{ $event->email_contact }}</a></div></div></div>@endif
                        @if($event->telephone)<div class="fact"><span class="fact-icon"><i class="bi bi-telephone"></i></span><div><div class="fact-label">Téléphone</div><div class="fact-value"><a href="tel:{{ $event->telephone }}">{{ $event->telephone }}</a></div></div></div>@endif
                        @if($event->site_web)<div class="fact"><span class="fact-icon"><i class="bi bi-link-45deg"></i></span><div><div class="fact-label">Site web</div><div class="fact-value"><a href="{{ $event->site_web }}" target="_blank" rel="noopener noreferrer">{{ $event->site_web }}</a></div></div></div>@endif
                    </div>
                </section>
            @endif

            <p class="text-muted small px-1 mb-0"><i class="bi bi-person me-1"></i>Créé par {{ optional($event->user)->name ?? 'utilisateur inconnu' }}</p>
        </aside>
    </div>
</div>
@endsection
