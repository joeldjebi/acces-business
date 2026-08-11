@extends('layouts.app')

@section('title', 'Check-in - ' . $event->titre)

@push('styles')
<style>
    .checkin-page { --ink:#171713; --muted:#746f65; --line:#ded6c8; --panel:#fffefa; --soft:#f8f4ec; --green:#2e7b65; --red:#a4514a; max-width:1280px; margin:0 auto; color:#2c2a25; }
    .checkin-head, .checkin-card { background:var(--panel); border:1px solid rgba(222,214,200,.78); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); }
    .checkin-head { display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; margin-bottom:18px; padding:22px; }
    .checkin-head h1 { color:var(--ink); font-size:clamp(1.5rem,3vw,2.3rem); margin:0; }
    .checkin-head p { color:var(--muted); margin:6px 0 0; }
    .action-btn { align-items:center; border:1px solid var(--line); border-radius:12px; color:var(--ink); display:inline-flex; gap:8px; justify-content:center; min-height:40px; padding:0 13px; text-decoration:none; background:#fffefa; }
    .action-btn.primary { background:var(--ink); border-color:var(--ink); color:#fff; }
    .action-btn.success { background:var(--green); border-color:var(--green); color:#fff; }
    .stats-grid { display:grid; gap:12px; grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:18px; }
    .stat { background:var(--panel); border:1px solid rgba(222,214,200,.78); border-radius:16px; padding:18px; }
    .stat span { color:var(--muted); display:block; font-size:.75rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; }
    .stat strong { color:var(--ink); display:block; font-size:2rem; margin-top:8px; }
    .checkin-grid { display:grid; gap:18px; grid-template-columns:minmax(0,1.6fr) minmax(300px,.75fr); }
    .checkin-card { overflow:hidden; }
    .card-head { border-bottom:1px solid var(--line); padding:18px 20px; }
    .card-head h2 { font-size:1.05rem; margin:0; }
    .card-body { padding:20px; }
    .filters { display:grid; gap:10px; grid-template-columns:minmax(220px,1fr) 160px auto; }
    .table { margin:0; }
    .table th { color:var(--muted); font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; }
    .badge-soft { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:700; padding:6px 10px; }
    .badge-ok { background:rgba(46,123,101,.12); color:var(--green); }
    .badge-wait { background:rgba(185,137,67,.14); color:#8a6128; }
    .timeline { display:grid; gap:12px; }
    .timeline-item { border:1px solid rgba(222,214,200,.72); border-radius:14px; padding:12px; }
    .timeline-item strong { display:block; }
    .timeline-item span { color:var(--muted); font-size:.84rem; }
    @media(max-width:980px){ .stats-grid,.checkin-grid{grid-template-columns:1fr 1fr;} .filters{grid-template-columns:1fr;} }
    @media(max-width:680px){ .stats-grid,.checkin-grid{grid-template-columns:1fr;} }
</style>
@endpush

@section('content')
<div class="checkin-page">
    <div class="checkin-head">
        <div>
            <h1>Check-in live</h1>
            <p>{{ $event->titre }} · {{ optional($event->date_debut)->format('d/m/Y') ?: '-' }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('events.show', $event) }}" class="action-btn"><i class="bi bi-arrow-left"></i>Événement</a>
            <a href="{{ route('events.registrations', $event) }}" class="action-btn"><i class="bi bi-people"></i>Inscriptions</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>{{ session('error') }}</div>@endif

    <section class="stats-grid">
        <article class="stat"><span>Éligibles</span><strong>{{ $stats['eligible'] }}</strong></article>
        <article class="stat"><span>Checkés</span><strong>{{ $stats['checked_in'] }}</strong></article>
        <article class="stat"><span>À l'entrée</span><strong>{{ $stats['waiting'] }}</strong></article>
        <article class="stat"><span>Représentants</span><strong>{{ $stats['represented'] }}</strong></article>
    </section>

    <div class="checkin-grid">
        <main class="checkin-card">
            <div class="card-head">
                <h2>Scanner ou saisir un token</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('events.check-in.token', $event) }}" class="filters mb-3">
                    @csrf
                    <input type="text" name="token" class="form-control" placeholder="Coller/scanner le token QR" autocomplete="off" autofocus required>
                    <button type="submit" class="action-btn primary"><i class="bi bi-qr-code-scan"></i>Valider</button>
                    <a href="{{ route('events.check-in', $event) }}" class="action-btn"><i class="bi bi-arrow-clockwise"></i>Actualiser</a>
                </form>

                <form method="GET" action="{{ route('events.check-in', $event) }}" class="filters mb-3">
                    <input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nom, email, téléphone, représentant, token">
                    <select name="status" class="form-select">
                        <option value="">Tous</option>
                        <option value="waiting" @selected(request('status') === 'waiting')>À checker</option>
                        <option value="checked_in" @selected(request('status') === 'checked_in')>Déjà checkés</option>
                    </select>
                    <button class="action-btn"><i class="bi bi-funnel"></i>Filtrer</button>
                </form>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead><tr><th>Participant</th><th>Type</th><th>Statut</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>
                                        <strong>{{ $registration->checkInDisplayName() }}</strong>
                                        <div class="text-muted small">{{ $registration->isRepresented() ? $registration->representant_email : $registration->email }}</div>
                                        <div class="text-muted small">Token: {{ $registration->token_unique }}</div>
                                    </td>
                                    <td>{{ $registration->isRepresented() ? 'Représentant' : 'Invité' }}</td>
                                    <td>
                                        @if($registration->checked_in_at)
                                            <span class="badge-soft badge-ok">Checké {{ $registration->checked_in_at->format('H:i') }}</span>
                                        @else
                                            <span class="badge-soft badge-wait">En attente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('events.registrations.check-in', [$event, $registration]) }}">
                                            @csrf
                                            <button class="action-btn success" type="submit"><i class="bi bi-check2-circle"></i>Check-in</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Aucun participant trouvé.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $registrations->links() }}
            </div>
        </main>

        <aside class="checkin-card">
            <div class="card-head"><h2>Derniers scans</h2></div>
            <div class="card-body">
                <div class="timeline">
                    @forelse($recentActivities as $activity)
                        <div class="timeline-item">
                            <strong>{{ $activity->title }}</strong>
                            <span>{{ $activity->registration?->checkInDisplayName() ?: 'Participant' }} · {{ $activity->created_at->format('d/m H:i') }}</span>
                            @if($activity->actor)<span>Par {{ $activity->actor->name }}</span>@endif
                        </div>
                    @empty
                        <div class="text-muted">Aucun scan pour le moment.</div>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
