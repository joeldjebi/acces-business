@extends('layouts.app')

@section('title', 'Check-in · ' . $event->titre)

@php
    $eligible = (int) $stats['eligible'];
    $checked = (int) $stats['checked_in'];
    $progress = $eligible ? min(100, round($checked / $eligible * 100)) : 0;
@endphp

@push('styles')
<style>
    .checkin-page { margin: 0 auto; max-width: 1400px; }
    .checkin-page *, .checkin-page *::before, .checkin-page *::after { min-width: 0; }

    .scanner { align-items: center; background: var(--app-soft); border-radius: 22px; color: var(--app-ink); display: grid; gap: 28px; grid-template-columns: minmax(0, 1fr) auto; margin-bottom: 20px; overflow: hidden; padding: 26px 28px; position: relative; }
    .scanner h2 { color: var(--app-ink); font-size: 1.4rem; font-weight: 700; letter-spacing: -.03em; margin: 0; }
    .scanner p { color: var(--app-muted); font-size: .9rem; margin: 4px 0 18px; }
    .scan-form { display: flex; gap: 10px; }
    .scan-input { flex: 1; position: relative; }
    .scan-input i { color: var(--app-gold); font-size: 1.2rem; left: 16px; position: absolute; top: 50%; transform: translateY(-50%); }
    .scan-input input { background: #fff; border: 1px solid var(--app-field-line); border-radius: 14px; color: var(--app-ink); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .95rem; height: 52px; padding: 0 16px 0 48px; transition: border-color .15s, box-shadow .15s; width: 100%; }
    .scan-input input::placeholder { color: #86868b; font-family: var(--bs-body-font-family); }
    .scan-input input:focus { border-color: var(--app-gold); box-shadow: 0 0 0 4px rgba(0, 113, 227, .18); outline: 0; }
    .scan-form .ui-btn { height: 52px; padding: 0 24px; }
    .scan-live { align-items: center; color: var(--app-green); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 8px; margin-bottom: 10px; }
    .scan-live::before { animation: live 2s infinite; background: #34c759; border-radius: 50%; box-shadow: 0 0 0 0 rgba(52, 199, 89, .5); content: ""; height: 8px; width: 8px; }
    @keyframes live { 70% { box-shadow: 0 0 0 8px transparent; } 100% { box-shadow: 0 0 0 0 transparent; } }
    /* Anneau façon app Activité */
    .gauge { --size: 132px; align-items: center; background: radial-gradient(closest-side, var(--app-soft) 79%, transparent 80% 100%), conic-gradient(#34c759 calc(var(--value) * 1%), rgba(52, 199, 89, .18) 0); border-radius: 50%; color: var(--app-ink); display: flex; flex-direction: column; height: var(--size); justify-content: center; width: var(--size); }
    .gauge strong { font-size: 2rem; font-variant-numeric: tabular-nums; font-weight: 600; letter-spacing: -.04em; line-height: 1; }
    .gauge span { color: var(--app-muted); font-size: .74rem; margin-top: 4px; }

    .checkin-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.7fr) minmax(300px, .8fr); }
    .status-tabs { background: var(--app-fill); border-radius: 9px; display: inline-flex; gap: 2px; padding: 2px; }
    .status-tabs a { border-radius: 7px; color: var(--app-ink); font-size: .8rem; font-weight: 500; padding: 5px 12px; text-decoration: none; white-space: nowrap; }
    .status-tabs a.is-active { background: #fff; box-shadow: 0 3px 8px rgba(0, 0, 0, .12), 0 3px 1px rgba(0, 0, 0, .04); color: var(--app-ink); }
    tr.is-checked { background: rgba(52, 199, 89, .035); }

    .feed { display: grid; padding: 4px 22px 18px; position: relative; }
    .feed-item { display: grid; gap: 12px; grid-template-columns: 12px minmax(0, 1fr); padding: 10px 0; position: relative; }
    .feed-item::before { background: var(--app-line); bottom: -10px; content: ""; left: 5px; position: absolute; top: 22px; width: 2px; }
    .feed-item:last-child::before { display: none; }
    .feed-dot { background: var(--app-green); border-radius: 50%; box-shadow: 0 0 0 4px rgba(52, 199, 89, .14); height: 12px; margin-top: 4px; width: 12px; }
    .feed-item strong { color: var(--app-ink); display: block; font-size: .86rem; font-weight: 600; }
    .feed-item span { color: var(--app-muted); display: block; font-size: .78rem; }
    .feed-time { color: var(--app-ink) !important; font-variant-numeric: tabular-nums; font-weight: 600; }

    @media (max-width: 1099.98px) { .checkin-grid { grid-template-columns: 1fr; } }
    @media (max-width: 767.98px) {
        .scanner { grid-template-columns: 1fr; padding: 22px; }
        .gauge { --size: 104px; justify-self: start; order: -1; }
        .scan-form { flex-direction: column; }
        .ci-table thead { display: none; }
        .ci-table tr { border-bottom: 1px solid var(--app-line); display: grid; gap: 8px; grid-template-columns: 1fr auto; padding: 14px 18px; }
        .ci-table td { border: 0 !important; padding: 0 !important; }
        .ci-table td.col-hide { display: none; }
        .ci-table td.col-status { grid-column: 1; }
        .ci-table td.col-actions { grid-column: 2; grid-row: 1 / span 2; align-self: center; }
    }
    @media (prefers-reduced-motion: reduce) { .scan-live::before { animation: none; } }
</style>
@endpush

@section('content')
<div class="checkin-page">
    <x-app.event-header :event="$event" active="check-in">
        <x-slot:actions>
            <a href="{{ route('events.check-in', $event) }}" class="ui-btn ui-btn-ghost"><i class="bi bi-arrow-clockwise"></i>Actualiser</a>
        </x-slot:actions>
    </x-app.event-header>


    <section class="scanner">
        <div>
            <span class="scan-live">Accueil en direct</span>
            <h2>Scanner un QR code</h2>
            <p>Placez le curseur dans le champ puis scannez la carte, ou saisissez le code inscrit sous le QR code.</p>
            <form method="POST" action="{{ route('events.check-in.token', $event) }}" class="scan-form">
                @csrf
                <label class="scan-input mb-0">
                    <i class="bi bi-qr-code-scan"></i>
                    <input type="text" name="token" placeholder="Scanner ou saisir le code…" autocomplete="off" autofocus required aria-label="Code de l’invitation">
                </label>
                <button type="submit" class="ui-btn ui-btn-gold"><i class="bi bi-check2-circle"></i>Valider l’entrée</button>
            </form>
        </div>
        <div class="gauge" style="--value: {{ $progress }}"><strong>{{ $progress }}%</strong><span>{{ $checked }} / {{ $eligible }} arrivés</span></div>
    </section>

    <section class="ui-stats">
        <article class="ui-stat" style="--accent: var(--app-ink)"><div class="ui-stat-top">Attendus<i class="bi bi-people"></i></div><strong>{{ number_format($eligible, 0, ',', ' ') }}</strong><small>Participants éligibles</small></article>
        <article class="ui-stat" style="--accent: var(--app-green)"><div class="ui-stat-top">Arrivés<i class="bi bi-door-open"></i></div><strong>{{ number_format($checked, 0, ',', ' ') }}</strong><div class="ui-meter"><span style="width: {{ $progress }}%"></span></div></article>
        <article class="ui-stat" style="--accent: var(--app-gold)"><div class="ui-stat-top">À l’entrée<i class="bi bi-hourglass-split"></i></div><strong>{{ number_format($stats['waiting'], 0, ',', ' ') }}</strong><small>Pas encore arrivés</small></article>
        <article class="ui-stat" style="--accent: var(--app-blue)"><div class="ui-stat-top">Représentants<i class="bi bi-person-badge"></i></div><strong>{{ number_format($stats['represented'], 0, ',', ' ') }}</strong><small>Viennent à la place d’un invité</small></article>
    </section>

    <div class="checkin-grid">
        <section class="ui-card" style="overflow: hidden">
            <div class="ui-card-head">
                <nav class="status-tabs" aria-label="Filtrer par statut">
                    @foreach(['' => 'Tous', 'waiting' => 'À checker', 'checked_in' => 'Arrivés'] as $value => $label)
                        <a href="{{ route('events.check-in', array_merge([$event], array_filter(array_merge(request()->except(['status', 'page']), ['status' => $value])))) }}" @class(['is-active' => (string) request('status', '') === (string) $value])>{{ $label }}</a>
                    @endforeach
                </nav>
                <form method="GET" action="{{ route('events.check-in', $event) }}" class="ui-toolbar" style="flex: 1 1 280px; justify-content: flex-end">
                    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                    <label class="ui-search mb-0" style="max-width: 320px">
                        <i class="bi bi-search"></i>
                        <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, représentant…" aria-label="Rechercher un participant">
                    </label>
                </form>
            </div>
            @if($registrations->count())
                <div class="table-responsive">
                    <table class="ui-table ci-table">
                        <thead><tr><th>Participant</th><th>Type</th><th>Statut</th><th class="text-end"></th></tr></thead>
                        <tbody>
                            @foreach($registrations as $registration)
                                <tr @class(['is-checked' => $registration->checked_in_at])>
                                    <td>
                                        <div class="ui-person">
                                            <span @class(['ui-avatar', 'gold' => $registration->isRepresented()])>{{ strtoupper(mb_substr($registration->checkInDisplayName(), 0, 1)) }}</span>
                                            <div>
                                                <strong>{{ $registration->checkInDisplayName() }}</strong>
                                                <small>{{ $registration->isRepresented() ? $registration->representant_email : $registration->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-hide"><span class="ui-pill {{ $registration->isRepresented() ? 'gold' : '' }}">{{ $registration->isRepresented() ? 'Représentant' : 'Invité' }}</span></td>
                                    <td class="col-status">
                                        @if($registration->checked_in_at)
                                            <span class="ui-pill dot success">Arrivé à {{ $registration->checked_in_at->format('H:i') }}</span>
                                        @else
                                            <span class="ui-pill dot warning">En attente</span>
                                        @endif
                                    </td>
                                    <td class="col-actions">
                                        <form method="POST" action="{{ route('events.registrations.check-in', [$event, $registration]) }}" class="ui-actions">
                                            @csrf
                                            @if($registration->checked_in_at)
                                                <button class="ui-btn ui-btn-soft ui-btn-sm" type="submit" title="Déjà enregistré : un nouveau check-in sera signalé comme double scan"><i class="bi bi-check2-all"></i>Déjà entré</button>
                                            @else
                                                <button class="ui-btn ui-btn-success ui-btn-sm" type="submit"><i class="bi bi-check2-circle"></i>Check-in</button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($registrations->hasPages())
                    <div class="ui-pager">{{ $registrations->links('pagination::bootstrap-5') }}</div>
                @endif
            @else
                <div class="ui-empty"><i class="bi bi-person-x"></i><strong>Aucun participant trouvé</strong>Modifiez la recherche ou le filtre de statut.</div>
            @endif
        </section>

        <aside class="ui-card">
            <div class="ui-card-head"><div><h2>Derniers scans</h2><p>Activité de l’accueil en temps réel.</p></div></div>
            @if($recentActivities->isNotEmpty())
                <div class="feed">
                    @foreach($recentActivities as $activity)
                        <div class="feed-item">
                            <span class="feed-dot"></span>
                            <div>
                                <strong>{{ $activity->registration?->checkInDisplayName() ?: 'Participant' }}</strong>
                                <span>{{ $activity->title }}</span>
                                <span><span class="feed-time">{{ $activity->created_at->format('H:i') }}</span> · {{ $activity->created_at->format('d/m') }}@if($activity->actor) · par {{ $activity->actor->name }}@endif</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ui-empty pt-2"><i class="bi bi-qr-code"></i><strong>Aucun scan</strong>Les arrivées s’afficheront ici.</div>
            @endif
        </aside>
    </div>
</div>
@endsection
