@extends('layouts.app')

@section('title', 'Événements')

@php
    $visibleEvents = $events->count();
    $totalEvents = $events->total();
    $publishedOnPage = $events->where('statut', 'publie')->count();
    $draftOnPage = $events->where('statut', 'brouillon')->count();
    $upcomingOnPage = $events->filter(fn ($event) => $event->date_debut && $event->date_debut->gte(now()->startOfDay()))->count();
    $activeFilters = collect(['search', 'statut', 'category_id', 'visibilite_id', 'date_from', 'date_to', 'user_id'])
        ->filter(fn ($key) => request()->filled($key))
        ->count();

    $statusLabels = [
        'publie' => 'Publié',
        'brouillon' => 'Brouillon',
        'annule' => 'Annulé',
        'termine' => 'Terminé',
        'reporte' => 'Reporté',
    ];
@endphp

@push('styles')
<style>
    .ops-page {
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
        max-width: 1480px;
        margin: 0 auto;
    }

    .ops-page *,
    .ops-page *::before,
    .ops-page *::after {
        min-width: 0;
    }

    .ops-head {
        align-items: flex-start;
        display: flex;
        gap: 20px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .ops-kicker {
        color: var(--gold);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .ops-title {
        color: var(--ink);
        font-size: clamp(1.7rem, 2.5vw, 2.6rem);
        font-weight: 600;
        line-height: 1.08;
        margin: 6px 0 0;
    }

    .ops-copy {
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 10px 0 0;
        max-width: 720px;
    }

    .ops-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .ops-btn {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        font-size: 0.88rem;
        font-weight: 600;
        gap: 8px;
        min-height: 44px;
        padding: 0 15px;
        text-decoration: none;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    .ops-btn.primary {
        background: var(--ink);
        color: #fff;
    }

    .ops-btn.secondary {
        background: var(--panel);
        border-color: var(--line);
        color: var(--ink);
    }

    .ops-btn.danger {
        background: rgba(164, 81, 74, 0.1);
        border-color: rgba(164, 81, 74, 0.28);
        color: var(--red);
    }

    .ops-btn.danger:hover {
        background: var(--red);
        border-color: var(--red);
        color: #fff;
    }

    .metric-strip {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 18px;
    }

    .metric {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        padding: 17px;
    }

    .metric span {
        color: var(--muted);
        display: block;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .metric strong {
        color: var(--ink);
        display: block;
        font-size: 1.8rem;
        font-weight: 600;
        line-height: 1;
        margin-top: 12px;
    }

    .filter-panel,
    .portfolio-panel,
    .pagination-panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .filter-panel {
        margin-bottom: 18px;
    }

    .panel-head {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding: 16px 18px;
    }

    .panel-head h2 {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .panel-head p {
        color: var(--muted);
        font-size: 0.8rem;
        margin: 3px 0 0;
    }

    .filter-count {
        background: rgba(185, 137, 67, 0.14);
        border: 1px solid rgba(185, 137, 67, 0.28);
        border-radius: 999px;
        color: #8a6128;
        font-size: 0.76rem;
        font-weight: 600;
        padding: 6px 10px;
        white-space: nowrap;
    }

    .filter-body {
        padding: 18px;
    }

    .filter-form .form-label {
        color: var(--muted);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .filter-form .form-control,
    .filter-form .form-select {
        background-color: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--ink);
        min-height: 42px;
    }

    .filter-form .form-control:focus,
    .filter-form .form-select:focus {
        border-color: rgba(185, 137, 67, 0.65);
        box-shadow: 0 0 0 0.2rem rgba(185, 137, 67, 0.12);
    }

    .table-row {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(260px, 1.4fr) minmax(180px, 0.8fr) minmax(150px, 0.7fr) minmax(100px, 0.45fr) minmax(92px, auto) 128px;
        padding: 16px 18px;
    }

    .table-row.table-head {
        background: var(--panel-soft);
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .table-row:last-child {
        border-bottom: 0;
    }

    .event-title {
        color: var(--ink);
        display: block;
        font-weight: 600;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }

    .event-meta,
    .event-cell-muted {
        color: var(--muted);
        font-size: 0.82rem;
        line-height: 1.5;
        margin-top: 5px;
        overflow-wrap: anywhere;
    }

    .meta-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 9px 13px;
    }

    .price {
        color: var(--ink);
        font-weight: 600;
    }

    .price.free {
        color: var(--green);
    }

    .status-pill {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.74rem;
        font-weight: 600;
        justify-content: center;
        min-height: 30px;
        padding: 6px 11px;
        text-align: center;
    }

    .status-pill.publie { background: rgba(46, 123, 101, 0.12); color: var(--green); }
    .status-pill.brouillon { background: rgba(185, 137, 67, 0.15); color: #8a6128; }
    .status-pill.annule { background: rgba(164, 81, 74, 0.13); color: var(--red); }
    .status-pill.termine { background: rgba(49, 95, 131, 0.13); color: var(--blue); }
    .status-pill.reporte { background: rgba(185, 137, 67, 0.15); color: #8a6128; }

    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .btn-action {
        align-items: center;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--ink);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        text-decoration: none;
        transition: border-color 0.2s ease, transform 0.2s ease;
        width: 36px;
    }

    .btn-action:hover {
        border-color: rgba(185, 137, 67, 0.55);
        color: var(--gold);
        transform: translateY(-1px);
    }

    .btn-action-delete {
        color: var(--red);
    }

    .empty-state {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        color: var(--muted);
        padding: 42px 20px;
        text-align: center;
    }

    .pagination-panel {
        display: flex;
        justify-content: center;
        margin-top: 18px;
        padding: 14px;
    }

    .metric,
    .filter-panel,
    .pagination-panel,
    .empty-state {
        border-color: rgba(223, 215, 203, 0.72);
        border-radius: 18px;
        box-shadow: 0 18px 42px rgba(39, 33, 25, 0.055);
    }

    .portfolio-panel {
        background: transparent;
        border: 0;
        box-shadow: none;
        overflow: visible;
    }

    .table-row:not(.table-head) {
        background: var(--panel);
        border: 1px solid rgba(223, 215, 203, 0.74);
        border-radius: 18px;
        box-shadow: 0 14px 34px rgba(39, 33, 25, 0.045);
        margin-bottom: 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .table-row:not(.table-head):hover {
        border-color: rgba(185, 137, 67, 0.34);
        box-shadow: 0 18px 42px rgba(39, 33, 25, 0.07);
        transform: translateY(-1px);
    }

    .table-row.table-head {
        background: transparent;
        border: 0;
        padding-bottom: 10px;
    }

    .panel-head {
        border-bottom-color: rgba(223, 215, 203, 0.62);
        padding: 20px 22px 16px;
    }

    .btn-action,
    .filter-form .form-control,
    .filter-form .form-select {
        border-radius: 12px;
    }

    @media (max-width: 1280px) {
        .table-row {
            grid-template-columns: minmax(260px, 1.4fr) minmax(170px, 0.8fr) minmax(140px, 0.7fr) minmax(92px, auto) 116px;
        }

        .table-row > :nth-child(4) {
            display: none;
        }
    }

    @media (max-width: 992px) {
        .ops-head {
            flex-direction: column;
        }

        .ops-actions {
            justify-content: flex-start;
        }

        .metric-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .table-row,
        .table-row.table-head {
            grid-template-columns: 1fr;
        }

        .table-row.table-head {
            display: none;
        }

        .action-buttons {
            justify-content: flex-start;
        }
    }

    @media (max-width: 576px) {
        .metric-strip {
            grid-template-columns: 1fr;
        }

        .ops-btn {
            justify-content: center;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="ops-page">
    <div class="ops-head">
        <div>
            <div class="ops-kicker">Portefeuille événementiel</div>
            <h1 class="ops-title">Événements</h1>
            <p class="ops-copy">
                Supervisez la production, les publications, les lieux et les statuts avec une lecture claire du portefeuille.
            </p>
        </div>
        <div class="ops-actions">
            <a href="{{ route('events.create') }}" class="ops-btn primary">
                <i class="bi bi-plus-lg"></i>
                Nouvel événement
            </a>
            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                <form action="{{ route('events.destroy-all') }}" method="POST" onsubmit="return confirm('Cette action va supprimer définitivement tous les événements, inscriptions, OTP, liens d’accès et fichiers liés. Continuer ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="ops-btn danger" {{ $totalEvents === 0 ? 'disabled' : '' }}>
                        <i class="bi bi-trash3"></i>
                        Vider les événements
                    </button>
                </form>
            @endif
            <a href="{{ route('events.index') }}" class="ops-btn secondary">
                <i class="bi bi-arrow-clockwise"></i>
                Actualiser
            </a>
        </div>
    </div>

    <section class="metric-strip">
        <div class="metric">
            <span>Total</span>
            <strong>{{ $totalEvents }}</strong>
        </div>
        <div class="metric">
            <span>Affichés</span>
            <strong>{{ $visibleEvents }}</strong>
        </div>
        <div class="metric">
            <span>Publiés</span>
            <strong>{{ $publishedOnPage }}</strong>
        </div>
        <div class="metric">
            <span>À venir</span>
            <strong>{{ $upcomingOnPage }}</strong>
        </div>
    </section>

    <section class="filter-panel">
        <div class="panel-head">
            <div>
                <h2>Filtres</h2>
                <p>Affinez la liste par statut, catégorie, visibilité, dates ou créateur.</p>
            </div>
            <span class="filter-count">{{ $activeFilters }} filtre(s) actif(s)</span>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('events.index') }}" class="filter-form">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Titre de l'événement">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="statut" class="form-label">Statut</label>
                        <select class="form-select" id="statut" name="statut">
                            <option value="">Tous</option>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}" {{ request('statut') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="category_id" class="form-label">Catégorie</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Toutes</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="visibilite_id" class="form-label">Visibilité</label>
                        <select class="form-select" id="visibilite_id" name="visibilite_id">
                            <option value="">Toutes</option>
                            @foreach($visibilites as $visibilite)
                                <option value="{{ $visibilite->id }}" {{ request('visibilite_id') == $visibilite->id ? 'selected' : '' }}>{{ $visibilite->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="date_from" class="form-label">Période</label>
                        <div class="d-flex gap-2">
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                        <div class="col-lg-3 col-md-6">
                            <label for="user_id" class="form-label">Créateur</label>
                            <select class="form-select" id="user_id" name="user_id">
                                <option value="">Tous</option>
                                @foreach($users as $creator)
                                    <option value="{{ $creator->id }}" {{ request('user_id') == $creator->id ? 'selected' : '' }}>{{ $creator->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-lg-4 col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="ops-btn primary border-0">
                            <i class="bi bi-search"></i>
                            Filtrer
                        </button>
                        <a href="{{ route('events.index') }}" class="ops-btn secondary">
                            <i class="bi bi-x-lg"></i>
                            Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @if($events->count() > 0)
        <section class="portfolio-panel">
            <div class="table-row table-head">
                <div>Événement</div>
                <div>Dates</div>
                <div>Lieu</div>
                <div>Tarif</div>
                <div>Statut</div>
                <div class="text-end">Actions</div>
            </div>

            @foreach($events as $event)
                <div class="table-row">
                    <div>
                        <span class="event-title">{{ $event->titre }}</span>
                        <div class="event-meta meta-inline">
                            <span><i class="bi bi-tag me-1"></i>{{ optional($event->category)->libelle ?: 'Sans catégorie' }}</span>
                            <span><i class="bi bi-person me-1"></i>{{ optional($event->user)->name ?: 'Créateur inconnu' }}</span>
                            <span><i class="bi bi-eye me-1"></i>{{ $event->vues ?? 0 }} vues</span>
                        </div>
                    </div>
                    <div>
                        <div class="event-cell-muted">
                            <strong style="color: var(--ink);">Début</strong><br>
                            {{ optional($event->date_debut)->format('d/m/Y') ?: '--' }} à {{ $event->heure_debut ?: '--' }}
                        </div>
                        <div class="event-cell-muted">
                            <strong style="color: var(--ink);">Fin</strong><br>
                            {{ optional($event->date_fin)->format('d/m/Y') ?: '--' }} à {{ $event->heure_fin ?: '--' }}
                        </div>
                    </div>
                    <div>
                        <span class="event-title" style="font-size: .92rem;">{{ $event->lieu ?: 'Lieu à confirmer' }}</span>
                        <span class="event-cell-muted">{{ $event->ville ?: $event->pays ?: 'Adresse non renseignée' }}</span>
                    </div>
                    <div>
                        <span class="price {{ $event->isFree() ? 'free' : '' }}">
                            @if($event->isFree())
                                Gratuit
                            @else
                                {{ number_format((float) $event->prix, 2, ',', ' ') }} {{ optional($event->devise)->libelle }}
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="status-pill {{ $event->statut }}">{{ $statusLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                    </div>
                    <div class="action-buttons">
                        <a href="{{ route('events.show', $event) }}" class="btn-action" title="Voir"><i class="bi bi-eye"></i></a>
                        <a href="{{ route('events.edit', $event) }}" class="btn-action" title="Modifier"><i class="bi bi-pencil"></i></a>
                        <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet événement ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-action-delete" title="Supprimer"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            @endforeach
        </section>

        @if($events->hasPages())
            <div class="pagination-panel">
                {{ $events->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="bi bi-calendar-x d-block mb-3" style="font-size: 2.6rem;"></i>
            Aucun événement ne correspond à votre recherche.
            <div class="mt-3">
                <a href="{{ route('events.create') }}" class="ops-btn primary">
                    <i class="bi bi-plus-lg"></i>
                    Créer un événement
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
