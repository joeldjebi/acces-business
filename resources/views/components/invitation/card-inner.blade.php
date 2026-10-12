@props(['experience', 'initials' => '', 'guest' => null, 'title' => '', 'date' => null, 'place' => null])

<div class="ie-card-inner">
    <span class="ie-card-frame" aria-hidden="true"></span>
    <span class="ie-card-logo ie-ci">@if($experience['logo'] ?? null)<img src="{{ $experience['logo'] }}" alt="">@else{{ $initials }}@endif</span>
    <small class="ie-ci">{{ $experience['brand'] ?? '' }}</small>
    @if($guest)<span class="ie-guest ie-ci">{{ $guest }}</span>@endif
    <strong class="ie-ci">{{ $title }}</strong>
    <span class="ie-rule ie-ci"></span>
    @if($date)<span class="ie-meta ie-ci">{{ $date }}</span>@endif
    @if($place)<span class="ie-meta ie-ci">{{ $place }}</span>@endif
</div>
<span class="ie-glare" aria-hidden="true"></span>
