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
    $days = collect(range(0, $rangeDays - 1))->map(fn ($i) => $since->copy()->addDays($i));
    $series = fn ($map) => $days->map(fn (Carbon $d) => (int) ($map[$d->toDateString()] ?? 0))->values();
    $activity = [
        'labels' => $days->map(fn (Carbon $d) => $d->locale('fr')->translatedFormat('d M'))->values(),
        'invitations' => $series($invitesByDay),
        'responses' => $series($responsesByDay),
        'confirmations' => $series($confirmsByDay),
    ];

    $responseBreakdown = collect([
        ['key' => 'present', 'label' => 'Présents', 'color' => '#1f8a5f'],
        ['key' => 'peut_etre', 'label' => 'Peut-être', 'color' => '#2f6db5'],
        ['key' => 'represente', 'label' => 'Représentés', 'color' => '#7a5cc2'],
        ['key' => 'en_attente', 'label' => 'En attente', 'color' => '#c48a2c'],
        ['key' => 'absent', 'label' => 'Absents', 'color' => '#c2544a'],
    ])->map(fn ($r) => $r + ['value' => (int) ($responses[$r['key']] ?? 0)]);

    $agenda = Event::forOrganization($organizationId)->withCount('registrations')
        ->whereDate('date_debut', '>=', now()->toDateString())->orderBy('date_debut')->take(5)->get();
    $recentRegistrations = EventRegistration::forOrganization($organizationId)->with('event')->latest('date_inscription')->take(5)->get();

    $usage = [
        ['label' => 'Événements', 'used' => $eventsCount, 'limit' => $plan['limits']['events']],
        ['label' => 'Utilisateurs', 'used' => $usersCount, 'limit' => $plan['limits']['users']],
        ['label' => 'Invitations', 'used' => $accessLinksCount, 'limit' => $plan['limits']['invitations']],
    ];
    $responseLabels = ['present' => 'Présent', 'peut_etre' => 'Peut-être', 'absent' => 'Absent', 'en_attente' => 'En attente', 'represente' => 'Représenté'];
    $responseTones = ['present' => 'success', 'peut_etre' => 'info', 'absent' => 'danger', 'represente' => 'gold'];
    $hour = (int) now()->format('G');
    $greeting = $hour < 5 || $hour >= 18 ? 'Bonsoir' : 'Bonjour';
    $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();

    $kpis = [
        ['label' => 'Invitations envoyées', 'icon' => 'bi-send', 'value' => number_format($accessLinksCount, 0, ',', ' '), 'note' => 'Liens d’accès personnels'],
        ['label' => 'Réponses reçues', 'icon' => 'bi-inbox', 'value' => number_format($registrationsCount, 0, ',', ' '), 'note' => 'Toutes réponses confondues'],
        ['label' => 'Taux de confirmation', 'icon' => 'bi-person-check', 'value' => $confirmationRate . ' %', 'note' => $confirmedRegistrations . ' confirmé(s)'],
        ['label' => 'Événements à venir', 'icon' => 'bi-calendar2-week', 'value' => $upcomingEvents, 'note' => $publishedEvents . ' publié(s) · ' . $draftEvents . ' brouillon(s)'],
    ];
@endphp

