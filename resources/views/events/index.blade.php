@extends('layouts.app')

@section('title', 'Événements')

@php
    $statusCounts = collect($statusCounts ?? []);
    $totalEvents = $statusCounts->sum() ?: $events->total();
    $advancedKeys = ['category_id', 'visibilite_id', 'date_from', 'date_to', 'user_id'];
    $advancedActive = collect($advancedKeys)->filter(fn ($key) => request()->filled($key))->count();
    $quota = $quota ?? \App\Support\SaasUsage::forOrganization(auth()->user()->organization);
    $eventLimit = $quota['limits']['events'] ?? null;
    $eventsUsed = $quota['usage']['events'] ?? $totalEvents;
    $canCreateEvent = $eventLimit === null || $eventsUsed < $eventLimit;
    $canManageAll = auth()->user()->isSuperAdmin() || auth()->user()->isAdmin();

    $statusLabels = [
        'publie' => 'Publié',
        'brouillon' => 'Brouillon',
        'annule' => 'Annulé',
        'termine' => 'Terminé',
        'reporte' => 'Reporté',
    ];
    $tabs = collect(['' => 'Tous'] + collect($statusLabels)->map(fn ($label) => $label . 's')->all())
        ->filter(fn ($label, $value) => $value === '' || $statusCounts->get($value, 0) > 0 || request('statut') === $value);
    $filterChips = collect([
        'category_id' => optional($categories->firstWhere('id', request('category_id')))->libelle,
        'visibilite_id' => optional($visibilites->firstWhere('id', request('visibilite_id')))->libelle,
        'date_from' => request('date_from') ? 'Dès le ' . \Illuminate\Support\Carbon::parse(request('date_from'))->format('d/m/Y') : null,
        'date_to' => request('date_to') ? 'Jusqu’au ' . \Illuminate\Support\Carbon::parse(request('date_to'))->format('d/m/Y') : null,
        'user_id' => optional($users->firstWhere('id', request('user_id')))->name,
    ])->filter();
@endphp

