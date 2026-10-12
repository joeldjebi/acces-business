@props(['name', 'brand' => '', 'logo' => null, 'icon' => 'bi-shield-check', 'demo' => [], 'active' => false])

@php $dark = in_array($name, ['card'], true); @endphp

<div @class(['scr', 'scr-' . $name, 'is-dark' => $dark, 'is-on' => $active]) data-screen="{{ $name }}" aria-hidden="{{ $active ? 'false' : 'true' }}">
    <div class="scr-status"><span>9:41</span><span><i class="bi bi-reception-4"></i> <i class="bi bi-wifi"></i> <i class="bi bi-battery-full"></i></span></div>

    @switch($name)
        @case('invitation')
            <div class="scr-title a" style="--s:0">Boîte de réception</div>
            <div class="mail a" style="--s:1">
                <div class="mail-from"><span class="mini-logo">@if($logo)<img src="{{ $logo }}" alt="">@else<i class="bi {{ $icon }}"></i>@endif</span><div><strong>{{ $brand }}</strong><small>Invitation · {{ $demo['event_name'] ?? '' }}</small></div><time>maintenant</time></div>
                <div class="mail-banner a" style="--s:2"><small>Vous êtes invité(e)</small><strong>{{ $demo['event_name'] ?? '' }}</strong><span>{{ $demo['event_date'] ?? '' }} · {{ $demo['event_place'] ?? '' }}</span></div>
                <p class="a" style="--s:3">Bonjour {{ \Illuminate\Support\Str::before($demo['guest_name'] ?? '', ' ') }}, nous serions honorés de vous compter parmi nos invités.</p>
                <span class="scr-btn a" style="--s:4">Répondre à l’invitation</span>
            </div>
            @break

        @case('response')
            <div class="scr-kicker a" style="--s:0">Votre réponse</div>
            <div class="scr-title a" style="--s:1">{{ $demo['event_name'] ?? '' }}</div>
            <div class="opts">
                <div class="opt sel a" style="--s:2"><i class="bi bi-check-circle-fill"></i>Je serai présent(e)</div>
                <div class="opt a" style="--s:3"><i class="bi bi-question-circle"></i>Peut-être</div>
                <div class="opt a" style="--s:4"><i class="bi bi-x-circle"></i>Je ne pourrai pas venir</div>
            </div>
            <div class="scr-link a" style="--s:5"><i class="bi bi-person-arms-up"></i> Me faire représenter <i class="bi bi-chevron-right"></i></div>
            <div class="toast a" style="--s:8"><i class="bi bi-check-circle-fill"></i>Présence confirmée</div>
            @break

        @case('card')
            <div class="scr-kicker a" style="--s:0">Votre carte d’invitation</div>
            <div class="inv-card a" style="--s:1">
                <div class="inv-head"><span class="mini-logo">@if($logo)<img src="{{ $logo }}" alt="">@else<i class="bi {{ $icon }}"></i>@endif</span>{{ $brand }}<span class="inv-tier">{{ $demo['guest_tier'] ?? '' }}</span></div>
                <small>Invitation nominative</small>
                <strong>{{ $demo['guest_name'] ?? '' }}</strong>
                <span>{{ $demo['event_name'] ?? '' }}<br>{{ $demo['event_date'] ?? '' }}</span>
                <div class="inv-qr a" style="--s:3"><x-landing.qr :seed="'card' . ($demo['guest_name'] ?? '')" /><span class="scan-glint"></span></div>
            </div>
            <div class="scr-note a" style="--s:4"><i class="bi bi-file-earmark-pdf"></i> Carte PDF envoyée par email</div>
            @break

        @case('wallet')
            <div class="scr-title wallet-title a" style="--s:0">Wallet <i class="bi bi-plus-circle-fill"></i></div>
            <div class="stack a" style="--s:1"><span></span><span></span></div>
            <x-landing.wallet-pass class="pass-in" variant="apple" :brand="$brand" :logo="$logo" :icon="$icon" :demo="$demo" />
            <div class="toast a" style="--s:7"><i class="bi bi-check-circle-fill"></i>Ajoutée au Wallet</div>
            @break

        @case('checkin')
            <div class="ok-wrap">
                <svg class="ok-check" viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="54"/><path d="M38 62l15 15 30-33"/></svg>
                <strong class="a" style="--s:3">Entrée validée</strong>
                <span class="a" style="--s:4">{{ $demo['guest_name'] ?? '' }}</span>
                <div class="ok-meta a" style="--s:5"><span><small>Accès</small>{{ $demo['guest_tier'] ?? '' }}</span><span><small>Heure</small>18:42</span><span><small>Porte</small>A</span></div>
            </div>
            @break
    @endswitch
</div>
