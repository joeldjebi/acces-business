@extends('layouts.app')

@section('title', 'Plateforme')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --gold:#b98943; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .platform h1 { color:var(--ink); font-size:clamp(1.8rem,2.8vw,2.8rem); font-weight:600; margin:0 0 8px; }
    .platform-copy { color:var(--muted); margin:0 0 22px; }
    .metric-grid { display:grid; gap:14px; grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:18px; }
    .metric, .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .metric span { color:var(--muted); display:block; font-size:.78rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
    .metric strong { color:var(--ink); display:block; font-size:2rem; font-weight:600; margin-top:10px; }
    .muted { color:var(--muted); }
    .platform-table { border-collapse:separate; border-spacing:0; width:100%; }
    .platform-table th { color:var(--muted); font-size:.74rem; font-weight:600; letter-spacing:.08em; padding:0 14px 12px; text-transform:uppercase; }
    .platform-table td { border-top:1px solid var(--line); padding:14px; vertical-align:middle; }
    .org-name { color:var(--ink); font-weight:600; }
    .status { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; justify-content:center; padding:6px 10px; }
    .status.active, .status.trialing { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status.suspended, .status.cancelled { background:rgba(164,81,74,.13); color:#a4514a; }
    @media (max-width:1100px){ .metric-grid{grid-template-columns:repeat(2,minmax(0,1fr));} .table-responsive{border:0;} }
    @media (max-width:640px){ .metric-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="platform">
    <h1>Supervision plateforme</h1>
    <p class="platform-copy">Vue globale des organisations, volumes, revenus en attente et activité SaaS.</p>

    <section class="metric-grid">
        <article class="metric"><span>Organisations</span><strong>{{ $organizationsCount }}</strong></article>
        <article class="metric"><span>Actives</span><strong>{{ $activeOrganizations }}</strong></article>
        <article class="metric"><span>Utilisateurs</span><strong>{{ $usersCount }}</strong></article>
        <article class="metric"><span>Revenu pending</span><strong>{{ number_format($pendingRevenue, 0, ',', ' ') }} XOF</strong></article>
    </section>

    <section class="metric-grid">
        <article class="metric"><span>Essais actifs</span><strong>{{ $trialOrganizations }}</strong></article>
        <article class="metric"><span>Événements</span><strong>{{ $eventsCount }}</strong></article>
        <article class="metric"><span>Inscriptions</span><strong>{{ $registrationsCount }}</strong></article>
        <article class="metric"><span>Séparation tenant</span><strong>OK</strong></article>
    </section>

    <section class="panel">
        <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1" style="font-weight:600;">10 derniers clients enregistrés</h2>
                <div class="muted">Organisations, entreprises ou agences récemment créées.</div>
            </div>
            <a href="{{ route('platform.organizations') }}" class="btn btn-outline-dark">Voir tout</a>
        </div>

        <div class="table-responsive">
            <table class="platform-table">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Plan</th>
                        <th>Utilisateurs</th>
                        <th>Événements</th>
                        <th>Statut</th>
                        <th>Créé le</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $organization)
                        <tr>
                            <td>
                                <div class="org-name">{{ $organization->name }}</div>
                                <div class="muted">{{ $organization->slug }}</div>
                            </td>
                            <td>{{ ucfirst($organization->plan) }}</td>
                            <td>{{ $organization->users_count }}</td>
                            <td>{{ $organization->events_count }}</td>
                            <td><span class="status {{ $organization->status }}">{{ ucfirst($organization->status) }}</span></td>
                            <td>{{ optional($organization->created_at)->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted text-center py-4">Aucun client enregistré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
