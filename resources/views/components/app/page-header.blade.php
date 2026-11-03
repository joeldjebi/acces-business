@props(['kicker' => null, 'title', 'subtitle' => null])

@once
    @push('styles')
    <style>
        .page-header { align-items: flex-end; display: flex; flex-wrap: wrap; gap: 16px 24px; justify-content: space-between; margin-bottom: 26px; }
        .page-header h1 { color: var(--app-ink); font-size: clamp(1.75rem, 3vw, 2.25rem); font-weight: 700; letter-spacing: -.035em; line-height: 1.1; margin: 0; }
        .page-header p { color: var(--app-muted); font-size: .95rem; line-height: 1.45; margin: 6px 0 0; max-width: 640px; }
        .page-header-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; }
        @media (max-width: 575.98px) { .page-header-actions { flex-wrap: nowrap; width: 100%; } .page-header-actions > .ui-btn { flex: 1; } }
    </style>
    @endpush
@endonce

<header {{ $attributes->class('page-header') }}>
    <div>
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="page-header-actions">{{ $actions }}</div>
    @endisset
</header>
