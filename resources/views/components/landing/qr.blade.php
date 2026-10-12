@props(['seed' => 'acces-business'])

@php
    // Decorative, deterministic QR-like matrix (not a scannable code).
    $n = 25;
    $finders = [[0, 0], [$n - 7, 0], [0, $n - 7]];
    $path = '';
    for ($y = 0; $y < $n; $y++) {
        for ($x = 0; $x < $n; $x++) {
            $on = null;
            foreach ($finders as [$fx, $fy]) {
                if ($x >= $fx - 1 && $x <= $fx + 7 && $y >= $fy - 1 && $y <= $fy + 7) {
                    $dx = $x - $fx;
                    $dy = $y - $fy;
                    $inside = $dx >= 0 && $dx < 7 && $dy >= 0 && $dy < 7;
                    $on = $inside && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));
                    break;
                }
            }
            $on ??= crc32("{$seed}:{$x}:{$y}") % 5 < 2;
            if ($on) {
                $path .= "M{$x} {$y}h1v1h-1z";
            }
        }
    }
@endphp

<svg viewBox="-2 -2 29 29" {{ $attributes->merge(['class' => 'qr-svg']) }} aria-hidden="true" shape-rendering="crispEdges"><rect x="-2" y="-2" width="29" height="29" rx="2" fill="#fff"/><path d="{{ $path }}" fill="#111"/></svg>
