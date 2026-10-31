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

    /* Toolbar */
    .toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; margin-bottom: 14px; }
    .tabs { display: inline-flex; gap: 4px; max-width: 100%; overflow-x: auto; scrollbar-width: none; }
    .tabs a { align-items: center; border-radius: 999px; color: var(--app-muted); display: inline-flex; font-size: .84rem; font-weight: 600; gap: 7px; height: 34px; padding: 0 12px; text-decoration: none; white-space: nowrap; }
    .tabs a:hover { background: var(--app-soft); color: var(--app-ink); }
    .tabs a.is-active { background: var(--app-ink); color: #fff; }
    .tabs span { font-size: .74rem; font-variant-numeric: tabular-nums; opacity: .7; }
    .search-form { align-items: center; display: flex; gap: 8px; }
    .search { position: relative; }
    .search i { color: var(--app-muted); left: 14px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); }
    .search input { background: var(--app-panel); border: 1px solid rgba(23, 23, 19, .13); border-radius: 999px; color: var(--app-ink); font-size: .86rem; height: 38px; padding: 0 16px 0 38px; width: 260px; }
    .search input:focus { border-color: var(--app-gold); box-shadow: 0 0 0 3px var(--app-gold-soft); outline: 0; }
    .filter-toggle { align-items: center; background: var(--app-panel); border: 1px solid rgba(23, 23, 19, .13); border-radius: 999px; color: var(--app-ink); display: inline-flex; font-size: .84rem; font-weight: 600; gap: 7px; height: 38px; padding: 0 14px; }
    .filter-toggle .count { background: var(--app-ink); border-radius: 99px; color: #fff; font-size: .68rem; padding: 1px 7px; }

    .filters { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); margin-bottom: 14px; padding: 18px; }
    .chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .chip { align-items: center; background: var(--app-soft); border-radius: 999px; color: var(--app-ink); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 6px; padding: 5px 10px 5px 12px; text-decoration: none; }
    .chip:hover { background: rgba(23, 23, 19, .09); color: var(--app-ink); }

    /* List */
    .event-list { overflow: visible; }
    .list-meta { color: var(--app-muted); font-size: .8rem; padding: 12px 20px; border-bottom: 1px solid var(--app-line); }
    .event-row { align-items: center; display: grid; gap: 16px; grid-template-columns: 52px minmax(0, 2fr) minmax(0, 1.2fr) minmax(0, 1.2fr) 90px 104px 34px; padding: 12px 20px; position: relative; }
    .event-row + .event-row { border-top: 1px solid var(--app-line); }
    .event-row:hover { background: rgba(23, 23, 19, .02); }
    .event-row.menu-open { z-index: 60; }
    .thumb { aspect-ratio: 1; border: 1px solid var(--app-line); border-radius: 12px; display: flex; flex-direction: column; justify-content: center; overflow: hidden; position: relative; text-align: center; }
    .thumb img { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
    .thumb strong { color: var(--app-ink); font-size: 1.05rem; font-weight: 700; line-height: 1.1; }
    .thumb span { color: var(--app-muted); font-size: .62rem; font-weight: 600; text-transform: uppercase; }
    .ev-title { color: var(--app-ink); display: block; font-size: .92rem; font-weight: 600; overflow: hidden; text-decoration: none; text-overflow: ellipsis; white-space: nowrap; }
    .ev-title::after { content: ""; inset: 0; position: absolute; }
    .ev-title:hover { color: var(--app-ink); }
    .cell-value { color: var(--app-ink); display: block; font-size: .85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .cell-sub { color: var(--app-muted); display: block; font-size: .78rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .event-row .ui-pill { justify-self: start; }
    .row-actions { position: relative; z-index: 2; }
    .events-page .page-header-actions .dropdown { position: relative; z-index: 70; }
    .events-page .dropdown-menu { z-index: 1065; }

    .empty-card { background: var(--app-panel); border: 1px dashed rgba(23, 23, 19, .16); border-radius: var(--app-radius); padding: 48px 24px; text-align: center; }
    .empty-card h2 { color: var(--app-ink); font-size: 1.1rem; font-weight: 650; }
    .empty-card p { color: var(--app-muted); margin: 6px auto 18px; max-width: 420px; }
    .pager { display: flex; justify-content: center; margin-top: 20px; }
    .pager nav { width: 100%; }
    .pager p.small { display: none; }
    .pager .pagination { gap: 4px; }
    .pager .page-link { border-radius: 10px !important; }

    @media (max-width: 1199.98px) {
        .event-row { grid-template-columns: 52px minmax(0, 2fr) minmax(0, 1.2fr) 104px 34px; }
        .event-row .col-place, .event-row .col-count { display: none; }
    }
    @media (max-width: 767.98px) {
        .event-row { grid-template-columns: 48px minmax(0, 1fr) 34px; row-gap: 4px; }
        .event-row .col-date { grid-column: 2; grid-row: 2; }
        .event-row .ui-pill { display: none; }
        .event-row .row-actions { grid-column: 3; grid-row: 1 / span 2; }
        .search input { width: 100%; }
        .search-form, .search { flex: 1; }
    }
    @media (max-width: 575.98px) { .toolbar { align-items: stretch; flex-direction: column; } }
</style>
@endpush

@section('content')
<div class="events-page">
    <x-app.page-header title="Événements">
        <x-slot:actions>
            @if($canManageAll)
                <div class="dropdown">
                    <button class="ui-btn ui-btn-ghost" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Plus d’actions"><i class="bi bi-three-dots"></i></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <form action="{{ route('events.destroy-all') }}" method="POST" data-confirm-title="Vider tous les événements ?" data-confirm="Tous les événements, inscriptions, codes OTP, liens d’accès et fichiers liés seront supprimés définitivement." data-confirm-button="Tout supprimer">
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


    @if(!$canCreateEvent)
        <div class="alert alert-warning">Votre forfait autorise {{ number_format((int) $eventLimit, 0, ',', ' ') }} événement(s). Passez à un forfait supérieur pour en créer de nouveaux.</div>
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
                <a class="chip" href="{{ route('events.index', request()->except([$key, 'page'])) }}">{{ $label }}<i class="bi bi-x"></i></a>
            @endforeach
        </div>
    @endif

    @if($events->count() > 0)
        <section class="ui-card event-list">
            <div class="list-meta">{{ $events->firstItem() }}–{{ $events->lastItem() }} sur {{ $events->total() }} événement(s){{ request('search') ? ' pour « ' . request('search') . ' »' : '' }}</div>
            @foreach($events as $event)
                @php
                    $image = \App\Support\EventMedia::storageUrl($event->image);
                    $start = $event->date_debut?->locale('fr');
                    $hours = collect([$event->heure_debut, $event->heure_fin])->filter()->map(fn ($h) => \Illuminate\Support\Str::substr($h, 0, 5))->implode(' – ');
                @endphp
                <article class="event-row">
                    <div class="thumb">
                        @if($image)
                            <img src="{{ $image }}" alt="" loading="lazy">
                        @else
                            <strong>{{ $start?->format('d') ?? '--' }}</strong>
                            <span>{{ $start?->translatedFormat('M') ?? '—' }}</span>
                        @endif
                    </div>
                    <div>
                        <a href="{{ route('events.show', $event) }}" class="ev-title">{{ $event->titre }}</a>
                        <span class="cell-sub">{{ optional($event->category)->libelle ?: 'Sans catégorie' }}</span>
                    </div>
                    <div class="col-date">
                        <span class="cell-value">{{ $start ? $start->translatedFormat('D d M Y') : 'Date à définir' }}</span>
                        @if($hours)<span class="cell-sub">{{ $hours }}</span>@endif
                    </div>
                    <div class="col-place">
                        <span class="cell-value">{{ $event->lieu ?: 'Lieu à confirmer' }}</span>
                        <span class="cell-sub">{{ collect([$event->ville, $event->pays])->filter()->implode(', ') }}</span>
                    </div>
                    <div class="col-count">
                        <span class="cell-value">{{ $event->registrations_count }}{{ $event->capacite_maximale ? ' / ' . $event->capacite_maximale : '' }}</span>
                        <span class="cell-sub">inscrit(s)</span>
                    </div>
                    <span class="ui-pill dot {{ ['publie' => 'success', 'brouillon' => 'warning', 'reporte' => 'warning', 'annule' => 'danger', 'termine' => 'info'][$event->statut] ?? '' }}">{{ $statusLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                    <div class="row-actions dropdown">
                        <button class="ui-icon-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions pour {{ $event->titre }}"><i class="bi bi-three-dots"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('events.edit', $event) }}"><i class="bi bi-pencil"></i>Modifier</a></li>
                            @if($canManageAll)
                                <li><a class="dropdown-item" href="{{ route('events.registrations', $event) }}"><i class="bi bi-people"></i>Inscriptions</a></li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('events.destroy', $event) }}" method="POST" data-confirm-title="Supprimer « {{ $event->titre }} » ?" data-confirm="L’événement, ses inscriptions et ses liens d’invitation seront supprimés définitivement.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash"></i>Supprimer</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </article>
            @endforeach
        </section>

        @if($events->hasPages())
            <div class="pager">{{ $events->links('pagination::bootstrap-5') }}</div>
        @endif
    @else
        <div class="empty-card">
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
    document.querySelectorAll('.event-row .dropdown').forEach((dropdown) => {
        const card = dropdown.closest('.event-row');
        dropdown.addEventListener('show.bs.dropdown', () => card.classList.add('menu-open'));
        dropdown.addEventListener('hidden.bs.dropdown', () => card.classList.remove('menu-open'));
    });
</script>
@endpush
