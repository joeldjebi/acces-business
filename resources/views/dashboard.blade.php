@extends('layouts.app')

@section('title', 'Tableau de bord')

@php
    $user = auth()->user();
    $roleLabel = ucfirst(str_replace('_', ' ', $user->role));
    $organizationId = $user->organization_id;

    $eventsCount = \App\Models\Event::forOrganization($organizationId)->count();
    $publishedEvents = \App\Models\Event::forOrganization($organizationId)->where('statut', 'publie')->count();
    $draftEvents = \App\Models\Event::forOrganization($organizationId)->where('statut', 'brouillon')->count();
    $upcomingEvents = \App\Models\Event::forOrganization($organizationId)->whereDate('date_debut', '>=', now()->toDateString())->count();

    $registrationsCount = \App\Models\EventRegistration::forOrganization($organizationId)->count();
    $confirmedRegistrations = \App\Models\EventRegistration::forOrganization($organizationId)->whereIn('statut_reponse', ['present', 'peut_etre'])->count();
    $pendingRegistrations = \App\Models\EventRegistration::forOrganization($organizationId)->where('statut_reponse', 'en_attente')->count();
    $absentRegistrations = \App\Models\EventRegistration::forOrganization($organizationId)->where('statut_reponse', 'absent')->count();
    $cardsSent = \App\Models\EventRegistration::forOrganization($organizationId)->where('carte_envoyee', true)->count();

    $usersCount = \App\Models\User::where('organization_id', $organizationId)->count();
    $operatorsCount = \App\Models\User::where('organization_id', $organizationId)->whereIn('role', ['super_admin', 'admin', 'manager'])->count();

    $confirmationRate = $registrationsCount > 0 ? round(($confirmedRegistrations / $registrationsCount) * 100) : 0;
    $cardDeliveryRate = $registrationsCount > 0 ? round(($cardsSent / $registrationsCount) * 100) : 0;
    $publicationRate = $eventsCount > 0 ? round(($publishedEvents / $eventsCount) * 100) : 0;

    $recentEvents = \App\Models\Event::forOrganization($organizationId)
        ->with(['category', 'visibilite'])
        ->orderByRaw('date_debut is null')
        ->orderBy('date_debut')
        ->take(6)
        ->get();

    $recentRegistrations = \App\Models\EventRegistration::forOrganization($organizationId)
        ->with('event')
        ->latest('date_inscription')
        ->take(6)
        ->get();

    $statutLabels = [
        'publie' => 'Publié',
        'brouillon' => 'Brouillon',
        'archive' => 'Archivé',
        'annule' => 'Annulé',
    ];

    $responseLabels = [
        'present' => 'Présent',
        'peut_etre' => 'Peut-être',
        'absent' => 'Absent',
        'en_attente' => 'En attente',
    ];

    $todayLabel = now()->translatedFormat('d M Y');
@endphp

