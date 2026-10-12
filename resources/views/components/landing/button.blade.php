@props(['href' => null, 'variant' => 'primary', 'icon' => null, 'iconAfter' => null])

@if($href)
<a href="{{ $href }}" {{ $attributes->class(['btn', 'btn-' . $variant]) }}>@if($icon)<i class="bi {{ $icon }}"></i>@endif<span>{{ $slot }}</span>@if($iconAfter)<i class="bi {{ $iconAfter }}"></i>@endif</a>
@else
<span {{ $attributes->class(['btn', 'btn-' . $variant]) }}>@if($icon)<i class="bi {{ $icon }}"></i>@endif<span>{{ $slot }}</span>@if($iconAfter)<i class="bi {{ $iconAfter }}"></i>@endif</span>
@endif
