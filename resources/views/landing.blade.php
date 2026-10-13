@php
    $landing = $landing ?? \App\Models\LandingPage::current();
    $settings = $settings ?? $landing->mergedSettings();
    $enabled = fn ($item) => (bool) ($item['enabled'] ?? true);
    $filterEnabled = fn ($items) => collect($items ?? [])->filter(fn ($item) => $enabled($item))->values();
    $words = fn (?string $text) => collect(preg_split('/\s+/u', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY));
    $brandName = $settings['brand']['name'] ?? 'Accès Business';
    $brandLogo = $landing->imageUrl($settings['brand']['logo'] ?? null);
    $heroImage = $landing->imageUrl($settings['hero']['image'] ?? null);
    $walletImage = $landing->imageUrl($settings['wallet']['image'] ?? null);
    $heroWords = $words($settings['hero']['title'] ?? '');
    $accentFrom = $heroWords->count() > 3 ? $heroWords->count() - 2 : PHP_INT_MAX;
    $features = $filterEnabled($settings['features'] ?? []);
    $journeySteps = $filterEnabled($settings['journey_steps'] ?? []);
    $demo = $settings['demo'] ?? [];
    $brandIcon = $settings['brand']['logo_icon'] ?? 'bi-shield-check';
    $metrics = $filterEnabled($settings['hero_metrics'] ?? []);
    $ticker = $filterEnabled($settings['trust_items'] ?? [])
        ->map(fn ($item) => ['icon' => $item['icon'] ?? 'bi-check2', 'label' => $item['label'] ?? ''])
        ->merge($features->map(fn ($item) => ['icon' => $item['icon'] ?? 'bi-check2', 'label' => $item['title'] ?? '']))
        ->filter(fn ($item) => $item['label'] !== '')
        ->values();
    $metricParts = function (?string $value) {
        return preg_match('/^(\D*)(\d[\d\s.,]*)(.*)$/u', (string) $value, $m)
            ? ['prefix' => $m[1], 'number' => (int) preg_replace('/\D/', '', $m[2]), 'suffix' => $m[3]]
            : null;
    };
    // Each bento card gets a living micro-animation chosen from its icon.
    $motionFor = function (?string $icon) {
        $icon = (string) $icon;
        return match (true) {
            str_contains($icon, 'qr') || str_contains($icon, 'scan') => 'scan',
            str_contains($icon, 'wallet') || str_contains($icon, 'credit-card') => 'wallet',
            str_contains($icon, 'bell') => 'bell',
            str_contains($icon, 'chat') || str_contains($icon, 'sms') || str_contains($icon, 'whatsapp') => 'chat',
            str_contains($icon, 'envelope') || str_contains($icon, 'mail') || str_contains($icon, 'send') => 'mail',
            str_contains($icon, 'calendar') => 'calendar',
            str_contains($icon, 'person') || str_contains($icon, 'people') => 'people',
            str_contains($icon, 'graph') || str_contains($icon, 'chart') => 'chart',
            str_contains($icon, 'geo') || str_contains($icon, 'map') => 'map',
            default => 'pulse',
        };
    };
    $guestName = $demo['guest_name'] ?? 'Marie Koffi';
    $guestFirst = \Illuminate\Support\Str::before($guestName, ' ');
    $guestInitials = collect(explode(' ', $guestName))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->implode('');
    // Wide cards fill the 3-column bento so the last row never has a hole.
    $wideCards = [[], [0], [0, $features->count() - 1]][(3 - $features->count() % 3) % 3];
    $journeyScreens = array_keys(\App\Models\LandingPage::JOURNEY_SCREENS);
    $footerBrand = $settings['footer']['brand'] ?? $brandName;
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings['brand']['meta_title'] ?? $brandName }}</title>
    <meta name="description" content="{{ $settings['brand']['meta_description'] ?? '' }}">
    <meta name="theme-color" content="{{ $settings['theme']['background'] ?? '#fbf8f1' }}">
    <meta property="og:title" content="{{ $settings['brand']['meta_title'] ?? $brandName }}">
    <meta property="og:description" content="{{ $settings['brand']['meta_description'] ?? '' }}">
    @if($heroImage)<meta property="og:image" content="{{ $heroImage }}">@endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --ink: {{ $settings['theme']['ink'] ?? '#171713' }};
            --gold: {{ $settings['theme']['gold'] ?? '#b98943' }};
            --bg: {{ $settings['theme']['background'] ?? '#fbf8f1' }};
            --text: color-mix(in srgb, var(--ink) 88%, #fff);
            --muted: color-mix(in srgb, var(--ink) 55%, #fff);
            --line: color-mix(in srgb, var(--ink) 9%, transparent);
            --panel: #ffffff;
            --gold-soft: color-mix(in srgb, var(--gold) 14%, transparent);
            --gold-light: color-mix(in srgb, var(--gold) 55%, #fff);
            --gold-deep: color-mix(in srgb, var(--gold) 78%, #000);
            --green: #2e7b65;
            --ease: cubic-bezier(.16, 1, .3, 1);
            --radius: 28px;
            --display: "Bricolage Grotesque", "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
            --font: "Instrument Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            interpolate-size: allow-keywords;
        }
        *, *::before, *::after { box-sizing: border-box; min-width: 0; }
        html { scroll-behavior: smooth; scroll-padding-top: 90px; -webkit-text-size-adjust: 100%; }
        /* The page theme follows the section in view: cream -> ink -> brand. */
        body { --page-bg: var(--bg); --page-fg: var(--ink); --page-muted: var(--muted); --page-line: var(--line); --page-card: #ffffff;
            background: var(--page-bg); color: var(--page-fg); font-family: var(--font); margin: 0; -webkit-font-smoothing: antialiased; transition: background-color .8s var(--ease), color .8s var(--ease); }
        body[data-theme="dark"] { --page-bg: var(--ink); --page-fg: #f6f1e7; --page-muted: rgba(246, 241, 231, .62); --page-line: rgba(255, 255, 255, .1); --page-card: rgba(255, 255, 255, .05); }
        body[data-theme="brand"] { --page-bg: var(--gold); --page-fg: #fff; --page-muted: rgba(255, 255, 255, .84); --page-line: rgba(255, 255, 255, .24); --page-card: rgba(255, 255, 255, .14); }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        ::selection { background: var(--gold); color: #fff; }
        .site { overflow-x: clip; position: relative; }
        .inner { margin: 0 auto; max-width: 1200px; padding: 0 clamp(16px, 4vw, 40px); }
        .section { padding: clamp(80px, 11vw, 150px) 0; position: relative; }
        .eyebrow { align-items: center; color: var(--gold); display: inline-flex; font-size: .74rem; font-weight: 700; gap: 8px; letter-spacing: .14em; text-transform: uppercase; }
        .eyebrow::before { background: currentColor; border-radius: 99px; content: ""; height: 6px; width: 6px; }
        body[data-theme="brand"] .eyebrow { color: #fff; }
        h1, h2, h3 { color: inherit; font-family: var(--display); letter-spacing: -.035em; margin: 0; }
        .accent { color: var(--gold); }

        /* Masked line reveal */
        [data-lines] .line { display: block; margin-bottom: -.08em; overflow: hidden; padding-bottom: .08em; }
        [data-lines] .line-in { display: inline-block; will-change: transform; }
        [data-lines] .w { display: inline-block; }

        /* Progress + navigation */
        .progress { background: var(--gold); height: 3px; left: 0; position: fixed; right: 0; top: 0; transform: scaleX(0); transform-origin: 0 50%; z-index: 60; }
        .nav { left: 0; padding: 14px 0; position: fixed; right: 0; top: 0; z-index: 50; }
        .nav-shell { align-items: center; -webkit-backdrop-filter: saturate(180%) blur(18px); backdrop-filter: saturate(180%) blur(18px); background: color-mix(in srgb, var(--page-bg) 72%, transparent); border: 1px solid transparent; border-radius: 999px; display: flex; gap: 20px; height: 60px; justify-content: space-between; padding: 0 8px 0 16px; transition: background-color .8s var(--ease), border-color .4s, box-shadow .4s; }
        .nav.is-scrolled .nav-shell { border-color: var(--page-line); box-shadow: 0 12px 30px -18px rgba(0, 0, 0, .25); }
        .brand { align-items: center; display: flex; font-family: var(--display); font-size: 1.1rem; font-weight: 800; gap: 10px; letter-spacing: -.03em; white-space: nowrap; }
        .brand-mark { align-items: center; background: var(--gold); border-radius: 11px; color: #fff; display: flex; flex-shrink: 0; height: 34px; justify-content: center; overflow: hidden; width: 34px; }
        body[data-theme="brand"] .brand-mark { background: var(--ink); }
        .brand-mark img { height: 100%; object-fit: cover; width: 100%; }
        .nav-links { display: flex; gap: 26px; }
        .nav-links a { color: var(--page-muted); font-size: .88rem; font-weight: 500; position: relative; transition: color .25s; }
        .nav-links a::after { background: currentColor; bottom: -4px; content: ""; height: 1.5px; left: 0; position: absolute; transform: scaleX(0); transform-origin: right; transition: transform .4s var(--ease); width: 100%; }
        .nav-links a:hover { color: var(--page-fg); }
        .nav-links a:hover::after { transform: scaleX(1); transform-origin: left; }
        .nav-actions { align-items: center; display: flex; gap: 6px; }

        /* Buttons */
        .btn { align-items: center; border: 1px solid transparent; border-radius: 999px; cursor: pointer; display: inline-flex; font-family: var(--font); font-size: .95rem; font-weight: 600; gap: 9px; justify-content: center; min-height: 50px; padding: 0 24px; position: relative; transition: transform .45s var(--ease), box-shadow .45s var(--ease), background-color .3s, color .3s, border-color .3s; white-space: nowrap; }
        .btn i { transition: transform .45s var(--ease); }
        .btn:hover i { transform: translateX(3px); }
        .btn:active { transform: scale(.97); }
        .btn-primary { background: var(--ink); box-shadow: 0 14px 30px -14px rgba(0, 0, 0, .55); color: #fff; }
        body[data-theme="dark"] .btn-primary { background: #fff; color: var(--ink); }
        .btn-primary:hover, .btn-gold:hover, .btn-light:hover { transform: translateY(-2px); }
        .btn-gold { background: var(--gold); box-shadow: 0 16px 34px -14px var(--gold); color: #fff; }
        body[data-theme="brand"] .btn-gold { background: var(--ink); box-shadow: none; }
        .btn-ghost { border-color: var(--page-line); color: var(--page-fg); }
        .btn-ghost:hover { border-color: currentColor; }
        .btn-sm { font-size: .84rem; min-height: 42px; padding: 0 16px; }
        .btn-light { background: #fff; color: var(--ink); }
        .btn-outline-light { border-color: rgba(255, 255, 255, .5); color: #fff; }
        .btn-outline-light:hover { background: rgba(255, 255, 255, .12); }

        /* Section heads */
        .section-head { margin-bottom: clamp(44px, 6vw, 72px); max-width: 860px; }
        .section-head.is-split { align-items: end; display: grid; gap: 24px 60px; grid-template-columns: 1.25fr 1fr; max-width: none; }
        .section-head h2 { font-size: clamp(2.3rem, 5.4vw, 4.4rem); font-weight: 800; line-height: .98; margin-top: 16px; }
        .section-head p { color: var(--page-muted); font-size: clamp(1.02rem, 1.4vw, 1.18rem); line-height: 1.55; margin: 16px 0 0; max-width: 520px; }
        .section-head.is-split p { margin: 0; }

        /* Hero */
        .hero { align-items: center; display: grid; gap: 30px; grid-template-columns: minmax(0, 1.05fr) minmax(0, .95fr); min-height: 100svh; padding-bottom: 70px; padding-top: 120px; position: relative; }
        .hero-badge { align-items: center; background: var(--panel); border: 1px solid var(--line); border-radius: 999px; box-shadow: 0 8px 20px -14px rgba(0, 0, 0, .25); display: inline-flex; font-size: .82rem; font-weight: 600; gap: 10px; padding: 7px 16px 7px 12px; }
        .hero-badge .dot { background: var(--green); border-radius: 50%; height: 8px; position: relative; width: 8px; }
        .hero-badge .dot::after { animation: ping 1.8s cubic-bezier(0, 0, .2, 1) infinite; background: var(--green); border-radius: 50%; content: ""; inset: 0; position: absolute; }
        @keyframes ping { 75%, 100% { opacity: 0; transform: scale(2.6); } }
        .hero h1 { font-size: clamp(2.6rem, 5.4vw, 4.9rem); font-weight: 800; line-height: .94; margin: 24px 0 22px; }
        .hero .lead { color: var(--muted); font-size: clamp(1.05rem, 1.5vw, 1.22rem); line-height: 1.55; margin: 0; max-width: 540px; }
        .hero-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
        .trust { align-items: center; display: flex; flex-wrap: wrap; gap: 8px; margin-top: 30px; }
        .trust span { align-items: center; background: var(--panel); border: 1px solid var(--line); border-radius: 12px; display: inline-flex; font-size: .8rem; font-weight: 600; gap: 7px; padding: 6px 11px 6px 6px; }
        .trust span i { align-items: center; background: var(--gold-soft); border-radius: 7px; color: var(--gold-deep); display: inline-flex; height: 22px; justify-content: center; width: 22px; }
        .hero-visual { align-items: center; display: grid; height: min(680px, 80vh); justify-items: center; min-height: 560px; position: relative; }
        .hero-visual > * { grid-area: 1 / 1; }
        .hero-disc { aspect-ratio: 1; background: radial-gradient(circle at 32% 28%, var(--gold-light), var(--gold) 58%, var(--gold-deep)); border-radius: 50%; width: min(470px, 92%); }
        .hero-orbit { animation: spin 70s linear infinite; aspect-ratio: 1; border: 1.5px dashed color-mix(in srgb, var(--gold) 55%, transparent); border-radius: 50%; position: relative; width: min(590px, 112%); }
        .hero-orbit::before { background: var(--gold); border-radius: 50%; box-shadow: 0 0 0 6px var(--gold-soft); content: ""; height: 12px; left: 50%; position: absolute; top: -6px; width: 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .hero-phone { position: relative; z-index: 2; }
        .chip { align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 18px; box-shadow: 0 24px 48px -22px rgba(0, 0, 0, .35); color: var(--ink); display: flex; gap: 10px; padding: 10px 14px 10px 10px; position: absolute; z-index: 3; }
        .chip strong { display: block; font-size: .84rem; font-weight: 700; line-height: 1.2; white-space: nowrap; }
        .chip small { color: var(--muted); display: block; font-size: .72rem; white-space: nowrap; }
        .chip-ic { align-items: center; border-radius: 12px; color: #fff; display: flex; flex: 0 0 36px; font-size: 1rem; height: 36px; justify-content: center; position: relative; }
        .chip-ic.green { background: var(--green); }
        .chip-ic.gold { background: var(--gold); }
        .chip-ic.ink { background: var(--ink); }
        .chip-ic .ring { animation: ring 2.4s ease-out infinite; border: 2px solid var(--green); border-radius: inherit; inset: -2px; position: absolute; }
        @keyframes ring { 0% { opacity: .8; transform: scale(1); } 100% { opacity: 0; transform: scale(1.7); } }
        .chip-a { right: 0; top: 13%; }
        .chip-b { bottom: 15%; left: -2%; }
        .chip-c { left: 2%; top: 28%; }
        .chip-d { bottom: 6%; right: 3%; }
        .chip-stats { display: grid; gap: 2px 18px; grid-auto-flow: column; padding: 2px 4px; }
        .chip-stats > div > span { color: var(--muted); display: block; font-size: .62rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; white-space: nowrap; }
        .chip-stats b { display: block; font-family: var(--display); font-size: 1.25rem; font-variant-numeric: tabular-nums; letter-spacing: -.03em; }
        .scroll-cue { align-items: center; bottom: 22px; color: var(--muted); display: flex; flex-direction: column; font-size: .68rem; font-weight: 600; gap: 8px; left: 50%; letter-spacing: .14em; position: absolute; text-transform: uppercase; transform: translateX(-50%); }
        .scroll-cue i { border: 1.5px solid currentColor; border-radius: 99px; height: 28px; position: relative; width: 18px; }
        .scroll-cue i::after { animation: cue 1.8s var(--ease) infinite; background: currentColor; border-radius: 99px; content: ""; height: 6px; left: 50%; position: absolute; top: 5px; transform: translateX(-50%); width: 3px; }
        @keyframes cue { 0% { opacity: 1; transform: translate(-50%, 0); } 100% { opacity: 0; transform: translate(-50%, 10px); } }

        /* Ticker */
        .ticker { border-block: 1px solid var(--page-line); -webkit-mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); mask-image: linear-gradient(90deg, transparent, #000 10%, #000 90%, transparent); overflow: hidden; padding: 20px 0; }
        .ticker-track { animation: marquee 50s linear infinite; display: flex; gap: 36px; width: max-content; }
        .ticker-item { align-items: center; display: flex; font-family: var(--display); font-size: 1.35rem; font-weight: 700; gap: 12px; letter-spacing: -.02em; white-space: nowrap; }
        .ticker-item i { color: var(--gold); font-size: 1.2rem; }
        .ticker-item::after { color: var(--gold); content: "✦"; font-size: .9rem; margin-left: 24px; }
        @keyframes marquee { to { transform: translateX(-50%); } }

        /* Bento with living micro-animations */
        .bento { display: grid; gap: 16px; grid-auto-flow: dense; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); color: var(--ink); display: flex; flex-direction: column; overflow: hidden; padding: 22px; position: relative; transition: box-shadow .6s var(--ease), translate .6s var(--ease); }
        .card:hover { box-shadow: 0 34px 60px -34px rgba(0, 0, 0, .35); translate: 0 -5px; }
        .card.is-dark { background: var(--ink); border-color: transparent; color: #f6f1e7; }
        .card.is-gold { background: var(--gold); border-color: transparent; color: #fff; }
        .card.is-wide { grid-column: span 2; }
        .card h3 { font-size: 1.3rem; font-weight: 700; letter-spacing: -.02em; margin: 18px 0 8px; }
        .card p { color: var(--muted); font-size: .95rem; line-height: 1.5; margin: 0; }
        .card.is-dark p, .card.is-gold p { color: rgba(255, 255, 255, .72); }
        .card-head { align-items: center; display: flex; gap: 10px; }
        .card-icon { align-items: center; background: var(--gold-soft); border-radius: 12px; color: var(--gold-deep); display: flex; font-size: 1.05rem; height: 40px; justify-content: center; width: 40px; }
        .card.is-dark .card-icon, .card.is-gold .card-icon { background: rgba(255, 255, 255, .12); color: #fff; }
        .card-num { color: color-mix(in srgb, currentColor 40%, transparent); font-family: var(--display); font-size: .82rem; font-weight: 700; margin-left: auto; }
        .stage { align-items: center; background: color-mix(in srgb, var(--bg) 70%, #fff); border-radius: 18px; display: flex; flex: 1; justify-content: center; margin-top: 16px; min-height: 160px; overflow: hidden; position: relative; }
        .card.is-dark .stage { background: rgba(255, 255, 255, .05); }
        .card.is-gold .stage { background: rgba(255, 255, 255, .14); }
        .stage, .stage * { animation-play-state: paused; }
        .card.is-inview .stage, .card.is-inview .stage * { animation-play-state: running; }
        .m-scan { background: #fff; border-radius: 14px; box-shadow: 0 10px 24px -14px rgba(0, 0, 0, .4); padding: 8px; position: relative; width: 110px; }
        .m-scan svg { display: block; height: auto; width: 100%; }
        .m-scan .laser { animation: laser 2.2s ease-in-out infinite; background: linear-gradient(90deg, transparent, var(--gold), transparent); box-shadow: 0 0 14px 2px var(--gold); height: 2px; left: 4px; position: absolute; right: 4px; top: 10%; }
        @keyframes laser { 0%, 100% { top: 10%; } 50% { top: 88%; } }
        .m-scan .ok { align-items: center; animation: pop 2.2s var(--ease) infinite; background: var(--green); border-radius: 50%; color: #fff; display: flex; height: 30px; justify-content: center; position: absolute; right: -12px; top: -12px; width: 30px; }
        @keyframes pop { 0%, 40% { transform: scale(0); } 55% { transform: scale(1.15); } 65%, 100% { transform: scale(1); } }
        .m-wallet { height: 120px; position: relative; width: 180px; }
        .m-wallet span { animation: shuffle 4.5s var(--ease) infinite; border-radius: 14px; box-shadow: 0 12px 24px -14px rgba(0, 0, 0, .5); height: 92px; left: 0; position: absolute; top: 14px; width: 180px; }
        .m-wallet span::after { background: rgba(255, 255, 255, .5); border-radius: 4px; content: ""; height: 6px; left: 14px; position: absolute; top: 14px; width: 60px; }
        .m-wallet span:nth-child(1) { background: linear-gradient(135deg, var(--ink), color-mix(in srgb, var(--ink) 70%, var(--gold))); }
        .m-wallet span:nth-child(2) { animation-delay: -1.5s; background: linear-gradient(135deg, var(--gold-light), var(--gold)); }
        .m-wallet span:nth-child(3) { animation-delay: -3s; background: linear-gradient(135deg, #2f6db5, #5b8fd6); }
        @keyframes shuffle { 0%, 30% { transform: translateY(0) scale(1); z-index: 3; } 40% { transform: translateY(-34px) rotate(-4deg) scale(.96); z-index: 3; } 50% { transform: translateY(16px) scale(.9); z-index: 1; } 70% { transform: translateY(8px) scale(.95); z-index: 2; } 100% { transform: translateY(0) scale(1); z-index: 3; } }
        .m-bell { color: var(--gold); font-size: 3.2rem; position: relative; }
        .card.is-gold .m-bell { color: #fff; }
        .m-bell i { animation: swing 2.4s ease-in-out infinite; display: inline-block; transform-origin: 50% 8%; }
        .m-bell b { align-items: center; animation: pop 2.4s var(--ease) infinite; background: #e5484d; border: 3px solid #fff; border-radius: 99px; color: #fff; display: flex; font-family: var(--font); font-size: .72rem; height: 24px; justify-content: center; min-width: 24px; position: absolute; right: -8px; top: 0; }
        @keyframes swing { 0%, 50%, 100% { transform: rotate(0); } 10% { transform: rotate(16deg); } 20% { transform: rotate(-14deg); } 30% { transform: rotate(10deg); } 40% { transform: rotate(-6deg); } }
        .m-chat { display: grid; gap: 8px; width: 84%; }
        .m-chat span { animation: bubble 5s var(--ease) infinite backwards; background: #fff; border-radius: 14px 14px 14px 4px; box-shadow: 0 6px 14px -8px rgba(0, 0, 0, .3); color: var(--ink); font-size: .76rem; font-weight: 600; max-width: 82%; padding: 8px 11px; width: max-content; }
        .m-chat span:nth-child(2) { animation-delay: .7s; background: var(--gold); border-radius: 14px 14px 4px 14px; color: #fff; justify-self: end; }
        .m-chat span:nth-child(3) { animation-delay: 1.4s; }
        @keyframes bubble { 0% { opacity: 0; transform: translateY(10px) scale(.95); } 10%, 80% { opacity: 1; transform: none; } 90%, 100% { opacity: 0; transform: translateY(-6px); } }
        .m-mail { height: 100px; position: relative; width: 140px; }
        .m-mail .back { background: var(--gold-deep); border-radius: 10px; bottom: 0; height: 66px; left: 0; position: absolute; right: 0; }
        .m-mail .letter { animation: letter 3s var(--ease) infinite; background: #fff; border-radius: 6px; bottom: 10px; box-shadow: 0 6px 12px -6px rgba(0, 0, 0, .3); height: 72px; left: 12px; position: absolute; right: 12px; z-index: 1; }
        .m-mail .letter::before { background: repeating-linear-gradient(180deg, rgba(0, 0, 0, .12) 0 3px, transparent 3px 10px); content: ""; height: 26px; left: 12px; position: absolute; right: 12px; top: 12px; }
        .m-mail .env { background: var(--gold); border-radius: 10px; bottom: 0; clip-path: polygon(0 0, 50% 45%, 100% 0, 100% 100%, 0 100%); height: 66px; left: 0; position: absolute; right: 0; z-index: 2; }
        @keyframes letter { 0%, 15% { transform: translateY(0); } 50%, 75% { transform: translateY(-36px); } 100% { transform: translateY(0); } }
        .m-cal { background: #fff; border-radius: 16px; box-shadow: 0 12px 24px -14px rgba(0, 0, 0, .4); overflow: hidden; text-align: center; width: 110px; }
        .m-cal small { background: var(--gold); color: #fff; display: block; font-size: .7rem; font-weight: 700; letter-spacing: .1em; padding: 6px; text-transform: uppercase; }
        .m-cal b { color: var(--ink); display: block; font-family: var(--display); font-size: 2.8rem; height: 64px; line-height: 64px; overflow: hidden; }
        .m-cal b span { animation: flip 4s var(--ease) infinite; display: block; }
        @keyframes flip { 0%, 40% { transform: translateY(0); } 50%, 90% { transform: translateY(-64px); } 100% { transform: translateY(-128px); } }
        .m-people { align-items: center; display: flex; gap: 12px; }
        .m-people .av { align-items: center; border-radius: 50%; box-shadow: 0 10px 20px -10px rgba(0, 0, 0, .4); color: #fff; display: flex; font-family: var(--display); font-weight: 700; height: 56px; justify-content: center; width: 56px; }
        .m-people .av:first-child { animation: dim 3.2s ease-in-out infinite; background: var(--ink); }
        .card.is-dark .m-people .av:first-child { background: rgba(255, 255, 255, .16); }
        .m-people .av:last-child { animation: grow 3.2s var(--ease) infinite; background: var(--gold); }
        .m-people i { animation: nudge 3.2s ease-in-out infinite; color: var(--gold); font-size: 1.3rem; }
        @keyframes dim { 0%, 30% { opacity: 1; } 50%, 80% { opacity: .35; } 100% { opacity: 1; } }
        @keyframes grow { 0%, 30% { opacity: .5; transform: scale(.8); } 50%, 80% { opacity: 1; transform: scale(1.08); } 100% { opacity: .5; transform: scale(.8); } }
        @keyframes nudge { 0%, 30% { transform: translateX(-4px); } 50%, 80% { transform: translateX(4px); } 100% { transform: translateX(-4px); } }
        .m-chart { align-items: flex-end; display: flex; gap: 8px; height: 100px; }
        .m-chart span { animation: bar 3s var(--ease) infinite; background: var(--ink); border-radius: 6px 6px 2px 2px; transform-origin: bottom; width: 18px; }
        .card.is-dark .m-chart span { background: rgba(255, 255, 255, .8); }
        .m-chart span:nth-child(even) { background: var(--gold); }
        .m-chart span:nth-child(1) { height: 40%; } .m-chart span:nth-child(2) { animation-delay: .1s; height: 65%; } .m-chart span:nth-child(3) { animation-delay: .2s; height: 50%; } .m-chart span:nth-child(4) { animation-delay: .3s; height: 85%; } .m-chart span:nth-child(5) { animation-delay: .4s; height: 100%; }
        @keyframes bar { 0% { transform: scaleY(.1); } 35%, 80% { transform: scaleY(1); } 100% { transform: scaleY(.1); } }
        .m-map { background-image: linear-gradient(var(--line) 1px, transparent 1px), linear-gradient(90deg, var(--line) 1px, transparent 1px); background-size: 22px 22px; inset: 0; position: absolute; }
        .m-map svg { height: 100%; inset: 0; position: absolute; width: 100%; }
        .m-map path { animation: route 3.6s ease-in-out infinite; fill: none; stroke: var(--gold); stroke-dasharray: 260; stroke-linecap: round; stroke-width: 4; }
        .m-map .pin { animation: bounce 1.6s ease-in-out infinite; color: var(--gold-deep); font-size: 1.6rem; position: absolute; right: 16%; top: 12%; }
        @keyframes route { 0% { stroke-dashoffset: 260; } 60%, 100% { stroke-dashoffset: 0; } }
        @keyframes bounce { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        .m-pulse { align-items: center; background: var(--gold); border-radius: 22px; color: #fff; display: flex; font-size: 1.6rem; height: 72px; justify-content: center; position: relative; width: 72px; }
        .card.is-gold .m-pulse { background: var(--ink); }
        .m-pulse::before, .m-pulse::after { animation: ring 2.4s ease-out infinite; border: 2px solid var(--gold); border-radius: 26px; content: ""; inset: -6px; position: absolute; }
        .card.is-gold .m-pulse::before, .card.is-gold .m-pulse::after { border-color: #fff; }
        .m-pulse::after { animation-delay: 1.2s; }

        /* Stats band */
        .stats { border-block: 1px solid var(--page-line); display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        .stat { padding: 34px 26px; }
        .stat + .stat { border-left: 1px solid var(--page-line); }
        .stat strong { display: block; font-family: var(--display); font-size: clamp(3rem, 6vw, 4.8rem); font-variant-numeric: tabular-nums; font-weight: 800; letter-spacing: -.05em; line-height: 1; }
        .stat strong small { color: var(--gold); font-size: .38em; letter-spacing: -.01em; margin-left: 4px; }
        .stat > span { color: var(--page-muted); display: block; font-size: .92rem; margin-top: 10px; }

        /* Showcase */
        .showcase-frame, .wallet-media { border-radius: 36px; box-shadow: 0 60px 120px -50px rgba(0, 0, 0, .5); overflow: hidden; position: relative; transform-origin: 50% 0; }
        .showcase-frame img, .wallet-media img { aspect-ratio: 16 / 9; object-fit: cover; width: 100%; }
        .showcase-frame::after { background: linear-gradient(180deg, transparent 50%, rgba(0, 0, 0, .35)); content: ""; inset: 0; position: absolute; }

        /* Journey */
        .journey-grid { display: grid; gap: clamp(30px, 6vw, 80px); grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .journey-device { order: 2; }
        .journey-sticky { align-items: center; display: flex; height: 100vh; justify-content: center; position: sticky; top: 0; }
        .journey-num { color: transparent; font-family: var(--display); font-size: clamp(14rem, 28vw, 24rem); font-weight: 800; left: 50%; letter-spacing: -.06em; line-height: 1; pointer-events: none; position: absolute; top: 50%; translate: -62% -54%; -webkit-text-stroke: 2px color-mix(in srgb, var(--gold) 50%, transparent); user-select: none; }
        .journey-num span { display: block; }
        .journey-glow { background: radial-gradient(circle, color-mix(in srgb, var(--gold) 34%, transparent), transparent 62%); height: 600px; left: 50%; position: absolute; top: 50%; transform: translate(-50%, -50%); width: 600px; }
        .journey-sticky .phone { position: relative; }
        /* --jh: height of one step; the track padding centres the first/last step on the sticky phone. */
        .journey-track { --jh: 30vh; --jpad: calc(50vh - var(--jh) / 2); padding: var(--jpad) 0 var(--jpad) 34px; position: relative; }
        .journey-rail { background: var(--page-line); bottom: calc(var(--jpad) + var(--jh) / 2); left: 10px; position: absolute; top: calc(var(--jpad) + var(--jh) / 2); width: 2px; }
        .journey-rail span { background: var(--gold); display: block; height: 100%; transform: scaleY(var(--jp, 0)); transform-origin: top; width: 100%; }
        .journey-steps { list-style: none; margin: 0; padding: 0; }
        .jstep { display: flex; flex-direction: column; justify-content: center; min-height: var(--jh); opacity: .25; position: relative; transition: opacity .6s var(--ease); }
        .jstep.is-active { opacity: 1; }
        .jstep::before { background: var(--page-bg); border: 2px solid var(--page-line); border-radius: 50%; content: ""; height: 16px; left: -31px; position: absolute; top: 50%; transform: translateY(-50%); transition: all .5s var(--ease); width: 16px; }
        .jstep.is-active::before { background: var(--gold); border-color: var(--gold); box-shadow: 0 0 0 6px color-mix(in srgb, var(--gold) 25%, transparent); }
        .jstep small { color: var(--gold); font-size: .78rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
        .jstep h3 { font-size: clamp(2rem, 4vw, 3.4rem); font-weight: 800; line-height: 1; margin: 12px 0 14px; }
        .jstep p { color: var(--page-muted); font-size: 1.08rem; line-height: 1.55; margin: 0; max-width: 440px; }

        /* Representation flow */
        .rep-grid { align-items: center; display: grid; gap: clamp(40px, 7vw, 96px); grid-template-columns: 1.05fr .95fr; }
        .rep-grid h2 { font-size: clamp(2.2rem, 4.6vw, 3.8rem); font-weight: 800; line-height: 1; margin: 16px 0 20px; }
        .rep-grid > div > p { color: var(--page-muted); font-size: 1.1rem; line-height: 1.6; margin: 0; }
        .rep-items { display: grid; gap: 12px; list-style: none; margin: 30px 0 0; padding: 0; }
        .rep-items li { align-items: flex-start; display: flex; gap: 12px; line-height: 1.5; }
        .rep-items li i { color: var(--gold); margin-top: 2px; }
        .flow { display: grid; gap: 16px; position: relative; }
        .flow-line { background: var(--page-line); bottom: 40px; left: 38px; position: absolute; top: 40px; width: 2px; }
        .flow-line span { background: var(--gold); display: block; height: 100%; transform: scaleY(0); transform-origin: top; width: 100%; }
        .flow-card { align-items: center; background: var(--page-card); border: 1px solid var(--page-line); border-radius: 22px; display: flex; gap: 16px; padding: 18px; position: relative; }
        .avatar { align-items: center; background: linear-gradient(135deg, var(--gold-light), var(--gold-deep)); border-radius: 50%; color: #fff; display: flex; flex-shrink: 0; font-size: .9rem; font-weight: 700; height: 40px; justify-content: center; position: relative; width: 40px; z-index: 1; }
        .avatar.alt { background: #3a3a36; }
        .flow-card strong, .feed-row strong { display: block; font-size: .96rem; }
        .flow-card small { color: var(--page-muted); display: block; font-size: .84rem; margin-top: 2px; }
        .badge { border-radius: 999px; font-size: .74rem; font-weight: 700; margin-left: auto; padding: 6px 11px; white-space: nowrap; }
        .badge.ok { background: rgba(48, 209, 88, .14); color: #5fd38a; }
        .badge.wait { background: color-mix(in srgb, var(--gold) 20%, transparent); color: var(--gold-light); }

        /* Check-in */
        .split { align-items: center; display: grid; gap: clamp(40px, 7vw, 96px); grid-template-columns: 1fr 1fr; }
        .device { background: linear-gradient(160deg, color-mix(in srgb, var(--ink) 90%, #fff), var(--ink)); border-radius: 40px; box-shadow: 0 60px 100px -40px rgba(0, 0, 0, .55); margin: 0 auto; max-width: 440px; padding: 22px; width: 100%; }
        .viewfinder { aspect-ratio: 16 / 10; background: radial-gradient(circle, rgba(255, 255, 255, .06), transparent 70%); border-radius: 24px; display: grid; overflow: hidden; place-items: center; position: relative; }
        .corner { border: 3px solid var(--gold); height: 34px; position: absolute; width: 34px; }
        .corner.tl { border-radius: 14px 0 0 0; border-width: 3px 0 0 3px; left: 24%; top: 14%; }
        .corner.tr { border-radius: 0 14px 0 0; border-width: 3px 3px 0 0; right: 24%; top: 14%; }
        .corner.bl { border-radius: 0 0 0 14px; border-width: 0 0 3px 3px; bottom: 14%; left: 24%; }
        .corner.br { border-radius: 0 0 14px 0; border-width: 0 3px 3px 0; bottom: 14%; right: 24%; }
        .viewfinder .qr { color: rgba(255, 255, 255, .85); font-size: clamp(3.4rem, 7vw, 4.6rem); }
        .viewfinder .laser { animation: laser2 2.6s ease-in-out infinite; background: linear-gradient(90deg, transparent, var(--gold-light), transparent); box-shadow: 0 0 18px 2px var(--gold); height: 2px; left: 22%; position: absolute; right: 22%; top: 16%; }
        @keyframes laser2 { 0%, 100% { top: 16%; } 50% { top: 82%; } }
        .feed { display: grid; gap: 10px; margin-top: 18px; }
        .feed-row { align-items: center; background: rgba(255, 255, 255, .05); border: 1px solid rgba(255, 255, 255, .07); border-radius: 18px; color: #fff; display: flex; gap: 12px; padding: 13px 14px; }
        .feed-row small { color: rgba(255, 255, 255, .5); font-size: .8rem; }
        .feed-row.enter { animation: rowIn .8s var(--ease) backwards; }
        @keyframes rowIn { from { opacity: 0; transform: translateY(-14px) scale(.97); } }

        /* Wallet */
        .wallet-grid { align-items: center; display: grid; gap: clamp(48px, 7vw, 96px); grid-template-columns: 1fr 1fr; }
        .platforms { display: grid; gap: 12px; margin-top: 32px; }
        .platform { align-items: center; background: var(--page-card); border: 1px solid var(--page-line); border-radius: 20px; display: flex; gap: 14px; padding: 14px 18px 14px 14px; }
        .platform strong { display: block; }
        .platform small { color: var(--page-muted); font-size: .86rem; }
        .platform-ic { align-items: center; border-radius: 14px; display: flex; flex-shrink: 0; font-size: 1.3rem; height: 46px; justify-content: center; width: 46px; }
        .platform-ic.is-apple { background: #000; color: #fff; }
        .platform-ic.is-google { background: #fff; border: 1px solid var(--line); color: #4285f4; }
        .platform-ok { color: var(--green); margin-left: auto; }
        .wallet-list { display: grid; gap: 12px; list-style: none; margin: 26px 0 0; padding: 0; }
        .wallet-list li { align-items: flex-start; display: flex; gap: 10px; line-height: 1.45; }
        .wallet-list i { color: var(--gold); }
        .wallet-badges { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 30px; }
        .add-badge { align-items: center; background: #000; border: 1px solid #444; border-radius: 12px; color: #fff; display: inline-flex; gap: 10px; min-height: 52px; padding: 8px 18px 8px 14px; }
        .add-badge i { font-size: 1.5rem; }
        .add-badge span { font-size: 1.02rem; font-weight: 600; line-height: 1.1; }
        .add-badge small { display: block; font-size: .66rem; font-weight: 500; opacity: .85; }
        .pass-label { align-items: center; -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); background: rgba(255, 255, 255, .85); border-radius: 99px; box-shadow: 0 10px 20px -12px rgba(0, 0, 0, .3); color: var(--ink); display: inline-flex; font-size: .8rem; font-weight: 600; gap: 7px; left: 50%; padding: 8px 14px; position: absolute; top: -48px; transform: translateX(-50%); white-space: nowrap; }
        .wallet-stage { margin-top: clamp(64px, 9vw, 120px); }
        .wallet-caption { bottom: 24px; left: 24px; z-index: 2; }

        /* Wallet passes */
        .pass { border-radius: 22px; box-shadow: 0 40px 80px -30px rgba(0, 0, 0, .55), inset 0 0 0 1px rgba(255, 255, 255, .08); color: #fff; padding: 18px 18px 20px; width: 280px; }
        .pass-apple { background: linear-gradient(165deg, color-mix(in srgb, var(--ink) 78%, var(--gold)), var(--ink) 55%); }
        .pass-google { background: linear-gradient(165deg, var(--gold-light), var(--gold) 45%, var(--gold-deep)); border-radius: 26px; }
        .pass-top { align-items: center; display: flex; gap: 9px; }
        .pass-logo { align-items: center; background: rgba(255, 255, 255, .14); border-radius: 9px; display: flex; font-size: .9rem; height: 30px; justify-content: center; overflow: hidden; width: 30px; }
        .pass-google .pass-logo { border-radius: 50%; }
        .pass-logo img { height: 100%; object-fit: cover; width: 100%; }
        .pass-brand { font-size: .84rem; font-weight: 600; }
        .pass-tier { margin-left: auto; text-align: right; }
        .pass-field { display: block; font-size: .88rem; font-weight: 600; line-height: 1.25; }
        .pass-field small { color: rgba(255, 255, 255, .62); display: block; font-size: .58rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .pass-apple .pass-field small { color: var(--gold-light); }
        .pass-event { font-size: 1.32rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.12; margin: 22px 0 16px; }
        .pass-fields { display: flex; gap: 18px; margin-bottom: 12px; }
        .pass-fields .pass-field { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .pass-code { align-items: center; background: #fff; border-radius: 14px; color: #111; display: flex; flex-direction: column; gap: 4px; margin: 18px auto 0; padding: 10px 10px 6px; width: 132px; }
        .pass-code .qr-svg { display: block; width: 112px; }
        .pass-code small { font-size: .56rem; letter-spacing: .08em; }
        .pass-stage { height: 560px; perspective: 1400px; position: relative; }
        .pass-stage > * { transition: transform 1.1s var(--ease), z-index 0s .35s, filter .9s; }
        .pass-slot { left: 50%; position: absolute; top: 50%; }
        .pass-slot-apple { transform: translate(-90%, -52%) rotateY(calc(var(--ry, 0deg) + 8deg)) rotateX(var(--rx, 0deg)) rotate(-5deg); z-index: 2; }
        .pass-slot-google { filter: brightness(.9); transform: translate(-10%, -44%) rotateY(calc(var(--ry, 0deg) - 8deg)) rotateX(var(--rx, 0deg)) rotate(5deg) scale(.94); z-index: 1; }
        .pass-stage.is-swapped .pass-slot-apple { filter: brightness(.9); transform: translate(-90%, -44%) rotateY(calc(var(--ry, 0deg) + 8deg)) rotateX(var(--rx, 0deg)) rotate(-5deg) scale(.94); z-index: 1; }
        .pass-stage.is-swapped .pass-slot-google { filter: none; transform: translate(-10%, -52%) rotateY(calc(var(--ry, 0deg) - 8deg)) rotateX(var(--rx, 0deg)) rotate(5deg); z-index: 2; }
        .pass-label { align-items: center; display: inline-flex; font-size: .8rem; font-weight: 600; gap: 7px; left: 50%; padding: 8px 14px; position: absolute; top: -48px; transform: translateX(-50%); white-space: nowrap; }

        @include('landing.device-css')

        /* Pricing */
        .pricing { align-items: stretch; display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .price { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); color: var(--ink); display: flex; flex-direction: column; padding: 34px 30px; position: relative; transition: box-shadow .6s var(--ease), translate .6s var(--ease); }
        .price:hover { box-shadow: 0 40px 80px -40px rgba(0, 0, 0, .35); translate: 0 -6px; }
        .price h3 { font-size: 1.15rem; font-weight: 700; }
        .price .sub { color: var(--muted); font-size: .94rem; line-height: 1.5; margin: 8px 0 0; }
        .price .amount { display: block; font-family: var(--display); font-size: clamp(2.2rem, 3.6vw, 2.9rem); font-weight: 800; letter-spacing: -.045em; margin: 26px 0 22px; }
        .price ul { border-top: 1px solid var(--line); display: grid; gap: 12px; list-style: none; margin: 0 0 30px; padding: 22px 0 0; }
        .price li { align-items: flex-start; display: flex; font-size: .95rem; gap: 10px; line-height: 1.45; }
        .price li i { color: var(--gold); }
        .price .btn { margin-top: auto; width: 100%; }
        .price.featured { background: var(--ink); border-color: transparent; color: #fff; margin-top: -12px; margin-bottom: 12px; }
        .price.featured .sub { color: rgba(255, 255, 255, .7); }
        .price.featured ul { border-color: rgba(255, 255, 255, .14); }
        .tag { background: var(--gold); border-radius: 999px; color: #fff; font-size: .72rem; font-weight: 700; padding: 5px 11px; position: absolute; right: 22px; top: 22px; }

        /* Security */
        .security-grid { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .sec-tile { background: var(--page-card); border: 1px solid var(--page-line); border-radius: var(--radius); padding: 30px 24px; }
        .sec-tile i { align-items: center; background: color-mix(in srgb, var(--gold) 22%, transparent); border-radius: 16px; color: var(--gold-light); display: inline-flex; font-size: 1.4rem; height: 54px; justify-content: center; margin-bottom: 40px; width: 54px; }
        .sec-tile strong { display: block; font-family: var(--display); font-size: 1.3rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.2; }

        /* FAQ */
        .faq-grid { align-items: start; display: grid; gap: clamp(30px, 6vw, 80px); grid-template-columns: .8fr 1.2fr; }
        .faq-aside { position: sticky; top: 110px; }
        .faq-aside h2 { font-size: clamp(2.3rem, 5vw, 4rem); font-weight: 800; line-height: .98; margin: 16px 0 26px; }
        .help-card { background: var(--ink); border-radius: 24px; color: #fff; padding: 22px; }
        .help-card strong { display: block; font-family: var(--display); font-size: 1.15rem; }
        .help-card p { color: rgba(255, 255, 255, .65); font-size: .9rem; line-height: 1.5; margin: 6px 0 16px; }
        .faq details { border-bottom: 1px solid var(--page-line); }
        .faq summary { align-items: center; cursor: pointer; display: flex; font-family: var(--display); font-size: clamp(1.1rem, 1.8vw, 1.4rem); font-weight: 700; gap: 20px; justify-content: space-between; letter-spacing: -.02em; list-style: none; padding: 26px 0; }
        .faq summary::-webkit-details-marker { display: none; }
        .faq summary i { align-items: center; border: 1px solid var(--page-line); border-radius: 50%; display: flex; flex-shrink: 0; height: 38px; justify-content: center; transition: transform .5s var(--ease), background-color .3s, color .3s; width: 38px; }
        .faq details[open] summary i { background: var(--gold); border-color: var(--gold); color: #fff; transform: rotate(45deg); }
        .faq details p { color: var(--page-muted); font-size: 1.05rem; line-height: 1.6; margin: 0; max-width: 640px; padding: 0 0 26px; }
        .faq details::details-content { block-size: 0; overflow: hidden; transition: block-size .5s var(--ease), content-visibility .5s allow-discrete; }
        .faq details[open]::details-content { block-size: auto; }

        /* CTA */
        .cta-grid { align-items: center; display: grid; gap: 40px; grid-template-columns: 1.2fr .8fr; }
        .cta h2 { font-size: clamp(2.4rem, 5.2vw, 4.6rem); font-weight: 800; line-height: .94; }
        .cta p { color: var(--page-muted); font-size: clamp(1.05rem, 1.5vw, 1.25rem); line-height: 1.55; margin: 22px 0 32px; max-width: 520px; }
        .cta-visual { display: grid; height: 640px; place-items: center; position: relative; }
        .cta-visual > * { grid-area: 1 / 1; }
        .cta-visual .phone { rotate: 6deg; }
        .cta-ring { animation: spin 50s linear infinite; border: 2px solid rgba(255, 255, 255, .35); border-radius: 50%; height: 520px; position: relative; width: 520px; }
        .cta-ring::before { background: #fff; border-radius: 50%; content: ""; height: 14px; left: 50%; position: absolute; top: -7px; width: 14px; }

        /* Footer */
        .footer { padding: 90px 0 30px; position: relative; }
        .footer-top { display: grid; gap: 30px; grid-template-columns: 1.4fr 1fr 1fr; }
        .footer-top p { color: var(--page-muted); line-height: 1.55; margin: 14px 0 0; max-width: 320px; }
        .footer h4 { color: var(--page-muted); font-size: .74rem; font-weight: 700; letter-spacing: .14em; margin: 0 0 14px; text-transform: uppercase; }
        .footer ul { display: grid; gap: 10px; list-style: none; margin: 0; padding: 0; }
        .footer ul a { opacity: .85; transition: opacity .2s, color .2s; }
        .footer ul a:hover { color: var(--gold); opacity: 1; }
        .wordmark { display: block; margin-top: 50px; overflow: visible; width: 100%; }
        .wordmark text { fill: var(--gold); font-family: var(--display); font-weight: 800; stroke: var(--gold); stroke-width: 2; }
        .wordmark.is-drawn text { fill: transparent; stroke-dasharray: 1600; stroke-dashoffset: 1600; }
        .footer-bottom { border-top: 1px solid var(--page-line); color: var(--page-muted); display: flex; flex-wrap: wrap; font-size: .82rem; gap: 12px; justify-content: space-between; margin-top: 30px; padding-top: 22px; }
        .to-top { align-items: center; border: 1px solid var(--page-line); border-radius: 50%; display: inline-flex; height: 36px; justify-content: center; width: 36px; }

        @media (max-width: 1060px) {
            .hero { grid-template-columns: 1fr; min-height: auto; padding-top: 130px; }
            .scroll-cue { display: none; }
            .section-head.is-split, .rep-grid, .split, .wallet-grid, .faq-grid, .cta-grid { grid-template-columns: 1fr; }
            .bento { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pricing { grid-template-columns: 1fr; margin: 0 auto; max-width: 520px; }
            .price.featured { margin: 0; }
            .security-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .faq-aside { position: static; }
            .cta-visual { display: none; }
        }
        @media (max-width: 900px) {
            .nav-links { display: none; }
            .journey-grid { gap: 0; grid-template-columns: 1fr; }
            .journey-device { align-self: start; background: linear-gradient(180deg, var(--page-bg) 84%, transparent); margin: 0 calc(-1 * clamp(16px, 4vw, 40px)); order: 0; padding: 74px 0 18px; position: sticky; top: 0; z-index: 3; }
            .journey-sticky { height: auto; position: static; }
            .journey-device .phone { zoom: .56; }
            .journey-num { font-size: 10rem; translate: -50% -52%; }
            .journey-glow { height: 360px; width: 360px; }
            .journey-track { --jh: 22vh; --jpad: 4vh; }
            .footer-top { grid-template-columns: 1fr 1fr; }
            .footer-top > :first-child { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .nav-actions .btn-ghost { display: none; }
            .brand > span:last-child { font-size: 1rem; }
            .hero { padding-top: 110px; }
            .hero-visual { height: 540px; min-height: 0; }
            .hero-phone .phone { zoom: .78; }
            .hero-disc { width: 330px; }
            .hero-orbit { width: 410px; }
            .chip { padding: 8px 11px 8px 8px; }
            .chip strong { font-size: .76rem; }
            .chip-ic { flex-basis: 30px; height: 30px; }
            .chip-c { display: none; }
            .chip-a { right: -4px; top: 6%; }
            .chip-b { bottom: 8%; left: -4px; }
            .chip-d { bottom: 0; right: -4px; }
            .bento { grid-template-columns: 1fr; }
            .card.is-wide { grid-column: auto; }
            .stat + .stat { border-left: 0; border-top: 1px solid var(--page-line); }
            .security-grid { grid-template-columns: 1fr; }
            .pass-stage { height: 440px; zoom: .74; }
            .hero-actions .btn { flex: 1; }
            .flow-card { flex-wrap: wrap; }
            .flow-card .badge { margin-left: 56px; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body data-theme="light">
<div class="site">
    <div class="progress" aria-hidden="true"></div>

    <nav class="nav" aria-label="Navigation principale">
        <div class="inner">
            <div class="nav-shell">
                <a href="{{ route('landing') }}" class="brand">
                    <span class="brand-mark">@if($brandLogo)<img src="{{ $brandLogo }}" alt="">@else<i class="bi {{ $brandIcon }}"></i>@endif</span>
                    <span>{{ $brandName }}</span>
                </a>
                <div class="nav-links">
                    @foreach($filterEnabled($settings['navigation'] ?? []) as $item)
                        <a href="#{{ $item['anchor'] ?? '' }}">{{ $item['label'] ?? '' }}</a>
                    @endforeach
                </div>
                <div class="nav-actions">
                    <x-landing.button :href="route('client.login')" variant="ghost" class="btn-sm">Connexion</x-landing.button>
                    <x-landing.button :href="route('register')" variant="gold" class="btn-sm" icon-after="bi-arrow-up-right">Créer un espace</x-landing.button>
                </div>
            </div>
        </div>
    </nav>

    @if($enabled($settings['hero'] ?? []))
    <header class="inner hero" data-theme-section="light">
        <div>
            @if(!empty($settings['hero']['eyebrow']))
                <div class="hero-badge" data-hero><span class="dot"></span>{{ $settings['hero']['eyebrow'] }}</div>
            @endif
            <h1 data-lines data-hero-title>@foreach($heroWords as $word)<span @class(['w', 'accent' => $loop->index >= $accentFrom])>{{ $word }}</span> @endforeach</h1>
            <p class="lead" data-hero>{{ $settings['hero']['body'] ?? '' }}</p>
            <div class="hero-actions" data-hero>
                <x-landing.button :href="route('register')" variant="primary" icon-after="bi-arrow-right">{{ $settings['hero']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                <x-landing.button :href="route('client.login')" variant="ghost">{{ $settings['hero']['secondary_label'] ?? 'Connexion' }}</x-landing.button>
            </div>
            @if($filterEnabled($settings['trust_items'] ?? [])->isNotEmpty())
                <div class="trust" data-hero>
                    @foreach($filterEnabled($settings['trust_items'] ?? []) as $item)
                        <span><i class="bi {{ $item['icon'] ?? 'bi-check2' }}"></i>{{ $item['label'] ?? '' }}</span>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="hero-visual" aria-hidden="true">
            <div class="hero-orbit" data-hero-orbit></div>
            <div class="hero-disc" data-hero-disc></div>
            <div class="hero-phone" data-hero-phone>
                <x-landing.phone data-hero-screens>
                    @foreach($journeyScreens as $screen)
                        <x-landing.screen :name="$screen" :active="$loop->first" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                    @endforeach
                </x-landing.phone>
            </div>
            @if(!empty($settings['hero']['floating_title']))
                <div class="chip chip-a" data-chip data-depth="40">
                    <span class="chip-ic green"><i class="bi bi-check-lg"></i><span class="ring"></span></span>
                    <div><strong>{{ $settings['hero']['floating_title'] }}</strong><small>{{ $settings['hero']['floating_text'] ?? '' }}</small></div>
                </div>
            @endif
            <div class="chip chip-c" data-chip data-depth="-30">
                <span class="chip-ic ink"><i class="bi bi-person-check"></i></span>
                <div><strong>{{ $guestFirst }} a confirmé</strong><small>{{ $demo['event_name'] ?? 'Il y a 2 min' }}</small></div>
            </div>
            @if($metrics->isNotEmpty())
                <div class="chip chip-b" data-chip data-depth="60">
                    <div class="chip-stats">
                        @foreach($metrics->take(3) as $metric)
                            @php $parts = $metricParts($metric['value'] ?? ''); @endphp
                            <div><span>{{ $metric['label'] ?? '' }}</span><b>@if($parts){{ $parts['prefix'] }}<span data-count="{{ $parts['number'] }}">{{ $parts['number'] }}</span>{{ $parts['suffix'] }}@else{{ $metric['value'] ?? '' }}@endif</b></div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="chip chip-d" data-chip data-depth="-50">
                <span class="chip-ic gold"><i class="bi bi-wallet2"></i></span>
                <div><strong>Ajoutée au Wallet</strong><small>Apple &amp; Google Wallet</small></div>
            </div>
        </div>
        <div class="scroll-cue" aria-hidden="true"><i></i>Défiler</div>
    </header>
    @endif

    @if($ticker->isNotEmpty())
        <div class="ticker" aria-hidden="true">
            <div class="ticker-track">
                @foreach([$ticker, $ticker] as $set)
                    @foreach($set as $item)<span class="ticker-item"><i class="bi {{ $item['icon'] }}"></i>{{ $item['label'] }}</span>@endforeach
                @endforeach
            </div>
        </div>
    @endif

    <main>
        @if($enabled($settings['features_section'] ?? []) && $features->isNotEmpty())
        <section class="section" id="features" data-theme-section="light">
            <div class="inner">
                <x-landing.section-head align="split" :eyebrow="$settings['features_section']['eyebrow'] ?? null" :title="$settings['features_section']['title'] ?? null" :body="$settings['features_section']['body'] ?? null" />
                <div class="bento">
                    @foreach($features as $feature)
                        <article @class(['card', 'is-dark' => $loop->index === 0, 'is-gold' => $loop->index === 4, 'is-wide' => in_array($loop->index, $wideCards, true)]) data-card>
                            <div class="card-head"><span class="card-icon"><i class="bi {{ $feature['icon'] ?? 'bi-check2' }}"></i></span><span class="card-num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span></div>
                            <div class="stage" aria-hidden="true">
                                @switch($motionFor($feature['icon'] ?? null))
                                    @case('scan')<div class="m-scan"><x-landing.qr seed="bento-{{ $loop->index }}" /><span class="laser"></span><span class="ok"><i class="bi bi-check-lg"></i></span></div>@break
                                    @case('wallet')<div class="m-wallet"><span></span><span></span><span></span></div>@break
                                    @case('bell')<div class="m-bell"><i class="bi bi-bell-fill"></i><b>3</b></div>@break
                                    @case('chat')<div class="m-chat"><span>Serez-vous des nôtres ?</span><span>Oui, avec plaisir !</span><span>Votre carte est prête</span></div>@break
                                    @case('mail')<div class="m-mail"><span class="back"></span><span class="letter"></span><span class="env"></span></div>@break
                                    @case('calendar')<div class="m-cal"><small>Jour J</small><b><span>J-2<br>J-1<br>J-2</span></b></div>@break
                                    @case('people')<div class="m-people"><span class="av">{{ $guestInitials }}</span><i class="bi bi-arrow-right"></i><span class="av">EN</span></div>@break
                                    @case('chart')<div class="m-chart"><span></span><span></span><span></span><span></span><span></span></div>@break
                                    @case('map')<div class="m-map"><svg viewBox="0 0 200 150" preserveAspectRatio="none"><path d="M20 130 C 60 120, 50 70, 100 72 S 150 30, 162 36"/></svg><i class="bi bi-geo-alt-fill pin"></i></div>@break
                                    @default<div class="m-pulse"><i class="bi {{ $feature['icon'] ?? 'bi-stars' }}"></i></div>
                                @endswitch
                            </div>
                            <h3>{{ $feature['title'] ?? '' }}</h3>
                            <p>{{ $feature['body'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($metrics->isNotEmpty())
        <section class="inner" data-theme-section="light">
            <div class="stats">
                @foreach($metrics as $metric)
                    @php $parts = $metricParts($metric['value'] ?? ''); @endphp
                    <div class="stat" data-reveal>
                        <strong>@if($parts){{ $parts['prefix'] }}<span data-count="{{ $parts['number'] }}">{{ $parts['number'] }}</span>@if($parts['suffix'] !== '')<small>{{ $parts['suffix'] }}</small>@endif @else{{ $metric['value'] ?? '' }}@endif</strong>
                        <span>{{ $metric['label'] ?? '' }}</span>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        @if($heroImage)
        <section class="section" data-theme-section="light">
            <div class="inner">
                <div class="showcase-frame" data-showcase>
                    <img src="{{ $heroImage }}" alt="{{ $settings['hero']['image_alt'] ?? '' }}" loading="lazy">
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['journey'] ?? []) && $journeySteps->isNotEmpty())
        <section class="section journey" id="parcours" data-journey data-theme-section="dark">
            <div class="inner">
                <x-landing.section-head align="split" :eyebrow="$settings['journey']['eyebrow'] ?? null" :title="$settings['journey']['title'] ?? null" :body="$settings['journey']['body'] ?? null" />
                <div class="journey-grid">
                    <div class="journey-device">
                        <div class="journey-sticky">
                            <div class="journey-glow" aria-hidden="true"></div>
                            <div class="journey-num" aria-hidden="true"><span data-journey-num>01</span></div>
                            <x-landing.phone>
                                @foreach($journeyScreens as $screen)
                                    <x-landing.screen :name="$screen" :active="$screen === ($journeySteps->first()['screen'] ?? 'invitation')" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                                @endforeach
                            </x-landing.phone>
                        </div>
                    </div>
                    <div class="journey-track">
                        <span class="journey-rail" aria-hidden="true"><span></span></span>
                        <ol class="journey-steps">
                            @foreach($journeySteps as $step)
                                <li @class(['jstep', 'is-active' => $loop->first]) data-screen="{{ $step['screen'] ?? 'invitation' }}">
                                    <small>Étape {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</small>
                                    <h3>{{ $step['title'] ?? '' }}</h3>
                                    <p>{{ $step['body'] ?? '' }}</p>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['representation'] ?? []))
        <section class="section" data-theme-section="dark">
            <div class="inner rep-grid">
                <div>
                    @if(!empty($settings['representation']['eyebrow']))<div class="eyebrow" data-reveal>{{ $settings['representation']['eyebrow'] }}</div>@endif
                    <h2 data-lines>{{ $settings['representation']['title'] ?? '' }}</h2>
                    <p data-reveal>{{ $settings['representation']['body'] ?? '' }}</p>
                    @if(!empty($settings['representation']['items']))
                        <ul class="rep-items">
                            @foreach($settings['representation']['items'] as $item)<li data-reveal><i class="bi bi-check-circle-fill"></i>{{ $item }}</li>@endforeach
                        </ul>
                    @endif
                </div>
                <div class="flow" data-flow aria-hidden="true">
                    <span class="flow-line"><span></span></span>
                    <div class="flow-card" data-reveal><span class="avatar">{{ $guestInitials }}</span><div><strong>{{ $guestName }}</strong><small>Se fait représenter par Eric N.</small></div><span class="badge ok">Confirmé</span></div>
                    <div class="flow-card" data-reveal><span class="avatar alt">EN</span><div><strong>Eric N.</strong><small>Carte représentant envoyée</small></div><span class="badge ok">Wallet prêt</span></div>
                    <div class="flow-card" data-reveal><span class="avatar alt"><i class="bi bi-clock-history"></i></span><div><strong>Organisateur</strong><small>Historique complet conservé</small></div><span class="badge wait">Tracé</span></div>
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['checkin'] ?? []))
        <section class="section" id="checkin" data-theme-section="light">
            <div class="inner split">
                <div class="device" data-reveal aria-hidden="true">
                    <div class="viewfinder">
                        <span class="corner tl"></span><span class="corner tr"></span><span class="corner bl"></span><span class="corner br"></span>
                        <i class="bi bi-qr-code qr"></i>
                        <span class="laser"></span>
                    </div>
                    <div class="feed" data-feed>
                        <div class="feed-row"><span class="avatar">{{ $guestInitials }}</span><div><strong>{{ $guestName }}</strong><small>{{ $demo['guest_tier'] ?? 'VIP' }} · QR scanné à 18:42</small></div><span class="badge ok">Entrée validée</span></div>
                        <div class="feed-row"><span class="avatar alt">JB</span><div><strong>Jean B.</strong><small>Déjà scanné à 18:18</small></div><span class="badge wait">Double scan</span></div>
                        <div class="feed-row"><span class="avatar alt">EN</span><div><strong>Eric N.</strong><small>Représentant autorisé</small></div><span class="badge ok">Autorisé</span></div>
                    </div>
                </div>
                <x-landing.section-head align="left" :eyebrow="$settings['checkin']['eyebrow'] ?? null" :title="$settings['checkin']['title'] ?? null" :body="$settings['checkin']['body'] ?? null" style="margin-bottom:0" />
            </div>
        </section>
        @endif

        @if($enabled($settings['wallet'] ?? []))
        <section class="section" id="wallet" data-theme-section="light">
            <div class="inner">
                <div class="wallet-grid">
                    <div>
                        <x-landing.section-head align="left" :eyebrow="$settings['wallet']['eyebrow'] ?? null" :title="$settings['wallet']['title'] ?? null" :body="$settings['wallet']['body'] ?? null" style="margin-bottom:0" />
                        <div class="platforms">
                            <div class="platform" data-reveal><span class="platform-ic is-apple"><i class="bi bi-apple"></i></span><div><strong>{{ $settings['wallet']['apple_label'] ?? 'Apple Wallet' }}</strong><small>{{ $settings['wallet']['apple_text'] ?? '' }}</small></div><i class="bi bi-check-circle-fill platform-ok"></i></div>
                            <div class="platform" data-reveal><span class="platform-ic is-google"><i class="bi bi-google"></i></span><div><strong>{{ $settings['wallet']['google_label'] ?? 'Google Wallet' }}</strong><small>{{ $settings['wallet']['google_text'] ?? '' }}</small></div><i class="bi bi-check-circle-fill platform-ok"></i></div>
                        </div>
                        @if(!empty($settings['wallet']['items']))
                            <ul class="wallet-list">@foreach($settings['wallet']['items'] as $item)<li data-reveal><i class="bi bi-check2"></i>{{ $item }}</li>@endforeach</ul>
                        @endif
                        <div class="wallet-badges" data-reveal aria-hidden="true">
                            <span class="add-badge"><i class="bi bi-apple"></i><span><small>Ajouter à</small>{{ $settings['wallet']['apple_label'] ?? 'Apple Wallet' }}</span></span>
                            <span class="add-badge"><i class="bi bi-google"></i><span><small>Ajouter à</small>{{ $settings['wallet']['google_label'] ?? 'Google Wallet' }}</span></span>
                        </div>
                    </div>
                    <div class="pass-stage" data-passes data-reveal aria-hidden="true">
                        <div class="pass-slot pass-slot-apple">
                            <span class="pass-label"><i class="bi bi-apple"></i>{{ $settings['wallet']['apple_label'] ?? 'Apple Wallet' }}</span>
                            <x-landing.wallet-pass variant="apple" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                        </div>
                        <div class="pass-slot pass-slot-google">
                            <span class="pass-label"><i class="bi bi-google"></i>{{ $settings['wallet']['google_label'] ?? 'Google Wallet' }}</span>
                            <x-landing.wallet-pass variant="google" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                        </div>
                    </div>
                </div>
                @if($walletImage)
                    <div class="wallet-stage">
                        <div class="wallet-media" data-showcase>
                            <img src="{{ $walletImage }}" alt="{{ $settings['wallet']['image_alt'] ?? '' }}" loading="lazy">
                            @if(!empty($settings['wallet']['caption_title']))
                                <div class="chip wallet-caption"><span class="chip-ic gold"><i class="bi bi-phone"></i></span><div><strong>{{ $settings['wallet']['caption_title'] }}</strong><small>{{ $settings['wallet']['caption_text'] ?? '' }}</small></div></div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </section>
        @endif

        @if($enabled($settings['pricing_section'] ?? []))
        <section class="section" id="pricing" data-theme-section="light">
            <div class="inner">
                <x-landing.section-head align="split" :eyebrow="$settings['pricing_section']['eyebrow'] ?? null" :title="$settings['pricing_section']['title'] ?? null" :body="$settings['pricing_section']['body'] ?? null" />
                <div class="pricing">
                    @foreach($filterEnabled($settings['plans'] ?? []) as $plan)
                        <article @class(['price', 'featured' => !empty($plan['featured'])]) data-reveal>
                            @if(!empty($plan['featured']))<span class="tag">Recommandé</span>@endif
                            <h3>{{ $plan['name'] ?? '' }}</h3>
                            <p class="sub">{{ $plan['subtitle'] ?? '' }}</p>
                            <span class="amount">{{ $plan['price'] ?? '' }}</span>
                            <ul>@foreach($plan['items'] ?? [] as $item)<li><i class="bi bi-check-circle-fill"></i>{{ $item }}</li>@endforeach</ul>
                            <x-landing.button :href="route('register')" :variant="!empty($plan['featured']) ? 'gold' : 'ghost'" icon-after="bi-arrow-right">{{ $settings['hero']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['security'] ?? []))
        <section class="section" data-theme-section="dark">
            <div class="inner">
                <x-landing.section-head align="split" :eyebrow="$settings['security']['eyebrow'] ?? null" :title="$settings['security']['title'] ?? null" :body="$settings['security']['body'] ?? null" />
                <div class="security-grid">
                    @foreach($filterEnabled($settings['security']['items'] ?? []) as $item)
                        <div class="sec-tile" data-reveal><i class="bi {{ $item['icon'] ?? 'bi-shield-check' }}"></i><strong>{{ $item['label'] ?? '' }}</strong></div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['faq_section'] ?? []))
        <section class="section" id="faq" data-theme-section="light">
            <div class="inner faq-grid">
                <div class="faq-aside">
                    @if(!empty($settings['faq_section']['eyebrow']))<div class="eyebrow" data-reveal>{{ $settings['faq_section']['eyebrow'] }}</div>@endif
                    <h2 data-lines>{{ $settings['faq_section']['title'] ?? '' }}</h2>
                    <div class="help-card" data-reveal>
                        <strong>Prêt à vous lancer ?</strong>
                        <p>Créez votre espace et envoyez votre première invitation en quelques minutes.</p>
                        <x-landing.button :href="route('register')" variant="gold" class="btn-sm" icon-after="bi-arrow-right">{{ $settings['cta']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                    </div>
                </div>
                <div class="faq">
                    @foreach($filterEnabled($settings['faqs'] ?? []) as $faq)
                        <details data-reveal @if($loop->first) open @endif>
                            <summary>{{ $faq['question'] ?? '' }}<i class="bi bi-plus-lg" aria-hidden="true"></i></summary>
                            <p>{{ $faq['answer'] ?? '' }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['cta'] ?? []))
        <section class="section cta" data-theme-section="brand">
            <div class="inner cta-grid">
                <div>
                    <h2 data-lines>{{ $settings['cta']['title'] ?? '' }}</h2>
                    <p data-reveal>{{ $settings['cta']['body'] ?? '' }}</p>
                    <div class="hero-actions" data-reveal style="margin-top:0">
                        <x-landing.button :href="route('register')" variant="light" icon-after="bi-arrow-right">{{ $settings['cta']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                        <x-landing.button :href="route('client.login')" variant="outline-light">{{ $settings['cta']['secondary_label'] ?? 'Connexion' }}</x-landing.button>
                    </div>
                </div>
                <div class="cta-visual" aria-hidden="true">
                    <span class="cta-ring"></span>
                    <x-landing.phone data-cta-phone>
                        <x-landing.screen name="wallet" :active="true" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                    </x-landing.phone>
                </div>
            </div>
        </section>
        @endif
    </main>

    <footer class="footer" data-theme-section="dark">
        <div class="inner">
            <div class="footer-top">
                <div>
                    <a href="{{ route('landing') }}" class="brand"><span class="brand-mark">@if($brandLogo)<img src="{{ $brandLogo }}" alt="">@else<i class="bi {{ $brandIcon }}"></i>@endif</span><span>{{ $footerBrand }}</span></a>
                    <p>{{ $settings['footer']['text'] ?? '' }}</p>
                </div>
                <div>
                    <h4>Découvrir</h4>
                    <ul>@foreach($filterEnabled($settings['navigation'] ?? []) as $item)<li><a href="#{{ $item['anchor'] ?? '' }}">{{ $item['label'] ?? '' }}</a></li>@endforeach</ul>
                </div>
                <div>
                    <h4>Espace</h4>
                    <ul>
                        <li><a href="{{ route('register') }}">Créer un espace</a></li>
                        <li><a href="{{ route('client.login') }}">Connexion</a></li>
                    </ul>
                </div>
            </div>
            <svg class="wordmark" viewBox="0 0 1200 220" aria-hidden="true" data-wordmark>
                <text x="600" y="180" text-anchor="middle" font-size="200" textLength="1180" lengthAdjust="spacingAndGlyphs">{{ $footerBrand }}</text>
            </svg>
            <div class="footer-bottom">
                <span>© {{ now()->year }} {{ $footerBrand }}. Tous droits réservés.</span>
                <a href="#" class="to-top" aria-label="Revenir en haut"><i class="bi bi-arrow-up"></i></a>
            </div>
        </div>
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
<script>
(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const gsap = window.gsap, ST = window.ScrollTrigger;
    const $ = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
    const fmt = new Intl.NumberFormat('fr-FR');
    const setTheme = (t) => { if (t && document.body.dataset.theme !== t) document.body.dataset.theme = t; };

    // Navigation state + reading progress
    const nav = $('.nav'), progress = $('.progress');
    const onScroll = () => {
        nav.classList.toggle('is-scrolled', scrollY > 10);
        const max = document.documentElement.scrollHeight - innerHeight;
        progress.style.transform = `scaleX(${max > 0 ? scrollY / max : 0})`;
    };
    addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    // Hero phone cycles through the invitee journey screens.
    const heroScreens = $$('[data-hero-screens] .scr');
    if (heroScreens.length > 1 && !reduced) {
        let i = 0;
        setInterval(() => {
            if (document.hidden) return;
            heroScreens[i].classList.remove('is-on');
            i = (i + 1) % heroScreens.length;
            heroScreens[i].classList.add('is-on');
        }, 2800);
    }

    // Live check-in feed, wallet passes swap + tilt
    const feed = $('[data-feed]');
    if (feed && !reduced) setInterval(() => { if (document.hidden) return; const row = feed.lastElementChild; row.classList.remove('enter'); void row.offsetWidth; feed.prepend(row); row.classList.add('enter'); }, 2600);
    const passes = $('[data-passes]');
    if (passes && !reduced) {
        setInterval(() => { if (!document.hidden && !passes.matches(':hover')) passes.classList.toggle('is-swapped'); }, 3800);
        passes.addEventListener('click', () => passes.classList.toggle('is-swapped'));
        if (matchMedia('(hover: hover)').matches) {
            passes.addEventListener('pointermove', (e) => { const r = passes.getBoundingClientRect(); passes.style.setProperty('--ry', `${((e.clientX - r.left) / r.width - .5) * 10}deg`); passes.style.setProperty('--rx', `${((e.clientY - r.top) / r.height - .5) * -10}deg`); });
            passes.addEventListener('pointerleave', () => { passes.style.setProperty('--rx', '0deg'); passes.style.setProperty('--ry', '0deg'); });
        }
    }

    // Journey: the step closest to the centre drives the phone, the giant number and the rail.
    const journey = $('[data-journey]');
    if (journey) {
        const steps = $$('.jstep', journey), screens = $$('.scr', journey), num = $('[data-journey-num]', journey), rail = $('.journey-rail', journey), device = $('.journey-device', journey);
        let current = 0, ticking = false;
        const update = () => {
            ticking = false;
            const top = innerWidth <= 900 ? Math.max(0, device.getBoundingClientRect().bottom) : 0;
            const mid = (top + innerHeight) / 2;
            let best = 0, dist = Infinity;
            steps.forEach((s, i) => { const r = s.getBoundingClientRect(); const d = Math.abs(r.top + r.height / 2 - mid); if (d < dist) { dist = d; best = i; } });
            const first = steps[0].getBoundingClientRect(), last = steps[steps.length - 1].getBoundingClientRect();
            const span = (last.top + last.height / 2) - (first.top + first.height / 2);
            rail.style.setProperty('--jp', span > 0 ? Math.min(1, Math.max(0, (mid - first.top - first.height / 2) / span)) : 1);
            if (best === current) return;
            current = best;
            steps.forEach((s, i) => s.classList.toggle('is-active', i === best));
            screens.forEach((s) => { const on = s.dataset.screen === steps[best].dataset.screen; s.classList.toggle('is-on', on); s.setAttribute('aria-hidden', on ? 'false' : 'true'); });
            const label = String(best + 1).padStart(2, '0');
            if (gsap && !reduced) gsap.fromTo(num, { y: 80, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .7, ease: 'power3.out', overwrite: true, onStart: () => { num.textContent = label; } });
            else num.textContent = label;
        };
        addEventListener('scroll', () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } }, { passive: true });
        addEventListener('resize', update);
        update();
    }

    const countUp = (el) => {
        const target = parseInt(el.dataset.count, 10) || 0;
        if (reduced || !gsap || !target) { el.textContent = fmt.format(target); return; }
        const obj = { v: 0 };
        gsap.to(obj, { v: target, duration: 1.8, ease: 'power3.out', onUpdate: () => { el.textContent = fmt.format(Math.round(obj.v)); } });
    };

    // Without GSAP or with reduced motion: content is static, only the theme follows the scroll.
    if (!gsap || !ST || reduced) {
        $$('[data-count]').forEach(countUp);
        $$('[data-card]').forEach((c) => c.classList.add('is-inview'));
        const io = new IntersectionObserver((entries) => entries.forEach((e) => { if (e.isIntersecting) setTheme(e.target.dataset.themeSection); }), { rootMargin: '-50% 0px -50% 0px' });
        $$('[data-theme-section]').forEach((s) => io.observe(s));
        return;
    }

    gsap.registerPlugin(ST);
    gsap.defaults({ ease: 'power3.out' });

    // Splits a heading into masked lines, keeping existing word spans (and their accent class).
    const splitLines = (el) => {
        if (!el.querySelector('.w')) {
            const words = el.textContent.trim().split(/\s+/);
            el.textContent = '';
            words.forEach((w, i) => { const s = document.createElement('span'); s.className = 'w'; s.textContent = w; el.append(s); if (i < words.length - 1) el.append(' '); });
        }
        el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
        const lines = [];
        $$('.w', el).forEach((w) => { const top = w.offsetTop; const last = lines[lines.length - 1]; if (!last || Math.abs(last.top - top) > 4) lines.push({ top, words: [w] }); else last.words.push(w); });
        el.textContent = '';
        lines.forEach((line) => {
            const outer = document.createElement('span'); outer.className = 'line'; outer.setAttribute('aria-hidden', 'true');
            const inner = document.createElement('span'); inner.className = 'line-in';
            line.words.forEach((w, i) => { inner.append(w); if (i < line.words.length - 1) inner.append(' '); });
            outer.append(inner); el.append(outer);
        });
    };

    // Hide what will be revealed before the first paint of the intro.
    gsap.set('[data-reveal], [data-card]', { autoAlpha: 0 });
    gsap.set('[data-hero], [data-hero-title], [data-hero-phone], [data-hero-disc], [data-hero-orbit], [data-chip]', { autoAlpha: 0 });

    document.fonts.ready.then(() => {
        $$('[data-lines]').forEach(splitLines);
        const heroTitle = $('[data-hero-title]');

        // Page theme follows the section crossing the middle of the viewport.
        $$('[data-theme-section]').forEach((section) => ST.create({ trigger: section, start: 'top 50%', end: 'bottom 50%', onToggle: (self) => { if (self.isActive) setTheme(section.dataset.themeSection); } }));

        // Hero intro
        const heroBits = $$('[data-hero]');
        gsap.timeline({ delay: .1 })
            .fromTo(heroBits.slice(0, 1), { y: 20, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .7 })
            .set(heroTitle, { autoAlpha: 1 }, 0)
            .from(heroTitle ? $$('.line-in', heroTitle) : [], { yPercent: 115, duration: 1.1, ease: 'power4.out', stagger: .1 }, .1)
            .fromTo(heroBits.slice(1), { y: 24, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .8, stagger: .1 }, .55)
            .fromTo('[data-hero-disc]', { scale: .4, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: 1.3, ease: 'expo.out' }, .2)
            .fromTo('[data-hero-orbit]', { scale: .6, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: 1.4, ease: 'expo.out' }, .3)
            .fromTo('[data-hero-phone]', { y: 120, rotation: -8, autoAlpha: 0 }, { y: 0, rotation: 0, autoAlpha: 1, duration: 1.3, ease: 'expo.out' }, .4)
            .fromTo('[data-chip]', { scale: .6, y: 30, autoAlpha: 0 }, { scale: 1, y: 0, autoAlpha: 1, duration: .8, ease: 'back.out(1.8)', stagger: .15 }, 1)
            .add(() => $$('.chip-b [data-count]').forEach(countUp), 1.1)
            .from('.scroll-cue', { autoAlpha: 0, duration: .6 }, 1.6)
            .add(() => {
                // Idle bob once the chips have landed
                $$('[data-chip]').forEach((chip, i) => gsap.to(chip, { y: 10, duration: 2.4 + i * .3, repeat: -1, yoyo: true, ease: 'sine.inOut' }));
            });

        // Scroll parallax on the hero layers (yPercent so it composes with the bob on y)
        const heroScrub = { trigger: '.hero', start: 'top top', end: 'bottom top', scrub: true };
        $$('[data-chip]').forEach((chip) => gsap.to(chip, { yPercent: -(parseFloat(chip.dataset.depth) || 30), ease: 'none', scrollTrigger: heroScrub }));
        gsap.to('[data-hero-phone]', { yPercent: -8, ease: 'none', scrollTrigger: heroScrub });
        gsap.to('[data-hero-disc]', { scale: 1.18, ease: 'none', scrollTrigger: heroScrub });

        // Headings: masked line-by-line reveal
        $$('[data-lines]').filter((el) => el !== heroTitle).forEach((el) => {
            gsap.from($$('.line-in', el), { yPercent: 115, duration: 1.05, ease: 'power4.out', stagger: .09, scrollTrigger: { trigger: el, start: 'top 88%' } });
        });

        // Generic reveals, batched so neighbours stagger naturally
        ST.batch('[data-reveal]', { start: 'top 92%', onEnter: (els) => gsap.fromTo(els, { y: 40, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .9, stagger: .08, overwrite: true }) });

        // Bento cards rise in, then start their micro-animation
        ST.batch('[data-card]', {
            start: 'top 90%',
            onEnter: (els) => {
                gsap.fromTo(els, { y: 70, rotation: (i) => (i % 2 ? 1.5 : -1.5), autoAlpha: 0 }, { y: 0, rotation: 0, autoAlpha: 1, duration: 1, stagger: .1, overwrite: true, clearProps: 'transform' });
                els.forEach((c) => c.classList.add('is-inview'));
            },
        });

        // Stat counters
        $$('.stats [data-count]').forEach((el) => ST.create({ trigger: el, start: 'top 90%', once: true, onEnter: () => countUp(el) }));

        // Showcase images open up as they enter
        $$('[data-showcase]').forEach((el) => gsap.fromTo(el, { scale: .86, borderRadius: 64 }, { scale: 1, borderRadius: 36, ease: 'none', scrollTrigger: { trigger: el, start: 'top 95%', end: 'top 30%', scrub: true } }));

        // Representation flow line draws with the scroll
        const flowLine = $('[data-flow] .flow-line span');
        if (flowLine) gsap.to(flowLine, { scaleY: 1, ease: 'none', scrollTrigger: { trigger: '[data-flow]', start: 'top 75%', end: 'bottom 55%', scrub: true } });

        // CTA phone floats in
        const ctaPhone = $('[data-cta-phone]');
        if (ctaPhone) gsap.from(ctaPhone, { y: 160, rotation: 12, autoAlpha: 0, duration: 1.4, ease: 'expo.out', scrollTrigger: { trigger: '.cta', start: 'top 70%' } });

        // Footer wordmark: stroke draws itself, then fills with gold
        const wordmark = $('[data-wordmark]'), word = wordmark && $('text', wordmark);
        if (word) {
            wordmark.classList.add('is-drawn');
            gsap.timeline({ scrollTrigger: { trigger: wordmark, start: 'top 98%', end: 'bottom 90%', scrub: 1 } })
                .to(word, { strokeDashoffset: 0, ease: 'none', duration: 1 })
                .to(word, { fill: getComputedStyle(word).stroke || '#b98943', ease: 'none', duration: .35 });
        }

        ST.refresh();
    });
})();
</script>
</body>
</html>
