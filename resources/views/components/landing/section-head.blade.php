@props(['eyebrow' => null, 'title' => null, 'body' => null, 'align' => 'center'])

<div {{ $attributes->class(['section-head', 'is-left' => $align === 'left', 'is-split' => $align === 'split']) }}>
    <div>
        @if($eyebrow)<div class="eyebrow" data-reveal>{{ $eyebrow }}</div>@endif
        @if($title)<h2 data-lines>{{ $title }}</h2>@endif
    </div>
    @if($body || trim($slot))
        <div>
            @if($body)<p data-reveal>{{ $body }}</p>@endif
            {{ $slot }}
        </div>
    @endif
</div>
