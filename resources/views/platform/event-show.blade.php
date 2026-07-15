@extends('layouts.app')

@section('title', 'Détail événement')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1480px; margin:0 auto; color:#2c2a25; }
    .panel,.metric { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .muted { color:var(--muted); }
    .grid { display:grid; gap:18px; }
    .metrics { grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:18px; }
    .main-grid { grid-template-columns:minmax(0,1.1fr) minmax(360px,.9fr); align-items:start; }
    .metric span { color:var(--muted); display:block; font-size:.74rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
    .metric strong { color:var(--ink); display:block; font-size:1.85rem; font-weight:600; margin-top:8px; }
    .section-title { color:var(--ink); font-size:1rem; font-weight:600; margin:0 0 4px; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; white-space:nowrap; }
    .status-active,.status-publie,.status-present,.status-paid,.status-verified { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-annule,.status-absent,.status-expired { background:rgba(164,81,74,.13); color:#a4514a; }
    .status-brouillon,.status-en_attente,.status-pending { background:rgba(185,137,67,.14); color:#8a6128; }
    .detail-grid { display:grid; gap:12px; grid-template-columns:repeat(2,minmax(0,1fr)); }
    .detail-item { background:var(--soft); border:1px solid var(--line); border-radius:14px; padding:13px; }
    .detail-item span { color:var(--muted); display:block; font-size:.74rem; font-weight:600; letter-spacing:.07em; text-transform:uppercase; }
    .detail-item strong { color:var(--ink); display:block; font-weight:600; margin-top:5px; overflow-wrap:anywhere; }
    .mini-table { border-collapse:separate; border-spacing:0; width:100%; }
    .mini-table th { color:var(--muted); font-size:.72rem; font-weight:600; letter-spacing:.07em; padding:0 10px 10px; text-transform:uppercase; white-space:nowrap; }
    .mini-table td { border-top:1px solid var(--line); padding:12px 10px; vertical-align:middle; }
    @media (max-width:1100px){ .metrics,.main-grid,.detail-grid{grid-template-columns:1fr;} .mini-table{min-width:760px;} }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">{{ $organization->name }} · événement</div>
            <h1 class="mb-1" style="font-weight:600;">{{ $event->titre }}</h1>
            <p class="muted mb-0">{{ $event->slug }} · {{ optional($event->date_debut)->format('d/m/Y') ?: 'date non définie' }}</p>
        </div>
        <a href="{{ route('platform.organizations.show', $organization) }}#events" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left me-2"></i> Organisation
        </a>
    </div>

    <section class="grid metrics">
        <article class="metric"><span>Inscriptions</span><strong>{{ $registrationsCount }}</strong></article>
        <article class="metric"><span>Liens envoyés</span><strong>{{ $accessLinksCount }}</strong></article>
        <article class="metric"><span>OTP</span><strong>{{ $otpCount }}</strong></article>
        <article class="metric"><span>Vues</span><strong>{{ $event->vues ?? 0 }}</strong></article>
    </section>

    <div class="grid main-grid">
        <main class="grid">
            <section class="panel">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <div>
                        <h2 class="section-title">Informations événement</h2>
                        <p class="muted mb-0">Données principales, publication, visibilité et propriétaire.</p>
                    </div>
                    <span class="status-pill status-{{ $event->statut }}">{{ ucfirst($event->statut) }}</span>
                </div>
                <div class="detail-grid">
                    <div class="detail-item"><span>Catégorie</span><strong>{{ optional($event->category)->libelle ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Visibilité</span><strong>{{ optional($event->visibilite)->libelle ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Créateur</span><strong>{{ optional($event->user)->name ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Publication</span><strong>{{ optional($event->date_publication)->format('d/m/Y H:i') ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Début</span><strong>{{ optional($event->date_debut)->format('d/m/Y') ?: '-' }} {{ $event->heure_debut ?: '' }}</strong></div>
                    <div class="detail-item"><span>Fin</span><strong>{{ optional($event->date_fin)->format('d/m/Y') ?: '-' }} {{ $event->heure_fin ?: '' }}</strong></div>
                    <div class="detail-item"><span>Tarification</span><strong>{{ optional($event->typeTarification)->libelle ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Prix</span><strong>{{ $event->prix ? number_format((float) $event->prix, 0, ',', ' ') . ' ' . optional($event->devise)->libelle : 'Gratuit' }}</strong></div>
                </div>
                @if($event->description)
                    <div class="mt-3 muted">{!! nl2br(e($event->description)) !!}</div>
                @endif
            </section>

            <section class="panel">
                <h2 class="section-title">Inscriptions récentes</h2>
                <p class="muted">Participants et réponses associés à cet événement.</p>
                <div class="table-responsive">
                    <table class="mini-table">
                        <thead><tr><th>Participant</th><th>Contact</th><th>Fonction</th><th>Réponse</th><th>Inscription</th></tr></thead>
                        <tbody>
                            @forelse($registrations as $registration)
                                <tr>
                                    <td>
                                        <strong>{{ trim(($registration->prenom ?? '') . ' ' . ($registration->nom ?? '')) ?: 'Invité' }}</strong>
                                        <div class="muted">{{ $registration->entreprise ?: '-' }}</div>
                                    </td>
                                    <td>{{ $registration->email }}<div class="muted">{{ $registration->telephone ?: '-' }}</div></td>
                                    <td>{{ $registration->fonction ?: '-' }}</td>
                                    <td><span class="status-pill status-{{ $registration->statut_reponse }}">{{ str_replace('_', ' ', ucfirst($registration->statut_reponse)) }}</span></td>
                                    <td>{{ optional($registration->date_inscription)->format('d/m/Y H:i') ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="muted text-center py-4">Aucune inscription.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <aside class="grid">
            <section class="panel">
                <h2 class="section-title">Localisation & contact</h2>
                <div class="detail-grid">
                    <div class="detail-item"><span>Lieu</span><strong>{{ $event->lieu ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Ville</span><strong>{{ $event->ville ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Pays</span><strong>{{ $event->pays ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Code postal</span><strong>{{ $event->code_postal ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Organisateur</span><strong>{{ $event->organisateur ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Email</span><strong>{{ $event->email_contact ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Téléphone</span><strong>{{ $event->telephone ?: '-' }}</strong></div>
                    <div class="detail-item"><span>Site web</span><strong>{{ $event->site_web ?: '-' }}</strong></div>
                </div>
            </section>

            <section class="panel">
                <h2 class="section-title">Liens d'invitation</h2>
                @forelse($accessLinks as $link)
                    <div class="d-flex justify-content-between gap-3 py-2 border-top">
                        <div>
                            <strong>{{ $link->email_destinataire }}</strong>
                            <div class="muted">{{ optional($link->envoye_le)->format('d/m/Y H:i') ?: '-' }}</div>
                        </div>
                        <span class="status-pill {{ $link->est_utilise ? 'status-active' : 'status-pending' }}">{{ $link->est_utilise ? 'Utilisé' : 'Envoyé' }}</span>
                    </div>
                @empty
                    <div class="muted">Aucun lien envoyé.</div>
                @endforelse
            </section>

            <section class="panel">
                <h2 class="section-title">OTP</h2>
                @forelse($otpVerifications as $otp)
                    <div class="d-flex justify-content-between gap-3 py-2 border-top">
                        <div>
                            <strong>{{ $otp->email }}</strong>
                            <div class="muted">Expire: {{ optional($otp->expires_at)->format('d/m/Y H:i') ?: '-' }}</div>
                        </div>
                        <span class="status-pill {{ $otp->is_verified ? 'status-verified' : 'status-pending' }}">{{ $otp->is_verified ? 'Vérifié' : 'En attente' }}</span>
                    </div>
                @empty
                    <div class="muted">Aucun OTP.</div>
                @endforelse
            </section>
        </aside>
    </div>
</div>
@endsection
