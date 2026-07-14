@extends('layouts.app')

@section('title', 'Vérification QR Code')

@push('styles')
<style>
    .qr-verified-container {
        max-width: 600px;
        margin: 50px auto;
        text-align: center;
    }
    
    .success-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .success-icon {
        font-size: 80px;
        color: #10b981;
        margin-bottom: 20px;
    }
    
    .participant-info {
        background: #f9fafb;
        padding: 20px;
        border-radius: 10px;
        margin: 30px 0;
        text-align: left;
    }
    
    .participant-info p {
        margin: 10px 0;
    }
</style>
@endpush

@section('content')
<div class="qr-verified-container">
    <div class="success-card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h1 style="color: #10b981;">QR Code valide</h1>
        <p class="text-muted">L'accès est autorisé</p>
        
        <div class="participant-info">
            <h4>Informations du participant</h4>
            <p><strong>Nom :</strong> {{ $registration->nom_complet ?: $registration->email }}</p>
            <p><strong>Email :</strong> {{ $registration->email }}</p>
            @if($registration->telephone)
            <p><strong>Téléphone :</strong> {{ $registration->telephone }}</p>
            @endif
            @if($registration->entreprise)
            <p><strong>Entreprise :</strong> {{ $registration->entreprise }}</p>
            @endif
            <p><strong>Statut :</strong> 
                @if($registration->statut_reponse === 'present')
                    <span class="badge bg-success">Présent</span>
                @elseif($registration->statut_reponse === 'peut_etre')
                    <span class="badge bg-warning">Peut-être</span>
                @else
                    <span class="badge bg-secondary">{{ ucfirst($registration->statut_reponse) }}</span>
                @endif
            </p>
        </div>
        
        <div class="mt-4">
            <p class="text-muted">
                <i class="bi bi-clock me-2"></i>
                Vérifié le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</div>
@endsection

