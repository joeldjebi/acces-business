@props(['kicker' => null, 'title', 'subtitle' => null])

@once
    @push('styles')
    <style>
        .page-header { align-items: flex-end; display: flex; flex-wrap: wrap; gap: 18px 24px; justify-content: space-between; margin-bottom: 26px; }
        .page-header-kicker { align-items: center; color: var(--app-gold-deep); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 8px; letter-spacing: .01em; }
        .page-header-kicker::before { background: var(--app-gold); border-radius: 99px; content: ""; height: 6px; width: 6px; }
        .page-header h1 { color: var(--app-ink); font-size: clamp(1.75rem, 3vw, 2.5rem); font-weight: 700; letter-spacing: -.035em; line-height: 1.08; margin: 8px 0 0; }
        .page-header p { color: var(--app-muted); font-size: .98rem; line-height: 1.55; margin: 8px 0 0; max-width: 640px; }
        .page-header-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; }
        @media (max-width: 575.98px) { .page-header-actions { flex-wrap: nowrap; width: 100%; } .page-header-actions > .ui-btn { flex: 1; } }
    </style>
    @endpush
@endonce

<header {{ $attributes->class('page-header') }}>
    <div>
        @if($kicker)<div class="page-header-kicker">{{ $kicker }}</div>@endif
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)
        <div class="page-header-actions">{{ $actions }}</div>
    @endisset
</header>
