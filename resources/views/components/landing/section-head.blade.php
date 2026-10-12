@props(['eyebrow' => null, 'title' => null, 'body' => null, 'align' => 'center'])

<div {{ $attributes->class(['section-head', 'reveal', 'is-left' => $align === 'left']) }}>
    @if($eyebrow)<div class="eyebrow">{{ $eyebrow }}</div>@endif
    @if($title)<h2>{{ $title }}</h2>@endif
    @if($body)<p>{{ $body }}</p>@endif
    {{ $slot }}
</div>
