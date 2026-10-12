@extends('layouts.app')

@section('title', 'Présences · ' . $event->titre)

@php
    $hasFilters = collect(request()->only(['search', 'type', 'card', 'date_from', 'date_to']))->filter()->isNotEmpty();
    $total = (int) $stats['total'];
@endphp

@push('styles')
<style>
    .confirmed-page { margin: 0 auto; max-width: 1400px; }
    .confirmed-page *, .confirmed-page *::before, .confirmed-page *::after { min-width: 0; }
    .type-tabs { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 2px; padding: 3px; }
    .type-tabs a { border-radius: 999px; color: var(--app-muted); font-size: .8rem; font-weight: 600; padding: 7px 13px; text-decoration: none; white-space: nowrap; }
    .type-tabs a.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }
    @media (max-width: 991.98px) {
        .att-table thead { display: none; }
        .att-table tr { border-bottom: 1px solid var(--app-line); display: grid; gap: 8px; grid-template-columns: 1fr auto; padding: 14px 18px; }
        .att-table td { border: 0 !important; padding: 0 !important; }
        .att-table td.col-hide { display: none; }
        .att-table td.col-actions { grid-column: 2; grid-row: 1; }
    }
</style>
@endpush

@section('content')
<div class="confirmed-page">
    <x-app.event-header :event="$event" active="confirmed-attendees" />

    <section class="ui-stats">
        <article class="ui-stat" style="--accent: var(--app-green)"><div class="ui-stat-top">Présences confirmées<i class="bi bi-person-check"></i></div><strong>{{ number_format($total, 0, ',', ' ') }}</strong><small>Invités et représentants</small></article>
        <article class="ui-stat" style="--accent: var(--app-ink)"><div class="ui-stat-top">Présences directes<i class="bi bi-person"></i></div><strong>{{ number_format($stats['directs'], 0, ',', ' ') }}</strong><small>{{ $total ? round($stats['directs'] / $total * 100) : 0 }}% du total</small><div class="ui-meter"><span style="width: {{ $total ? round($stats['directs'] / $total * 100) : 0 }}%"></span></div></article>
        <article class="ui-stat" style="--accent: var(--app-gold)"><div class="ui-stat-top">Représentants<i class="bi bi-person-badge"></i></div><strong>{{ number_format($stats['representes'], 0, ',', ' ') }}</strong><small>{{ $total ? round($stats['representes'] / $total * 100) : 0 }}% du total</small><div class="ui-meter"><span style="width: {{ $total ? round($stats['representes'] / $total * 100) : 0 }}%"></span></div></article>
        <article class="ui-stat" style="--accent: var(--app-blue)"><div class="ui-stat-top">Cartes envoyées<i class="bi bi-postcard"></i></div><strong>{{ number_format($stats['cartes_envoyees'], 0, ',', ' ') }}</strong><small>{{ $total ? round($stats['cartes_envoyees'] / $total * 100) : 0 }}% des confirmés</small><div class="ui-meter"><span style="width: {{ $total ? min(100, round($stats['cartes_envoyees'] / $total * 100)) : 0 }}%"></span></div></article>
    </section>

    <section class="ui-card" style="overflow: hidden">
        <div class="ui-card-head">
            <nav class="type-tabs" aria-label="Type de présence">
                @foreach(['' => 'Toutes', 'direct' => 'Directes', 'represente' => 'Représentées'] as $value => $label)
                    <a href="{{ route('events.confirmed-attendees', array_merge([$event], array_filter(array_merge(request()->except(['type', 'page']), ['type' => $value])))) }}" @class(['is-active' => (string) request('type', '') === (string) $value])>{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" class="ui-toolbar" style="flex: 1 1 480px; justify-content: flex-end">
                @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
                <label class="ui-search mb-0" style="max-width: 300px">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, entreprise…" aria-label="Rechercher">
                </label>
                <select name="card" class="form-select" aria-label="Carte">
                    <option value="">Carte</option>
                    <option value="sent" @selected(request('card') === 'sent')>Envoyée</option>
                    <option value="not_sent" @selected(request('card') === 'not_sent')>Non envoyée</option>
                </select>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" aria-label="Confirmé à partir du">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" aria-label="Confirmé jusqu’au">
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">Filtrer</button>
                @if($hasFilters)<a href="{{ route('events.confirmed-attendees', $event) }}" class="ui-btn ui-btn-soft ui-btn-sm" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a>@endif
            </form>
        </div>

        @if($attendees->count())
            <div class="table-responsive">
                <table class="ui-table att-table">
                    <thead><tr><th>Participant</th><th>Type</th><th>Contact</th><th>Confirmé le</th><th>Carte</th><th class="text-end"></th></tr></thead>
                    <tbody>
                        @foreach($attendees as $registration)
                            @php
                                $isRepresented = $registration->statut_reponse === 'represente';
                                $representativeName = trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? ''));
                                $cardSent = $isRepresented ? $registration->representant_carte_envoyee : $registration->carte_envoyee;
                                $confirmedAt = $isRepresented ? $registration->representant_confirme_le : $registration->date_reponse;
                            @endphp
                            <tr>
                                <td>
                                    <div class="ui-person">
                                        <span @class(['ui-avatar', 'gold' => $isRepresented])>{{ strtoupper(mb_substr($registration->prenom ?: $registration->nom ?: $registration->email, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $registration->nom_complet ?: '—' }}</strong>
                                            <small>{{ $isRepresented ? 'Représenté par ' . ($representativeName ?: $registration->representant_email) : ($registration->entreprise ?: 'Entreprise non renseignée') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="col-hide"><span class="ui-pill dot {{ $isRepresented ? 'gold' : 'success' }}">{{ $isRepresented ? 'Représenté' : 'Direct' }}</span></td>
                                <td class="col-hide">{{ $isRepresented ? $registration->representant_email : $registration->email }}<span class="ui-sub">{{ $isRepresented ? ($registration->representant_contact ?: '—') : ($registration->telephone ?: '—') }}</span></td>
                                <td class="col-hide">{{ $confirmedAt?->format('d/m/Y') ?: '—' }}<span class="ui-sub">{{ $confirmedAt?->format('H:i') }}</span></td>
                                <td class="col-hide">
                                    @if($cardSent)
                                        <span class="ui-pill success"><i class="bi bi-check2"></i>Envoyée</span>
                                    @else
                                        <span class="ui-pill">Non envoyée</span>
                                    @endif
                                </td>
                                <td class="col-actions"><div class="ui-actions"><a href="{{ route('events.confirmed-attendees.show', [$event, $registration]) }}" class="ui-btn ui-btn-soft ui-btn-sm">Détail <i class="bi bi-chevron-right"></i></a></div></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($attendees->hasPages())
                <div class="ui-pager">{{ $attendees->links('pagination::bootstrap-5') }}</div>
            @endif
        @else
            <div class="ui-empty">
                <i class="bi bi-person-check"></i>
                <strong>Aucune présence confirmée</strong>
                {{ $hasFilters ? 'Aucun participant ne correspond à ces critères.' : 'Les invités qui confirment leur venue apparaîtront ici.' }}
            </div>
        @endif
    </section>
</div>
@endsection
