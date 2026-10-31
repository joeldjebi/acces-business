@props(['kicker' => null, 'title', 'subtitle' => null])

@once
    @push('styles')
    <style>
        .page-header { align-items: flex-end; display: flex; flex-wrap: wrap; gap: 16px 24px; justify-content: space-between; margin-bottom: 24px; }
        .page-header h1 { color: var(--app-ink); font-size: clamp(1.5rem, 2.4vw, 1.9rem); font-weight: 700; letter-spacing: -.03em; line-height: 1.15; margin: 0; }
        .page-header p { color: var(--app-muted); font-size: .92rem; line-height: 1.5; margin: 6px 0 0; max-width: 640px; }
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
