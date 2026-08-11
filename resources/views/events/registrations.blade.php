@extends('layouts.app')

@section('title', 'Inscriptions - ' . $event->titre)

@push('styles')
<style>
    .registrations-container {
        max-width: 1200px;
        margin: 30px auto;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        text-align: center;
    }

    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 10px 0;
    }

    .stat-card.present h3 { color: #10b981; }
    .stat-card.peut-etre h3 { color: #f59e0b; }
    .stat-card.absent h3 { color: #ef4444; }
    .stat-card.represente h3 { color: #b98943; }
    .stat-card.total h3 { color: #667eea; }

    .badge-response {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-response.present {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-response.peut_etre {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-response.absent {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-response.represente {
        background: #f4e8d2;
        color: #725322;
    }

    .representative-details {
        color: #6b6258;
        font-size: .85rem;
        line-height: 1.45;
        margin-top: 6px;
    }

    .badge-response.en_attente {
        background: #f3f4f6;
        color: #374151;
    }

    .filter-card {
        background: #fff;
        border: 1px solid #e5ded2;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        margin-bottom: 24px;
        padding: 18px;
    }

    .filter-card .form-control,
    .filter-card .form-select {
        border-color: #ded6c8;
        border-radius: 12px;
        min-height: 40px;
    }
</style>
@endpush

@section('content')
<div class="registrations-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Inscriptions - {{ $event->titre }}</h1>
        <a href="{{ route('events.show', $event) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Retour à l'événement
        </a>
    </div>

    @php
        $presents = $stats['present'] ?? 0;
        $peutEtre = $stats['peut_etre'] ?? 0;
        $absents = $stats['absent'] ?? 0;
        $representes = $stats['represente'] ?? 0;
        $total = $stats['total'] ?? $registrations->total();
    @endphp

    <div class="stats-cards">
        <div class="stat-card total">
            <i class="bi bi-people" style="font-size: 2rem; color: #667eea;"></i>
            <h3>{{ $total }}</h3>
            <p class="text-muted">Total inscrits</p>
        </div>

        <div class="stat-card present">
            <i class="bi bi-check-circle" style="font-size: 2rem; color: #10b981;"></i>
            <h3>{{ $presents }}</h3>
            <p class="text-muted">Présents</p>
        </div>

        <div class="stat-card peut-etre">
            <i class="bi bi-question-circle" style="font-size: 2rem; color: #f59e0b;"></i>
            <h3>{{ $peutEtre }}</h3>
            <p class="text-muted">Peut-être</p>
        </div>

        <div class="stat-card absent">
            <i class="bi bi-x-circle" style="font-size: 2rem; color: #ef4444;"></i>
            <h3>{{ $absents }}</h3>
            <p class="text-muted">Absents</p>
        </div>

        <div class="stat-card represente">
            <i class="bi bi-person-badge" style="font-size: 2rem; color: #b98943;"></i>
            <h3>{{ $representes }}</h3>
            <p class="text-muted">Représentés</p>
        </div>
    </div>

    <form method="GET" class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Recherche</label>
                <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, téléphone...">
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Réponse</label>
                <select name="response" class="form-select">
                    <option value="">Toutes</option>
                    <option value="en_attente" @selected(request('response') === 'en_attente')>En attente</option>
                    <option value="present" @selected(request('response') === 'present')>Présent</option>
                    <option value="peut_etre" @selected(request('response') === 'peut_etre')>Peut-être</option>
                    <option value="absent" @selected(request('response') === 'absent')>Absent</option>
                    <option value="represente" @selected(request('response') === 'represente')>Représenté</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Représentant</label>
                <select name="representative_status" class="form-select">
                    <option value="">Tous</option>
                    <option value="en_attente" @selected(request('representative_status') === 'en_attente')>En attente</option>
                    <option value="mail_envoye" @selected(request('representative_status') === 'mail_envoye')>Mail envoyé</option>
                    <option value="mail_echec" @selected(request('representative_status') === 'mail_echec')>Mail échoué</option>
                    <option value="confirme" @selected(request('representative_status') === 'confirme')>Confirmé</option>
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
            <div class="col-lg-1 col-md-6">
                <label class="form-label">Par page</label>
                <select name="per_page" class="form-select">
                    @foreach([10, 20, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6 d-flex gap-2">
                <button type="submit" class="btn btn-dark flex-fill"><i class="bi bi-funnel me-1"></i>Filtrer</button>
                <a href="{{ route('events.registrations', $event) }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle"></i></a>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Entreprise</th>
                            <th>Fonction</th>
                            <th>Réponse</th>
                            <th>Date d'inscription</th>
                            <th>Carte envoyée</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $registration)
                        <tr>
                            <td>
                                <strong>{{ $registration->nom_complet ?: '-' }}</strong>
                            </td>
                            <td>{{ $registration->email }}</td>
                            <td>{{ $registration->telephone ?: '-' }}</td>
                            <td>{{ $registration->entreprise ?: '-' }}</td>
                            <td>{{ $registration->fonction ?: '-' }}</td>
                            <td>
                                <span class="badge-response {{ $registration->statut_reponse }}">
                                    @if($registration->statut_reponse === 'present')
                                        Présent
                                    @elseif($registration->statut_reponse === 'peut_etre')
                                        Peut-être
                                    @elseif($registration->statut_reponse === 'absent')
                                        Absent
                                    @elseif($registration->statut_reponse === 'represente')
                                        Représenté
                                    @else
                                        En attente
                                    @endif
                                </span>
                                @if($registration->statut_reponse === 'represente')
                                    <div class="representative-details">
                                        <strong>Représentant :</strong> {{ trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? '')) ?: '-' }}<br>
                                        {{ $registration->representant_fonction ?: '-' }} · {{ $registration->representant_contact ?: '-' }}<br>
                                        {{ $registration->representant_email ?: '-' }}<br>
                                        <strong>Statut :</strong>
                                        @if($registration->representant_statut === 'confirme')
                                            confirmé le {{ $registration->representant_confirme_le?->format('d/m/Y H:i') ?: '-' }}
                                        @elseif($registration->representant_statut === 'mail_envoye')
                                            mail envoyé le {{ $registration->representant_mail_envoye_le?->format('d/m/Y H:i') ?: '-' }}, en attente de confirmation
                                        @elseif($registration->representant_statut === 'mail_echec')
                                            mail non envoyé · {{ $registration->representant_mail_erreur ?: 'erreur inconnue' }}
                                        @else
                                            en attente d’envoi
                                        @endif<br>
                                        <strong>Carte représentant :</strong>
                                        @if($registration->representant_carte_envoyee)
                                            envoyée le {{ $registration->representant_carte_envoyee_le?->format('d/m/Y H:i') ?: '-' }}
                                        @elseif($registration->representant_carte_erreur)
                                            non envoyée · {{ $registration->representant_carte_erreur }}
                                        @else
                                            non envoyée
                                        @endif<br>
                                        <strong>Notification invité :</strong>
                                        @if($registration->represente_notification_envoyee)
                                            envoyée le {{ $registration->represente_notification_envoyee_le?->format('d/m/Y H:i') ?: '-' }}
                                        @elseif($registration->represente_notification_erreur)
                                            non envoyée · {{ $registration->represente_notification_erreur }}
                                        @else
                                            non envoyée
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td>{{ $registration->date_inscription->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($registration->carte_envoyee)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle me-1"></i>Oui
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-x-circle me-1"></i>Non
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('events.verify-qr', $registration->token_unique) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-info"
                                       data-bs-toggle="tooltip"
                                       title="Voir le QR code">
                                        <i class="bi bi-qr-code"></i>
                                    </a>
                                    <a href="{{ route('events.registrations.show', [$event, $registration]) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       data-bs-toggle="tooltip"
                                       title="Voir le détail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($registration->statut_reponse === 'represente' && $registration->representant_statut !== 'confirme')
                                        <form method="POST" action="{{ route('events.registrations.representative.resend', [$event, $registration]) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Renvoyer le lien au représentant">
                                                <i class="bi bi-envelope-arrow-up"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Aucune inscription pour le moment.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($registrations->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $registrations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
