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
    
    .badge-response.en_attente {
        background: #f3f4f6;
        color: #374151;
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
        $presents = $registrations->where('statut_reponse', 'present')->count();
        $peutEtre = $registrations->where('statut_reponse', 'peut_etre')->count();
        $absents = $registrations->where('statut_reponse', 'absent')->count();
        $total = $registrations->total();
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
    </div>
    
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
                            <td>
                                <span class="badge-response {{ $registration->statut_reponse }}">
                                    @if($registration->statut_reponse === 'present')
                                        Présent
                                    @elseif($registration->statut_reponse === 'peut_etre')
                                        Peut-être
                                    @elseif($registration->statut_reponse === 'absent')
                                        Absent
                                    @else
                                        En attente
                                    @endif
                                </span>
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
                                <a href="{{ route('events.verify-qr', $registration->token_unique) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-info" 
                                   data-bs-toggle="tooltip" 
                                   title="Voir le QR code">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
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

