@extends('layouts.app')

@section('title', 'Tableau de bord')

@use('App\Models\Event')
@use('App\Models\EventAccessLink')
@use('App\Models\EventRegistration')
@use('Illuminate\Support\Carbon')

@php
    $user = auth()->user();
    $organizationId = $user->organization_id;
    $organization = $user->organization;
    $organizationSettings = $organization?->settings ?? [];
    $branding = $organizationSettings['branding'] ?? [];
    $brandName = $branding['brand_name'] ?? $organization?->name ?? 'Accès Business';
    $plan = \App\Support\SaasPlans::get($organization?->plan);
    $billingCycle = $organizationSettings['billing_cycle'] ?? 'monthly';
    $confirmedStatuses = ['present', 'peut_etre', 'represente'];

    // Totals (all scoped to the organization)
    $eventsCount = Event::forOrganization($organizationId)->count();
    $publishedEvents = Event::forOrganization($organizationId)->where('statut', 'publie')->count();
    $draftEvents = Event::forOrganization($organizationId)->where('statut', 'brouillon')->count();
    $upcomingEvents = Event::forOrganization($organizationId)->whereDate('date_debut', '>=', now()->toDateString())->count();
    $responses = EventRegistration::forOrganization($organizationId)->selectRaw('statut_reponse, count(*) as total')->groupBy('statut_reponse')->pluck('total', 'statut_reponse');
    $registrationsCount = (int) $responses->sum();
    $confirmedRegistrations = (int) collect($confirmedStatuses)->sum(fn ($s) => $responses[$s] ?? 0);
    $cardsSent = EventRegistration::forOrganization($organizationId)->where('carte_envoyee', true)->count();
    $checkedIn = EventRegistration::forOrganization($organizationId)->whereNotNull('checked_in_at')->count();
    $accessLinksCount = EventAccessLink::forOrganization($organizationId)->count();
    $usersCount = \App\Models\User::where('organization_id', $organizationId)->count();
    $confirmationRate = $registrationsCount > 0 ? round($confirmedRegistrations / $registrationsCount * 100) : 0;

    // Daily series over the last 90 days
    $rangeDays = 90;
    $since = now()->subDays($rangeDays - 1)->startOfDay();
    $daily = fn ($query, string $column) => $query->where($column, '>=', $since)
        ->selectRaw("DATE($column) as day, count(*) as total")->groupBy('day')->pluck('total', 'day');
    $invitesByDay = $daily(EventAccessLink::forOrganization($organizationId), 'envoye_le');
    $responsesByDay = $daily(EventRegistration::forOrganization($organizationId), 'date_inscription');
    $confirmsByDay = $daily(EventRegistration::forOrganization($organizationId)->whereIn('statut_reponse', $confirmedStatuses), 'date_inscription');
    $checkinsByDay = $daily(EventRegistration::forOrganization($organizationId), 'checked_in_at');
    $days = collect(range(0, $rangeDays - 1))->map(fn ($i) => $since->copy()->addDays($i));
    $series = fn ($map) => $days->map(fn (Carbon $d) => (int) ($map[$d->toDateString()] ?? 0))->values();
    $activity = [
        'labels' => $days->map(fn (Carbon $d) => $d->locale('fr')->translatedFormat('d M'))->values(),
        'invitations' => $series($invitesByDay),
        'responses' => $series($responsesByDay),
        'confirmations' => $series($confirmsByDay),
    ];
    $sparkline = fn ($map) => $series($map)->slice(-14)->values();
    $last30 = fn ($map) => $series($map)->slice(-30)->sum();
    $prev30 = fn ($map) => $series($map)->slice(-60, 30)->sum();
    $delta = function ($map) use ($last30, $prev30) {
        $now = $last30($map); $before = $prev30($map);
        return $before > 0 ? (int) round(($now - $before) / $before * 100) : ($now > 0 ? null : 0);
    };

    $responseBreakdown = collect([
        ['key' => 'present', 'label' => 'Présents', 'color' => '#1f8a5f'],
        ['key' => 'en_attente', 'label' => 'En attente', 'color' => '#c48a2c'],
        ['key' => 'peut_etre', 'label' => 'Peut-être', 'color' => '#2f6db5'],
        ['key' => 'absent', 'label' => 'Absents', 'color' => '#c2544a'],
        ['key' => 'represente', 'label' => 'Représentés', 'color' => '#7a5cc2'],
    ])->map(fn ($r) => $r + ['value' => (int) ($responses[$r['key']] ?? 0)]);

    $funnel = collect([
        ['label' => 'Invitations envoyées', 'value' => $accessLinksCount],
        ['label' => 'Réponses reçues', 'value' => $registrationsCount],
        ['label' => 'Présences confirmées', 'value' => $confirmedRegistrations],
        ['label' => 'Arrivés le jour J', 'value' => $checkedIn],
    ]);
    $funnelMax = max(1, $funnel->max('value'));

    $topEvents = Event::forOrganization($organizationId)
        ->withCount(['registrations', 'registrations as confirmed_count' => fn ($q) => $q->whereIn('statut_reponse', $confirmedStatuses)])
        ->orderByDesc('registrations_count')->take(6)->get()
        ->filter(fn ($e) => $e->registrations_count > 0)->values();

    $eventQuery = fn () => Event::forOrganization($organizationId)->with(['category'])->withCount('registrations');
    $agenda = $eventQuery()->whereDate('date_debut', '>=', now()->toDateString())->orderBy('date_debut')->take(4)->get();
    $agendaIsUpcoming = $agenda->isNotEmpty();
    if (!$agendaIsUpcoming) {
        $agenda = $eventQuery()->orderByDesc('date_debut')->take(4)->get();
    }
    $spotlight = $agendaIsUpcoming ? $agenda->first() : null;
    $spotlightImage = $spotlight ? \App\Support\EventMedia::storageUrl($spotlight->image) : null;
    $recentRegistrations = EventRegistration::forOrganization($organizationId)->with('event')->latest('date_inscription')->take(5)->get();

    $usage = [
        ['label' => 'Événements', 'used' => $eventsCount, 'limit' => $plan['limits']['events']],
        ['label' => 'Utilisateurs', 'used' => $usersCount, 'limit' => $plan['limits']['users']],
        ['label' => 'Invitations', 'used' => $accessLinksCount, 'limit' => $plan['limits']['invitations']],
    ];
    $statutLabels = ['publie' => 'Publié', 'brouillon' => 'Brouillon', 'archive' => 'Archivé', 'annule' => 'Annulé', 'termine' => 'Terminé', 'reporte' => 'Reporté'];
    $responseLabels = ['present' => 'Présent', 'peut_etre' => 'Peut-être', 'absent' => 'Absent', 'en_attente' => 'En attente', 'represente' => 'Représenté'];
    $hour = (int) now()->format('G');
    $greeting = $hour < 5 || $hour >= 18 ? 'Bonsoir' : 'Bonjour';
    $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();

    $chartBreakdown = $responseBreakdown->where('value', '>', 0)->values();
    $chartTopEvents = $topEvents->map(fn ($e) => ['label' => \Illuminate\Support\Str::limit($e->titre, 28), 'registered' => $e->registrations_count, 'confirmed' => $e->confirmed_count])->values();

    $kpis = [
        ['label' => 'Invitations envoyées', 'icon' => 'bi-send', 'value' => $accessLinksCount, 'suffix' => '', 'spark' => $sparkline($invitesByDay), 'delta' => $delta($invitesByDay), 'note' => 'Liens d’accès personnels'],
        ['label' => 'Réponses reçues', 'icon' => 'bi-inbox', 'value' => $registrationsCount, 'suffix' => '', 'spark' => $sparkline($responsesByDay), 'delta' => $delta($responsesByDay), 'note' => $cardsSent . ' carte(s) envoyée(s)'],
        ['label' => 'Taux de confirmation', 'icon' => 'bi-person-check', 'value' => $confirmationRate, 'suffix' => '%', 'spark' => $sparkline($confirmsByDay), 'delta' => $delta($confirmsByDay), 'note' => $confirmedRegistrations . ' confirmé(s)'],
        ['label' => 'Événements à venir', 'icon' => 'bi-calendar2-week', 'value' => $upcomingEvents, 'suffix' => '', 'spark' => null, 'delta' => null, 'note' => $publishedEvents . ' publié(s) · ' . $draftEvents . ' brouillon(s)'],
    ];
