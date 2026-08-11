@extends('layouts.app')

@section('title', 'Détail présence - ' . $event->titre)

@push('styles')
<style>
    .attendee-detail { --ink:#171713; --muted:#746f65; --line:#ded6c8; --panel:#fffefa; --soft:#f8f4ec; max-width: 980px; margin: 0 auto; }
    .detail-head, .detail-card { background: var(--panel); border:1px solid rgba(222,214,200,.78); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); }
    .detail-head { display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; margin-bottom:18px; padding:22px; }
    .detail-head h1 { color:var(--ink); font-size:clamp(1.5rem,3vw,2.2rem); margin:0; }
    .detail-head p { color:var(--muted); margin:6px 0 0; }
    .detail-card { margin-bottom:18px; overflow:hidden; }
    .detail-card h2 { border-bottom:1px solid var(--line); color:var(--ink); font-size:1.05rem; margin:0; padding:18px 20px; }
    .info-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:0; }
    .info-item { border-bottom:1px solid rgba(222,214,200,.7); padding:16px 20px; }
    .info-item:nth-child(odd) { border-right:1px solid rgba(222,214,200,.7); }
    .info-label { color:var(--muted); font-size:.76rem; letter-spacing:.08em; text-transform:uppercase; }
    .info-value { color:var(--ink); font-weight:600; margin-top:4px; overflow-wrap:anywhere; }
    .action-btn { align-items:center; border:1px solid var(--line); border-radius:12px; color:var(--ink); display:inline-flex; gap:8px; min-height:40px; padding:0 13px; text-decoration:none; }
    .action-btn.primary { background:var(--ink); border-color:var(--ink); color:#fff; }
    @media(max-width:720px){ .info-grid{grid-template-columns:1fr;} .info-item:nth-child(odd){border-right:0;} }
</style>
@endpush

@section('content')
@php
    $isRepresented = $registration->statut_reponse === 'represente';
    $representativeName = trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? ''));
@endphp
<div class="attendee-detail">
    <div class="detail-head">
        <div>
            <h1>Détail de la présence</h1>
            <p>{{ $event->titre }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('events.confirmed-attendees', $event) }}" class="action-btn"><i class="bi bi-arrow-left"></i>Présences confirmées</a>
            <a href="{{ route('events.verify-qr', $registration->token_unique) }}" target="_blank" class="action-btn primary"><i class="bi bi-qr-code"></i>Voir QR</a>
        </div>
    </div>

    <section class="detail-card">
        <h2>Invité initial</h2>
        <div class="info-grid">
            <div class="info-item"><div class="info-label">Nom complet</div><div class="info-value">{{ $registration->nom_complet ?: '-' }}</div></div>
            <div class="info-item"><div class="info-label">Email</div><div class="info-value">{{ $registration->email ?: '-' }}</div></div>
            <div class="info-item"><div class="info-label">Téléphone</div><div class="info-value">{{ $registration->telephone ?: '-' }}</div></div>
            <div class="info-item"><div class="info-label">Entreprise</div><div class="info-value">{{ $registration->entreprise ?: '-' }}</div></div>
            <div class="info-item"><div class="info-label">Fonction</div><div class="info-value">{{ $registration->fonction ?: '-' }}</div></div>
            <div class="info-item"><div class="info-label">Réponse</div><div class="info-value">{{ $isRepresented ? 'S’est fait représenter' : 'Présence directe confirmée' }}</div></div>
            <div class="info-item"><div class="info-label">Date réponse</div><div class="info-value">{{ $registration->date_reponse?->format('d/m/Y H:i') ?: '-' }}</div></div>
            <div class="info-item"><div class="info-label">Carte invité</div><div class="info-value">{{ $registration->carte_envoyee ? 'Envoyée' : 'Non envoyée' }}</div></div>
        </div>
    </section>

    @if($isRepresented)
        <section class="detail-card">
            <h2>Représentant</h2>
            <div class="info-grid">
                <div class="info-item"><div class="info-label">Nom complet</div><div class="info-value">{{ $representativeName ?: '-' }}</div></div>
                <div class="info-item"><div class="info-label">Email</div><div class="info-value">{{ $registration->representant_email ?: '-' }}</div></div>
                <div class="info-item"><div class="info-label">Contact</div><div class="info-value">{{ $registration->representant_contact ?: '-' }}</div></div>
                <div class="info-item"><div class="info-label">Fonction</div><div class="info-value">{{ $registration->representant_fonction ?: '-' }}</div></div>
                <div class="info-item"><div class="info-label">Statut</div><div class="info-value">{{ $registration->representant_statut ?: '-' }}</div></div>
                <div class="info-item"><div class="info-label">Confirmé le</div><div class="info-value">{{ $registration->representant_confirme_le?->format('d/m/Y H:i') ?: '-' }}</div></div>
                <div class="info-item"><div class="info-label">Mail confirmation</div><div class="info-value">{{ $registration->representant_mail_envoye ? 'Envoyé' : 'Non envoyé' }} {{ $registration->representant_mail_envoye_le ? '· ' . $registration->representant_mail_envoye_le->format('d/m/Y H:i') : '' }}</div></div>
                <div class="info-item"><div class="info-label">Carte représentant</div><div class="info-value">{{ $registration->representant_carte_envoyee ? 'Envoyée' : 'Non envoyée' }} {{ $registration->representant_carte_envoyee_le ? '· ' . $registration->representant_carte_envoyee_le->format('d/m/Y H:i') : '' }}</div></div>
                <div class="info-item"><div class="info-label">Notification invité</div><div class="info-value">{{ $registration->represente_notification_envoyee ? 'Envoyée' : 'Non envoyée' }} {{ $registration->represente_notification_envoyee_le ? '· ' . $registration->represente_notification_envoyee_le->format('d/m/Y H:i') : '' }}</div></div>
                <div class="info-item"><div class="info-label">IP confirmation</div><div class="info-value">{{ $registration->representant_confirmation_ip ?: '-' }}</div></div>
            </div>
        </section>
    @endif
</div>
@endsection
