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
    $accentFrom = max(0, $heroWords->count() - 2);
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
@endphp
<!doctype html>
<html lang="fr" class="no-js">
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @if($heroImage)<link rel="preload" as="image" href="{{ $heroImage }}">@endif
    <script>document.documentElement.classList.replace('no-js', 'js');</script>
    <style>
        :root {
            --ink: {{ $settings['theme']['ink'] ?? '#171713' }};
            --gold: {{ $settings['theme']['gold'] ?? '#b98943' }};
            --bg: {{ $settings['theme']['background'] ?? '#fbf8f1' }};
            --text: color-mix(in srgb, var(--ink) 88%, #fff);
            --muted: color-mix(in srgb, var(--ink) 55%, #fff);
            --line: color-mix(in srgb, var(--ink) 9%, transparent);
            --panel: color-mix(in srgb, var(--bg) 35%, #fff);
            --gold-soft: color-mix(in srgb, var(--gold) 14%, transparent);
            --gold-light: color-mix(in srgb, var(--gold) 55%, #fff);
            --gold-deep: color-mix(in srgb, var(--gold) 78%, #000);
            --green: #2e7b65;
            --ease: cubic-bezier(.16, 1, .3, 1);
            --radius: 28px;
            --font: -apple-system, BlinkMacSystemFont, "SF Pro Display", Inter, ui-sans-serif, system-ui, "Segoe UI", sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; min-width: 0; }
        html { scroll-behavior: smooth; scroll-padding-top: 72px; -webkit-text-size-adjust: 100%; }
        body { background: var(--bg); color: var(--text); font-family: var(--font); margin: 0; -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility; }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
        ::selection { background: var(--gold); color: #fff; }
        .site { overflow-x: clip; position: relative; }
        .inner { margin: 0 auto; max-width: 1180px; padding: 0 clamp(18px, 4vw, 40px); }
        .section { padding: clamp(80px, 12vw, 160px) 0; position: relative; }

        /* Progress + navigation */
        .progress { background: linear-gradient(90deg, var(--gold-deep), var(--gold), var(--gold-light)); height: 2px; left: 0; position: fixed; right: 0; top: 0; transform: scaleX(var(--p, 0)); transform-origin: 0 50%; z-index: 60; }
        .nav { backdrop-filter: saturate(180%) blur(20px); -webkit-backdrop-filter: saturate(180%) blur(20px); background: color-mix(in srgb, var(--bg) 72%, transparent); border-bottom: 1px solid transparent; left: 0; position: fixed; right: 0; top: 0; transition: border-color .4s, background .4s; z-index: 50; }
        .nav.is-scrolled { background: color-mix(in srgb, var(--bg) 84%, transparent); border-color: var(--line); }
        .nav .inner { align-items: center; display: flex; gap: 24px; height: 60px; justify-content: space-between; }
        .brand { align-items: center; color: var(--ink); display: flex; font-size: .98rem; font-weight: 700; gap: 10px; letter-spacing: -.01em; }
        .brand-mark { align-items: center; background: var(--ink); border-radius: 10px; color: #fff; display: flex; height: 32px; justify-content: center; overflow: hidden; width: 32px; }
        .brand-mark img { height: 100%; object-fit: cover; width: 100%; }
        .nav-links { display: flex; gap: 28px; }
        .nav-links a { color: var(--muted); font-size: .84rem; transition: color .25s; }
        .nav-links a:hover { color: var(--ink); }
        .nav-actions { align-items: center; display: flex; gap: 8px; }

        /* Buttons */
        .btn { align-items: center; border: 1px solid transparent; border-radius: 999px; cursor: pointer; display: inline-flex; font-size: .95rem; font-weight: 600; gap: 8px; justify-content: center; letter-spacing: -.01em; min-height: 46px; padding: 0 22px; position: relative; transition: transform .5s var(--ease), box-shadow .5s var(--ease), background .3s, color .3s; white-space: nowrap; }
        .btn:active { transform: scale(.97); }
        .btn-primary { background: var(--ink); color: #fff; box-shadow: 0 10px 30px -10px color-mix(in srgb, var(--ink) 60%, transparent); }
        .btn-primary:hover { box-shadow: 0 18px 40px -12px color-mix(in srgb, var(--ink) 70%, transparent); transform: translateY(-2px); }
        .btn-gold { background: linear-gradient(135deg, var(--gold-light), var(--gold) 45%, var(--gold-deep)); color: #fff; box-shadow: 0 12px 34px -12px var(--gold); }
        .btn-gold:hover { transform: translateY(-2px); box-shadow: 0 20px 44px -14px var(--gold); }
        .btn-ghost { border-color: var(--line); color: var(--ink); background: color-mix(in srgb, #fff 55%, transparent); }
        .btn-ghost:hover { border-color: color-mix(in srgb, var(--ink) 22%, transparent); }
        .btn-link { color: var(--gold-deep); min-height: auto; padding: 0 4px; }
        .btn-link i { transition: transform .4s var(--ease); }
        .btn-link:hover i { transform: translateX(4px); }
        .btn-sm { font-size: .8rem; min-height: 34px; padding: 0 14px; }
        .btn-light { background: #fff; color: var(--ink); }
        .btn-outline-light { border-color: rgba(255, 255, 255, .22); color: #fff; }
        .btn-outline-light:hover { border-color: rgba(255, 255, 255, .5); }

        /* Typography */
        .eyebrow { align-items: center; color: var(--gold-deep); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 8px; letter-spacing: .02em; }
        h1, h2, h3 { color: var(--ink); letter-spacing: -.035em; margin: 0; }
        .section-head { margin: 0 auto clamp(48px, 7vw, 80px); max-width: 820px; text-align: center; }
        .section-head.is-left { margin-left: 0; text-align: left; }
        .section-head h2 { font-size: clamp(2.1rem, 5.2vw, 4rem); font-weight: 700; line-height: 1.04; margin: 14px 0 18px; }
        .section-head p { color: var(--muted); font-size: clamp(1.05rem, 1.6vw, 1.3rem); line-height: 1.5; margin: 0 auto; max-width: 680px; }
        .section-head.is-left p { margin-left: 0; }
        .dark { background: var(--ink); color: #f5f5f7; }
        .dark h2, .dark h3, .dark .section-head h2 { color: #f5f5f7; }
        .dark .section-head p, .dark p { color: rgba(245, 245, 247, .62); }
        .dark .eyebrow { color: var(--gold-light); }
        .gold-text { background: linear-gradient(110deg, var(--gold-deep), var(--gold) 35%, var(--gold-light) 50%, var(--gold) 65%, var(--gold-deep)); background-size: 220% 100%; -webkit-background-clip: text; background-clip: text; color: transparent; animation: shimmer 6s linear infinite; }

        /* Hero */
        .hero { padding: clamp(120px, 16vw, 168px) 0 0; text-align: center; }
        .hero-badge { align-items: center; background: color-mix(in srgb, #fff 60%, transparent); border: 1px solid var(--line); border-radius: 999px; color: var(--ink); display: inline-flex; font-size: .82rem; font-weight: 500; gap: 10px; padding: 7px 16px 7px 10px; }
        .pulse { background: var(--green); border-radius: 50%; box-shadow: 0 0 0 0 color-mix(in srgb, var(--green) 60%, transparent); height: 8px; width: 8px; animation: pulse 2.2s infinite; }
        .hero h1 { font-size: clamp(2.7rem, 7.6vw, 6.4rem); font-weight: 700; letter-spacing: -.05em; line-height: 1; margin: 26px auto 26px; max-width: 1040px; }
        .hero h1 .w { display: inline-block; padding-bottom: .08em; }
        .js .hero h1 .w { filter: blur(12px); opacity: 0; transform: translateY(.45em); animation: wordIn 1.1s var(--ease) forwards; animation-delay: calc(var(--i) * 70ms + 120ms); }
        .hero .lead { color: var(--muted); font-size: clamp(1.08rem, 1.9vw, 1.42rem); line-height: 1.48; margin: 0 auto; max-width: 720px; }
        .hero-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; margin-top: 36px; }
        .js .hero-fade { opacity: 0; transform: translateY(16px); animation: fadeUp 1s var(--ease) forwards; animation-delay: var(--d, .6s); }
        .trust-row { display: flex; flex-wrap: wrap; gap: 8px 22px; justify-content: center; margin-top: 30px; }
        .trust-item { align-items: center; color: var(--muted); display: flex; font-size: .86rem; gap: 7px; }
        .trust-item i { color: var(--gold); }

        .hero-stage { margin: clamp(56px, 8vw, 88px) auto 0; max-width: 1280px; padding: 0 clamp(12px, 3vw, 32px); perspective: 1600px; }
        .hero-media { border-radius: calc(var(--radius) + 12px); box-shadow: 0 60px 120px -40px color-mix(in srgb, var(--ink) 55%, transparent), 0 0 0 1px var(--line); overflow: hidden; position: relative; transform: scale(var(--zoom, .9)); transform-origin: 50% 0; will-change: transform; }
        .hero-media img { aspect-ratio: 16 / 10; height: auto; object-fit: cover; transform: scale(calc(1.12 - (var(--zoom, .9) - .9))); transition: transform .1s linear; width: 100%; }
        .hero-media::after { background: linear-gradient(180deg, transparent 55%, color-mix(in srgb, var(--ink) 35%, transparent)); content: ""; inset: 0; pointer-events: none; position: absolute; }
        .glass { backdrop-filter: blur(22px) saturate(170%); -webkit-backdrop-filter: blur(22px) saturate(170%); background: color-mix(in srgb, #fff 68%, transparent); border: 1px solid rgba(255, 255, 255, .6); border-radius: 22px; box-shadow: 0 24px 60px -20px rgba(0, 0, 0, .35); color: var(--ink); }
        .chip { position: absolute; z-index: 2; }
        .chip-scan { align-items: center; display: flex; gap: 12px; padding: 14px 18px 14px 14px; right: 4%; text-align: left; top: 7%; animation: floaty 6s ease-in-out infinite; }
        .chip-scan .ic { align-items: center; background: var(--green); border-radius: 12px; color: #fff; display: flex; font-size: 1.15rem; height: 40px; justify-content: center; width: 40px; }
        .chip-scan strong { display: block; font-size: .95rem; letter-spacing: -.01em; }
        .chip-scan span { color: var(--green); font-size: .8rem; font-weight: 600; }
        .chip-stats { bottom: 6%; display: grid; gap: 4px 28px; grid-auto-flow: column; left: 4%; padding: 18px 24px; text-align: left; animation: floaty 7s ease-in-out -2s infinite; }
        .chip-stats span { color: var(--muted); display: block; font-size: .7rem; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }
        .chip-stats strong { display: block; font-size: clamp(1.4rem, 2.6vw, 2.1rem); font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.03em; }

        /* Ticker */
        .ticker { border-block: 1px solid var(--line); margin-top: clamp(64px, 9vw, 110px); mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent); -webkit-mask-image: linear-gradient(90deg, transparent, #000 12%, #000 88%, transparent); overflow: hidden; padding: 22px 0; }
        .ticker-track { display: flex; gap: 56px; width: max-content; animation: marquee 48s linear infinite; }
        .ticker:hover .ticker-track { animation-play-state: paused; }
        .ticker-item { align-items: center; color: var(--muted); display: flex; font-size: 1.02rem; font-weight: 500; gap: 10px; letter-spacing: -.01em; white-space: nowrap; }
        .ticker-item i { color: var(--gold); font-size: 1.1rem; }

        /* Bento features */
        .bento { display: grid; gap: 16px; grid-auto-flow: dense; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .tile { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); isolation: isolate; overflow: hidden; padding: 30px; position: relative; transition: transform .7s var(--ease), box-shadow .7s var(--ease); }
        .tile:hover { box-shadow: 0 40px 80px -40px color-mix(in srgb, var(--ink) 35%, transparent); transform: translateY(-6px); }
        .spot::before { background: radial-gradient(420px circle at var(--mx, 50%) var(--my, 50%), color-mix(in srgb, var(--gold) 16%, transparent), transparent 60%); content: ""; inset: 0; opacity: 0; pointer-events: none; position: absolute; transition: opacity .4s; z-index: -1; }
        .spot:hover::before { opacity: 1; }
        .tile .ico { align-items: center; background: var(--gold-soft); border-radius: 16px; color: var(--gold-deep); display: flex; font-size: 1.35rem; height: 52px; justify-content: center; margin-bottom: 28px; width: 52px; }
        .tile h3 { font-size: 1.22rem; font-weight: 650; letter-spacing: -.02em; margin-bottom: 10px; }
        .tile p { color: var(--muted); font-size: .98rem; line-height: 1.55; margin: 0; }
        .tile .num { color: color-mix(in srgb, var(--ink) 18%, transparent); font-size: .78rem; font-variant-numeric: tabular-nums; font-weight: 600; position: absolute; right: 26px; top: 26px; }
        .tile.is-wide { grid-column: span 2; }
        .tile.is-hero { background: var(--ink); border-color: transparent; display: flex; flex-direction: column; grid-column: span 2; grid-row: span 2; justify-content: flex-end; min-height: 420px; padding: 40px; }
        .tile.is-hero::after { background: radial-gradient(circle at 80% 10%, color-mix(in srgb, var(--gold) 55%, transparent), transparent 55%); content: ""; inset: 0; opacity: .55; position: absolute; z-index: -1; animation: breathe 8s ease-in-out infinite; }
        .tile.is-hero .ico { background: rgba(255, 255, 255, .1); color: var(--gold-light); font-size: 2rem; height: 76px; margin-bottom: auto; width: 76px; }
        .tile.is-hero h3 { color: #fff; font-size: clamp(1.8rem, 3vw, 2.6rem); line-height: 1.05; margin-top: 40px; }
        .tile.is-hero p { color: rgba(255, 255, 255, .62); font-size: 1.1rem; max-width: 420px; }
        .tile.is-hero .num { color: rgba(255, 255, 255, .3); }

        /* Representation (dark, scroll-lit) */
        .rep-grid { align-items: center; display: grid; gap: clamp(40px, 7vw, 96px); grid-template-columns: 1.1fr .9fr; }
        .scrub { font-size: clamp(2rem, 4.4vw, 3.5rem); font-weight: 700; letter-spacing: -.04em; line-height: 1.08; margin: 16px 0 26px; }
        .js .scrub .sw { color: rgba(245, 245, 247, .18); transition: color .35s; }
        .js .scrub .sw.on { color: #f5f5f7; }
        .steps { counter-reset: step; display: grid; gap: 4px; margin-top: 36px; }
        .step { align-items: flex-start; border-top: 1px solid rgba(255, 255, 255, .1); display: flex; gap: 18px; padding: 18px 0; }
        .step::before { color: var(--gold-light); content: counter(step, decimal-leading-zero); counter-increment: step; font-size: .8rem; font-variant-numeric: tabular-nums; font-weight: 600; padding-top: 3px; }
        .step span { color: rgba(245, 245, 247, .8); line-height: 1.5; }
        .flow { display: grid; gap: 14px; position: relative; }
        .flow::before { background: linear-gradient(180deg, transparent, var(--gold), transparent); bottom: 40px; content: ""; left: 37px; position: absolute; top: 40px; width: 1px; }
        .flow-card { align-items: center; backdrop-filter: blur(10px); background: rgba(255, 255, 255, .05); border: 1px solid rgba(255, 255, 255, .1); border-radius: 22px; display: flex; gap: 16px; padding: 18px; position: relative; }
        .avatar { align-items: center; background: linear-gradient(135deg, var(--gold-light), var(--gold-deep)); border-radius: 50%; color: #fff; display: flex; flex-shrink: 0; font-size: .9rem; font-weight: 700; height: 40px; justify-content: center; width: 40px; }
        .avatar.alt { background: rgba(255, 255, 255, .12); }
        .flow-card strong { color: #fff; display: block; font-size: .98rem; }
        .flow-card small { color: rgba(245, 245, 247, .55); display: block; font-size: .84rem; margin-top: 2px; }
        .badge { border-radius: 999px; font-size: .74rem; font-weight: 600; margin-left: auto; padding: 6px 11px; white-space: nowrap; }
        .badge.ok { background: color-mix(in srgb, var(--green) 18%, transparent); color: #5fc2a2; }
        .badge.wait { background: var(--gold-soft); color: var(--gold-light); }

        /* Check-in */
        .split { align-items: center; display: grid; gap: clamp(40px, 7vw, 96px); grid-template-columns: 1fr 1fr; }
        .device { background: linear-gradient(160deg, color-mix(in srgb, var(--ink) 92%, #fff), var(--ink)); border-radius: 44px; box-shadow: 0 60px 100px -40px color-mix(in srgb, var(--ink) 70%, transparent), inset 0 0 0 1px rgba(255, 255, 255, .08); margin: 0 auto; max-width: 440px; padding: 22px; }
        .viewfinder { aspect-ratio: 16 / 10; background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, .06), transparent 70%); border-radius: 26px; display: grid; overflow: hidden; place-items: center; position: relative; }
        .viewfinder .corner { border: 3px solid var(--gold); height: 34px; position: absolute; width: 34px; }
        .corner.tl { border-radius: 14px 0 0 0; border-width: 3px 0 0 3px; left: 24%; top: 14%; }
        .corner.tr { border-radius: 0 14px 0 0; border-width: 3px 3px 0 0; right: 24%; top: 14%; }
        .corner.bl { border-radius: 0 0 0 14px; border-width: 0 0 3px 3px; bottom: 14%; left: 24%; }
        .corner.br { border-radius: 0 0 14px 0; border-width: 0 3px 3px 0; bottom: 14%; right: 24%; }
        .viewfinder .qr { color: rgba(255, 255, 255, .85); font-size: clamp(3.4rem, 7vw, 4.6rem); }
        .laser { background: linear-gradient(90deg, transparent, var(--gold-light), transparent); box-shadow: 0 0 18px 2px var(--gold); height: 2px; left: 22%; position: absolute; right: 22%; top: 16%; animation: laser 2.6s ease-in-out infinite; }
        .feed { display: grid; gap: 10px; margin-top: 18px; }
        .feed-row { align-items: center; background: rgba(255, 255, 255, .05); border: 1px solid rgba(255, 255, 255, .07); border-radius: 18px; display: flex; gap: 12px; padding: 13px 14px; }
        .feed-row strong { color: #fff; display: block; font-size: .92rem; }
        .feed-row small { color: rgba(245, 245, 247, .5); font-size: .8rem; }
        .feed-row.enter { animation: rowIn .8s var(--ease); }
        .stat-row { display: grid; gap: 28px; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr)); margin-top: 44px; }
        .stat strong { color: var(--ink); display: block; font-size: clamp(2.4rem, 5vw, 3.6rem); font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.05em; line-height: 1; }
        .stat > span { color: var(--muted); display: block; font-size: .9rem; margin-top: 8px; }

        /* Wallet */
        .wallet-stage { margin: 0 auto; max-width: 1040px; perspective: 1400px; }
        .wallet-stage { margin-top: clamp(64px, 9vw, 120px); }
        .wallet-media { border-radius: calc(var(--radius) + 8px); box-shadow: 0 70px 120px -50px color-mix(in srgb, var(--ink) 55%, transparent), 0 0 0 1px var(--line); overflow: hidden; position: relative; transform: scale(var(--zoom, .94)); transform-origin: 50% 0; }
        .wallet-media img { aspect-ratio: 16 / 9; object-fit: cover; width: 100%; }
        .wallet-caption { bottom: 24px; left: 24px; padding: 16px 20px; position: absolute; }
        .wallet-caption strong { display: block; font-size: 1rem; letter-spacing: -.01em; }
        .wallet-caption span { color: var(--muted); font-size: .86rem; }
        .wallet-grid { align-items: center; display: grid; gap: clamp(48px, 7vw, 96px); grid-template-columns: 1fr 1fr; }
        .platforms { display: grid; gap: 12px; margin-top: 36px; }
        .platform { align-items: center; background: var(--panel); border: 1px solid var(--line); border-radius: 20px; display: flex; gap: 14px; padding: 14px 18px 14px 14px; }
        .platform strong { color: var(--ink); display: block; font-size: 1rem; letter-spacing: -.01em; }
        .platform small { color: var(--muted); font-size: .86rem; }
        .platform-ic { align-items: center; border-radius: 14px; display: flex; flex-shrink: 0; font-size: 1.3rem; height: 46px; justify-content: center; width: 46px; }
        .platform-ic.is-apple { background: #000; color: #fff; }
        .platform-ic.is-google { background: #fff; border: 1px solid var(--line); color: #4285f4; }
        .platform-ok { color: var(--green); margin-left: auto; }
        .wallet-list { display: grid; gap: 12px; list-style: none; margin: 28px 0 0; padding: 0; }
        .wallet-list li { align-items: flex-start; color: var(--text); display: flex; gap: 10px; line-height: 1.45; }
        .wallet-list i { color: var(--gold); }
        .wallet-badges { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 32px; }
        .add-badge { align-items: center; background: #000; border: 1px solid #444; border-radius: 12px; color: #fff; display: inline-flex; gap: 10px; min-height: 52px; padding: 8px 18px 8px 14px; }
        .add-badge i { font-size: 1.5rem; }
        .add-badge span { font-size: 1.02rem; font-weight: 600; letter-spacing: -.01em; line-height: 1.1; }
        .add-badge small { display: block; font-size: .66rem; font-weight: 500; opacity: .85; }

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

        /* Journey (sticky phone) */
        .journey { padding-bottom: clamp(24px, 4vw, 56px); }
        .journey-grid { display: grid; gap: clamp(32px, 6vw, 80px); grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .journey-device { order: 2; }
        .journey-sticky { align-items: center; display: flex; flex-direction: column; gap: 22px; position: sticky; top: max(84px, calc(50vh - 330px)); }
        .journey-halo { background: radial-gradient(circle, color-mix(in srgb, var(--gold) 30%, transparent), transparent 62%); filter: blur(10px); height: 640px; left: 50%; pointer-events: none; position: absolute; top: 50%; transform: translate(-50%, -50%); width: 640px; z-index: -1; animation: breathe 8s ease-in-out infinite; }
        .journey-dots { display: flex; gap: 8px; }
        .journey-dots span { background: color-mix(in srgb, var(--ink) 18%, transparent); border-radius: 99px; height: 6px; transition: width .6s var(--ease), background .4s; width: 6px; }
        .journey-dots span.is-on { background: var(--ink); width: 24px; }
        .journey-steps { list-style: none; margin: 0; padding: 0 0 0 4px; position: relative; }
        .journey .section-head { margin-bottom: clamp(16px, 3vw, 40px); }
        .journey-steps { padding-block: max(0px, calc(330px - 23vh)); }
        .journey-rail { background: var(--line); bottom: 23vh; left: 21px; position: absolute; top: 23vh; width: 2px; }
        .journey-rail span { background: linear-gradient(180deg, var(--gold-light), var(--gold-deep)); display: block; height: calc(var(--jp, 0) * 100%); transition: height .2s linear; width: 100%; }
        .jstep { align-items: center; display: flex; gap: 28px; min-height: 46vh; opacity: .28; transition: opacity .6s var(--ease); }
        .jstep.is-active { opacity: 1; }
        .jnum { align-items: center; background: var(--bg); border: 2px solid var(--line); border-radius: 50%; color: var(--muted); display: flex; flex-shrink: 0; font-size: .74rem; font-variant-numeric: tabular-nums; font-weight: 700; height: 36px; justify-content: center; position: relative; transition: all .5s var(--ease); width: 36px; z-index: 1; }
        .jstep.is-active .jnum { background: var(--ink); border-color: var(--ink); color: #fff; transform: scale(1.12); }
        .jicon { align-items: center; background: var(--gold-soft); border-radius: 14px; color: var(--gold-deep); display: inline-flex; font-size: 1.25rem; height: 46px; justify-content: center; margin-bottom: 14px; width: 46px; }
        .jstep h3 { font-size: clamp(1.7rem, 3.2vw, 2.6rem); font-weight: 700; letter-spacing: -.04em; line-height: 1.06; margin-bottom: 14px; }
        .jstep p { color: var(--muted); font-size: clamp(1.02rem, 1.4vw, 1.2rem); line-height: 1.55; margin: 0; max-width: 460px; }

        /* Phone */
        .phone { background: linear-gradient(145deg, #3a3a3c, #1c1c1e 40%, #0b0b0c); border-radius: 56px; box-shadow: 0 80px 120px -50px color-mix(in srgb, var(--ink) 75%, transparent), inset 0 0 0 1.5px rgba(255, 255, 255, .14); height: 620px; padding: 11px; position: relative; width: 300px; }
        .phone-btn { background: #2c2c2e; border-radius: 2px; position: absolute; width: 4px; }
        .phone-btn-a { height: 30px; left: -3px; top: 120px; }
        .phone-btn-b { height: 54px; left: -3px; top: 168px; }
        .phone-btn-c { height: 84px; right: -3px; top: 180px; }
        .phone-screen { background: #f2f2f7; border-radius: 46px; height: 100%; overflow: hidden; position: relative; }
        .island { background: #000; border-radius: 20px; height: 28px; left: 50%; position: absolute; top: 11px; transform: translateX(-50%); width: 92px; z-index: 5; }
        .scr { color: #111; display: flex; flex-direction: column; font-size: 13px; inset: 0; opacity: 0; padding: 56px 18px 22px; pointer-events: none; position: absolute; transform: scale(.94) translateY(14px); transition: opacity .6s var(--ease), transform .9s var(--ease); }
        .scr.is-on { opacity: 1; transform: none; }
        .scr .a { opacity: 0; transform: translateY(14px); transition: opacity .7s var(--ease), transform .7s var(--ease); transition-delay: 0s; }
        .scr.is-on .a { opacity: 1; transform: none; transition-delay: calc(var(--s, 0) * 90ms + 180ms); }
        .scr.is-dark { background: linear-gradient(180deg, #1c1c1e, #000); color: #fff; }
        .scr-status { display: flex; font-size: 12px; font-weight: 600; justify-content: space-between; left: 0; padding: 16px 28px 0 32px; position: absolute; right: 0; top: 0; }
        .scr-status i { font-size: 11px; }
        .scr-title { font-size: 26px; font-weight: 700; letter-spacing: -.03em; line-height: 1.1; margin: 8px 0 14px; }
        .scr-kicker { color: #8e8e93; font-size: 12px; font-weight: 600; letter-spacing: .02em; margin-top: 8px; }
        .scr.is-dark .scr-kicker { color: rgba(255, 255, 255, .55); }
        .mini-logo { align-items: center; background: var(--ink); border-radius: 9px; color: #fff; display: inline-flex; flex-shrink: 0; font-size: 13px; height: 30px; justify-content: center; overflow: hidden; width: 30px; }
        .mini-logo img { height: 100%; object-fit: cover; width: 100%; }
        .mail { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px -18px rgba(0, 0, 0, .3); padding: 14px; }
        .mail-from { align-items: center; display: flex; gap: 10px; }
        .mail-from strong { display: block; font-size: 13px; }
        .mail-from small { color: #8e8e93; display: block; font-size: 11px; }
        .mail-from time { color: #8e8e93; font-size: 10px; margin-left: auto; }
        .mail-banner { background: linear-gradient(140deg, var(--ink), color-mix(in srgb, var(--ink) 60%, var(--gold))); border-radius: 14px; color: #fff; margin: 14px 0 12px; padding: 20px 14px; }
        .mail-banner small { color: var(--gold-light); display: block; font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .mail-banner strong { display: block; font-size: 19px; letter-spacing: -.02em; margin: 6px 0 4px; }
        .mail-banner span { color: rgba(255, 255, 255, .7); font-size: 11px; }
        .mail p { color: #3a3a3c; font-size: 12px; line-height: 1.5; margin: 0 0 14px; }
        .scr-btn { background: var(--ink); border-radius: 12px; color: #fff; display: block; font-size: 13px; font-weight: 600; padding: 12px; text-align: center; }
        .opts { display: grid; gap: 10px; margin-top: 8px; }
        .opt { align-items: center; background: #fff; border: 1.5px solid transparent; border-radius: 16px; color: #3a3a3c; display: flex; font-size: 13.5px; font-weight: 600; gap: 10px; padding: 15px 14px; }
        .opt i { color: #c7c7cc; font-size: 17px; }
        .opt.sel { border-color: var(--gold); box-shadow: 0 0 0 4px var(--gold-soft); color: #111; }
        .opt.sel i { color: var(--gold); }
        .scr.is-on .opt.sel { animation: tap .7s var(--ease) 1.1s both; }
        .scr-link { color: var(--gold-deep); font-size: 12.5px; font-weight: 600; margin-top: 16px; text-align: center; }
        .toast { align-items: center; align-self: center; background: rgba(28, 28, 30, .92); border-radius: 99px; color: #fff; display: inline-flex; font-size: 12px; font-weight: 600; gap: 7px; margin-top: auto; padding: 10px 16px; }
        .toast i { color: #30d158; }
        .inv-card { background: linear-gradient(160deg, #2c2c2e, #111 60%); border: 1px solid color-mix(in srgb, var(--gold) 45%, transparent); border-radius: 22px; margin-top: 14px; padding: 18px; }
        .inv-head { align-items: center; display: flex; font-size: 12px; font-weight: 600; gap: 8px; margin-bottom: 22px; }
        .inv-tier { background: var(--gold-soft); border-radius: 99px; color: var(--gold-light); font-size: 10px; margin-left: auto; padding: 4px 9px; }
        .inv-card small { color: var(--gold-light); display: block; font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .inv-card strong { display: block; font-size: 22px; letter-spacing: -.03em; margin: 6px 0; }
        .inv-card > span { color: rgba(255, 255, 255, .6); display: block; font-size: 11.5px; line-height: 1.5; }
        .inv-qr { background: #fff; border-radius: 16px; margin: 18px auto 0; overflow: hidden; padding: 10px; position: relative; width: 150px; }
        .inv-qr .qr-svg { display: block; width: 100%; }
        .scan-glint { background: linear-gradient(180deg, transparent, color-mix(in srgb, var(--gold) 55%, transparent), transparent); height: 30%; left: 0; position: absolute; right: 0; top: -30%; }
        .scr.is-on .scan-glint { animation: glint 2.4s ease-in-out 1s infinite; }
        .scr-note { color: rgba(255, 255, 255, .55); font-size: 11.5px; margin-top: auto; text-align: center; }
        .wallet-title { align-items: center; display: flex; justify-content: space-between; }
        .wallet-title i { color: #0a84ff; font-size: 22px; }
        .stack { display: grid; gap: 0; margin-bottom: -6px; }
        .stack span { border-radius: 14px 14px 0 0; height: 30px; margin: 0 6px; }
        .stack span:first-child { background: linear-gradient(90deg, #5e5ce6, #0a84ff); }
        .stack span:last-child { background: linear-gradient(90deg, #30d158, #0a9396); margin-top: -12px; }
        .scr .pass { padding: 14px 14px 16px; position: relative; width: 100%; }
        .scr .pass-event { font-size: 17px; margin: 14px 0 10px; }
        .scr .pass-code { margin-top: 10px; width: 108px; }
        .scr .pass-code .qr-svg { width: 88px; }
        .pass-in { opacity: 0; transform: translateY(120px); transition: opacity .6s var(--ease), transform 1s var(--ease); }
        .scr.is-on .pass-in { opacity: 1; transform: none; transition-delay: .35s; }
        .scr-checkin { background: linear-gradient(180deg, #f2fbf6, #fff); }
        .ok-wrap { align-items: center; display: flex; flex: 1; flex-direction: column; justify-content: center; text-align: center; }
        .ok-check { height: 120px; width: 120px; }
        .ok-check circle { fill: none; stroke: #30d158; stroke-dasharray: 340; stroke-dashoffset: 340; stroke-width: 6; }
        .ok-check path { fill: none; stroke: #30d158; stroke-dasharray: 80; stroke-dashoffset: 80; stroke-linecap: round; stroke-linejoin: round; stroke-width: 8; }
        .scr.is-on .ok-check circle { animation: draw .9s var(--ease) .25s forwards; }
        .scr.is-on .ok-check path { animation: draw .6s var(--ease) .9s forwards; }
        .ok-wrap strong { font-size: 24px; letter-spacing: -.03em; margin-top: 18px; }
        .ok-wrap > span { color: #636366; font-size: 14px; margin-top: 4px; }
        .ok-meta { background: #fff; border-radius: 16px; box-shadow: 0 10px 30px -18px rgba(0, 0, 0, .3); display: grid; gap: 4px; grid-template-columns: repeat(3, 1fr); margin-top: 26px; padding: 12px 6px; width: 100%; }
        .ok-meta span { font-size: 14px; font-weight: 700; }
        .ok-meta small { color: #8e8e93; display: block; font-size: 9.5px; font-weight: 600; letter-spacing: .06em; text-transform: uppercase; }

        /* Pricing */
        .pricing { align-items: stretch; display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .price { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); display: flex; flex-direction: column; padding: 36px 32px; position: relative; transition: transform .7s var(--ease), box-shadow .7s var(--ease); }
        .price:hover { box-shadow: 0 40px 80px -40px color-mix(in srgb, var(--ink) 35%, transparent); transform: translateY(-6px); }
        .price h3 { font-size: 1.05rem; font-weight: 600; letter-spacing: -.01em; }
        .price .sub { color: var(--muted); font-size: .94rem; line-height: 1.5; margin: 8px 0 0; }
        .price .amount { color: var(--ink); display: block; font-size: clamp(2rem, 3.4vw, 2.6rem); font-weight: 700; letter-spacing: -.045em; margin: 28px 0 24px; }
        .price ul { display: grid; gap: 12px; list-style: none; margin: 0 0 32px; padding: 24px 0 0; border-top: 1px solid var(--line); }
        .price li { align-items: flex-start; display: flex; font-size: .95rem; gap: 10px; line-height: 1.45; }
        .price li i { color: var(--gold); }
        .price .btn { margin-top: auto; width: 100%; }
        .price.featured { background: var(--ink); border-color: transparent; color: #f5f5f7; }
        .price.featured::before { background: conic-gradient(from var(--angle), transparent 0 60%, var(--gold-light), var(--gold), transparent 90%); border-radius: inherit; content: ""; inset: -1px; -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; padding: 1.5px; pointer-events: none; position: absolute; animation: spin 5s linear infinite; }
        .price.featured h3, .price.featured .amount { color: #fff; }
        .price.featured .sub { color: rgba(245, 245, 247, .6); }
        .price.featured ul { border-color: rgba(255, 255, 255, .1); }
        .price.featured li i { color: var(--gold-light); }
        .tag { background: var(--gold-soft); border-radius: 999px; color: var(--gold-light); font-size: .72rem; font-weight: 600; padding: 5px 10px; position: absolute; right: 24px; top: 24px; }
        @property --angle { syntax: "<angle>"; initial-value: 0deg; inherits: false; }

        /* Security */
        .security-grid { display: grid; gap: 16px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .sec-tile { background: rgba(255, 255, 255, .04); border: 1px solid rgba(255, 255, 255, .08); border-radius: var(--radius); isolation: isolate; overflow: hidden; padding: 32px 26px; position: relative; text-align: center; }
        .sec-tile i { align-items: center; background: radial-gradient(circle, color-mix(in srgb, var(--gold) 35%, transparent), transparent 70%); border-radius: 50%; color: var(--gold-light); display: inline-flex; font-size: 1.7rem; height: 76px; justify-content: center; margin-bottom: 18px; width: 76px; }
        .sec-tile strong { color: #fff; display: block; font-size: 1.05rem; font-weight: 600; letter-spacing: -.01em; }
        .glow { background: radial-gradient(ellipse at center, color-mix(in srgb, var(--gold) 26%, transparent), transparent 65%); height: 600px; left: 50%; pointer-events: none; position: absolute; top: -260px; transform: translateX(-50%); width: min(1100px, 100%); }

        /* FAQ */
        .faq { margin: 0 auto; max-width: 860px; }
        .faq details { border-bottom: 1px solid var(--line); }
        .faq summary { align-items: center; color: var(--ink); cursor: pointer; display: flex; font-size: clamp(1.05rem, 1.8vw, 1.3rem); font-weight: 600; gap: 20px; justify-content: space-between; letter-spacing: -.02em; list-style: none; padding: 28px 0; }
        .faq summary::-webkit-details-marker { display: none; }
        .faq summary i { align-items: center; background: var(--gold-soft); border-radius: 50%; color: var(--gold-deep); display: flex; flex-shrink: 0; height: 34px; justify-content: center; transition: transform .5s var(--ease), background .3s; width: 34px; }
        .faq details[open] summary i { background: var(--ink); color: #fff; transform: rotate(45deg); }
        .faq details p { color: var(--muted); font-size: 1.05rem; line-height: 1.6; margin: 0; max-width: 720px; padding: 0 0 28px; }
        .faq details::details-content { block-size: 0; overflow: hidden; transition: block-size .5s var(--ease), content-visibility .5s allow-discrete; }
        .faq details[open]::details-content { block-size: auto; }
        :root { interpolate-size: allow-keywords; }

        /* CTA */
        .cta { border-radius: calc(var(--radius) + 12px); isolation: isolate; overflow: hidden; padding: clamp(64px, 10vw, 128px) clamp(24px, 6vw, 80px); position: relative; text-align: center; }
        .cta::before { background: radial-gradient(circle, var(--gold), transparent 60%); content: ""; filter: blur(40px); height: 520px; left: 50%; opacity: .35; position: absolute; top: 55%; transform: translate(-50%, -50%); width: 520px; z-index: -1; animation: orbit 14s ease-in-out infinite; }
        .cta h2 { font-size: clamp(2.3rem, 6vw, 4.6rem); font-weight: 700; letter-spacing: -.05em; line-height: 1.02; margin: 0 auto 22px; max-width: 900px; }
        .cta p { font-size: clamp(1.02rem, 1.6vw, 1.25rem); line-height: 1.55; margin: 0 auto 38px; max-width: 640px; }

        /* Footer */
        .footer { border-top: 1px solid var(--line); color: var(--muted); font-size: .84rem; padding: 32px 0 40px; }
        .footer .inner { align-items: center; display: flex; flex-wrap: wrap; gap: 14px 28px; justify-content: space-between; }
        .footer strong { color: var(--ink); }

        /* Reveal */
        .js .reveal { opacity: 0; transform: translateY(40px); transition: opacity 1.1s var(--ease), transform 1.1s var(--ease); transition-delay: calc(var(--d, 0) * 80ms); }
        .js .reveal.is-visible { opacity: 1; transform: none; }

        @keyframes wordIn { to { filter: blur(0); opacity: 1; transform: none; } }
        @keyframes fadeUp { to { opacity: 1; transform: none; } }
        @keyframes floaty { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
        @keyframes pulse { 70% { box-shadow: 0 0 0 10px transparent; } 100% { box-shadow: 0 0 0 0 transparent; } }
        @keyframes marquee { to { transform: translateX(-50%); } }
        @keyframes shimmer { to { background-position: -220% 0; } }
        @keyframes breathe { 50% { opacity: .9; transform: scale(1.08); } }
        @keyframes laser { 0%, 100% { top: 16%; } 50% { top: 82%; } }
        @keyframes rowIn { from { opacity: 0; transform: translateY(-14px) scale(.97); } }
        @keyframes tap { 40% { transform: scale(.96); } 100% { transform: none; } }
        @keyframes glint { 0% { top: -30%; } 60%, 100% { top: 110%; } }
        @keyframes draw { to { stroke-dashoffset: 0; } }
        @keyframes spin { to { --angle: 360deg; } }
        @keyframes orbit { 0%, 100% { transform: translate(-50%, -50%); } 33% { transform: translate(-30%, -60%); } 66% { transform: translate(-70%, -40%); } }

        @media (max-width: 1060px) {
            .bento { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .rep-grid, .split, .wallet-grid { grid-template-columns: 1fr; }
            .pass-stage { height: 520px; }
            .pricing { grid-template-columns: 1fr; margin: 0 auto; max-width: 520px; }
            .security-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 900px) {
            .nav-links { display: none; }
            .journey-grid { gap: 0; grid-template-columns: 1fr; }
            .journey-device { align-self: start; background: linear-gradient(180deg, var(--bg) 82%, transparent); margin: 0 calc(-1 * clamp(18px, 4vw, 40px)); order: 0; padding: 8px 0 26px; position: sticky; top: 60px; z-index: 3; }
            .journey-sticky { gap: 12px; position: static; }
            .journey-halo { height: 380px; width: 380px; }
            .phone { zoom: .58; }
            .journey-rail { left: 17px; }
            .jstep { gap: 18px; min-height: 44vh; }
            .journey-steps { padding-block: 0 8vh; }
            .jnum { height: 32px; width: 32px; }
            .jstep h3 { font-size: 1.6rem; }
        }
        @media (max-width: 640px) {
            .nav-actions .btn-ghost { display: none; }
            .bento { grid-template-columns: 1fr; }
            .tile.is-wide, .tile.is-hero { grid-column: auto; grid-row: auto; }
            .tile.is-hero { min-height: 340px; }
            .hero-media img { aspect-ratio: 4 / 5; object-position: 68% 50%; }
            .chip-scan { right: 12px; top: 12px; }
            .chip-stats { bottom: 12px; gap: 4px 18px; left: 12px; padding: 14px 16px; right: 12px; }
            .security-grid { grid-template-columns: 1fr; }
            .wallet-caption { bottom: 12px; left: 12px; right: 12px; }
            .hero-actions .btn { width: 100%; }
            .pass-stage { height: 440px; zoom: .74; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; scroll-behavior: auto !important; }
            .js .hero h1 .w, .js .hero-fade, .js .reveal { filter: none; opacity: 1; transform: none; }
            .hero-media, .wallet-media { transform: none; }
            .ok-check circle, .ok-check path { stroke-dashoffset: 0; }
            .js .scrub .sw { color: #f5f5f7; }
        }
    </style>
</head>
<body>
<div class="site">
    <div class="progress" aria-hidden="true"></div>

    <nav class="nav" aria-label="Navigation principale">
        <div class="inner">
            <a href="{{ route('landing') }}" class="brand">
                <span class="brand-mark">@if($brandLogo)<img src="{{ $brandLogo }}" alt="{{ $brandName }}">@else<i class="bi {{ $settings['brand']['logo_icon'] ?? 'bi-shield-check' }}"></i>@endif</span>
                <span>{{ $brandName }}</span>
            </a>
            <div class="nav-links">
                @foreach($filterEnabled($settings['navigation'] ?? []) as $item)
                    <a href="#{{ $item['anchor'] ?? '' }}">{{ $item['label'] ?? '' }}</a>
                @endforeach
            </div>
            <div class="nav-actions">
                <x-landing.button :href="route('client.login')" variant="ghost" class="btn-sm">Connexion</x-landing.button>
                <x-landing.button :href="route('register')" variant="primary" class="btn-sm">Créer un espace</x-landing.button>
            </div>
        </div>
    </nav>

    @if($enabled($settings['hero'] ?? []))
    <header class="hero">
        <div class="inner">
            @if(!empty($settings['hero']['eyebrow']))
                <div class="hero-badge hero-fade" style="--d:.05s"><span class="pulse"></span>{{ $settings['hero']['eyebrow'] }}</div>
            @endif
            <h1 aria-label="{{ $settings['hero']['title'] ?? '' }}">
                @foreach($heroWords as $word)
                    <span @class(['w', 'gold-text' => $loop->index >= $accentFrom && $heroWords->count() > 3]) style="--i:{{ $loop->index }}" aria-hidden="true">{{ $word }}</span>
                @endforeach
            </h1>
            <p class="lead hero-fade" style="--d:.55s">{{ $settings['hero']['body'] ?? '' }}</p>
            <div class="hero-actions hero-fade" style="--d:.7s">
                <x-landing.button :href="route('register')" variant="primary" icon-after="bi-arrow-right">{{ $settings['hero']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                <x-landing.button :href="route('client.login')" variant="link" icon-after="bi-chevron-right">{{ $settings['hero']['secondary_label'] ?? 'Connexion' }}</x-landing.button>
            </div>
            <div class="trust-row hero-fade" style="--d:.85s">
                @foreach($filterEnabled($settings['trust_items'] ?? []) as $item)
                    <div class="trust-item"><i class="bi {{ $item['icon'] ?? 'bi-check2' }}"></i>{{ $item['label'] ?? '' }}</div>
                @endforeach
            </div>
        </div>

        @if($heroImage)
        <div class="hero-stage hero-fade" style="--d:.95s">
            <div class="hero-media" data-zoom>
                <img src="{{ $heroImage }}" alt="{{ $settings['hero']['image_alt'] ?? '' }}" fetchpriority="high">
                @if(!empty($settings['hero']['floating_title']))
                <div class="chip chip-scan glass">
                    <span class="ic"><i class="bi bi-check-lg"></i></span>
                    <div><strong>{{ $settings['hero']['floating_title'] }}</strong><span>{{ $settings['hero']['floating_text'] ?? '' }}</span></div>
                </div>
                @endif
                @if($metrics->isNotEmpty())
                <div class="chip chip-stats glass">
                    @foreach($metrics as $metric)
                        @php $parts = $metricParts($metric['value'] ?? ''); @endphp
                        <div>
                            <span>{{ $metric['label'] ?? '' }}</span>
                            @if($parts)
                                <strong>{{ $parts['prefix'] }}<b data-count="{{ $parts['number'] }}" style="font-weight:inherit">{{ $parts['number'] }}</b>{{ $parts['suffix'] }}</strong>
                            @else
                                <strong>{{ $metric['value'] ?? '' }}</strong>
                            @endif
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($ticker->isNotEmpty())
        <div class="ticker" aria-hidden="true">
            <div class="ticker-track">
                @foreach([$ticker, $ticker] as $set)
                    @foreach($set as $item)
                        <span class="ticker-item"><i class="bi {{ $item['icon'] }}"></i>{{ $item['label'] }}</span>
                    @endforeach
                @endforeach
            </div>
        </div>
        @endif
    </header>
    @endif

    <main>
        @if($enabled($settings['journey'] ?? []) && $journeySteps->isNotEmpty())
        <section class="section journey" id="parcours" data-journey>
            <div class="inner">
                <x-landing.section-head :eyebrow="$settings['journey']['eyebrow'] ?? null" :title="$settings['journey']['title'] ?? null" :body="$settings['journey']['body'] ?? null" />
                <div class="journey-grid">
                    <div class="journey-device">
                        <div class="journey-sticky">
                            <div class="journey-halo" aria-hidden="true"></div>
                            <x-landing.phone>
                                @foreach(array_keys(\App\Models\LandingPage::JOURNEY_SCREENS) as $screen)
                                    <x-landing.screen :name="$screen" :active="$screen === ($journeySteps->first()['screen'] ?? 'invitation')" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                                @endforeach
                            </x-landing.phone>
                            <div class="journey-dots" aria-hidden="true">
                                @foreach($journeySteps as $step)<span @class(['is-on' => $loop->first])></span>@endforeach
                            </div>
                        </div>
                    </div>
                    <ol class="journey-steps">
                        <span class="journey-rail" aria-hidden="true"><span></span></span>
                        @foreach($journeySteps as $step)
                            <li @class(['jstep', 'is-active' => $loop->first]) data-screen="{{ $step['screen'] ?? 'invitation' }}">
                                <span class="jnum">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <div>
                                    <span class="jicon"><i class="bi {{ $step['icon'] ?? 'bi-check2' }}"></i></span>
                                    <h3>{{ $step['title'] ?? '' }}</h3>
                                    <p>{{ $step['body'] ?? '' }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </section>
        @endif
        @if($enabled($settings['features_section'] ?? []) && $features->isNotEmpty())
        <section class="section" id="features">
            <div class="inner">
                <x-landing.section-head :eyebrow="$settings['features_section']['eyebrow'] ?? null" :title="$settings['features_section']['title'] ?? null" :body="$settings['features_section']['body'] ?? null" />
                <div class="bento">
                    @foreach($features as $feature)
                        <article @class(['tile', 'spot', 'reveal', 'is-hero' => $loop->first, 'is-wide' => $loop->index === 5]) style="--d:{{ $loop->index % 4 }}">
                            <span class="num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="ico"><i class="bi {{ $feature['icon'] ?? 'bi-check2' }}"></i></span>
                            <h3>{{ $feature['title'] ?? '' }}</h3>
                            <p>{{ $feature['body'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['representation'] ?? []))
        <section class="section dark">
            <div class="inner rep-grid">
                <div>
                    <div class="eyebrow reveal">{{ $settings['representation']['eyebrow'] ?? '' }}</div>
                    <h2 class="scrub" data-scrub>
                        @foreach($words($settings['representation']['title'] ?? '') as $word)<span class="sw">{{ $word }}</span> @endforeach
                    </h2>
                    <p class="reveal" style="font-size:1.12rem;line-height:1.6;margin:0">{{ $settings['representation']['body'] ?? '' }}</p>
                    @if(!empty($settings['representation']['items']))
                    <div class="steps">
                        @foreach($settings['representation']['items'] as $item)
                            <div class="step reveal" style="--d:{{ $loop->index + 1 }}"><span>{{ $item }}</span></div>
                        @endforeach
                    </div>
                    @endif
                </div>
                <div class="flow">
                    <div class="flow-card reveal" style="--d:1"><span class="avatar">AK</span><div><strong>Aminata K.</strong><small>Invitée représentée par Eric N.</small></div><span class="badge ok">Confirmé</span></div>
                    <div class="flow-card reveal" style="--d:3"><span class="avatar alt">EN</span><div><strong>Eric N.</strong><small>Carte représentant envoyée</small></div><span class="badge ok">Wallet prêt</span></div>
                    <div class="flow-card reveal" style="--d:5"><span class="avatar alt"><i class="bi bi-clock-history"></i></span><div><strong>Organisateur</strong><small>Historique complet conservé</small></div><span class="badge wait">Trace</span></div>
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['checkin'] ?? []))
        <section class="section" id="checkin">
            <div class="inner split">
                <div class="device reveal">
                    <div class="viewfinder">
                        <span class="corner tl"></span><span class="corner tr"></span><span class="corner bl"></span><span class="corner br"></span>
                        <i class="bi bi-qr-code qr"></i>
                        <span class="laser"></span>
                    </div>
                    <div class="feed" data-feed>
                        <div class="feed-row"><span class="avatar">MK</span><div><strong>Marie Koffi</strong><small>VIP · QR scanné à 18:42</small></div><span class="badge ok">Entrée validée</span></div>
                        <div class="feed-row"><span class="avatar alt">JB</span><div><strong>Jean B.</strong><small>Déjà scanné à 18:18</small></div><span class="badge wait">Double scan</span></div>
                        <div class="feed-row"><span class="avatar alt">CN</span><div><strong>Représentant</strong><small>Confirme pour Cabinet N.</small></div><span class="badge ok">Autorisé</span></div>
                    </div>
                </div>
                <div>
                    <x-landing.section-head align="left" :eyebrow="$settings['checkin']['eyebrow'] ?? null" :title="$settings['checkin']['title'] ?? null" :body="$settings['checkin']['body'] ?? null" style="margin-bottom:0" />
                    @if($metrics->isNotEmpty())
                    <div class="stat-row">
                        @foreach($metrics as $metric)
                            @php $parts = $metricParts($metric['value'] ?? ''); @endphp
                            <div class="stat reveal" style="--d:{{ $loop->index + 1 }}">
                                <strong>@if($parts){{ $parts['prefix'] }}<span data-count="{{ $parts['number'] }}">{{ $parts['number'] }}</span>{{ $parts['suffix'] }}@else{{ $metric['value'] ?? '' }}@endif</strong>
                                <span>{{ $metric['label'] ?? '' }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['wallet'] ?? []))
        <section class="section" id="wallet">
            <div class="inner">
                <div class="wallet-grid">
                    <div>
                        <x-landing.section-head align="left" :eyebrow="$settings['wallet']['eyebrow'] ?? null" :title="$settings['wallet']['title'] ?? null" :body="$settings['wallet']['body'] ?? null" style="margin-bottom:0" />
                        <div class="platforms">
                            <div class="platform reveal" style="--d:1"><span class="platform-ic is-apple"><i class="bi bi-apple"></i></span><div><strong>{{ $settings['wallet']['apple_label'] ?? 'Apple Wallet' }}</strong><small>{{ $settings['wallet']['apple_text'] ?? '' }}</small></div><i class="bi bi-check-circle-fill platform-ok"></i></div>
                            <div class="platform reveal" style="--d:2"><span class="platform-ic is-google"><i class="bi bi-google"></i></span><div><strong>{{ $settings['wallet']['google_label'] ?? 'Google Wallet' }}</strong><small>{{ $settings['wallet']['google_text'] ?? '' }}</small></div><i class="bi bi-check-circle-fill platform-ok"></i></div>
                        </div>
                        @if(!empty($settings['wallet']['items']))
                        <ul class="wallet-list">
                            @foreach($settings['wallet']['items'] as $item)<li class="reveal" style="--d:{{ $loop->index + 3 }}"><i class="bi bi-check2"></i>{{ $item }}</li>@endforeach
                        </ul>
                        @endif
                        <div class="wallet-badges reveal" style="--d:6">
                            <span class="add-badge"><i class="bi bi-apple"></i><span><small>Ajouter à</small>{{ $settings['wallet']['apple_label'] ?? 'Apple Wallet' }}</span></span>
                            <span class="add-badge"><i class="bi bi-google"></i><span><small>Ajouter à</small>{{ $settings['wallet']['google_label'] ?? 'Google Wallet' }}</span></span>
                        </div>
                    </div>
                    <div class="pass-stage reveal" data-passes data-tilt>
                        <div class="pass-slot pass-slot-apple">
                            <span class="pass-label glass"><i class="bi bi-apple"></i>{{ $settings['wallet']['apple_label'] ?? 'Apple Wallet' }}</span>
                            <x-landing.wallet-pass variant="apple" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                        </div>
                        <div class="pass-slot pass-slot-google">
                            <span class="pass-label glass"><i class="bi bi-google"></i>{{ $settings['wallet']['google_label'] ?? 'Google Wallet' }}</span>
                            <x-landing.wallet-pass variant="google" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                        </div>
                    </div>
                </div>
                @if($walletImage)
                <div class="wallet-stage reveal">
                    <div class="wallet-media" data-zoom>
                        <img src="{{ $walletImage }}" alt="{{ $settings['wallet']['image_alt'] ?? '' }}" loading="lazy">
                        @if(!empty($settings['wallet']['caption_title']))
                        <div class="wallet-caption glass"><strong>{{ $settings['wallet']['caption_title'] }}</strong><span>{{ $settings['wallet']['caption_text'] ?? '' }}</span></div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </section>
        @endif

        @if($enabled($settings['pricing_section'] ?? []))
        <section class="section" id="pricing" style="padding-top:0">
            <div class="inner">
                <x-landing.section-head :eyebrow="$settings['pricing_section']['eyebrow'] ?? null" :title="$settings['pricing_section']['title'] ?? null" :body="$settings['pricing_section']['body'] ?? null" />
                <div class="pricing">
                    @foreach($filterEnabled($settings['plans'] ?? []) as $plan)
                        <article @class(['price', 'reveal', 'featured' => !empty($plan['featured'])]) style="--d:{{ $loop->index }}">
                            @if(!empty($plan['featured']))<span class="tag">Recommandé</span>@endif
                            <h3>{{ $plan['name'] ?? '' }}</h3>
                            <p class="sub">{{ $plan['subtitle'] ?? '' }}</p>
                            <span class="amount">{{ $plan['price'] ?? '' }}</span>
                            <ul>@foreach($plan['items'] ?? [] as $item)<li><i class="bi bi-check-circle-fill"></i>{{ $item }}</li>@endforeach</ul>
                            <x-landing.button :href="route('register')" :variant="!empty($plan['featured']) ? 'gold' : 'ghost'">{{ $settings['hero']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['security'] ?? []))
        <section class="section dark" style="overflow:hidden">
            <div class="glow" aria-hidden="true"></div>
            <div class="inner" style="position:relative">
                <x-landing.section-head :eyebrow="$settings['security']['eyebrow'] ?? null" :title="$settings['security']['title'] ?? null" :body="$settings['security']['body'] ?? null" />
                <div class="security-grid">
                    @foreach($filterEnabled($settings['security']['items'] ?? []) as $item)
                        <div class="sec-tile spot reveal" style="--d:{{ $loop->index }}"><i class="bi {{ $item['icon'] ?? 'bi-shield-check' }}"></i><strong>{{ $item['label'] ?? '' }}</strong></div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['faq_section'] ?? []))
        <section class="section" id="faq">
            <div class="inner">
                <x-landing.section-head :eyebrow="$settings['faq_section']['eyebrow'] ?? null" :title="$settings['faq_section']['title'] ?? null" />
                <div class="faq">
                    @foreach($filterEnabled($settings['faqs'] ?? []) as $faq)
                        <details class="reveal" style="--d:{{ $loop->index }}" @if($loop->first) open @endif>
                            <summary>{{ $faq['question'] ?? '' }}<i class="bi bi-plus-lg"></i></summary>
                            <p>{{ $faq['answer'] ?? '' }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        @if($enabled($settings['cta'] ?? []))
        <section class="section" style="padding-top:0">
            <div class="inner">
                <div class="cta dark reveal">
                    <h2>{{ $settings['cta']['title'] ?? '' }}</h2>
                    <p>{{ $settings['cta']['body'] ?? '' }}</p>
                    <div class="hero-actions" style="margin-top:0">
                        <x-landing.button :href="route('register')" variant="gold" icon-after="bi-arrow-right">{{ $settings['cta']['primary_label'] ?? 'Créer mon espace' }}</x-landing.button>
                        <x-landing.button :href="route('client.login')" variant="outline-light">{{ $settings['cta']['secondary_label'] ?? 'Connexion' }}</x-landing.button>
                    </div>
                </div>
            </div>
        </section>
        @endif
    </main>

    <footer class="footer">
        <div class="inner">
            <strong>{{ $settings['footer']['brand'] ?? $brandName }}</strong>
            <span>{{ $settings['footer']['text'] ?? '' }}</span>
            <span>© {{ now()->year }}</span>
        </div>
    </footer>
</div>
<script>
(() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const clamp = (v, min = 0, max = 1) => Math.min(max, Math.max(min, v));

    const countUp = (el) => {
        const target = parseInt(el.dataset.count, 10) || 0;
        if (reduced || target === 0) { el.textContent = target.toLocaleString('fr-FR'); return; }
        const start = performance.now(), duration = 1600;
        const tick = (now) => {
            const t = clamp((now - start) / duration);
            el.textContent = Math.round(target * (1 - Math.pow(1 - t, 4))).toLocaleString('fr-FR');
            if (t < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            entry.target.querySelectorAll('[data-count]').forEach(countUp);
            if (entry.target.matches('[data-count]')) countUp(entry.target);
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    document.querySelectorAll('.reveal, .chip-stats').forEach((el) => observer.observe(el));

    const nav = document.querySelector('.nav');
    const progress = document.querySelector('.progress');
    const zooms = [...document.querySelectorAll('[data-zoom]')];
    const scrubs = [...document.querySelectorAll('[data-scrub]')].map((el) => ({ el, words: [...el.querySelectorAll('.sw')] }));

    const onScroll = () => {
        const vh = window.innerHeight;
        const max = document.documentElement.scrollHeight - vh;
        nav.classList.toggle('is-scrolled', window.scrollY > 8);
        progress.style.setProperty('--p', max > 0 ? (window.scrollY / max).toFixed(4) : 0);
        if (reduced) return;
        zooms.forEach((el) => {
            const rect = el.getBoundingClientRect();
            const p = clamp((vh - rect.top) / (vh * 0.75));
            const base = el.hasAttribute('data-tilt') ? 0.92 : 0.88;
            el.style.setProperty('--zoom', (base + (1 - base) * p).toFixed(4));
        });
        scrubs.forEach(({ el, words }) => {
            const rect = el.getBoundingClientRect();
            const p = clamp((vh * 0.85 - rect.top) / (vh * 0.5));
            const lit = Math.round(p * words.length);
            words.forEach((w, i) => w.classList.toggle('on', i < lit));
        });
    };
    let ticking = false;
    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => { onScroll(); ticking = false; });
    }, { passive: true });
    window.addEventListener('resize', onScroll);
    onScroll();

    document.querySelectorAll('.spot').forEach((el) => {
        el.addEventListener('pointermove', (e) => {
            const r = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${e.clientX - r.left}px`);
            el.style.setProperty('--my', `${e.clientY - r.top}px`);
        });
    });

    if (!reduced && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('[data-tilt]').forEach((el) => {
            el.addEventListener('pointermove', (e) => {
                const r = el.getBoundingClientRect();
                el.style.setProperty('--ry', `${((e.clientX - r.left) / r.width - 0.5) * 8}deg`);
                el.style.setProperty('--rx', `${((e.clientY - r.top) / r.height - 0.5) * -8}deg`);
            });
            el.addEventListener('pointerleave', () => { el.style.setProperty('--rx', '0deg'); el.style.setProperty('--ry', '0deg'); });
        });
    }

    const journey = document.querySelector('[data-journey]');
    if (journey) {
        const steps = [...journey.querySelectorAll('.jstep')];
        const screens = [...journey.querySelectorAll('.scr')];
        const dots = [...journey.querySelectorAll('.journey-dots span')];
        const rail = journey.querySelector('.journey-rail span');
        const device = journey.querySelector('.journey-device');
        let current = -1;
        const update = () => {
            const deviceBottom = window.innerWidth <= 900 ? Math.max(0, device.getBoundingClientRect().bottom) : 0;
            const mid = (deviceBottom + window.innerHeight) / 2;
            let best = 0, bestDist = Infinity;
            steps.forEach((step, i) => {
                const r = step.getBoundingClientRect();
                const d = Math.abs(r.top + r.height / 2 - mid);
                if (d < bestDist) { bestDist = d; best = i; }
            });
            const first = steps[0].getBoundingClientRect(), last = steps[steps.length - 1].getBoundingClientRect();
            const span = (last.top + last.height / 2) - (first.top + first.height / 2);
            rail?.parentElement.style.setProperty('--jp', span > 0 ? clamp((mid - first.top - first.height / 2) / span).toFixed(3) : 1);
            if (best === current) return;
            current = best;
            const screen = steps[best].dataset.screen;
            steps.forEach((s, i) => s.classList.toggle('is-active', i === best));
            dots.forEach((d, i) => d.classList.toggle('is-on', i === best));
            screens.forEach((s) => {
                const on = s.dataset.screen === screen;
                s.classList.toggle('is-on', on);
                s.setAttribute('aria-hidden', on ? 'false' : 'true');
            });
        };
        window.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
        window.addEventListener('resize', update);
        update();
    }

    const passes = document.querySelector('[data-passes]');
    if (passes && !reduced) {
        setInterval(() => { if (!document.hidden && !passes.matches(':hover')) passes.classList.toggle('is-swapped'); }, 3800);
        passes.addEventListener('click', () => passes.classList.toggle('is-swapped'));
    }

    const feed = document.querySelector('[data-feed]');
    if (feed && !reduced) {
        setInterval(() => {
            if (document.hidden) return;
            const row = feed.lastElementChild;
            row.classList.remove('enter');
            void row.offsetWidth;
            feed.prepend(row);
            row.classList.add('enter');
        }, 2600);
    }
})();
</script>
</body>
</html>