@endphp

@push('styles')
<style>
    .dash { margin: 0 auto; max-width: 1400px; }
    .dash *, .dash *::before, .dash *::after { min-width: 0; }
    .tile { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: var(--app-radius); box-shadow: var(--app-shadow); }
    .tile-head { align-items: flex-start; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; padding: 18px 22px 0; }
    .tile-head h2 { color: var(--app-ink); font-size: 1rem; font-weight: 650; letter-spacing: -.015em; margin: 0; }
    .tile-head p { color: var(--app-muted); font-size: .8rem; margin: 3px 0 0; }
    .tile-link { align-items: center; color: var(--app-gold-deep); display: inline-flex; font-size: .82rem; font-weight: 600; gap: 4px; text-decoration: none; white-space: nowrap; }
    .tile-link:hover { color: var(--app-ink); }

    /* KPI tiles */
    .kpis { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); margin-bottom: 20px; }
    .kpi { overflow: hidden; padding: 18px 20px 14px; position: relative; transition: transform .5s var(--app-ease), box-shadow .5s var(--app-ease); }
    .kpi:hover { box-shadow: 0 1px 2px rgba(23, 23, 19, .04), 0 24px 40px -20px rgba(23, 23, 19, .2); transform: translateY(-2px); }
    .kpi-top { align-items: center; color: var(--app-muted); display: flex; font-size: .8rem; font-weight: 500; gap: 8px; }
    .kpi-top i { align-items: center; background: rgba(23, 23, 19, .05); border-radius: 8px; color: var(--app-ink); display: inline-flex; font-size: .85rem; height: 26px; justify-content: center; width: 26px; }
    .kpi-row { align-items: flex-end; display: flex; gap: 10px; justify-content: space-between; margin-top: 14px; }
    .kpi-value { color: var(--app-ink); font-size: 2rem; font-weight: 700; letter-spacing: -.045em; line-height: 1; }
    .kpi-value small { color: var(--app-muted); font-size: 1rem; font-weight: 600; margin-left: 1px; }
    .kpi-spark { flex: 0 0 96px; height: 34px; }
    .kpi-foot { align-items: center; color: var(--app-muted); display: flex; flex-wrap: wrap; font-size: .76rem; gap: 6px; margin-top: 10px; }
    .delta { align-items: center; border-radius: 99px; display: inline-flex; font-size: .7rem; font-weight: 700; gap: 2px; padding: 2px 7px; }
    .delta.up { background: rgba(31, 138, 95, .1); color: #1f8a5f; }
    .delta.down { background: rgba(194, 84, 74, .1); color: #a4514a; }
    .delta.flat { background: rgba(23, 23, 19, .06); color: var(--app-muted); }

    .grid-2 { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.65fr) minmax(300px, 1fr); margin-bottom: 20px; }
    .stack { display: grid; gap: 20px; }

    /* Activity chart */
    .range { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 2px; padding: 3px; }
    .range button { background: transparent; border: 0; border-radius: 999px; color: var(--app-muted); font-size: .76rem; font-weight: 600; padding: 5px 11px; }
    .range button.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }
    .legend { display: flex; flex-wrap: wrap; gap: 6px 16px; padding: 12px 22px 0; }
    .legend span { align-items: center; color: var(--app-text); display: inline-flex; font-size: .78rem; gap: 7px; }
    .legend i { border-radius: 2px; height: 3px; width: 14px; }
    .legend strong { color: var(--app-ink); font-variant-numeric: tabular-nums; }
    .chart-box { height: 270px; padding: 10px 14px 14px; position: relative; }

    /* Donut */
    .donut-wrap { align-items: center; display: grid; gap: 18px; grid-template-columns: 150px minmax(0, 1fr); padding: 16px 22px 22px; }
    .donut { height: 150px; position: relative; width: 150px; }
    .donut-center { display: flex; flex-direction: column; inset: 0; justify-content: center; pointer-events: none; position: absolute; text-align: center; }
    .donut-center strong { color: var(--app-ink); font-size: 1.7rem; font-weight: 700; letter-spacing: -.04em; line-height: 1; }
    .donut-center span { color: var(--app-muted); font-size: .7rem; margin-top: 3px; }
    .donut-legend { display: grid; gap: 9px; }
    .donut-legend div { align-items: center; color: var(--app-text); display: flex; font-size: .82rem; gap: 8px; }
    .donut-legend i { border-radius: 3px; flex: 0 0 10px; height: 10px; }
    .donut-legend strong { color: var(--app-ink); font-variant-numeric: tabular-nums; margin-left: auto; }
    .donut-legend em { color: var(--app-muted); font-size: .72rem; font-style: normal; min-width: 34px; text-align: right; }

    /* Funnel */
    .funnel { display: grid; gap: 12px; padding: 16px 22px 22px; }
    .funnel-row { display: grid; gap: 6px; }
    .funnel-meta { align-items: baseline; display: flex; font-size: .82rem; gap: 8px; justify-content: space-between; }
    .funnel-meta span { color: var(--app-text); }
    .funnel-meta strong { color: var(--app-ink); font-variant-numeric: tabular-nums; }
    .funnel-meta em { color: var(--app-muted); font-size: .72rem; font-style: normal; margin-left: 6px; }
    .funnel-track { background: rgba(23, 23, 19, .05); border-radius: 6px; height: 12px; overflow: hidden; }
    .funnel-bar { background: linear-gradient(90deg, #171713, #4a4740); border-radius: 0 4px 4px 0; height: 100%; transform-origin: left; animation: grow 1.1s var(--app-ease) backwards; animation-delay: calc(var(--i) * 120ms + 200ms); }
    .funnel-row:last-child .funnel-bar { background: linear-gradient(90deg, #8a6128, #c48a2c); }
    @keyframes grow { from { transform: scaleX(0); } }

    /* Spotlight */
    .spotlight { background: #141412; border-radius: 22px; color: #fff; display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); isolation: isolate; min-height: 230px; overflow: hidden; position: relative; text-decoration: none; }
    .spotlight:hover { color: #fff; }
    .spotlight::before { background: radial-gradient(80% 90% at 0% 0%, rgba(185, 137, 67, .35), transparent 60%); content: ""; inset: 0; position: absolute; z-index: -1; }
    .spotlight-body { display: flex; flex-direction: column; padding: 22px 24px; }
    .spotlight-kicker { align-items: center; color: #d8b978; display: inline-flex; font-size: .74rem; font-weight: 600; gap: 8px; }
    .live { background: #30d158; border-radius: 50%; box-shadow: 0 0 0 0 rgba(48, 209, 88, .6); height: 7px; width: 7px; animation: live 2s infinite; }
    @keyframes live { 70% { box-shadow: 0 0 0 8px transparent; } 100% { box-shadow: 0 0 0 0 transparent; } }
    .spotlight h2 { color: #fff; font-size: 1.45rem; font-weight: 700; letter-spacing: -.03em; line-height: 1.15; margin: 10px 0 6px; }
    .spotlight-meta { color: rgba(255, 255, 255, .6); display: flex; flex-wrap: wrap; font-size: .84rem; gap: 4px 14px; }
    .spotlight-meta i { color: #d8b978; margin-right: 5px; }
    .spotlight-stats { display: flex; gap: 24px; margin-top: auto; padding-top: 18px; }
    .spotlight-stats strong { display: block; font-size: 1.5rem; font-weight: 700; letter-spacing: -.04em; line-height: 1; }
    .spotlight-stats span { color: rgba(255, 255, 255, .5); display: block; font-size: .72rem; margin-top: 5px; }
    .spotlight-media { background: linear-gradient(135deg, #2a2722, #171713); position: relative; }
    .spotlight-media img { height: 100%; inset: 0; object-fit: cover; position: absolute; transition: transform 1.2s var(--app-ease); width: 100%; }
    .spotlight:hover .spotlight-media img { transform: scale(1.04); }
    .spotlight-media::after { background: linear-gradient(90deg, #141412, transparent 45%); content: ""; inset: 0; position: absolute; }
    .spotlight-media .placeholder { color: rgba(216, 185, 120, .22); display: grid; font-size: 5rem; inset: 0; place-items: center; position: absolute; }
    .countdown { -webkit-backdrop-filter: blur(12px); backdrop-filter: blur(12px); background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .16); border-radius: 14px; bottom: 16px; padding: 8px 12px; position: absolute; right: 16px; text-align: center; z-index: 1; }
    .countdown strong { display: block; font-size: 1.35rem; font-weight: 700; line-height: 1; }
    .countdown span { color: rgba(255, 255, 255, .7); font-size: .68rem; }

    /* Lists */
    .list { padding: 8px 10px 10px; }
    .list-row { align-items: center; border-radius: 12px; color: inherit; display: grid; gap: 14px; grid-template-columns: 50px minmax(0, 1fr) auto; padding: 10px 12px; text-decoration: none; transition: background .2s; }
    a.list-row:hover { background: rgba(23, 23, 19, .035); color: inherit; }
    .date-chip { background: var(--app-bg); border-radius: 10px; padding: 7px 0; text-align: center; }
    .date-chip strong { color: var(--app-ink); display: block; font-size: 1.1rem; font-weight: 700; line-height: 1; }
    .date-chip span { color: var(--app-gold-deep); display: block; font-size: .62rem; font-weight: 700; letter-spacing: .06em; margin-top: 3px; text-transform: uppercase; }
    .row-title { color: var(--app-ink); display: block; font-size: .9rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .row-meta { color: var(--app-muted); display: block; font-size: .78rem; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .feed-avatar { align-items: center; background: linear-gradient(140deg, #2c2a25, #171713); border-radius: 50%; color: #d8b978; display: flex; font-size: .78rem; font-weight: 600; height: 36px; justify-content: center; justify-self: center; width: 36px; }
    .empty { color: var(--app-muted); font-size: .86rem; padding: 30px 20px; text-align: center; }
    .empty i { color: var(--app-gold); display: block; font-size: 1.6rem; margin-bottom: 6px; }

    /* Plan */
    .plan { padding: 14px 22px 20px; }
    .plan-name { align-items: baseline; display: flex; gap: 10px; justify-content: space-between; margin-bottom: 14px; }
    .plan-name strong { color: var(--app-ink); font-size: 1.25rem; font-weight: 700; letter-spacing: -.03em; }
    .plan-name span { background: var(--app-gold-soft); border-radius: 99px; color: var(--app-gold-deep); font-size: .72rem; font-weight: 600; padding: 4px 10px; }
    .usage { display: grid; gap: 12px; }
    .usage-meta { color: var(--app-muted); display: flex; font-size: .78rem; justify-content: space-between; }
    .usage-meta strong { color: var(--app-ink); font-weight: 600; }
    .meter { background: rgba(23, 23, 19, .06); border-radius: 99px; height: 6px; margin-top: 6px; overflow: hidden; }
    .meter span { background: var(--accent, var(--app-ink)); border-radius: inherit; display: block; height: 100%; transform-origin: left; animation: grow 1.2s var(--app-ease) .3s backwards; }

    .reveal { animation: rise .7s var(--app-ease) backwards; animation-delay: calc(var(--i, 0) * 60ms); }
    @keyframes rise { from { opacity: 0; transform: translateY(14px); } }

    @media (max-width: 1199.98px) { .grid-2 { grid-template-columns: 1fr; } }
    @media (max-width: 991.98px) { .kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) {
        .spotlight { grid-template-columns: 1fr; }
        .spotlight-media { height: 150px; order: -1; }
        .spotlight-media::after { background: linear-gradient(0deg, #141412, transparent 60%); }
        .chart-box { height: 220px; }
    }
    @media (max-width: 479.98px) {
        .kpis { gap: 12px; }
        .kpi { padding: 14px; }
        .kpi-value { font-size: 1.6rem; }
        .kpi-spark { display: none !important; }
        .donut-wrap { grid-template-columns: 1fr; justify-items: center; }
        .donut-legend { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) { .reveal, .meter span, .funnel-bar { animation: none; } }
</style>
@endpush

@section('content')
<div class="dash">
    <x-app.page-header :kicker="$brandName . ' · ' . now()->locale('fr')->translatedFormat('l d F Y')" :title="$greeting . ', ' . $firstName" subtitle="Voici la dynamique de vos invitations, de vos réponses et de vos événements.">
        <x-slot:actions>
            <a href="{{ route('events.index') }}" class="ui-btn ui-btn-ghost"><i class="bi bi-calendar3"></i>Tous les événements</a>
        </x-slot:actions>
    </x-app.page-header>

    <section class="kpis">
        @foreach($kpis as $kpi)
            <article class="tile kpi reveal" style="--i: {{ $loop->index }}">
                <div class="kpi-top"><i class="bi {{ $kpi['icon'] }}"></i>{{ $kpi['label'] }}</div>
                <div class="kpi-row">
                    <div class="kpi-value"><span data-count="{{ $kpi['value'] }}">{{ number_format($kpi['value'], 0, ',', ' ') }}</span>@if($kpi['suffix'])<small>{{ $kpi['suffix'] }}</small>@endif</div>
                    @if($kpi['spark'])<canvas class="kpi-spark" data-spark='@json($kpi['spark'])' aria-hidden="true"></canvas>@endif
                </div>
                <div class="kpi-foot">
                    @if($kpi['delta'] !== null && $kpi['spark'])
                        <span @class(['delta', 'up' => $kpi['delta'] > 0, 'down' => $kpi['delta'] < 0, 'flat' => $kpi['delta'] === 0])>
                            <i class="bi {{ $kpi['delta'] > 0 ? 'bi-arrow-up-right' : ($kpi['delta'] < 0 ? 'bi-arrow-down-right' : 'bi-dash') }}"></i>{{ abs($kpi['delta']) }}%
                        </span>
                        <span>vs 30 jours précédents</span>
                    @else
                        <span>{{ $kpi['note'] }}</span>
                    @endif
                </div>
            </article>
        @endforeach
    </section>

    <div class="grid-2">
        <section class="tile reveal" style="--i: 4">
            <div class="tile-head">
                <div><h2>Activité</h2><p>Invitations, réponses et confirmations par jour.</p></div>
                <div class="range" role="group" aria-label="Période">
                    @foreach([7 => '7 j', 30 => '30 j', 90 => '90 j'] as $value => $label)
                        <button type="button" data-range="{{ $value }}" @class(['is-active' => $value === 30])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="legend" id="activityLegend">
                <span><i style="background:#2f6db5"></i>Invitations <strong data-total="invitations">0</strong></span>
                <span><i style="background:#c48a2c"></i>Réponses <strong data-total="responses">0</strong></span>
                <span><i style="background:#1f8a5f"></i>Confirmations <strong data-total="confirmations">0</strong></span>
            </div>
            <div class="chart-box"><canvas id="activityChart" role="img" aria-label="Évolution quotidienne des invitations, réponses et confirmations"></canvas></div>
            <table class="visually-hidden">
                <caption>Activité quotidienne (90 jours)</caption>
                <thead><tr><th>Jour</th><th>Invitations</th><th>Réponses</th><th>Confirmations</th></tr></thead>
                <tbody>
                    @foreach($activity['labels'] as $i => $label)
                        @if($activity['invitations'][$i] || $activity['responses'][$i] || $activity['confirmations'][$i])
                            <tr><td>{{ $label }}</td><td>{{ $activity['invitations'][$i] }}</td><td>{{ $activity['responses'][$i] }}</td><td>{{ $activity['confirmations'][$i] }}</td></tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </section>

        <section class="tile reveal" style="--i: 5">
            <div class="tile-head"><div><h2>Réponses des invités</h2><p>Répartition sur l’ensemble de vos événements.</p></div></div>
            @if($registrationsCount)
                <div class="donut-wrap">
                    <div class="donut">
                        <canvas id="responsesChart" role="img" aria-label="Répartition des réponses"></canvas>
                        <div class="donut-center"><strong data-count="{{ $confirmationRate }}">{{ $confirmationRate }}</strong><span>% confirmés</span></div>
                    </div>
                    <div class="donut-legend">
                        @foreach($responseBreakdown as $row)
                            <div><i style="background: {{ $row['color'] }}"></i>{{ $row['label'] }}<strong>{{ $row['value'] }}</strong><em>{{ round($row['value'] / $registrationsCount * 100) }}%</em></div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="empty"><i class="bi bi-pie-chart"></i>Les réponses apparaîtront ici dès vos premières invitations.</div>
            @endif
        </section>
    </div>

    <div class="grid-2">
        <div class="stack">
            @if($spotlight)
                @php $days = (int) now()->startOfDay()->diffInDays($spotlight->date_debut->copy()->startOfDay(), false); @endphp
                <a href="{{ route('events.show', $spotlight) }}" class="spotlight reveal" style="--i: 6">
                    <div class="spotlight-body">
                        <span class="spotlight-kicker"><span class="live"></span>Prochain événement</span>
                        <h2>{{ $spotlight->titre }}</h2>
                        <div class="spotlight-meta">
                            <span><i class="bi bi-calendar3"></i>{{ $spotlight->date_debut->locale('fr')->translatedFormat('d F Y') }}{{ $spotlight->heure_debut ? ' · ' . \Illuminate\Support\Str::substr($spotlight->heure_debut, 0, 5) : '' }}</span>
                            <span><i class="bi bi-geo-alt"></i>{{ $spotlight->lieu ?: 'Lieu à confirmer' }}</span>
                        </div>
                        <div class="spotlight-stats">
                            <div><strong>{{ $spotlight->registrations_count }}</strong><span>Inscrit(s)</span></div>
                            <div><strong>{{ $spotlight->capacite_maximale ? number_format($spotlight->capacite_maximale, 0, ',', ' ') : '∞' }}</strong><span>Places</span></div>
                        </div>
                    </div>
                    <div class="spotlight-media">
                        @if($spotlightImage)<img src="{{ $spotlightImage }}" alt="">@else<span class="placeholder"><i class="bi bi-stars"></i></span>@endif
                        <div class="countdown"><strong>{{ $days === 0 ? 'J' : 'J-' . $days }}</strong><span>{{ $days === 0 ? 'Aujourd’hui' : 'jour(s)' }}</span></div>
                    </div>
                </a>
            @endif

            <section class="tile reveal" style="--i: 7">
                <div class="tile-head">
                    <div><h2>Top des événements</h2><p>Inscrits et confirmés par événement.</p></div>
                    <a href="{{ route('events.index') }}" class="tile-link">Tout voir <i class="bi bi-arrow-right"></i></a>
                </div>
                @if($topEvents->isNotEmpty())
                    <div class="legend">
                        <span><i style="background:#171713"></i>Inscrits</span>
                        <span><i style="background:#1f8a5f"></i>Confirmés</span>
                    </div>
                    <div class="chart-box" style="height: {{ 70 + $topEvents->count() * 46 }}px"><canvas id="topEventsChart" role="img" aria-label="Inscrits et confirmés par événement"></canvas></div>
                @else
                    <div class="empty"><i class="bi bi-bar-chart"></i>Aucune inscription pour le moment.</div>
                @endif
            </section>

            <section class="tile reveal" style="--i: 8">
                <div class="tile-head"><div><h2>Dernières réponses</h2><p>Activité récente de vos invités.</p></div></div>
                @if($recentRegistrations->isNotEmpty())
                    <div class="list">
                        @foreach($recentRegistrations as $registration)
                            <div class="list-row">
                                <span class="feed-avatar">{{ strtoupper(mb_substr($registration->prenom ?: $registration->email, 0, 1)) }}</span>
                                <div>
                                    <span class="row-title">{{ $registration->nom_complet ?: $registration->email }}</span>
                                    <span class="row-meta">{{ optional($registration->event)->titre ?: 'Événement supprimé' }} · {{ optional($registration->date_inscription)?->locale('fr')->diffForHumans() }}</span>
                                </div>
                                <span class="ui-pill dot {{ ['present' => 'success', 'peut_etre' => 'info', 'absent' => 'danger', 'represente' => 'gold'][$registration->statut_reponse] ?? '' }}">{{ $responseLabels[$registration->statut_reponse] ?? ucfirst($registration->statut_reponse) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty"><i class="bi bi-inbox"></i>Les réponses apparaîtront ici dès les premiers retours.</div>
                @endif
            </section>
        </div>

        <aside class="stack">
            <section class="tile reveal" style="--i: 6">
                <div class="tile-head"><div><h2>Entonnoir de conversion</h2><p>De l’invitation jusqu’à l’entrée.</p></div></div>
                <div class="funnel">
                    @foreach($funnel as $step)
                        @php $previous = $loop->first ? null : $funnel[$loop->index - 1]['value']; @endphp
                        <div class="funnel-row">
                            <div class="funnel-meta">
                                <span>{{ $step['label'] }}</span>
                                <span><strong>{{ number_format($step['value'], 0, ',', ' ') }}</strong>@if($previous !== null)<em>{{ $previous ? round($step['value'] / $previous * 100) : 0 }}%</em>@endif</span>
                            </div>
                            <div class="funnel-track"><div class="funnel-bar" style="width: {{ max($step['value'] ? 2 : 0, round($step['value'] / $funnelMax * 100)) }}%; --i: {{ $loop->index }}"></div></div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="tile reveal" style="--i: 7">
                <div class="tile-head">
                    <div><h2>{{ $agendaIsUpcoming ? 'Agenda' : 'Derniers événements' }}</h2><p>{{ $agendaIsUpcoming ? 'Vos prochains rendez-vous.' : 'Aucun événement à venir.' }}</p></div>
                </div>
                @if($agenda->isNotEmpty())
                    <div class="list">
                        @foreach($agenda as $event)
                            <a href="{{ route('events.show', $event) }}" class="list-row">
                                <div class="date-chip"><strong>{{ optional($event->date_debut)->format('d') ?: '--' }}</strong><span>{{ optional($event->date_debut)?->locale('fr')->translatedFormat('M') ?: '—' }}</span></div>
                                <div><span class="row-title">{{ $event->titre }}</span><span class="row-meta">{{ $event->registrations_count }} inscrit(s) · {{ $event->lieu ?: 'Lieu à confirmer' }}</span></div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="empty"><i class="bi bi-calendar-plus"></i><a href="{{ route('events.create') }}" class="tile-link">Créer le premier événement</a></div>
                @endif
            </section>

            <section class="tile reveal" style="--i: 8">
                <div class="tile-head">
                    <div><h2>Abonnement</h2><p>Consommation de votre forfait.</p></div>
                    @if($user->isSuperAdmin())<a href="{{ route('saas.plans') }}" class="tile-link">Gérer <i class="bi bi-arrow-right"></i></a>@endif
                </div>
                <div class="plan">
                    <div class="plan-name"><strong>{{ $plan['name'] }}</strong><span>{{ $billingCycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</span></div>
                    <div class="usage">
                        @foreach($usage as $row)
                            @php $pct = $row['limit'] ? min(100, round($row['used'] / $row['limit'] * 100)) : 0; @endphp
                            <div style="--accent: {{ $pct >= 90 ? 'var(--app-red)' : ($pct >= 70 ? 'var(--app-gold)' : 'var(--app-ink)') }}">
                                <div class="usage-meta"><strong>{{ $row['label'] }}</strong><span>{{ number_format($row['used'], 0, ',', ' ') }} / {{ $row['limit'] ? number_format($row['limit'], 0, ',', ' ') : '∞' }}</span></div>
                                <div class="meter"><span style="width: {{ $row['limit'] ? $pct : 100 }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ink = '#171713', muted = '#7a746a', grid = 'rgba(23, 23, 19, .07)';
    const fmt = new Intl.NumberFormat('fr-FR');

    // Count-up for KPI numbers
    document.querySelectorAll('[data-count]').forEach((el) => {
        const target = parseInt(el.dataset.count, 10) || 0;
        if (reduced || !target) { el.textContent = fmt.format(target); return; }
        const start = performance.now(), duration = 1200;
        const tick = (now) => {
            const t = Math.min(1, (now - start) / duration);
            el.textContent = fmt.format(Math.round(target * (1 - Math.pow(1 - t, 4))));
            if (t < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });

    if (!window.Chart) return;
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 11;
    Chart.defaults.color = muted;
    if (reduced) { Chart.defaults.animation = false; } else { Chart.defaults.animation.duration = 900; Chart.defaults.animation.easing = 'easeOutQuart'; }
    const tooltip = {
        backgroundColor: '#171713', padding: 10, cornerRadius: 10, titleColor: '#fff', bodyColor: 'rgba(255,255,255,.85)',
        titleFont: { weight: '600' }, boxPadding: 4, usePointStyle: true,
    };

    // Sparklines
    document.querySelectorAll('[data-spark]').forEach((canvas) => {
        const data = JSON.parse(canvas.dataset.spark);
        new Chart(canvas, {
            type: 'line',
            data: { labels: data.map((_, i) => i), datasets: [{ data, borderColor: ink, borderWidth: 2, tension: .4, pointRadius: 0, fill: true,
                backgroundColor: (ctx) => { const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 34); g.addColorStop(0, 'rgba(23,23,19,.14)'); g.addColorStop(1, 'rgba(23,23,19,0)'); return g; } }] },
            options: { responsive: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false, beginAtZero: true } } },
        });
    });

    // Activity
    const activity = @json($activity);
    const activityCanvas = document.getElementById('activityChart');
    if (activityCanvas) {
        const area = (color) => (ctx) => {
            const { chartArea, ctx: c } = ctx.chart;
            if (!chartArea) return 'transparent';
            const g = c.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
            g.addColorStop(0, color + '24'); g.addColorStop(1, color + '00');
            return g;
        };
        const ds = (label, key, color) => ({ label, key, data: [], borderColor: color, backgroundColor: area(color), fill: true, borderWidth: 2, cubicInterpolationMode: 'monotone',
            pointRadius: 0, pointHoverRadius: 5, pointHoverBorderWidth: 2, pointHoverBorderColor: '#fff', pointHoverBackgroundColor: color });
        const chart = new Chart(activityCanvas, {
            type: 'line',
            data: { labels: [], datasets: [ds('Invitations', 'invitations', '#2f6db5'), ds('Réponses', 'responses', '#c48a2c'), ds('Confirmations', 'confirmations', '#1f8a5f')] },
            options: {
                maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip },
                scales: {
                    x: { grid: { display: false }, border: { display: false }, ticks: { maxTicksLimit: 7, maxRotation: 0 } },
                    y: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { precision: 0, maxTicksLimit: 5 } },
                },
            },
        });
        const setRange = (days) => {
            chart.data.labels = activity.labels.slice(-days);
            chart.data.datasets.forEach((d) => { d.data = activity[d.key].slice(-days); });
            chart.update();
            document.querySelectorAll('[data-total]').forEach((el) => {
                el.textContent = fmt.format(activity[el.dataset.total].slice(-days).reduce((a, b) => a + b, 0));
            });
            document.querySelectorAll('[data-range]').forEach((b) => b.classList.toggle('is-active', Number(b.dataset.range) === days));
        };
        document.querySelectorAll('[data-range]').forEach((b) => b.addEventListener('click', () => setRange(Number(b.dataset.range))));
        setRange(30);
    }

    // Responses donut
    const breakdown = @json($chartBreakdown);
    const donut = document.getElementById('responsesChart');
    if (donut && breakdown.length) {
        new Chart(donut, {
            type: 'doughnut',
            data: { labels: breakdown.map((r) => r.label), datasets: [{ data: breakdown.map((r) => r.value), backgroundColor: breakdown.map((r) => r.color), borderColor: '#fff', borderWidth: 2, hoverOffset: 4, borderRadius: 3 }] },
            options: { cutout: '74%', plugins: { legend: { display: false }, tooltip }, },
        });
    }

    // Top events
    const top = @json($chartTopEvents);
    const topCanvas = document.getElementById('topEventsChart');
    if (topCanvas && top.length) {
        const bar = (label, key, color) => ({ label, data: top.map((e) => e[key]), backgroundColor: color, borderRadius: 4, borderSkipped: 'start', maxBarThickness: 14, categoryPercentage: .7, barPercentage: .9 });
        new Chart(topCanvas, {
            type: 'bar',
            data: { labels: top.map((e) => e.label), datasets: [bar('Inscrits', 'registered', ink), bar('Confirmés', 'confirmed', '#1f8a5f')] },
            options: {
                indexAxis: 'y', maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: {
                    x: { beginAtZero: true, grid: { color: grid }, border: { display: false }, ticks: { precision: 0, maxTicksLimit: 5 } },
                    y: { grid: { display: false }, border: { display: false }, ticks: { color: ink, font: { size: 12 },
                        callback(value) { const label = this.getLabelForValue(value); const max = window.innerWidth < 576 ? 16 : 28; return label.length > max ? label.slice(0, max - 1) + '…' : label; } } },
                },
            },
        });
    }
})();
</script>
@endpush
