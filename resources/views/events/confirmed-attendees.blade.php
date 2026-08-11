@extends('layouts.app')

@section('title', 'Présences confirmées - ' . $event->titre)

@push('styles')
<style>
    .confirmed-page {
        --ink: #171713;
        --muted: #746f65;
        --line: #ded6c8;
        --panel: #fffefa;
        --soft: #f8f4ec;
        --gold: #b98943;
        max-width: 1280px;
        margin: 0 auto;
    }
    .confirmed-header,
    .filter-panel,
    .table-panel,
    .stat-card {
        background: var(--panel);
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 18px;
        box-shadow: 0 18px 42px rgba(39, 33, 25, .055);
    }
    .confirmed-header {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        justify-content: space-between;
        margin-bottom: 18px;
        padding: 22px;
    }
    .confirmed-header h1 {
        color: var(--ink);
        font-size: clamp(1.6rem, 3vw, 2.4rem);
        margin: 0;
    }
    .confirmed-header p {
        color: var(--muted);
        margin: 6px 0 0;
    }
    .action-btn {
        align-items: center;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--ink);
        display: inline-flex;
        gap: 8px;
        min-height: 40px;
        padding: 0 13px;
        text-decoration: none;
    }
    .action-btn.primary {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }
    .stats-grid {
        display: grid;
        gap: 12px;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        margin-bottom: 18px;
    }
    .stat-card {
        padding: 18px;
    }
    .stat-card span {
        color: var(--muted);
        display: block;
        font-size: .78rem;
        text-transform: uppercase;
    }
    .stat-card strong {
        color: var(--ink);
        display: block;
        font-size: 2rem;
        margin-top: 6px;
    }
    .filter-panel {
        margin-bottom: 18px;
        padding: 18px;
    }
    .form-control,
    .form-select {
        border-color: var(--line);
        border-radius: 12px;
        min-height: 42px;
    }
    .table-panel {
        overflow: hidden;
    }
    .table thead th {
        background: var(--soft);
        border-bottom-color: var(--line);
        color: var(--muted);
        font-size: .76rem;
        letter-spacing: .06em;
        text-transform: uppercase;
    }
    .badge-presence {
        border-radius: 999px;
        display: inline-flex;
        font-size: .8rem;
        font-weight: 600;
        padding: 6px 10px;
    }
    .badge-presence.direct { background: rgba(46, 123, 101, .12); color: #2e7b65; }
    .badge-presence.represente { background: rgba(185, 137, 67, .16); color: #725322; }
    .muted-line { color: var(--muted); font-size: .86rem; }
    @media (max-width: 900px) { .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px) { .stats-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="confirmed-page">
    <div class="confirmed-header">
        <div>
            <h1>Présences confirmées</h1>
            <p>{{ $event->titre }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('events.show', $event) }}" class="action-btn"><i class="bi bi-arrow-left"></i>Événement</a>
            <a href="{{ route('events.registrations', $event) }}" class="action-btn"><i class="bi bi-people"></i>Toutes les inscriptions</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card"><span>Total confirmé</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="stat-card"><span>Présences directes</span><strong>{{ $stats['directs'] }}</strong></div>
        <div class="stat-card"><span>Représentants confirmés</span><strong>{{ $stats['representes'] }}</strong></div>
        <div class="stat-card"><span>Cartes envoyées</span><strong>{{ $stats['cartes_envoyees'] }}</strong></div>
    </div>

    <form method="GET" class="filter-panel">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <label class="form-label">Recherche</label>
                <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, téléphone, entreprise...">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">Tous</option>
                    <option value="direct" @selected(request('type') === 'direct')>Direct</option>
                    <option value="represente" @selected(request('type') === 'represente')>Représenté</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Carte</label>
                <select name="card" class="form-select">
                    <option value="">Toutes</option>
                    <option value="sent" @selected(request('card') === 'sent')>Envoyée</option>
                    <option value="not_sent" @selected(request('card') === 'not_sent')>Non envoyée</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Du</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Au</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="action-btn primary"><i class="bi bi-funnel"></i>Filtrer</button>
                <a href="{{ route('events.confirmed-attendees', $event) }}" class="action-btn"><i class="bi bi-x-circle"></i>Réinitialiser</a>
            </div>
        </div>
    </form>

    <div class="table-panel">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Invité</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Confirmation</th>
                        <th>Carte</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendees as $registration)
                        @php
                            $isRepresented = $registration->statut_reponse === 'represente';
                            $representativeName = trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? ''));
                            $cardSent = $isRepresented ? $registration->representant_carte_envoyee : $registration->carte_envoyee;
                            $confirmedAt = $isRepresented ? $registration->representant_confirme_le : $registration->date_reponse;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $registration->nom_complet ?: '-' }}</strong>
                                <div class="muted-line">{{ $registration->entreprise ?: 'Entreprise non renseignée' }}</div>
                                @if($isRepresented)
                                    <div class="muted-line">Représentant : {{ $representativeName ?: $registration->representant_email }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge-presence {{ $isRepresented ? 'represente' : 'direct' }}">
                                    {{ $isRepresented ? 'Représenté' : 'Direct' }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $isRepresented ? $registration->representant_email : $registration->email }}</div>
                                <div class="muted-line">{{ $isRepresented ? ($registration->representant_contact ?: '-') : ($registration->telephone ?: '-') }}</div>
                            </td>
                            <td>{{ $confirmedAt?->format('d/m/Y H:i') ?: '-' }}</td>
                            <td>
                                @if($cardSent)
                                    <span class="badge bg-success">Envoyée</span>
                                @else
                                    <span class="badge bg-secondary">Non envoyée</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('events.confirmed-attendees.show', [$event, $registration]) }}" class="action-btn"><i class="bi bi-eye"></i>Détail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Aucune présence confirmée ne correspond aux filtres.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($attendees->hasPages())
        <div class="d-flex justify-content-center mt-4">{{ $attendees->links() }}</div>
    @endif
</div>
@endsection
