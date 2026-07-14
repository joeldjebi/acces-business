@extends('layouts.app')

@section('title', 'QR Code invalide')

@push('styles')
<style>
    .qr-invalid-container {
        max-width: 600px;
        margin: 50px auto;
        text-align: center;
    }
    
    .error-card {
        background: white;
        padding: 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .error-icon {
        font-size: 80px;
        color: #ef4444;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div class="qr-invalid-container">
    <div class="error-card">
        <i class="bi bi-x-circle-fill error-icon"></i>
        <h1 style="color: #ef4444;">QR Code invalide</h1>
        <p class="text-muted">Ce QR code n'est pas valide ou a expiré.</p>
        
        <div class="mt-4">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">
                <i class="bi bi-arrow-left me-2"></i>Retour au tableau de bord
            </a>
        </div>
    </div>
</div>
@endsection

