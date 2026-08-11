@extends('layouts.app')

@section('title', 'Confirmation de représentation - ' . $registration->event->titre)

@push('styles')
<style>
    .represent-confirm {
        --ink: #171713;
        --muted: #746f65;
        --line: #ded6c8;
        --gold: #b98943;
        max-width: 760px;
        margin: 36px auto;
        padding: 0 16px;
    }
    .confirm-card {
        background: #fffefa;
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 24px;
        box-shadow: 0 20px 55px rgba(39, 33, 25, .07);
        overflow: hidden;
    }
    .confirm-hero {
        background: var(--ink);
        color: #fff;
        padding: 30px;
    }
    .confirm-hero span {
        color: #d8b476;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .confirm-hero h1 {
        font-size: clamp(1.7rem, 4vw, 2.4rem);
        font-weight: 500;
        margin: 8px 0 0;
    }
    .confirm-body {
        padding: 28px;
    }
    .event-line {
        border: 1px solid var(--line);
        border-radius: 18px;
        padding: 16px;
        color: var(--muted);
        margin: 20px 0;
    }
    .confirm-btn {
        background: var(--ink);
        border: 0;
        border-radius: 999px;
        color: #fff;
        min-height: 46px;
        padding: 0 24px;
        font-weight: 600;
    }
    .status-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: rgba(46, 123, 101, .12);
        color: #2e7b65;
        padding: 10px 14px;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
@php($walletLinks = app(\App\Services\WalletPassService::class)->linksFor($registration))
<div class="represent-confirm">
    <div class="confirm-card">
        <div class="confirm-hero">
            <span>Confirmation de représentation</span>
            <h1>{{ $registration->event->titre }}</h1>
        </div>
        <div class="confirm-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

            <p class="mb-2">Bonjour {{ trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? '')) ?: $registration->representant_email }},</p>
            <p class="text-muted mb-0">Vous avez été désigné(e) pour représenter {{ $registration->nom_complet ?: $registration->email }}.</p>

            <div class="event-line">
                @if($registration->event->date_debut)
                    <div><i class="bi bi-calendar3 me-2"></i>{{ $registration->event->date_debut->format('d/m/Y') }}</div>
                @endif
                @if($registration->event->heure_debut)
                    <div><i class="bi bi-clock me-2"></i>{{ $registration->event->heure_debut }}{{ $registration->event->heure_fin ? ' - ' . $registration->event->heure_fin : '' }}</div>
                @endif
                @if($registration->event->lieu || $registration->event->ville)
                    <div><i class="bi bi-geo-alt me-2"></i>{{ $registration->event->lieu ?: $registration->event->ville }}</div>
                @endif
            </div>

            @if($registration->representant_statut === 'confirme')
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="status-pill"><i class="bi bi-check2-circle me-2"></i>Présence confirmée</div>
                    <a href="{{ route('invitation.download', $registration->token_unique) }}" class="confirm-btn d-inline-flex align-items-center text-decoration-none">
                        <i class="bi bi-download me-2"></i>Télécharger ma carte
                    </a>
                    @if($walletLinks['apple'] ?? null)
                        <a href="{{ $walletLinks['apple'] }}" class="confirm-btn d-inline-flex align-items-center text-decoration-none">
                            <i class="bi bi-wallet2 me-2"></i>Apple Wallet
                        </a>
                    @endif
                    @if($walletLinks['google'] ?? null)
                        <a href="{{ $walletLinks['google'] }}" class="confirm-btn d-inline-flex align-items-center text-decoration-none">
                            <i class="bi bi-google me-2"></i>Google Wallet
                        </a>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ route('representations.confirm.store', $registration->representant_token) }}">
                    @csrf
                    <button type="submit" class="confirm-btn"><i class="bi bi-check2-circle me-2"></i>Confirmer ma présence</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
