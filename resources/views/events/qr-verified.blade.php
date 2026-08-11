@extends('layouts.app')

@section('title', 'Vérification QR Code')

@push('styles')
<style>
    .qr-verified-container { max-width: 760px; margin: 32px auto; }
    .success-card { background:#fffefa; border:1px solid #ded6c8; padding:34px; border-radius:20px; box-shadow:0 18px 42px rgba(39,33,25,.055); text-align:center; }
    .success-icon { font-size:68px; color:#2e7b65; margin-bottom:14px; }
    .participant-info { background:#f8f4ec; border:1px solid #ded6c8; padding:20px; border-radius:14px; margin:24px 0; text-align:left; }
    .participant-info p { margin:10px 0; }
    .action-btn { align-items:center; border:1px solid #ded6c8; border-radius:12px; color:#171713; display:inline-flex; gap:8px; justify-content:center; min-height:42px; padding:0 14px; text-decoration:none; background:#fffefa; }
    .action-btn.primary { background:#171713; border-color:#171713; color:#fff; }
    .action-btn.success { background:#2e7b65; border-color:#2e7b65; color:#fff; }
</style>
@endpush

@section('content')
@php($attendeeName = $registration->checkInDisplayName())
<div class="qr-verified-container">
    <div class="success-card">
        <i class="bi bi-check-circle-fill success-icon"></i>
        <h1 style="color:#2e7b65;">QR Code valide</h1>
        <p class="text-muted">{{ $registration->checked_in_at ? 'Participant déjà validé à l’entrée.' : 'Accès autorisé.' }}</p>

        @if(session('success'))<div class="alert alert-success text-start"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>@endif
        @if(session('warning'))<div class="alert alert-warning text-start"><i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}</div>@endif
        @if(session('error'))<div class="alert alert-danger text-start"><i class="bi bi-x-circle me-2"></i>{{ session('error') }}</div>@endif
        
        <div class="participant-info">
            <h4>Informations du participant</h4>
            <p><strong>Participant :</strong> {{ $attendeeName ?: $registration->email }}</p>
            <p><strong>Type :</strong> {{ $registration->isRepresented() ? 'Représentant' : 'Invité direct' }}</p>
            <p><strong>Email :</strong> {{ $registration->isRepresented() ? ($registration->representant_email ?: '-') : $registration->email }}</p>
            <p><strong>Téléphone :</strong> {{ $registration->checkInContact() ?: '-' }}</p>
            @if($registration->isRepresented())
                <p><strong>Représente :</strong> {{ $registration->nom_complet ?: $registration->email }}</p>
            @endif
            @if($registration->entreprise)<p><strong>Entreprise :</strong> {{ $registration->entreprise }}</p>@endif
            @if($registration->fonction)<p><strong>Fonction :</strong> {{ $registration->fonction }}</p>@endif
            <p><strong>Statut :</strong> {{ $registration->statut_reponse }} {{ $registration->representant_statut ? '· ' . $registration->representant_statut : '' }}</p>
            <p><strong>Check-in :</strong> {{ $registration->checked_in_at ? $registration->checked_in_at->format('d/m/Y H:i') : 'Non validé' }}</p>
            @if($registration->checkedInBy)<p><strong>Validé par :</strong> {{ $registration->checkedInBy->name }}</p>@endif
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            @if($canCheckIn)
                <form method="POST" action="{{ route('events.verify-qr.check-in', $registration->token_unique) }}">
                    @csrf
                    <button type="submit" class="action-btn success"><i class="bi bi-check2-circle"></i>Valider le check-in</button>
                </form>
                <a href="{{ route('events.check-in', $registration->event) }}" class="action-btn"><i class="bi bi-speedometer2"></i>Console check-in</a>
            @endif
            <a href="{{ route('invitation.download', $registration->token_unique) }}" class="action-btn primary"><i class="bi bi-download"></i>Carte PDF</a>
        </div>
        
        <div class="mt-4 text-muted">
            <i class="bi bi-clock me-2"></i>Vérifié le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</div>
@endsection