@push('styles')
<style>
    body {
        background: #f5f3ee;
    }

    .dash {
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
        overflow-x: hidden;
    }

    .dash *,
    .dash *::before,
    .dash *::after {
        min-width: 0;
    }

    .dash-topbar {
        align-items: center;
        display: flex;
        gap: 20px;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .dash-kicker {
        color: var(--gold);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .dash-title {
        color: var(--ink);
        font-size: clamp(1.8rem, 2.7vw, 3rem);
        font-weight: 600;
        letter-spacing: 0;
        line-height: 1.08;
        margin: 6px 0 0;
    }

    .dash-subtitle {
        color: var(--muted);
        font-size: 0.96rem;
        line-height: 1.6;
        margin: 10px 0 0;
        max-width: 760px;
    }

    .topbar-actions {
        align-items: center;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: flex-end;
    }

    .operator-chip {
        align-items: center;
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 999px;
        display: inline-flex;
        gap: 10px;
        min-height: 44px;
        padding: 6px 12px 6px 6px;
        box-shadow: 0 10px 24px rgba(39, 33, 25, 0.05);
        max-width: 230px;
    }

    .operator-avatar {
        align-items: center;
        background: var(--ink);
        border-radius: 999px;
        color: #d8b978;
        display: flex;
        font-size: 0.86rem;
        font-weight: 600;
        height: 32px;
        justify-content: center;
        width: 32px;
    }

    .operator-chip strong,
    .operator-chip span {
        display: block;
        line-height: 1.1;
    }

    .operator-chip strong {
        color: var(--ink);
        font-size: 0.84rem;
        font-weight: 600;
        max-width: 150px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .operator-chip span {
        color: var(--muted);
        font-size: 0.72rem;
        margin-top: 3px;
    }

    .dash-button {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        font-size: 0.88rem;
        font-weight: 600;
        gap: 8px;
        min-height: 44px;
        padding: 0 15px;
        text-decoration: none;
        transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
        white-space: nowrap;
    }

    .dash-button:hover {
        transform: translateY(-1px);
    }

    .dash-button.primary {
        background: var(--ink);
        color: #fff;
    }

    .dash-button.ghost {
        background: var(--panel);
        border: 1px solid var(--line);
        color: var(--ink);
    }

    .kpi-strip {
        display: grid;
        gap: 14px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 18px;
    }

    .kpi-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        padding: 18px;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        background: var(--accent, var(--gold));
        content: '';
        height: 3px;
        left: 18px;
        position: absolute;
        right: 18px;
        top: 0;
    }

    .kpi-head {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 14px;
        min-height: 40px;
    }

    .kpi-label {
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .kpi-icon {
        align-items: center;
        background: var(--panel-soft);
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--accent, var(--gold));
        display: flex;
        flex: 0 0 38px;
        height: 38px;
        justify-content: center;
        width: 38px;
    }

    .kpi-value {
        color: var(--ink);
        font-size: 2rem;
        font-weight: 600;
        line-height: 1;
        margin-top: 18px;
    }

    .kpi-note {
        color: var(--muted);
        font-size: 0.82rem;
        line-height: 1.45;
        margin-top: 8px;
    }

    .work-grid {
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(0, 1.8fr) minmax(340px, 0.9fr);
        align-items: start;
    }

    .work-grid > .panel:last-child {
        grid-column: 1 / -1;
    }

    .panel {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .panel-head {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: flex;
        gap: 16px;
        justify-content: space-between;
        padding: 18px 20px;
    }

    .panel-head > div {
        min-width: 0;
    }

    .panel-title {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .panel-caption {
        color: var(--muted);
        font-size: 0.8rem;
        margin: 4px 0 0;
    }

    .panel-link {
        color: var(--gold);
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .portfolio-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        border-bottom: 1px solid var(--line);
    }

    .summary-cell {
        border-right: 1px solid var(--line);
        padding: 16px 20px;
    }

    .summary-cell:last-child {
        border-right: 0;
    }

    .summary-cell span {
        color: var(--muted);
        display: block;
        font-size: 0.75rem;
        font-weight: 500;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .summary-cell strong {
        color: var(--ink);
        display: block;
        font-size: 1.55rem;
        font-weight: 600;
        margin-top: 7px;
    }

    .event-table {
        width: 100%;
    }

    .event-table-row {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 16px;
        grid-template-columns: 86px minmax(0, 1fr) minmax(124px, 0.34fr) minmax(92px, auto);
        padding: 16px 20px;
    }

    .event-table-row:last-child {
        border-bottom: 0;
    }

    .date-card {
        background: var(--panel-soft);
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        width: 86px;
        justify-self: start;
    }

    .date-card strong {
        color: var(--ink);
        display: block;
        font-size: 1.3rem;
        font-weight: 600;
        line-height: 1;
    }

    .date-card span {
        color: var(--muted);
        display: block;
        font-size: 0.7rem;
        font-weight: 600;
        margin-top: 6px;
        text-transform: uppercase;
    }

    .item-title {
        color: var(--ink);
        display: block;
        font-weight: 600;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .item-meta {
        color: var(--muted);
        display: block;
        font-size: 0.82rem;
        line-height: 1.45;
        margin-top: 5px;
        overflow-wrap: anywhere;
    }

    .muted-label {
        color: var(--muted);
        display: block;
        font-size: 0.72rem;
        font-weight: 500;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    .capacity-value {
        color: var(--ink);
        display: block;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 5px;
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
        white-space: normal;
    }

    .event-table-row > .status-pill,
    .feed-row > .status-pill {
        justify-self: end;
    }

    .status-pill.publie,
    .status-pill.present {
        background: rgba(46, 123, 101, 0.12);
        color: var(--green);
    }

    .status-pill.brouillon,
    .status-pill.en_attente {
        background: rgba(185, 137, 67, 0.15);
        color: #8a6128;
    }

    .status-pill.absent,
    .status-pill.annule {
        background: rgba(164, 81, 74, 0.13);
        color: var(--red);
    }

    .status-pill.peut_etre,
    .status-pill.archive {
        background: rgba(49, 95, 131, 0.13);
        color: var(--blue);
    }

    .side-stack {
        display: grid;
        gap: 18px;
    }

    .quality-panel {
        padding: 20px;
    }

    .quality-main {
        align-items: center;
        display: grid;
        gap: 18px;
        grid-template-columns: 112px minmax(0, 1fr);
    }

    .ring {
        align-items: center;
        background:
            radial-gradient(circle at center, var(--panel) 0 55%, transparent 56%),
            conic-gradient(var(--green) calc(var(--value) * 1%), #e9e1d5 0);
        border-radius: 999px;
        display: flex;
        height: 112px;
        justify-content: center;
        width: 112px;
    }

    .ring strong {
        color: var(--ink);
        font-size: 1.35rem;
        font-weight: 600;
    }

    .quality-copy strong {
        color: var(--ink);
        display: block;
        font-size: 1rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .quality-copy span {
        color: var(--muted);
        display: block;
        font-size: 0.84rem;
        line-height: 1.55;
        margin-top: 7px;
    }

    .progress-stack {
        display: grid;
        gap: 14px;
        margin-top: 20px;
    }

    .progress-meta {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 7px;
    }

    .progress-meta strong,
    .progress-meta span {
        font-size: 0.82rem;
        font-weight: 600;
    }

    .progress-meta strong {
        color: var(--ink);
    }

    .progress-meta span {
        color: var(--muted);
    }

    .premium-progress {
        background: #e9e1d5;
        border-radius: 999px;
        height: 8px;
        overflow: hidden;
    }

    .premium-progress span {
        background: var(--accent, var(--gold));
        display: block;
        height: 100%;
    }

    .action-list {
        display: grid;
        gap: 12px;
        padding: 16px;
    }

    .action-panel {
        border-color: rgba(185, 137, 67, 0.46);
        box-shadow: 0 20px 52px rgba(126, 89, 36, 0.13);
    }

    .action-panel .panel-head {
        background: #171713;
        border-bottom-color: rgba(185, 137, 67, 0.36);
    }

    .action-panel .panel-title {
        color: #fffaf1;
    }

    .action-panel .panel-caption {
        color: rgba(255, 250, 241, 0.68);
    }

    .action-row {
        align-items: center;
        background: #fffefa;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--ink);
        display: grid;
        gap: 12px;
        grid-template-columns: 40px minmax(0, 1fr) 18px;
        padding: 13px;
        position: relative;
        text-decoration: none;
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
    }

    .action-row::before {
        background: var(--gold);
        border-radius: 8px 0 0 8px;
        content: '';
        inset: 10px auto 10px 0;
        position: absolute;
        width: 3px;
    }

    .action-row:hover {
        background: #fff;
        border-color: rgba(185, 137, 67, 0.45);
        color: var(--ink);
        transform: translateY(-1px);
    }

    .action-icon {
        align-items: center;
        background: rgba(185, 137, 67, 0.12);
        border: 1px solid rgba(185, 137, 67, 0.28);
        border-radius: 8px;
        color: #8a6128;
        display: flex;
        height: 40px;
        justify-content: center;
        width: 40px;
    }

    .action-row strong,
    .action-row span {
        display: block;
        overflow-wrap: anywhere;
    }

    .action-row strong {
        font-size: 0.9rem;
        font-weight: 600;
    }

    .action-row span {
        color: var(--muted);
        font-size: 0.78rem;
        line-height: 1.35;
        margin-top: 3px;
    }

    .feed-list {
        display: grid;
        gap: 0;
    }

    .feed-row {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 14px;
        grid-template-columns: 42px minmax(0, 1fr) minmax(104px, auto);
        padding: 15px 20px;
    }

    .feed-row:last-child {
        border-bottom: 0;
    }

    .feed-avatar {
        align-items: center;
        background: var(--ink);
        border-radius: 8px;
        color: #d8b978;
        display: flex;
        font-size: 0.82rem;
        font-weight: 600;
        height: 42px;
        justify-content: center;
        width: 42px;
    }

    .empty-state {
        color: var(--muted);
        font-size: 0.9rem;
        padding: 28px 20px;
        text-align: center;
    }

    .kpi-card,
    .panel {
        border-color: rgba(223, 215, 203, 0.72);
        border-radius: 18px;
        box-shadow: 0 18px 42px rgba(39, 33, 25, 0.055);
    }

    .kpi-card::before {
        left: 22px;
        right: auto;
        width: 48px;
        border-radius: 999px;
    }

    .panel-head {
        border-bottom-color: rgba(223, 215, 203, 0.62);
        padding: 20px 22px 16px;
    }

    .portfolio-summary {
        border-bottom-color: rgba(223, 215, 203, 0.62);
    }

    .summary-cell {
        border-right-color: rgba(223, 215, 203, 0.62);
    }

    .event-table-row,
    .feed-row {
        border-bottom-color: rgba(223, 215, 203, 0.56);
    }

    .action-panel {
        border-radius: 20px;
    }

    .action-panel .panel-head {
        border-radius: 18px 18px 0 0;
    }

    .action-row {
        border-radius: 14px;
    }

    .action-icon,
    .date-card,
    .feed-avatar,
    .kpi-icon {
        border-radius: 12px;
    }

    @media (max-width: 1280px) {
        .work-grid {
            grid-template-columns: 1fr;
        }

        .side-stack {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .work-grid > .panel:last-child {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 1024px) {
        .kpi-strip {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dash-topbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .topbar-actions {
            justify-content: flex-start;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            padding: 18px;
        }

        .kpi-strip,
        .portfolio-summary,
        .side-stack {
            grid-template-columns: 1fr;
        }

        .summary-cell {
            border-right: 0;
            border-bottom: 1px solid var(--line);
        }

        .summary-cell:last-child {
            border-bottom: 0;
        }

        .event-table-row,
        .feed-row,
        .quality-main {
            grid-template-columns: 1fr;
        }

        .event-table-row > .status-pill,
        .feed-row > .status-pill {
            justify-self: start;
        }

        .date-card,
        .feed-avatar {
            display: none;
        }

        .panel-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .dash-button,
        .operator-chip {
            width: 100%;
        }

        .dash-button {
            justify-content: center;
        }

        .operator-chip {
            border-radius: 8px;
        }
    }

    @media (max-width: 430px) {
        .main-content {
            padding: 12px;
        }

        .kpi-card,
        .panel-head,
        .event-table-row,
        .feed-row,
        .quality-panel,
        .action-list,
        .summary-cell {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="dash">
    <div class="dash-topbar">
        <div>
            <div class="dash-kicker">Console événementielle · {{ $todayLabel }}</div>
            <h1 class="dash-title">Pilotage opérationnel</h1>
            <p class="dash-subtitle">
                Vue consolidée des événements, invitations, réponses et permissions pour décider vite, sans interface décorative inutile.
            </p>
        </div>

        <div class="topbar-actions">
            <div class="operator-chip">
                <span class="operator-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                <div>
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $roleLabel }}</span>
                </div>
            </div>
            <a href="{{ route('events.create') }}" class="dash-button primary">
                <i class="bi bi-plus-lg"></i>
                Nouvel événement
            </a>
            <a href="{{ route('events.index') }}" class="dash-button ghost">
                <i class="bi bi-calendar3"></i>
                Planning
            </a>
        </div>
    </div>

    <section class="kpi-strip">
        <article class="kpi-card" style="--accent: var(--gold);">
            <div class="kpi-head">
                <span class="kpi-label">Portefeuille</span>
                <span class="kpi-icon"><i class="bi bi-calendar2-week"></i></span>
            </div>
            <div class="kpi-value">{{ $eventsCount }}</div>
            <div class="kpi-note">{{ $publishedEvents }} publié(s), {{ $draftEvents }} brouillon(s)</div>
        </article>

        <article class="kpi-card" style="--accent: var(--green);">
            <div class="kpi-head">
                <span class="kpi-label">Confirmations</span>
                <span class="kpi-icon"><i class="bi bi-person-check"></i></span>
            </div>
            <div class="kpi-value">{{ $confirmationRate }}%</div>
            <div class="kpi-note">{{ $confirmedRegistrations }} retour(s) favorable(s)</div>
        </article>

        <article class="kpi-card" style="--accent: var(--blue);">
            <div class="kpi-head">
                <span class="kpi-label">Invitations</span>
                <span class="kpi-icon"><i class="bi bi-send-check"></i></span>
            </div>
            <div class="kpi-value">{{ $cardDeliveryRate }}%</div>
            <div class="kpi-note">{{ $cardsSent }} carte(s) envoyée(s)</div>
        </article>

        <article class="kpi-card" style="--accent: var(--red);">
            <div class="kpi-head">
                <span class="kpi-label">Équipe</span>
                <span class="kpi-icon"><i class="bi bi-shield-lock"></i></span>
            </div>
            <div class="kpi-value">{{ $operatorsCount }}</div>
            <div class="kpi-note">{{ $usersCount }} utilisateur(s) dans l'espace</div>
        </article>
    </section>

    <div class="work-grid">
        <main class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Portefeuille événements</h2>
                    <p class="panel-caption">État de production, agenda et capacité des prochains dossiers.</p>
                </div>
                <a href="{{ route('events.index') }}" class="panel-link">Voir tous les événements</a>
            </div>

            <div class="portfolio-summary">
                <div class="summary-cell">
                    <span>À venir</span>
                    <strong>{{ $upcomingEvents }}</strong>
                </div>
                <div class="summary-cell">
                    <span>Publication</span>
                    <strong>{{ $publicationRate }}%</strong>
                </div>
                <div class="summary-cell">
                    <span>Réponses</span>
                    <strong>{{ $registrationsCount }}</strong>
                </div>
            </div>

            @if($recentEvents->isNotEmpty())
                <div class="event-table">
                    @foreach($recentEvents as $event)
                        <div class="event-table-row">
                            <div class="date-card">
                                <strong>{{ optional($event->date_debut)->format('d') ?: '--' }}</strong>
                                <span>{{ optional($event->date_debut)->translatedFormat('M') ?: 'date' }}</span>
                            </div>

                            <div>
                                <span class="item-title">{{ $event->titre }}</span>
                                <span class="item-meta">
                                    {{ $event->lieu ?: 'Lieu à confirmer' }}
                                    @if($event->ville)
                                        · {{ $event->ville }}
                                    @endif
                                    @if($event->category)
                                        · {{ $event->category->libelle }}
                                    @endif
                                </span>
                            </div>

                            <div>
                                <span class="muted-label">Capacité</span>
                                <span class="capacity-value">{{ $event->capacite_maximale ? number_format($event->capacite_maximale, 0, ',', ' ') . ' places' : 'Non définie' }}</span>
                            </div>

                            <span class="status-pill {{ $event->statut }}">{{ $statutLabels[$event->statut] ?? ucfirst($event->statut) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">Aucun événement disponible. Créez le premier dossier pour alimenter le portefeuille.</div>
            @endif
        </main>

        <aside class="side-stack">
            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h2 class="panel-title">Qualité des accès</h2>
                        <p class="panel-caption">Diffusion et réponses invités.</p>
                    </div>
                </div>

                <div class="quality-panel">
                    <div class="quality-main">
                        <div class="ring" style="--value: {{ $confirmationRate }};">
                            <strong>{{ $confirmationRate }}%</strong>
                        </div>
                        <div class="quality-copy">
                            <strong>{{ $confirmedRegistrations }} confirmation(s) sur {{ $registrationsCount }} réponse(s)</strong>
                            <span>{{ $pendingRegistrations }} en attente · {{ $absentRegistrations }} refus · {{ $cardsSent }} cartes envoyées</span>
                        </div>
                    </div>

                    <div class="progress-stack">
                        <div style="--accent: var(--green);">
                            <div class="progress-meta">
                                <strong>Confirmations</strong>
                                <span>{{ $confirmationRate }}%</span>
                            </div>
                            <div class="premium-progress"><span style="width: {{ $confirmationRate }}%;"></span></div>
                        </div>
                        <div style="--accent: var(--blue);">
                            <div class="progress-meta">
                                <strong>Cartes diffusées</strong>
                                <span>{{ $cardDeliveryRate }}%</span>
                            </div>
                            <div class="premium-progress"><span style="width: {{ $cardDeliveryRate }}%;"></span></div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="panel action-panel">
                <div class="panel-head">
                    <div>
                        <h2 class="panel-title">Actions prioritaires</h2>
                        <p class="panel-caption">Raccourcis utiles, sans surcharge.</p>
                    </div>
                </div>

                <div class="action-list">
                    <a href="{{ route('events.create') }}" class="action-row">
                        <span class="action-icon"><i class="bi bi-calendar-plus"></i></span>
                        <span>
                            <strong>Créer un événement</strong>
                            <span>Ouvrir un nouveau dossier de production.</span>
                        </span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    <a href="{{ route('events.index') }}" class="action-row">
                        <span class="action-icon"><i class="bi bi-kanban"></i></span>
                        <span>
                            <strong>Superviser le planning</strong>
                            <span>Revoir les événements actifs et à venir.</span>
                        </span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                    @if($user->isSuperAdmin())
                        <a href="{{ route('users.index') }}" class="action-row">
                            <span class="action-icon"><i class="bi bi-people"></i></span>
                            <span>
                                <strong>Gérer les accès</strong>
                                <span>Contrôler les rôles et les permissions.</span>
                            </span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    @endif
                </div>
            </section>
        </aside>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Flux des inscriptions</h2>
                    <p class="panel-caption">Derniers retours participants et statut associé.</p>
                </div>
            </div>

            @if($recentRegistrations->isNotEmpty())
                <div class="feed-list">
                    @foreach($recentRegistrations as $registration)
                        <div class="feed-row">
                            <div class="feed-avatar">{{ strtoupper(substr($registration->prenom ?: $registration->email, 0, 1)) }}</div>
                            <div>
                                <span class="item-title">{{ $registration->nom_complet ?: $registration->email }}</span>
                                <span class="item-meta">
                                    {{ optional($registration->event)->titre ?: 'Événement supprimé' }}
                                    · {{ optional($registration->date_inscription)->diffForHumans() }}
                                </span>
                            </div>
                            <span class="status-pill {{ $registration->statut_reponse }}">
                                {{ $responseLabels[$registration->statut_reponse] ?? ucfirst($registration->statut_reponse) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">Les inscriptions apparaîtront ici dès les premiers retours invités.</div>
            @endif
        </section>
    </div>
</div>
@endsection
