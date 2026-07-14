@extends('layouts.app')

@section('title', 'Confirmation - ' . $event->titre)

@push('styles')
<style>
    .confirmation-page {
        --ink: #171713;
        --muted: #746f65;
        --line: #ded6c8;
        --panel: #fffefa;
        --gold: #b98943;
        max-width: 880px;
        margin: 0 auto;
    }

    .confirmation-card {
        background: var(--panel);
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 28px;
        box-shadow: 0 28px 75px rgba(39, 33, 25, .09);
        padding: 40px;
    }

    .success-icon {
        align-items: center;
        background: rgba(46, 123, 101, .12);
        border: 1px solid rgba(46, 123, 101, .22);
        border-radius: 24px;
        color: #2e7b65;
        display: inline-flex;
        font-size: 2.3rem;
        height: 72px;
        justify-content: center;
        width: 72px;
        margin-bottom: 22px;
    }

    .confirmation-title {
        color: var(--ink);
        font-size: clamp(1.8rem, 3vw, 2.8rem);
        font-weight: 500;
        line-height: 1.08;
        margin-bottom: 14px;
    }

    .event-panel,
    .info-section,
    .message-box {
        border-radius: 22px;
        padding: 22px;
        margin-top: 18px;
    }

    .event-panel {
        background: #171713;
        color: #fff;
    }

    .event-panel h2 {
        font-size: 1.4rem;
        font-weight: 500;
        margin-bottom: 14px;
    }

    .event-panel p {
        color: rgba(255, 255, 255, .74);
        margin-bottom: 7px;
    }

    .message-box {
        background: rgba(46, 123, 101, .09);
        border: 1px solid rgba(46, 123, 101, .18);
        color: #2e5e4f;
    }

    .info-section {
        background: #fff;
        border: 1px solid var(--line);
    }

    .info-item {
        color: var(--muted);
        display: flex;
        gap: 12px;
        margin: 12px 0;
    }

    .info-item i {
        color: var(--gold);
        width: 20px;
    }

    .btn-download {
        align-items: center;
        background: var(--ink);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        gap: 9px;
        min-height: 48px;
        padding: 0 20px;
        text-decoration: none;
    }

    .btn-download:hover {
        background: #2c2a25;
        color: #fff;
    }

    @media (max-width: 720px) {
        .confirmation-card {
            border-radius: 22px;
            padding: 26px;
        }
    }
</style>
@endpush

@section('content')
<div class="confirmation-page">
    <div class="confirmation-card">
        <div class="success-icon">
            <i class="bi bi-check2"></i>
        </div>
        <h1 class="confirmation-title">Votre réponse a bien été prise en compte.</h1>
        <p class="text-muted mb-0">Merci. Les informations utiles vous ont été envoyées par email si votre réponse le prévoit.</p>

        <div class="event-panel">
            <h2>{{ $event->titre }}</h2>
            @if($event->date_debut)
                <p><i class="bi bi-calendar3 me-2"></i>{{ $event->date_debut->format('d/m/Y') }} @if($event->date_fin && $event->date_debut->format('Y-m-d') !== $event->date_fin->format('Y-m-d'))- {{ $event->date_fin->format('d/m/Y') }}@endif</p>
            @endif
            @if($event->heure_debut)
                <p><i class="bi bi-clock me-2"></i>{{ $event->heure_debut }}{{ $event->heure_fin ? ' - ' . $event->heure_fin : '' }}</p>
            @endif
            @if($event->lieu || $event->ville)
                <p><i class="bi bi-geo-alt me-2"></i>{{ $event->lieu ?: $event->ville }}</p>
            @endif
        </div>

        @if($message)
            <div class="message-box">
                <i class="bi bi-info-circle me-2"></i>{{ $message }}
            </div>
        @endif

        @if($invitationToken)
            <div class="info-section">
                <h2 class="h5 mb-3">Carte d’invitation</h2>
                <p class="text-muted">
                    Votre carte avec QR code a été envoyée{{ $email ? ' à ' . $email : '' }}.
                    <span id="download-status" style="display: none; color: #2e7b65; font-weight: 600;">Téléchargement en cours...</span>
                </p>
                <a href="{{ route('invitation.download', ['token' => $invitationToken]) }}" class="btn-download" id="download-btn">
                    <i class="bi bi-download"></i> Télécharger la carte PDF
                </a>
            </div>
        @endif

        <div class="info-section">
            <h2 class="h5 mb-3">À retenir</h2>
            <div class="info-item">
                <i class="bi bi-qr-code"></i>
                <span>Présentez votre QR code à l’entrée de l’événement.</span>
            </div>
            <div class="info-item">
                <i class="bi bi-shield-check"></i>
                <span>La carte d’invitation est personnelle et non transférable.</span>
            </div>
            @if($email)
                <div class="info-item">
                    <i class="bi bi-envelope"></i>
                    <span>Email de référence : <strong>{{ $email }}</strong></span>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@if($invitationToken)
<script>
    (function() {
        const downloadUrl = '{{ route("invitation.download", ["token" => $invitationToken]) }}';
        const downloadStatus = document.getElementById('download-status');
        const downloadBtn = document.getElementById('download-btn');
        let downloadAttempted = false;

        function triggerDownload() {
            if (downloadAttempted) return;
            downloadAttempted = true;

            if (downloadStatus) {
                downloadStatus.style.display = 'inline';
            }

            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = downloadUrl;
            document.body.appendChild(iframe);

            setTimeout(function() {
                iframe.remove();
                if (downloadStatus) {
                    downloadStatus.textContent = 'Téléchargement lancé.';
                }
            }, 5000);
        }

        setTimeout(triggerDownload, 1200);

        downloadBtn?.addEventListener('click', function() {
            if (downloadStatus) {
                downloadStatus.style.display = 'inline';
                downloadStatus.textContent = 'Téléchargement en cours...';
            }
        });
    })();
</script>
@endif
@endpush
@endsection