@push('styles')
<style>
    .dash { margin: 0 auto; max-width: 1320px; }
    .dash *, .dash *::before, .dash *::after { min-width: 0; }
    .dash-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); margin-bottom: 20px; }
    .dash-grid.thirds { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .card-link { color: var(--app-gold-deep); font-size: .82rem; font-weight: 600; text-decoration: none; white-space: nowrap; }
    .card-link:hover { color: var(--app-ink); }

    .range { background: var(--app-soft); border-radius: 999px; display: inline-flex; gap: 2px; padding: 3px; }
    .range button { background: transparent; border: 0; border-radius: 999px; color: var(--app-muted); font-size: .76rem; font-weight: 600; padding: 4px 11px; }
    .range button.is-active { background: var(--app-panel); color: var(--app-ink); box-shadow: 0 0 0 1px var(--app-line); }
    .legend { display: flex; flex-wrap: wrap; gap: 6px 16px; padding: 0 22px; }
    .legend span { align-items: center; color: var(--app-text); display: inline-flex; font-size: .78rem; gap: 7px; }
    .legend i { border-radius: 2px; height: 3px; width: 14px; }
    .legend strong { color: var(--app-ink); font-variant-numeric: tabular-nums; }
    .chart-box { height: 260px; padding: 10px 14px 14px; position: relative; }

    .bars { display: grid; gap: 14px; }
    .bar-meta { display: flex; font-size: .84rem; justify-content: space-between; margin-bottom: 6px; }
    .bar-meta span { color: var(--app-text); }
    .bar-meta strong { color: var(--app-ink); font-variant-numeric: tabular-nums; font-weight: 600; }
    .bar-meta em { color: var(--app-muted); font-style: normal; font-weight: 400; margin-left: 6px; }
    .bar-meta + .ui-meter span { background: var(--bar); }
    .rate { align-items: baseline; border-bottom: 1px solid var(--app-line); display: flex; gap: 8px; margin-bottom: 16px; padding-bottom: 14px; }
    .rate strong { color: var(--app-ink); font-size: 1.6rem; font-weight: 700; letter-spacing: -.03em; line-height: 1; }
    .rate span { color: var(--app-muted); font-size: .82rem; }

    .rows { padding: 0 10px 10px; }
    .row-item { align-items: center; border-radius: 10px; color: inherit; display: grid; gap: 12px; grid-template-columns: 44px minmax(0, 1fr) auto; padding: 9px 12px; text-decoration: none; }
    a.row-item:hover { background: var(--app-soft); color: inherit; }
    .row-item + .row-item { border-top: 1px solid var(--app-line); }
    .date-chip { border: 1px solid var(--app-line); border-radius: 10px; padding: 5px 0; text-align: center; }
    .date-chip strong { color: var(--app-ink); display: block; font-size: 1rem; font-weight: 700; line-height: 1.1; }
    .date-chip span { color: var(--app-muted); display: block; font-size: .62rem; font-weight: 600; text-transform: uppercase; }
    .row-title { color: var(--app-ink); display: block; font-size: .88rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .row-meta { color: var(--app-muted); display: block; font-size: .78rem; margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .row-item .ui-avatar { flex-basis: 36px; font-size: .78rem; height: 36px; justify-self: center; width: 36px; }

    .usage { display: grid; gap: 14px; }
    .usage-meta { color: var(--app-muted); display: flex; font-size: .82rem; justify-content: space-between; margin-bottom: 6px; }
    .usage-meta strong { color: var(--app-ink); font-weight: 600; }
    .usage .ui-meter span { background: var(--accent, var(--app-ink)); }

    @media (max-width: 1199.98px) { .dash-grid, .dash-grid.thirds { grid-template-columns: 1fr; } }
    @media (max-width: 767.98px) { .chart-box { height: 220px; } }
</style>
@endpush

@section('content')
<div class="dash">
    <x-app.page-header :title="$greeting . ', ' . $firstName" :subtitle="ucfirst(now()->locale('fr')->translatedFormat('l d F Y'))" />

    <section class="ui-stats">
        @foreach($kpis as $kpi)
            <article class="ui-stat">
                <div class="ui-stat-top">{{ $kpi['label'] }}<i class="bi {{ $kpi['icon'] }}"></i></div>
                <strong>{{ $kpi['value'] }}</strong>
                <small>{{ $kpi['note'] }}</small>
            </article>
        @endforeach
    </section>

    <div class="dash-grid">
        <section class="ui-card">
            <div class="ui-card-head">
                <div><h2>Activité</h2><p>Invitations, réponses et confirmations par jour.</p></div>
                <div class="range" role="group" aria-label="Période">
                    @foreach([7 => '7 j', 30 => '30 j', 90 => '90 j'] as $value => $label)
                        <button type="button" data-range="{{ $value }}" @class(['is-active' => $value === 30])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            <div class="legend">
                <span><i style="background:#2f6db5"></i>Invitations <strong data-total="invitations">0</strong></span>
                <span><i style="background:#c48a2c"></i>Réponses <strong data-total="responses">0</strong></span>
                <span><i style="background:#1f8a5f"></i>Confirmations <strong data-total="confirmations">0</strong></span>
            </div>
            <div class="chart-box"><canvas id="activityChart" role="img" aria-label="Évolution quotidienne des invitations, réponses et confirmations"></canvas></div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head"><div><h2>Réponses des invités</h2><p>Tous événements confondus.</p></div></div>
            <div class="ui-card-body pt-0">
                @if($registrationsCount)
                    <div class="rate"><strong>{{ $confirmationRate }} %</strong><span>de présences confirmées</span></div>
                    <div class="bars">
                        @foreach($responseBreakdown as $row)
                            @php $pct = round($row['value'] / $registrationsCount * 100); @endphp
                            <div>
                                <div class="bar-meta"><span>{{ $row['label'] }}</span><strong>{{ $row['value'] }}<em>{{ $pct }} %</em></strong></div>
                                <div class="ui-meter" style="--bar: {{ $row['color'] }}"><span style="width: {{ $pct }}%"></span></div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="ui-empty py-4"><i class="bi bi-inbox"></i>Les réponses apparaîtront dès vos premières invitations.</div>
                @endif
            </div>
        </section>
    </div>

    <div class="dash-grid thirds">
        <section class="ui-card">
            <div class="ui-card-head">
                <div><h2>Prochains événements</h2></div>
                <a href="{{ route('events.index') }}" class="card-link">Tout voir</a>
            </div>
            @if($agenda->isNotEmpty())
                <div class="rows">
                    @foreach($agenda as $event)
                        <a href="{{ route('events.show', $event) }}" class="row-item">
                            <div class="date-chip"><strong>{{ $event->date_debut->format('d') }}</strong><span>{{ $event->date_debut->locale('fr')->translatedFormat('M') }}</span></div>
                            <div><span class="row-title">{{ $event->titre }}</span><span class="row-meta">{{ $event->registrations_count }} inscrit(s) · {{ $event->lieu ?: 'Lieu à confirmer' }}</span></div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="ui-empty py-4"><i class="bi bi-calendar-plus"></i>Aucun événement à venir.<div class="mt-2"><a href="{{ route('events.create') }}" class="card-link">Créer un événement</a></div></div>
            @endif
        </section>

        <section class="ui-card">
            <div class="ui-card-head"><div><h2>Dernières réponses</h2></div></div>
            @if($recentRegistrations->isNotEmpty())
                <div class="rows">
                    @foreach($recentRegistrations as $registration)
                        <div class="row-item">
                            <span class="ui-avatar">{{ strtoupper(mb_substr($registration->prenom ?: $registration->email, 0, 1)) }}</span>
                            <div>
                                <span class="row-title">{{ $registration->nom_complet ?: $registration->email }}</span>
                                <span class="row-meta">{{ optional($registration->event)->titre ?: 'Événement supprimé' }} · {{ optional($registration->date_inscription)?->locale('fr')->diffForHumans() }}</span>
                            </div>
                            <span class="ui-pill {{ $responseTones[$registration->statut_reponse] ?? '' }}">{{ $responseLabels[$registration->statut_reponse] ?? ucfirst($registration->statut_reponse) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ui-empty py-4"><i class="bi bi-inbox"></i>Aucune réponse pour le moment.</div>
            @endif
        </section>

        <section class="ui-card">
            <div class="ui-card-head">
                <div><h2>Forfait {{ $plan['name'] }}</h2><p>{{ $billingCycle === 'yearly' ? 'Facturation annuelle' : 'Facturation mensuelle' }}</p></div>
                @if($user->isSuperAdmin())<a href="{{ route('saas.plans') }}" class="card-link">Gérer</a>@endif
            </div>
            <div class="ui-card-body pt-0">
                <div class="usage">
                    @foreach($usage as $row)
                        @php $pct = $row['limit'] ? min(100, round($row['used'] / $row['limit'] * 100)) : 0; @endphp
                        <div style="--accent: {{ $pct >= 90 ? 'var(--app-red)' : 'var(--app-ink)' }}">
                            <div class="usage-meta"><strong>{{ $row['label'] }}</strong><span>{{ number_format($row['used'], 0, ',', ' ') }} / {{ $row['limit'] ? number_format($row['limit'], 0, ',', ' ') : '∞' }}</span></div>
                            <div class="ui-meter"><span style="width: {{ $row['limit'] ? $pct : 0 }}%"></span></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
    const canvas = document.getElementById('activityChart');
    if (!canvas || !window.Chart) return;
    const activity = @json($activity);
    const fmt = new Intl.NumberFormat('fr-FR');
    Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#7a746a';
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) Chart.defaults.animation = false;

    const ds = (label, key, color) => ({ label, key, data: [], borderColor: color, backgroundColor: color, borderWidth: 2, cubicInterpolationMode: 'monotone',
        pointRadius: 0, pointHoverRadius: 4, pointHoverBorderWidth: 2, pointHoverBorderColor: '#fff' });
    const chart = new Chart(canvas, {
        type: 'line',
        data: { labels: [], datasets: [ds('Invitations', 'invitations', '#2f6db5'), ds('Réponses', 'responses', '#c48a2c'), ds('Confirmations', 'confirmations', '#1f8a5f')] },
        options: {
            maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: { backgroundColor: '#171713', padding: 10, cornerRadius: 8, boxPadding: 4, usePointStyle: true } },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { maxTicksLimit: 7, maxRotation: 0 } },
                y: { beginAtZero: true, grid: { color: 'rgba(23, 23, 19, .07)' }, border: { display: false }, ticks: { precision: 0, maxTicksLimit: 5 } },
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
})();
</script>
@endpush