@push('styles')
<style>
    .events-page { margin: 0 auto; max-width: 1400px; }
    .events-page *, .events-page *::before, .events-page *::after { min-width: 0; }

    .quota-note { align-items: center; background: #fbf4e8; border: 1px solid rgba(185, 137, 67, .28); border-radius: 14px; color: #7a5727; display: flex; font-size: .9rem; gap: 10px; margin-bottom: 18px; padding: 12px 16px; }

    /* Toolbar */
    .toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; margin-bottom: 14px; }
    .tabs { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 2px; max-width: 100%; overflow-x: auto; padding: 4px; scrollbar-width: none; }
    .tabs a { align-items: center; border-radius: 999px; color: var(--app-muted); display: inline-flex; font-size: .84rem; font-weight: 600; gap: 7px; height: 34px; padding: 0 14px; text-decoration: none; transition: background .25s, color .25s, box-shadow .25s; white-space: nowrap; }
    .tabs a:hover { color: var(--app-ink); }
    .tabs a.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 12px -4px rgba(23, 23, 19, .12); color: var(--app-ink); }
    .tabs span { background: rgba(23, 23, 19, .07); border-radius: 99px; font-size: .7rem; font-variant-numeric: tabular-nums; padding: 1px 7px; }
    .tabs a.is-active span { background: var(--app-ink); color: #fff; }
    .search-form { align-items: center; display: flex; gap: 8px; }
    .search { position: relative; }
    .search i { color: var(--app-muted); left: 14px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); }
    .search input { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 999px; color: var(--app-ink); font-size: .88rem; height: 40px; padding: 0 16px 0 38px; transition: border-color .2s, box-shadow .2s, width .4s var(--app-ease); width: 240px; }
    .search input:focus { border-color: var(--app-gold); box-shadow: 0 0 0 4px var(--app-gold-soft); outline: 0; width: 290px; }
    .filter-toggle { align-items: center; background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 999px; color: var(--app-ink); display: inline-flex; font-size: .84rem; font-weight: 600; gap: 7px; height: 40px; padding: 0 14px; }
    .filter-toggle .count { background: var(--app-ink); border-radius: 99px; color: #fff; font-size: .68rem; padding: 1px 7px; }

    .filters { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); margin-bottom: 14px; padding: 18px; }
    .filters .form-label { color: var(--app-muted); font-size: .76rem; font-weight: 600; margin-bottom: 6px; }
    .filters .form-control, .filters .form-select { font-size: .88rem; height: 40px; }
    .chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .chip { align-items: center; background: var(--app-gold-soft); border-radius: 999px; color: var(--app-gold-deep); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 6px; padding: 5px 6px 5px 12px; text-decoration: none; }
    .chip i { align-items: center; background: rgba(138, 97, 40, .14); border-radius: 50%; display: inline-flex; font-size: .62rem; height: 18px; justify-content: center; width: 18px; }
    .chip:hover { color: var(--app-ink); }
    .result-meta { color: var(--app-muted); font-size: .8rem; margin: 0 0 10px 4px; }

    /* List */
    .event-list { display: grid; gap: 10px; }
    .event-card { align-items: center; background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); display: grid; gap: 18px; grid-template-columns: 76px minmax(0, 2.2fr) minmax(0, 1.1fr) minmax(0, 1.1fr) 90px 110px auto; padding: 12px 16px 12px 12px; position: relative; transition: transform .45s var(--app-ease), box-shadow .45s var(--app-ease), border-color .3s; animation: rise .6s var(--app-ease) backwards; animation-delay: calc(var(--i, 0) * 40ms); }
    .event-card:hover { border-color: rgba(185, 137, 67, .3); box-shadow: 0 1px 2px rgba(23, 23, 19, .04), 0 22px 40px -22px rgba(23, 23, 19, .28); transform: translateY(-2px); }
    @keyframes rise { from { opacity: 0; transform: translateY(10px); } }
    .thumb { aspect-ratio: 1; background: linear-gradient(140deg, #2c2a25, #141412); border-radius: 14px; color: #fff; display: flex; flex-direction: column; justify-content: center; overflow: hidden; position: relative; text-align: center; }
    .thumb img { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
    .thumb.has-img::after { background: linear-gradient(0deg, rgba(10, 10, 9, .75), transparent 70%); content: ""; inset: 0; position: absolute; }
    .thumb strong, .thumb span { position: relative; z-index: 1; }
    .thumb strong { font-size: 1.35rem; font-weight: 700; letter-spacing: -.03em; line-height: 1; }
    .thumb span { color: #d8b978; font-size: .64rem; font-weight: 700; letter-spacing: .08em; margin-top: 3px; text-transform: uppercase; }
    .thumb.has-img { justify-content: flex-end; padding-bottom: 8px; }
    .ev-title { color: var(--app-ink); display: block; font-size: .98rem; font-weight: 650; letter-spacing: -.015em; overflow: hidden; text-decoration: none; text-overflow: ellipsis; white-space: nowrap; }
    .ev-title::after { content: ""; inset: 0; position: absolute; }
    .ev-title:hover { color: var(--app-ink); }
    .ev-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 7px; }
    .tag { align-items: center; background: rgba(23, 23, 19, .05); border-radius: 7px; color: var(--app-muted); display: inline-flex; font-size: .72rem; font-weight: 500; gap: 5px; padding: 3px 8px; white-space: nowrap; }
    .cell-label { color: var(--app-muted); display: block; font-size: .7rem; font-weight: 600; letter-spacing: .04em; margin-bottom: 3px; text-transform: uppercase; }
    .cell-value { color: var(--app-ink); display: block; font-size: .86rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cell-sub { color: var(--app-muted); display: block; font-size: .78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cell-value.free { color: var(--app-green); }
    .pill { align-items: center; border-radius: 999px; display: inline-flex; font-size: .72rem; font-weight: 600; gap: 6px; justify-self: start; padding: 5px 10px; white-space: nowrap; }
    .pill::before { background: currentColor; border-radius: 50%; content: ""; height: 6px; width: 6px; }
    .pill.publie { background: rgba(46, 123, 101, .1); color: var(--app-green); }
    .pill.brouillon, .pill.reporte { background: rgba(185, 137, 67, .13); color: var(--app-gold-deep); }
    .pill.annule { background: rgba(164, 81, 74, .1); color: var(--app-red); }
    .pill.termine { background: rgba(49, 95, 131, .1); color: var(--app-blue); }
    .row-actions { align-items: center; display: flex; gap: 6px; justify-content: flex-end; position: relative; z-index: 2; }
    .icon-btn { align-items: center; background: transparent; border: 1px solid transparent; border-radius: 10px; color: var(--app-muted); display: inline-flex; height: 34px; justify-content: center; text-decoration: none; transition: background .2s, color .2s, border-color .2s; width: 34px; }
    .icon-btn:hover, .icon-btn[aria-expanded="true"] { background: rgba(23, 23, 19, .05); color: var(--app-ink); }
    .open-btn { align-items: center; background: rgba(23, 23, 19, .05); border-radius: 999px; color: var(--app-ink); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 4px; height: 32px; padding: 0 12px; text-decoration: none; transition: background .2s, color .2s; }
    .open-btn:hover { background: var(--app-ink); color: #fff; }
    .event-card:has(.dropdown-menu.show), .event-card.menu-open { z-index: 60; }
    .events-page .page-header-actions .dropdown { position: relative; z-index: 70; }
    .events-page .dropdown-menu { z-index: 1065; }

    .empty-card { background: var(--app-panel); border: 1px dashed rgba(23, 23, 19, .16); border-radius: 22px; padding: 56px 24px; text-align: center; }
    .empty-icon { align-items: center; background: var(--app-gold-soft); border-radius: 20px; color: var(--app-gold-deep); display: inline-flex; font-size: 1.8rem; height: 64px; justify-content: center; margin-bottom: 16px; width: 64px; }
    .empty-card h2 { color: var(--app-ink); font-size: 1.2rem; font-weight: 650; letter-spacing: -.02em; }
    .empty-card p { color: var(--app-muted); margin: 6px auto 20px; max-width: 420px; }
    .pager { display: flex; justify-content: center; margin-top: 22px; }
    .pager nav { width: 100%; }
    .pager p.small { display: none; }
    .pager .pagination { gap: 4px; }
    .pager .page-link { border-radius: 10px !important; }

    @media (max-width: 1279.98px) {
        .event-card { grid-template-columns: 72px minmax(0, 2fr) minmax(0, 1.1fr) minmax(0, 1fr) 110px auto; }
        .event-card .col-price { display: none; }
    }
    @media (max-width: 991.98px) {
        .event-card { grid-template-columns: 64px minmax(0, 1fr) auto; row-gap: 10px; }
        .event-card .col-date, .event-card .col-place { grid-column: 2 / -1; }
        .event-card .col-date .cell-label, .event-card .col-place .cell-label { display: none; }
        .event-card .pill { grid-column: 3; grid-row: 1; justify-self: end; }
        .event-card .row-actions { grid-column: 1 / -1; justify-content: flex-start; border-top: 1px solid var(--app-line); padding-top: 10px; }
        .search input, .search input:focus { width: 100%; }
        .search-form, .search { flex: 1; }
    }
    @media (max-width: 575.98px) {
        .toolbar { align-items: stretch; flex-direction: column; }
        .event-card .ev-tags .tag:nth-child(n+3) { display: none; }
    }
    @media (prefers-reduced-motion: reduce) { .event-card { animation: none; } }
</style>
@endpush

@section('content')
<div class="events-page">
    <x-app.page-header kicker="Portefeuille événementiel" title="Événements" subtitle="Publiez, suivez et pilotez chacun de vos événements depuis un seul endroit.">
        <x-slot:actions>
            @if($canManageAll)
                <div class="dropdown">
                    <button class="ui-btn ui-btn-ghost" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Plus d’actions"><i class="bi bi-three-dots"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('events.index') }}"><i class="bi bi-arrow-clockwise"></i>Actualiser</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('events.destroy-all') }}" method="POST" onsubmit="return confirm('Cette action va supprimer définitivement tous les événements, inscriptions, OTP, liens d’accès et fichiers liés. Continuer ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger" @disabled($totalEvents === 0)><i class="bi bi-trash3"></i>Vider tous les événements</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endif
            @if($canCreateEvent)
                <a href="{{ route('events.create') }}" class="ui-btn ui-btn-primary"><i class="bi bi-plus-lg"></i>Nouvel événement</a>
            @else
                <span class="ui-btn ui-btn-primary is-disabled" title="Limite du forfait atteinte"><i class="bi bi-lock"></i>Limite atteinte</span>
            @endif
        </x-slot:actions>
    </x-app.page-header>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    @if(!$canCreateEvent)
        <div class="quota-note"><i class="bi bi-info-circle"></i>Votre forfait autorise {{ number_format((int) $eventLimit, 0, ',', ' ') }} événement(s). Passez à un forfait supérieur pour en créer de nouveaux.</div>
    @endif

    <div class="toolbar">
        <nav class="tabs" aria-label="Filtrer par statut">
            @foreach($tabs as $value => $label)
                <a href="{{ route('events.index', array_filter(array_merge(request()->except(['statut', 'page']), ['statut' => $value]))) }}" @class(['is-active' => (string) request('statut', '') === (string) $value])>
                    {{ $label }}<span>{{ $value === '' ? $totalEvents : $statusCounts->get($value, 0) }}</span>
                </a>
            @endforeach
        </nav>
        <form method="GET" action="{{ route('events.index') }}" class="search-form" role="search">
            @foreach(request()->except(['search', 'page']) as $key => $value)
                @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
            @endforeach
            <label class="search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Rechercher…" aria-label="Rechercher un événement">
            </label>
            <button type="button" class="filter-toggle" data-bs-toggle="collapse" data-bs-target="#advancedFilters" aria-expanded="{{ $advancedActive ? 'true' : 'false' }}">
                <i class="bi bi-sliders2"></i>Filtres @if($advancedActive)<span class="count">{{ $advancedActive }}</span>@endif
            </button>
        </form>
    </div>

    <div @class(['collapse', 'show' => $advancedActive]) id="advancedFilters">
        <form method="GET" action="{{ route('events.index') }}" class="filters">
            @if(request('statut'))<input type="hidden" name="statut" value="{{ request('statut') }}">@endif
            @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
            <div class="row g-3 align-items-end">
                <div class="col-lg col-md-6">
                    <label for="category_id" class="form-label">Catégorie</label>
                    <select class="form-select" id="category_id" name="category_id">
                        <option value="">Toutes</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg col-md-6">
                    <label for="visibilite_id" class="form-label">Visibilité</label>
                    <select class="form-select" id="visibilite_id" name="visibilite_id">
                        <option value="">Toutes</option>
                        @foreach($visibilites as $visibilite)
                            <option value="{{ $visibilite->id }}" @selected(request('visibilite_id') == $visibilite->id)>{{ $visibilite->libelle }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg col-md-6">
                    <label for="date_from" class="form-label">Du</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-lg col-md-6">
                    <label for="date_to" class="form-label">Au</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                @if($canManageAll)
                    <div class="col-lg col-md-6">
                        <label for="user_id" class="form-label">Créateur</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">Tous</option>
                            @foreach($users as $creator)
                                <option value="{{ $creator->id }}" @selected(request('user_id') == $creator->id)>{{ $creator->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-lg-auto col-md-6 d-flex gap-2">
                    <button type="submit" class="ui-btn ui-btn-primary flex-fill">Appliquer</button>
                    <a href="{{ route('events.index') }}" class="ui-btn ui-btn-ghost" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>

    @if($filterChips->isNotEmpty())
        <div class="chips">
            @foreach($filterChips as $key => $label)
                <a class="chip" href="{{ route('events.index', request()->except([$key, 'page'])) }}">{{ $label }}<i class="bi bi-x-lg"></i></a>
            @endforeach
        </div>
    @endif

    @if($events->count() > 0)
        <p class="result-meta">{{ $events->firstItem() }}–{{ $events->lastItem() }} sur {{ $events->total() }} événement(s){{ request('search') ? ' pour « ' . request('search') . ' »' : '' }}</p>
        <div class="event-list">
            @foreach($events as $event)
                @php
                    $image = \App\Support\EventMedia::storageUrl($event->image);
                    $start = $event->date_debut?->locale('fr');
                @endphp
                <article class="event-card" style="--i: {{ $loop->index }}">
                    <div @class(['thumb', 'has-img' => $image])>
                        @if($image)<img src="{{ $image }}" alt="" loading="lazy">@endif
                        <strong>{{ $start?->format('d') ?? '--' }}</strong>
                        <span>{{ $start?->translatedFormat('M') ?? '—' }}</span>
                    </div>
                    <div>
                        <a href="{{ route('events.show', $event) }}" class="ev-title">{{ $event->titre }}</a>
                        <div class="ev-tags">
                            <span class="tag"><i class="bi bi-tag"></i>{{ optional($event->category)->libelle ?: 'Sans catégorie' }}</span>
                            @if($event->visibilite)<span class="tag"><i class="bi bi-eye"></i>{{ $event->visibilite->libelle }}</span>@endif
                            <span class="tag"><i class="bi bi-person"></i>{{ optional($event->user)->name ?: 'Créateur inconnu' }}</span>
                        </div>
                    </div>
                    <div class="col-date">
                        <span class="cell-label">Date</span>
                        <span class="cell-value">{{ $start ? $start->translatedFormat('D d M Y') : 'Date à définir' }}</span>
                        <span class="cell-sub">{{ $event->heure_debut ? \Illuminate\Support\Str::substr($event->heure_debut, 0, 5) : '--:--' }} → {{ $event->date_fin && $event->date_debut && !$event->date_fin->isSameDay($event->date_debut) ? $event->date_fin->format('d/m') . ' ' : '' }}{{ $event->heure_fin ? \Illuminate\Support\Str::substr($event->heure_fin, 0, 5) : '--:--' }}</span>
                    </div>
                    <div class="col-place">
                        <span class="cell-label">Lieu</span>
                        <span class="cell-value">{{ $event->lieu ?: 'Lieu à confirmer' }}</span>
                        <span class="cell-sub">{{ collect([$event->ville, $event->pays])->filter()->implode(', ') ?: 'Adresse non renseignée' }}</span>
                    </div>
                    <div class="col-price">
                        <span class="cell-label">Inscrits</span>
                        <span class="cell-value">{{ $event->registrations_count }}{{ $event->capacite_maximale ? ' / ' . $event->capacite_maximale : '' }}</span>
                        <span @class(['cell-sub', 'text-success' => $event->isFree()])>{{ $event->isFree() ? 'Gratuit' : number_format((float) $event->prix, 0, ',', ' ') . ' ' . optional($event->devise)->libelle }}</span>
                    </div>
                    <span class="pill {{ $event->statut }}">{{ $statusLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                    <div class="row-actions">
                        <a href="{{ route('events.show', $event) }}" class="open-btn">Ouvrir <i class="bi bi-chevron-right"></i></a>
                        <div class="dropdown">
                            <button class="icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions pour {{ $event->titre }}"><i class="bi bi-three-dots"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('events.show', $event) }}"><i class="bi bi-eye"></i>Voir le détail</a></li>
                                <li><a class="dropdown-item" href="{{ route('events.edit', $event) }}"><i class="bi bi-pencil"></i>Modifier</a></li>
                                @if($canManageAll)
                                    <li><a class="dropdown-item" href="{{ route('events.registrations', $event) }}"><i class="bi bi-people"></i>Inscriptions</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i>Supprimer</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if($events->hasPages())
            <div class="pager">{{ $events->links('pagination::bootstrap-5') }}</div>
        @endif
    @else
        <div class="empty-card">
            <span class="empty-icon"><i class="bi bi-calendar2-x"></i></span>
            @if(request()->hasAny(['search', 'statut', ...$advancedKeys]) && collect(request()->only(['search', 'statut', ...$advancedKeys]))->filter()->isNotEmpty())
                <h2>Aucun résultat</h2>
                <p>Aucun événement ne correspond à vos critères. Essayez d’élargir la recherche.</p>
                <a href="{{ route('events.index') }}" class="ui-btn ui-btn-ghost"><i class="bi bi-arrow-counterclockwise"></i>Réinitialiser les filtres</a>
            @else
                <h2>Votre premier événement vous attend</h2>
                <p>Créez un événement, invitez vos convives et suivez leurs réponses en temps réel.</p>
                @if($canCreateEvent)
                    <a href="{{ route('events.create') }}" class="ui-btn ui-btn-primary"><i class="bi bi-plus-lg"></i>Créer un événement</a>
                @endif
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.event-card .dropdown').forEach((dropdown) => {
        const card = dropdown.closest('.event-card');
        dropdown.addEventListener('show.bs.dropdown', () => card.classList.add('menu-open'));
        dropdown.addEventListener('hidden.bs.dropdown', () => card.classList.remove('menu-open'));
    });
</script>
@endpush
