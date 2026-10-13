@php
    // Login / register share the landing identity: same menu, theme and phone mockup, on a single non-scrolling screen.
    $landing = \App\Models\LandingPage::current();
    $settings = $landing->mergedSettings();
    $brandName = $settings['brand']['name'] ?? 'Accès Business';
    $brandLogo = $landing->imageUrl($settings['brand']['logo'] ?? null);
    $brandIcon = $settings['brand']['logo_icon'] ?? 'bi-shield-check';
    $demo = $settings['demo'] ?? [];
    $navigation = collect($settings['navigation'] ?? [])->filter(fn ($item) => (bool) ($item['enabled'] ?? true));
    $screens = array_keys(\App\Models\LandingPage::JOURNEY_SCREENS);
    $authPage = $authPage ?? 'login';
@endphp
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $settings['theme']['background'] ?? '#fbf8f1' }}">
    <title>@yield('title') · {{ $brandName }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,700;12..96,800&family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --ink: {{ $settings['theme']['ink'] ?? '#171713' }};
            --gold: {{ $settings['theme']['gold'] ?? '#b98943' }};
            --bg: {{ $settings['theme']['background'] ?? '#fbf8f1' }};
            --muted: color-mix(in srgb, var(--ink) 55%, #fff);
            --line: color-mix(in srgb, var(--ink) 10%, transparent);
            --panel: #ffffff;
            --gold-soft: color-mix(in srgb, var(--gold) 14%, transparent);
            --gold-light: color-mix(in srgb, var(--gold) 55%, #fff);
            --gold-deep: color-mix(in srgb, var(--gold) 78%, #000);
            --green: #2e7b65;
            --danger: #b4423b;
            --ease: cubic-bezier(.16, 1, .3, 1);
            --display: "Bricolage Grotesque", "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
            --font: "Instrument Sans", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        *, *::before, *::after { box-sizing: border-box; min-width: 0; }
        html, body { height: 100%; overflow: hidden; }
        body { background: var(--bg); color: var(--ink); font-family: var(--font); margin: 0; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        h1, h2 { font-family: var(--display); letter-spacing: -.035em; margin: 0; }

        /* Ambient background */
        .ambient { inset: 0; overflow: hidden; pointer-events: none; position: fixed; z-index: 0; }
        .blob { border-radius: 50%; filter: blur(70px); opacity: .55; position: absolute; }
        .blob-a { animation: drift 18s ease-in-out infinite alternate; background: var(--gold-light); height: 46vmax; left: -12vmax; top: -16vmax; width: 46vmax; }
        .blob-b { animation: drift 22s ease-in-out infinite alternate-reverse; background: color-mix(in srgb, var(--gold) 35%, #fff); bottom: -20vmax; height: 50vmax; right: -14vmax; width: 50vmax; }
        .grid-lines { background-image: linear-gradient(var(--line) 1px, transparent 1px), linear-gradient(90deg, var(--line) 1px, transparent 1px); background-size: 64px 64px; inset: 0; -webkit-mask-image: radial-gradient(circle at 50% 40%, #000, transparent 70%); mask-image: radial-gradient(circle at 50% 40%, #000, transparent 70%); opacity: .5; position: absolute; }
        @keyframes drift { to { transform: translate(6vmax, 4vmax) scale(1.08); } }

        /* Shell: nav + one screen, never taller than the viewport */
        .shell { display: grid; grid-template-rows: auto minmax(0, 1fr); height: 100svh; position: relative; z-index: 1; }
        .inner { margin: 0 auto; max-width: 1320px; padding: 0 clamp(16px, 3vw, 32px); width: 100%; }
        .nav { padding: 14px 0; }
        .nav-shell { align-items: center; -webkit-backdrop-filter: saturate(180%) blur(18px); backdrop-filter: saturate(180%) blur(18px); background: color-mix(in srgb, #fff 62%, transparent); border: 1px solid var(--line); border-radius: 999px; box-shadow: 0 12px 30px -20px rgba(0, 0, 0, .3); display: flex; gap: 20px; height: 60px; justify-content: space-between; padding: 0 8px 0 16px; }
        .brand { align-items: center; display: flex; font-family: var(--display); font-size: 1.1rem; font-weight: 800; gap: 10px; letter-spacing: -.03em; white-space: nowrap; }
        .brand-mark { align-items: center; background: var(--gold); border-radius: 11px; color: #fff; display: flex; flex-shrink: 0; height: 34px; justify-content: center; overflow: hidden; width: 34px; }
        .brand-mark img { height: 100%; object-fit: cover; width: 100%; }
        .nav-links { display: flex; gap: 26px; }
        .nav-links a { color: var(--muted); font-size: .88rem; font-weight: 500; position: relative; transition: color .25s; }
        .nav-links a::after { background: currentColor; bottom: -4px; content: ""; height: 1.5px; left: 0; position: absolute; transform: scaleX(0); transform-origin: right; transition: transform .4s var(--ease); width: 100%; }
        .nav-links a:hover { color: var(--ink); }
        .nav-links a:hover::after { transform: scaleX(1); transform-origin: left; }
        .nav-actions { align-items: center; display: flex; gap: 6px; }
        .pill { align-items: center; border: 1px solid transparent; border-radius: 999px; display: inline-flex; font-size: .84rem; font-weight: 600; gap: 8px; min-height: 42px; padding: 0 16px; transition: transform .45s var(--ease), background-color .3s, border-color .3s, color .3s; white-space: nowrap; }
        .pill:hover { transform: translateY(-1px); }
        .pill-ghost { border-color: var(--line); }
        .pill-ghost:hover { border-color: var(--ink); }
        .pill-gold { background: var(--gold); box-shadow: 0 14px 30px -14px var(--gold); color: #fff; }
        .pill.is-current { background: var(--ink); border-color: var(--ink); box-shadow: none; color: #fff; }

        /* Stage */
        .stage { align-items: center; display: grid; gap: clamp(20px, 3vw, 48px); grid-template-columns: minmax(300px, 1fr) auto minmax(360px, 420px); height: 100%; padding-bottom: clamp(14px, 3vh, 32px); padding-top: clamp(4px, 1vh, 12px); }
        .copy { align-self: center; }
        .badge { align-items: center; background: var(--panel); border: 1px solid var(--line); border-radius: 999px; box-shadow: 0 8px 20px -14px rgba(0, 0, 0, .25); display: inline-flex; font-size: .8rem; font-weight: 600; gap: 10px; padding: 7px 15px 7px 12px; }
        .badge .dot { background: var(--green); border-radius: 50%; height: 8px; position: relative; width: 8px; }
        .badge .dot::after { animation: ping 1.8s cubic-bezier(0, 0, .2, 1) infinite; background: var(--green); border-radius: 50%; content: ""; inset: 0; position: absolute; }
        @keyframes ping { 75%, 100% { opacity: 0; transform: scale(2.6); } }
        .copy h1 { font-size: clamp(2.2rem, 3.5vw, 3.7rem); font-weight: 800; line-height: .95; margin: 20px 0 18px; }
        .copy h1 .accent { color: var(--gold); }
        .copy .lead { color: var(--muted); font-size: clamp(.98rem, 1.2vw, 1.1rem); line-height: 1.55; margin: 0; max-width: 440px; }
        .perks { display: grid; gap: 10px; list-style: none; margin: 26px 0 0; padding: 0; }
        .perks li { align-items: center; display: flex; font-size: .9rem; font-weight: 600; gap: 10px; }
        .perks i { align-items: center; background: var(--gold-soft); border-radius: 9px; color: var(--gold-deep); display: inline-flex; flex-shrink: 0; height: 28px; justify-content: center; width: 28px; }
        [data-lines] .line { display: block; margin-bottom: -.08em; overflow: hidden; padding-bottom: .08em; }
        [data-lines] .line-in { display: inline-block; }
        [data-lines] .w { display: inline-block; }

        .visual { align-items: center; display: grid; height: 100%; justify-items: center; max-height: 640px; position: relative; width: clamp(240px, 21vw, 310px); }
        .visual > * { grid-area: 1 / 1; }
        .disc { aspect-ratio: 1; background: radial-gradient(circle at 32% 28%, var(--gold-light), var(--gold) 58%, var(--gold-deep)); border-radius: 50%; width: 100%; }
        .orbit { animation: spin 70s linear infinite; aspect-ratio: 1; border: 1.5px dashed color-mix(in srgb, var(--gold) 55%, transparent); border-radius: 50%; position: relative; width: 128%; }
        .orbit::before { background: var(--gold); border-radius: 50%; box-shadow: 0 0 0 6px var(--gold-soft); content: ""; height: 12px; left: 50%; position: absolute; top: -6px; width: 12px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .device { position: relative; z-index: 2; }
        .device .phone { zoom: var(--phone-zoom, .74); }
        .chip { align-items: center; background: #fff; border: 1px solid var(--line); border-radius: 16px; box-shadow: 0 24px 48px -22px rgba(0, 0, 0, .35); display: flex; gap: 10px; padding: 9px 13px 9px 9px; position: absolute; z-index: 3; }
        .chip strong { display: block; font-size: .8rem; line-height: 1.2; white-space: nowrap; }
        .chip small { color: var(--muted); display: block; font-size: .7rem; white-space: nowrap; }
        .chip-ic { align-items: center; border-radius: 11px; color: #fff; display: flex; flex: 0 0 34px; height: 34px; justify-content: center; position: relative; }
        .chip-ic.green { background: var(--green); }
        .chip-ic.gold { background: var(--gold); }
        .chip-ic.ink { background: var(--ink); }
        .chip-ic .ring { animation: ring 2.4s ease-out infinite; border: 2px solid var(--green); border-radius: inherit; inset: -2px; position: absolute; }
        @keyframes ring { 0% { opacity: .8; transform: scale(1); } 100% { opacity: 0; transform: scale(1.7); } }
        .chip-a { right: -16%; top: 14%; }
        .chip-b { bottom: 24%; left: -14%; }
        .chip-c { bottom: 6%; right: -8%; }

        /* Form card */
        .card { -webkit-backdrop-filter: blur(20px); backdrop-filter: blur(20px); background: color-mix(in srgb, #fff 86%, transparent); border: 1px solid var(--line); border-radius: 28px; box-shadow: 0 40px 90px -40px rgba(0, 0, 0, .35); max-height: 100%; overflow-y: auto; overscroll-behavior: contain; padding: clamp(22px, 3.2vh, 34px) clamp(22px, 2.4vw, 32px); scrollbar-width: thin; }
        .kicker { color: var(--gold-deep); font-size: .72rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
        .card h2 { font-size: clamp(1.7rem, 2.4vw, 2.1rem); font-weight: 800; line-height: 1; margin: 8px 0 6px; }
        .card .intro { color: var(--muted); font-size: .9rem; line-height: 1.5; margin: 0 0 clamp(14px, 2.4vh, 22px); }
        .field { margin-bottom: clamp(10px, 1.6vh, 14px); }
        .field-row { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; }
        .label { display: block; font-size: .78rem; font-weight: 600; margin-bottom: 6px; }
        .control { position: relative; }
        .control i { color: color-mix(in srgb, var(--ink) 40%, #fff); left: 13px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); transition: color .25s; }
        .input { background: #fff; border: 1px solid color-mix(in srgb, var(--ink) 14%, transparent); border-radius: 12px; color: var(--ink); font: inherit; font-size: .9rem; height: 44px; outline: none; padding: 0 12px 0 38px; transition: border-color .25s, box-shadow .25s; width: 100%; }
        .input::placeholder { color: color-mix(in srgb, var(--ink) 38%, #fff); }
        .input:focus { border-color: var(--gold); box-shadow: 0 0 0 4px var(--gold-soft); }
        .control:focus-within i { color: var(--gold-deep); }
        .input[readonly] { background: color-mix(in srgb, var(--bg) 60%, #fff); }
        .input.is-invalid { border-color: var(--danger); }
        .error { color: var(--danger); font-size: .78rem; font-weight: 600; margin-top: 5px; }
        .hint { color: var(--muted); font-size: .74rem; margin-top: 5px; }
        .check { align-items: center; color: var(--muted); cursor: pointer; display: flex; font-size: .86rem; gap: 9px; margin: 4px 0 clamp(14px, 2vh, 20px); }
        .check input { accent-color: var(--ink); height: 16px; margin: 0; width: 16px; }
        .alert { align-items: flex-start; background: color-mix(in srgb, var(--danger) 8%, #fff); border: 1px solid color-mix(in srgb, var(--danger) 25%, transparent); border-radius: 12px; color: var(--danger); display: flex; font-size: .86rem; font-weight: 600; gap: 8px; margin-bottom: 14px; padding: 10px 12px; }
        .submit { align-items: center; background: var(--ink); border: 0; border-radius: 999px; box-shadow: 0 16px 30px -16px rgba(0, 0, 0, .6); color: #fff; cursor: pointer; display: flex; font: inherit; font-size: .95rem; font-weight: 600; gap: 10px; height: 50px; justify-content: center; overflow: hidden; position: relative; transition: transform .45s var(--ease), box-shadow .45s var(--ease); width: 100%; }
        .submit::before { background: linear-gradient(110deg, transparent 30%, rgba(255, 255, 255, .22), transparent 70%); content: ""; inset: 0; position: absolute; transform: translateX(-100%); }
        .submit:hover { box-shadow: 0 20px 36px -16px rgba(0, 0, 0, .65); transform: translateY(-2px); }
        .submit:hover::before { transform: translateX(100%); transition: transform .9s var(--ease); }
        .submit i { transition: transform .45s var(--ease); }
        .submit:hover i { transform: translateX(3px); }
        .alt { align-items: center; border-top: 1px solid var(--line); color: var(--muted); display: flex; flex-wrap: wrap; font-size: .86rem; gap: 6px 10px; justify-content: space-between; margin-top: clamp(14px, 2.2vh, 20px); padding-top: clamp(12px, 2vh, 18px); }
        .alt a { align-items: center; color: var(--ink); display: inline-flex; font-weight: 600; gap: 6px; }
        .alt a:hover { color: var(--gold-deep); }

@include('landing.device-css')

        @media (max-width: 1180px) {
            .stage { grid-template-columns: minmax(0, 1fr) minmax(340px, 420px); }
            .visual { display: none; }
        }
        @media (max-width: 900px) {
            .nav-links { display: none; }
        }
        @media (max-width: 760px) {
            .stage { align-content: start; grid-template-columns: 1fr; }
            .copy { display: none; }
            .card { padding: 22px 18px; }
            .nav-actions .pill-ghost { display: none; }
        }
        @media (max-width: 760px) and (max-height: 700px) {
            .nav { padding: 8px 0; }
            .nav-shell { height: 54px; }
            .card .intro { display: none; }
            .card h2 { margin-bottom: 14px; }
            .field { margin-bottom: 8px; }
        }
        @media (max-width: 420px) {
            .field-row { gap: 0; grid-template-columns: 1fr; }
        }
        @media (max-height: 720px) and (min-width: 1181px) {
            .device .phone { --phone-zoom: .66; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
<div class="ambient" aria-hidden="true"><span class="grid-lines"></span><span class="blob blob-a" data-blob></span><span class="blob blob-b" data-blob></span></div>

<div class="shell">
    <nav class="nav" aria-label="Navigation principale">
        <div class="inner">
            <div class="nav-shell" data-in>
                <a href="{{ route('landing') }}" class="brand">
                    <span class="brand-mark">@if($brandLogo)<img src="{{ $brandLogo }}" alt="">@else<i class="bi {{ $brandIcon }}"></i>@endif</span>
                    <span>{{ $brandName }}</span>
                </a>
                <div class="nav-links">
                    @foreach($navigation as $item)
                        <a href="{{ route('landing') }}#{{ $item['anchor'] ?? '' }}">{{ $item['label'] ?? '' }}</a>
                    @endforeach
                </div>
                <div class="nav-actions">
                    <a href="{{ route('client.login') }}" @class(['pill', 'pill-ghost', 'is-current' => $authPage === 'login']) @if($authPage === 'login') aria-current="page" @endif>Connexion</a>
                    <a href="{{ route('register') }}" @class(['pill', 'pill-gold', 'is-current' => $authPage === 'register']) @if($authPage === 'register') aria-current="page" @endif>Créer un espace <i class="bi bi-arrow-up-right"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <main class="inner stage">
        <section class="copy">
            <div class="badge" data-in><span class="dot"></span>@yield('eyebrow')</div>
            <h1 data-lines>@yield('headline')</h1>
            <p class="lead" data-in>@yield('lead')</p>
            <ul class="perks">@yield('perks')</ul>
        </section>

        <div class="visual" aria-hidden="true" data-visual>
            <div class="orbit" data-orbit></div>
            <div class="disc" data-disc></div>
            <div class="device" data-device>
                <x-landing.phone data-screens>
                    @foreach($screens as $screen)
                        <x-landing.screen :name="$screen" :active="$loop->first" :brand="$brandName" :logo="$brandLogo" :icon="$brandIcon" :demo="$demo" />
                    @endforeach
                </x-landing.phone>
            </div>
            <div class="chip chip-a" data-chip data-depth="18"><span class="chip-ic green"><i class="bi bi-check-lg"></i><span class="ring"></span></span><div><strong>Session sécurisée</strong><small>Accès chiffré</small></div></div>
            <div class="chip chip-b" data-chip data-depth="-24"><span class="chip-ic ink"><i class="bi bi-people"></i></span><div><strong>Espace isolé</strong><small>Par organisation</small></div></div>
            <div class="chip chip-c" data-chip data-depth="12"><span class="chip-ic gold"><i class="bi bi-wallet2"></i></span><div><strong>Cartes Wallet</strong><small>Apple &amp; Google</small></div></div>
        </div>

        <section class="card" data-card>
            @yield('form')
        </section>
    </main>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
(() => {
    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const gsap = window.gsap;
    const $$ = (s, r = document) => [...r.querySelectorAll(s)];

    // Phone cycles through the invitee journey screens.
    const screens = $$('[data-screens] .scr');
    if (screens.length > 1 && !reduced) {
        let i = 0;
        setInterval(() => { if (document.hidden) return; screens[i].classList.remove('is-on'); i = (i + 1) % screens.length; screens[i].classList.add('is-on'); }, 2800);
    }
    if (!gsap || reduced) return;

    // Split the headline into masked lines.
    const splitLines = (el) => {
        // Wrap loose text into word spans, keep existing ones (accent words).
        [...el.childNodes].forEach((node) => {
            if (node.nodeType !== 3) return;
            const frag = document.createDocumentFragment();
            node.textContent.split(/(\s+)/).forEach((part) => {
                if (!part) return;
                if (/^\s+$/.test(part)) { frag.append(' '); return; }
                const s = document.createElement('span'); s.className = 'w'; s.textContent = part; frag.append(s);
            });
            node.replaceWith(frag);
        });
        el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
        const lines = [];
        $$('.w', el).forEach((w) => { const last = lines[lines.length - 1]; if (!last || Math.abs(last.top - w.offsetTop) > 4) lines.push({ top: w.offsetTop, words: [w] }); else last.words.push(w); });
        el.textContent = '';
        lines.forEach((line) => {
            const outer = document.createElement('span'); outer.className = 'line'; outer.setAttribute('aria-hidden', 'true');
            const inner = document.createElement('span'); inner.className = 'line-in';
            line.words.forEach((w, i) => { inner.append(w); if (i < line.words.length - 1) inner.append(' '); });
            outer.append(inner); el.append(outer);
        });
    };

    const fields = $$('[data-card] > *, [data-card] form > *');
    gsap.set('[data-in], [data-chip], [data-device], [data-disc], [data-orbit], [data-card], .perks li', { autoAlpha: 0 });
    gsap.set(fields, { autoAlpha: 0 });

    document.fonts.ready.then(() => {
        const title = document.querySelector('[data-lines]');
        if (title) splitLines(title);
        gsap.timeline({ defaults: { ease: 'power3.out' } })
            .fromTo('.nav-shell', { y: -30, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .8 })
            .fromTo('.badge', { y: 16, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .6 }, .15)
            .from(title ? $$('.line-in', title) : [], { yPercent: 115, duration: 1.05, ease: 'power4.out', stagger: .09 }, .2)
            .fromTo('.lead', { y: 20, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .7 }, .55)
            .fromTo('.perks li', { x: -16, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: .6, stagger: .08 }, .7)
            .fromTo('[data-disc]', { scale: .4, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: 1.3, ease: 'expo.out' }, .2)
            .fromTo('[data-orbit]', { scale: .6, autoAlpha: 0 }, { scale: 1, autoAlpha: 1, duration: 1.4, ease: 'expo.out' }, .3)
            .fromTo('[data-device]', { y: 120, rotation: -8, autoAlpha: 0 }, { y: 0, rotation: 0, autoAlpha: 1, duration: 1.3, ease: 'expo.out' }, .35)
            .fromTo('[data-chip]', { scale: .6, y: 24, autoAlpha: 0 }, { scale: 1, y: 0, autoAlpha: 1, duration: .8, ease: 'back.out(1.8)', stagger: .14 }, .95)
            .fromTo('[data-card]', { x: 60, autoAlpha: 0 }, { x: 0, autoAlpha: 1, duration: 1.1, ease: 'expo.out' }, .25)
            .fromTo(fields, { y: 18, autoAlpha: 0 }, { y: 0, autoAlpha: 1, duration: .6, stagger: .05, clearProps: 'transform' }, .45)
            .add(() => $$('[data-chip]').forEach((chip, i) => gsap.to(chip, { y: 8, duration: 2.4 + i * .3, repeat: -1, yoyo: true, ease: 'sine.inOut' })));

        // Pointer parallax on the visual (desktop only)
        if (matchMedia('(hover: hover)').matches) {
            const layers = [['[data-device]', 14], ['[data-disc]', -10], ['[data-blob]', 30]].concat($$('[data-chip]').map((c) => [c, parseFloat(c.dataset.depth) || 16]));
            const movers = layers.map(([target, depth]) => ({ depth, x: gsap.quickTo(target, 'xPercent', { duration: 1.2, ease: 'power3.out' }) }));
            addEventListener('pointermove', (e) => { const nx = e.clientX / innerWidth - .5; movers.forEach((m) => m.x(nx * m.depth * .2)); });
        }
    });
})();
</script>
</body>
</html>
