@extends('layouts.app')

@section('title', 'Confirmation - ' . $event->titre)
@section('hide_flash', '1')

@php
    $walletLinks = $walletLinks ?? ['apple' => null, 'google' => null];
    $hasWallet = $invitationToken && ($walletLinks['apple'] || $walletLinks['google']);
    $start = $event->date_debut?->locale('fr');
    $time = fn ($value) => $value ? \Illuminate\Support\Str::substr($value, 0, 5) : null;
@endphp

@push('styles')
<style>
    .confirm-page { margin: 0 auto; max-width: 720px; padding: 32px 0 48px; }
    .confirm-page *, .confirm-page *::before, .confirm-page *::after { min-width: 0; }

    .confirm-hero { text-align: center; }
    .check { height: 84px; margin: 0 auto 18px; width: 84px; }
    .check circle { fill: rgba(46, 123, 101, .1); stroke: var(--app-green); stroke-dasharray: 260; stroke-dashoffset: 260; stroke-width: 3; animation: draw .9s var(--app-ease) .1s forwards; }
    .check path { fill: none; stroke: var(--app-green); stroke-dasharray: 60; stroke-dashoffset: 60; stroke-linecap: round; stroke-linejoin: round; stroke-width: 5; animation: draw .5s var(--app-ease) .7s forwards; }
    @keyframes draw { to { stroke-dashoffset: 0; } }
    .confirm-hero h1 { color: var(--app-ink); font-size: clamp(1.7rem, 4vw, 2.4rem); font-weight: 700; letter-spacing: -.035em; line-height: 1.1; margin: 0 0 10px; }
    .confirm-hero p { color: var(--app-muted); font-size: 1rem; line-height: 1.55; margin: 0 auto; max-width: 520px; }

    .ticket { background: radial-gradient(100% 140% at 0% 0%, rgba(185, 137, 67, .4), transparent 60%), #141412; border-radius: 24px; box-shadow: 0 30px 60px -30px rgba(23, 23, 19, .6); color: #fff; margin-top: 28px; overflow: hidden; position: relative; }
    .ticket-top { padding: 24px 26px 20px; }
    .ticket-kicker { color: #d8b978; font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .ticket h2 { color: #fff; font-size: 1.45rem; font-weight: 700; letter-spacing: -.025em; line-height: 1.2; margin: 8px 0 0; }
    .ticket-facts { border-top: 1px dashed rgba(255, 255, 255, .18); display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); padding: 18px 26px 22px; position: relative; }
    .ticket-facts::before, .ticket-facts::after { background: var(--app-bg); border-radius: 50%; content: ""; height: 22px; position: absolute; top: -11px; width: 22px; }
    .ticket-facts::before { left: -11px; }
    .ticket-facts::after { right: -11px; }
    .ticket-facts span { color: rgba(255, 255, 255, .5); display: block; font-size: .68rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
    .ticket-facts strong { display: block; font-size: .95rem; font-weight: 600; margin-top: 3px; }

    .notice { align-items: flex-start; background: #eef6f2; border: 1px solid rgba(46, 123, 101, .2); border-radius: 16px; color: #22604f; display: flex; font-size: .92rem; gap: 10px; line-height: 1.5; margin-top: 18px; padding: 14px 16px; }
    .notice.is-warning { background: #fbf4e8; border-color: rgba(185, 137, 67, .28); color: #7a5727; }

    .block { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 22px; box-shadow: var(--app-shadow); margin-top: 18px; padding: 24px 26px; }
    .block h2 { color: var(--app-ink); font-size: 1.1rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
    .block > p { color: var(--app-muted); font-size: .9rem; margin: 6px 0 0; }

    .wallet-buttons { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 18px; }
    .wallet-btn { align-items: center; background: #000; border: 1px solid #000; border-radius: 14px; color: #fff; display: inline-flex; flex: 1 1 220px; gap: 12px; min-height: 58px; padding: 10px 18px; text-decoration: none; transition: transform .35s var(--app-ease), box-shadow .35s var(--app-ease); }
    .wallet-btn:hover { box-shadow: 0 16px 30px -14px rgba(0, 0, 0, .6); color: #fff; transform: translateY(-2px); }
    .wallet-btn:active { transform: scale(.98); }
    .wallet-btn i { font-size: 1.7rem; line-height: 1; }
    .wallet-btn small { display: block; font-size: .7rem; font-weight: 500; opacity: .8; }
    .wallet-btn strong { display: block; font-size: 1.05rem; font-weight: 600; letter-spacing: -.01em; line-height: 1.15; }
    .wallet-btn.is-secondary { background: #fff; border-color: var(--app-line); color: var(--app-ink); }
    .wallet-btn.is-secondary:hover { color: var(--app-ink); }
    .wallet-btn .bi-google { color: #4285f4; }
    .wallet-hint { align-items: center; color: var(--app-muted); display: none; font-size: .82rem; gap: 8px; margin: 14px 0 0; }
    .is-desktop .wallet-hint { display: flex; }
    .wallet-perks { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-top: 18px; }
    .wallet-perks div { align-items: center; color: var(--app-text); display: flex; font-size: .84rem; gap: 8px; }
    .wallet-perks i { color: var(--app-gold); }

    .pdf-row { align-items: center; border-top: 1px solid var(--app-line); display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; margin-top: 20px; padding-top: 18px; }
    .pdf-row span { color: var(--app-muted); font-size: .86rem; }
    #download-status { color: var(--app-green); font-weight: 600; }

    .tips { display: grid; gap: 12px; margin-top: 16px; }
    .tip { align-items: center; display: flex; gap: 12px; font-size: .9rem; color: var(--app-text); }
    .tip i { align-items: center; background: var(--app-gold-soft); border-radius: 10px; color: var(--app-gold-deep); display: flex; flex: 0 0 34px; height: 34px; justify-content: center; }

    .reveal { animation: rise .7s var(--app-ease) backwards; animation-delay: calc(var(--i, 0) * 90ms + 200ms); }
    @keyframes rise { from { opacity: 0; transform: translateY(14px); } }
    @media (max-width: 575.98px) {
        .confirm-page { padding-top: 12px; }
        .block, .ticket-top, .ticket-facts { padding-left: 20px; padding-right: 20px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .reveal { animation: none; }
        .check circle, .check path { animation: none; stroke-dashoffset: 0; }
    }
</style>
@endpush

@section('content')
<div class="confirm-page">
    <header class="confirm-hero">
        <svg class="check" viewBox="0 0 84 84" aria-hidden="true"><circle cx="42" cy="42" r="39"/><path d="M27 43l10 10 21-22"/></svg>
        <h1>Votre réponse est enregistrée</h1>
        <p>Merci ! Les informations utiles vous ont été envoyées par email{{ $email ? ' à ' . $email : '' }}.</p>
    </header>

    <article class="ticket reveal" style="--i: 0">
        <div class="ticket-top">
            <div class="ticket-kicker">{{ optional($event->category)->libelle ?? 'Invitation' }}</div>
            <h2>{{ $event->titre }}</h2>
        </div>
        <div class="ticket-facts">
            @if($start)
                <div><span>Date</span><strong>{{ $start->translatedFormat('l d F Y') }}@if($event->date_fin && !$event->date_fin->isSameDay($event->date_debut)) → {{ $event->date_fin->locale('fr')->translatedFormat('d F') }}@endif</strong></div>
            @endif
            @if($time($event->heure_debut))
                <div><span>Heure</span><strong>{{ $time($event->heure_debut) }}{{ $time($event->heure_fin) ? ' – ' . $time($event->heure_fin) : '' }}</strong></div>
            @endif
            @if($event->lieu || $event->ville)
                <div><span>Lieu</span><strong>{{ $event->lieu ?: $event->ville }}</strong></div>
            @endif
        </div>
    </article>

    @if($message)
        <div @class(['notice', 'reveal', 'is-warning' => session('warning') && !session('success')]) style="--i: 1">
            <i class="bi {{ session('warning') && !session('success') ? 'bi-exclamation-triangle' : 'bi-info-circle' }}"></i><span>{{ $message }}</span>
        </div>
    @endif

    @if($invitationToken)
        <section class="block reveal" style="--i: 2" id="walletBlock">
            <h2>{{ $hasWallet ? 'Ajoutez votre carte à votre Wallet' : 'Votre carte d’invitation' }}</h2>
            <p>{{ $hasWallet ? 'Gardez votre QR code d’accès toujours à portée de main, sans chercher l’email le jour J.' : 'Votre carte avec QR code vous a été envoyée par email.' }}</p>

            @if($hasWallet)
                <div class="wallet-buttons">
                    @if($walletLinks['apple'])
                        <a href="{{ $walletLinks['apple'] }}" class="wallet-btn" data-wallet="apple" rel="nofollow">
                            <i class="bi bi-apple"></i>
                            <span><small>Ajouter à</small><strong>Apple Wallet</strong></span>
                        </a>
                    @endif
                    @if($walletLinks['google'])
                        <a href="{{ $walletLinks['google'] }}" class="wallet-btn" data-wallet="google" target="_blank" rel="noopener nofollow">
                            <i class="bi bi-google"></i>
                            <span><small>Ajouter à</small><strong>Google Wallet</strong></span>
                        </a>
                    @endif
                </div>
                <p class="wallet-hint"><i class="bi bi-phone"></i>Astuce : ouvrez cette page ou l’email reçu sur votre téléphone pour ajouter la carte directement.</p>
                <div class="wallet-perks">
                    <div><i class="bi bi-qr-code"></i>QR code d’accès personnel</div>
                    <div><i class="bi bi-lightning-charge"></i>Accès instantané à l’entrée</div>
                    <div><i class="bi bi-envelope-check"></i>Toujours disponible par email</div>
                </div>
            @endif

            <div class="pdf-row">
                <span>Préférez-vous le format PDF ? <span id="download-status" style="display: none;">Téléchargement en cours…</span></span>
                <a href="{{ route('invitation.download', ['token' => $invitationToken]) }}" class="ui-btn ui-btn-ghost" id="download-btn"><i class="bi bi-download"></i>Télécharger la carte PDF</a>
            </div>
        </section>
    @endif

    <section class="block reveal" style="--i: 3">
        <h2>À retenir</h2>
        <div class="tips">
            <div class="tip"><i class="bi bi-qr-code-scan"></i>Présentez votre QR code à l’entrée de l’événement.</div>
            <div class="tip"><i class="bi bi-shield-check"></i>La carte d’invitation est personnelle et non transférable.</div>
            @if($email)<div class="tip"><i class="bi bi-envelope"></i><span>Email de référence : <strong>{{ $email }}</strong></span></div>@endif
        </div>
    </section>
</div>

@push('scripts')
@if($invitationToken)
<script>
    (function () {
        // Put the wallet that matches the visitor's device first.
        const block = document.getElementById('walletBlock');
        const ua = navigator.userAgent || '';
        const isIOS = /iPhone|iPad|iPod/i.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const isAndroid = /Android/i.test(ua);
        const apple = block?.querySelector('[data-wallet="apple"]');
        const google = block?.querySelector('[data-wallet="google"]');
        if (isIOS && apple && google) {
            google.classList.add('is-secondary');
        } else if (isAndroid && apple && google) {
            apple.classList.add('is-secondary');
            google.parentNode.insertBefore(google, apple);
        } else if (!isIOS && !isAndroid) {
            block?.classList.add('is-desktop');
            google?.classList.add('is-secondary');
        }

        const downloadUrl = @json(route('invitation.download', ['token' => $invitationToken]));
        const downloadStatus = document.getElementById('download-status');
        const downloadBtn = document.getElementById('download-btn');
        let downloadAttempted = false;

        function triggerDownload() {
            if (downloadAttempted) return;
            downloadAttempted = true;
            if (downloadStatus) downloadStatus.style.display = 'inline';

            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = downloadUrl;
            document.body.appendChild(iframe);

            setTimeout(function () {
                iframe.remove();
                if (downloadStatus) downloadStatus.textContent = 'Téléchargement lancé.';
            }, 5000);
        }

        setTimeout(triggerDownload, 1200);

        downloadBtn?.addEventListener('click', function () {
            if (downloadStatus) {
                downloadStatus.style.display = 'inline';
                downloadStatus.textContent = 'Téléchargement en cours…';
            }
        });
    })();
</script>
@endif
@endpush
@endsection
