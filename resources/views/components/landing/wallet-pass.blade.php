@props(['variant' => 'apple', 'brand' => '', 'logo' => null, 'icon' => 'bi-shield-check', 'demo' => []])

<div {{ $attributes->class(['pass', 'pass-' . $variant]) }}>
    <div class="pass-top">
        <span class="pass-logo">@if($logo)<img src="{{ $logo }}" alt="">@else<i class="bi {{ $icon }}"></i>@endif</span>
        <span class="pass-brand">{{ $brand }}</span>
        <span class="pass-field pass-tier"><small>Accès</small>{{ $demo['guest_tier'] ?? 'VIP' }}</span>
    </div>
    <div class="pass-event">{{ $demo['event_name'] ?? '' }}</div>
    <div class="pass-fields">
        <span class="pass-field"><small>Date</small>{{ $demo['event_date'] ?? '' }}</span>
        <span class="pass-field"><small>Lieu</small>{{ $demo['event_place'] ?? '' }}</span>
    </div>
    <div class="pass-fields">
        <span class="pass-field"><small>Invité</small>{{ $demo['guest_name'] ?? '' }}</span>
    </div>
    <div class="pass-code">
        <x-landing.qr :seed="$variant . ($demo['guest_name'] ?? '')" />
        <small>AB-2026-0184</small>
    </div>
</div>
