@props([
    'experience',
    'title' => 'Votre invitation',
    'date' => null,
    'place' => null,
    'guest' => null,
    'storageKey' => null,
    'preview' => false,
])

@php
    $template = $experience['template'] ?? 'classic';
    $brand = $experience['brand'] ?? 'Accès Business';
    $initials = \Illuminate\Support\Str::of($brand)->explode(' ')->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
    $uid = 'ie-' . \Illuminate\Support\Str::random(6);
    $logo = $experience['logo'] ?? null;
@endphp

@once
@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
<style>
    .ie { --ie-w: min(330px, 80vw); align-items: center; display: flex; inset: 0; justify-content: center; overflow: hidden; position: fixed; z-index: 2000; -webkit-tap-highlight-color: transparent; }
    .ie.is-preview { --ie-w: min(280px, 70vw); border-radius: 20px; position: absolute; z-index: 5; }
    .ie[hidden] { display: none; }
    .ie-bg { inset: 0; position: absolute; }
    .ie--classic .ie-bg { background: radial-gradient(90% 60% at 50% 35%, #fffaf0 0%, #f1e8d8 55%, #e3d6bf 100%); }
    .ie--prestige .ie-bg { background: radial-gradient(70% 55% at 50% 30%, #2a241b 0%, #12110f 55%, #070706 100%); }
    .ie--modern .ie-bg { background: radial-gradient(90% 70% at 50% 40%, #ffffff 0%, #f3f1ec 60%, #e9e5dc 100%); }
    .ie-vignette { background: radial-gradient(120% 90% at 50% 45%, transparent 55%, rgba(0, 0, 0, .18)); inset: 0; pointer-events: none; position: absolute; }
    .ie--prestige .ie-vignette { background: radial-gradient(100% 80% at 50% 40%, transparent 40%, rgba(0, 0, 0, .6)); }
    .ie-canvas { height: 100%; inset: 0; pointer-events: none; position: absolute; width: 100%; z-index: 1; }
    .ie-flash { background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, .95), rgba(255, 255, 255, 0) 60%); inset: 0; opacity: 0; pointer-events: none; position: absolute; z-index: 8; }
    .ie--prestige .ie-flash { background: radial-gradient(circle at 50% 50%, rgba(255, 226, 160, .85), rgba(255, 226, 160, 0) 60%); }
    .ie-rays { aspect-ratio: 1; background: repeating-conic-gradient(from 0deg, rgba(216, 185, 120, .22) 0deg 6deg, transparent 6deg 18deg); border-radius: 50%; left: 50%; mask-image: radial-gradient(circle, #000 15%, transparent 68%); -webkit-mask-image: radial-gradient(circle, #000 15%, transparent 68%); opacity: 0; pointer-events: none; position: absolute; top: 50%; transform: translate(-50%, -50%); width: 160vmax; z-index: 0; }
    .ie-shapes { inset: 0; pointer-events: none; position: absolute; z-index: 0; }
    .ie-shapes i { border-radius: 50%; position: absolute; }
    .ie-shapes i:nth-child(1) { background: color-mix(in srgb, var(--ie-accent) 30%, transparent); height: 180px; left: -40px; top: 8%; width: 180px; }
    .ie-shapes i:nth-child(2) { border: 2px solid color-mix(in srgb, var(--ie-primary) 30%, transparent); height: 120px; right: -30px; top: 18%; width: 120px; }
    .ie-shapes i:nth-child(3) { background: color-mix(in srgb, var(--ie-primary) 14%, transparent); bottom: 10%; height: 220px; right: -70px; width: 220px; }
    .ie-shapes i:nth-child(4) { border: 2px solid color-mix(in srgb, var(--ie-accent) 45%, transparent); bottom: 18%; height: 70px; left: 10%; width: 70px; }
    .ie-shapes i:nth-child(5) { background: var(--ie-accent); border-radius: 4px; height: 14px; left: 22%; top: 28%; transform: rotate(20deg); width: 14px; }

    .ie-stage { align-items: center; display: flex; flex-direction: column; gap: 26px; position: relative; z-index: 2; }
    .ie-hint { align-items: center; color: #6f675b; display: inline-flex; font-size: .84rem; font-weight: 500; gap: 8px; letter-spacing: .02em; margin: 0; }
    .ie-hint i { font-size: 1rem; }
    .ie--prestige .ie-hint { color: rgba(255, 240, 210, .7); }
    .ie-top { display: flex; gap: 8px; position: absolute; right: 16px; top: calc(14px + env(safe-area-inset-top)); z-index: 10; }
    .ie-skip { -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); background: rgba(255, 255, 255, .7); border: 1px solid rgba(23, 23, 19, .1); border-radius: 999px; color: #2c2a25; font-size: .78rem; font-weight: 600; padding: 7px 14px; }
    .ie--prestige .ie-skip { background: rgba(255, 255, 255, .08); border-color: rgba(255, 255, 255, .15); color: #fff; }
    .ie-continue { background: #171713; border: 0; border-radius: 999px; bottom: calc(38px + env(safe-area-inset-bottom)); box-shadow: 0 16px 36px -14px rgba(0, 0, 0, .6); color: #fff; font-size: .9rem; font-weight: 600; left: 50%; opacity: 0; padding: 14px 26px; position: absolute; transform: translateX(-50%); visibility: hidden; white-space: nowrap; z-index: 10; }
    .ie--prestige .ie-continue { background: linear-gradient(135deg, #e8cf94, #b98943 55%, #8a6128); color: #141412; }
    .ie-continue i { margin-left: 6px; }

    /* Envelope */
    .ie-scene { aspect-ratio: 1.45; perspective: 1600px; position: relative; width: var(--ie-w); }
    .ie-env { cursor: pointer; inset: 0; position: absolute; transform-style: preserve-3d; }
    .ie-shadow { background: radial-gradient(closest-side, rgba(40, 28, 10, .35), transparent); bottom: -22%; height: 26%; left: 8%; position: absolute; right: 8%; z-index: -1; }
    .ie--prestige .ie-shadow { background: radial-gradient(closest-side, rgba(0, 0, 0, .8), transparent); }
    .ie-env-back { background: var(--ie-env-inner); border-radius: 12px; inset: 0; position: absolute; }
    .ie-env-back::after { background: repeating-linear-gradient(135deg, rgba(0, 0, 0, .025) 0 2px, transparent 2px 7px); border-radius: inherit; content: ""; inset: 0; position: absolute; }
    .ie-fold { inset: 0; position: absolute; z-index: 3; }
    .ie-fold-l { background: linear-gradient(90deg, var(--ie-env-side), var(--ie-env)); border-radius: 12px 0 0 12px; clip-path: polygon(0 0, 52% 54%, 0 100%); }
    .ie-fold-r { background: linear-gradient(270deg, var(--ie-env-side), var(--ie-env)); border-radius: 0 12px 12px 0; clip-path: polygon(100% 0, 48% 54%, 100% 100%); }
    .ie-fold-b { background: linear-gradient(0deg, var(--ie-env-side), var(--ie-env)); border-radius: 0 0 12px 12px; clip-path: polygon(0 100%, 50% 44%, 100% 100%); filter: drop-shadow(0 -2px 3px rgba(0, 0, 0, .08)); }
    .ie-flap { height: 58%; left: 0; position: absolute; right: 0; top: 0; transform-origin: 50% 0; transform-style: preserve-3d; z-index: 4; }
    .ie-flap-face { backface-visibility: hidden; -webkit-backface-visibility: hidden; clip-path: polygon(0 0, 100% 0, 50% 100%); inset: 0; position: absolute; }
    .ie-flap-out { background: linear-gradient(180deg, var(--ie-env-flap), var(--ie-env)); border-radius: 12px 12px 0 0; }
    .ie-flap-out::after { background: linear-gradient(165deg, rgba(255, 255, 255, .35), transparent 45%); content: ""; inset: 0; position: absolute; }
    .ie-flap-in { background: var(--ie-env-inner); transform: rotateX(180deg); }
    .ie-seal { height: 66px; left: 50%; margin: -33px 0 0 -33px; position: absolute; top: 58%; width: 66px; z-index: 5; }
    .ie-seal-half { align-items: center; background: radial-gradient(circle at 35% 30%, color-mix(in srgb, var(--ie-seal) 65%, #fff), var(--ie-seal) 45%, color-mix(in srgb, var(--ie-seal) 65%, #000)); border-radius: 50%; box-shadow: 0 6px 14px rgba(0, 0, 0, .3), inset 0 -3px 6px rgba(0, 0, 0, .25), inset 0 2px 3px rgba(255, 255, 255, .3); color: rgba(255, 255, 255, .92); display: flex; font-family: "Cormorant Garamond", Georgia, serif; font-size: 1.4rem; font-weight: 600; inset: 0; justify-content: center; overflow: hidden; position: absolute; }
    .ie-seal-half::before { border: 1.5px dashed rgba(255, 255, 255, .35); border-radius: 50%; content: ""; inset: 7px; position: absolute; }
    .ie-seal-half img { border-radius: 50%; height: 60%; object-fit: cover; opacity: .92; width: 60%; }
    .ie-seal-l { clip-path: polygon(0 0, 54% 0, 46% 22%, 57% 40%, 44% 62%, 55% 80%, 48% 100%, 0 100%); }
    .ie-seal-r { clip-path: polygon(54% 0, 100% 0, 100% 100%, 48% 100%, 55% 80%, 44% 62%, 57% 40%, 46% 22%); }
    .ie-seal-ring { border: 2px solid var(--ie-seal); border-radius: 50%; inset: -4px; opacity: 0; position: absolute; }
    .ie-frag { background: var(--ie-seal); border-radius: 40% 60% 50% 50%; height: 9px; left: 50%; margin: -4px 0 0 -4px; opacity: 0; position: absolute; top: 50%; width: 9px; }

    /* Card */
    .ie-card { background: #fffdf8; border-radius: 8px; bottom: 6%; box-shadow: 0 12px 34px -12px rgba(0, 0, 0, .4); container-type: inline-size; left: 6%; overflow: hidden; position: absolute; right: 6%; top: 7%; transform-origin: 50% 100%; z-index: 2; }
    .ie-card-inner { align-items: center; display: flex; flex-direction: column; height: 100%; justify-content: center; padding: 6% 9%; position: relative; text-align: center; }
    .ie-card-frame { border: 1px solid color-mix(in srgb, var(--ie-accent) 45%, transparent); border-radius: 4px; inset: 5%; pointer-events: none; position: absolute; }
    .ie-card-logo { align-items: center; background: var(--ie-primary); border-radius: 8px; color: #fff; display: flex; flex-shrink: 0; font-size: .7rem; font-weight: 700; height: clamp(18px, 9cqi, 26px); justify-content: center; margin-bottom: 4%; overflow: hidden; width: clamp(18px, 9cqi, 26px); }
    .ie-card-logo img { height: 100%; object-fit: cover; width: 100%; }
    .ie-card small { color: var(--ie-accent); font-size: clamp(.45rem, 3.2cqi, .58rem); font-weight: 700; letter-spacing: .18em; text-transform: uppercase; }
    .ie-card .ie-guest { font-family: "Cormorant Garamond", Georgia, serif; font-size: clamp(.7rem, 5.4cqi, 1.05rem); font-style: italic; margin-top: 2px; opacity: .78; }
    .ie-card strong { display: -webkit-box; font-family: "Cormorant Garamond", Georgia, serif; font-size: clamp(.85rem, 7.8cqi, 1.5rem); font-weight: 600; -webkit-line-clamp: 3; -webkit-box-orient: vertical; line-height: 1.1; margin: 3px 0 4px; overflow: hidden; }
    .ie-card .ie-meta { font-size: clamp(.5rem, 3.6cqi, .66rem); letter-spacing: .02em; line-height: 1.4; opacity: .72; }
    .ie-card .ie-rule { background: var(--ie-accent); flex-shrink: 0; height: 1px; margin: 4% auto 3%; opacity: .7; transform-origin: center; width: 40px; }
    .ie-glare { background: linear-gradient(105deg, transparent 30%, rgba(255, 255, 255, .75) 48%, rgba(255, 255, 255, 0) 62%); inset: 0; pointer-events: none; position: absolute; transform: translateX(-130%); }
    .ie.has-js .ie-card .ie-ci { opacity: 0; }

    .ie--classic { --ie-env: #f0e5cf; --ie-env-side: #e6d7b9; --ie-env-inner: #d9c7a3; --ie-env-flap: #ecdfc4; --ie-seal: var(--ie-accent); }
    .ie--prestige { --ie-env: #1d1b18; --ie-env-side: #161512; --ie-env-inner: #0c0c0a; --ie-env-flap: #2a2722; --ie-seal: #c79b52; }
    .ie--prestige .ie-fold, .ie--prestige .ie-flap-out { box-shadow: inset 0 0 0 1px rgba(216, 185, 120, .4); }
    .ie--prestige .ie-fold-b::after { background: linear-gradient(105deg, transparent 35%, rgba(216, 185, 120, .4) 50%, transparent 65%); background-size: 260% 100%; content: ""; inset: 0; position: absolute; animation: ieFoil 3.4s ease-in-out infinite; }
    .ie--prestige .ie-card { background: radial-gradient(120% 80% at 50% 0%, #26221b, #12110f); box-shadow: 0 0 0 1px rgba(216, 185, 120, .5), 0 24px 50px -16px rgba(0, 0, 0, .8); color: #f5ecd8; }
    .ie--prestige .ie-card strong { background: linear-gradient(100deg, #b98943, #f3dfa8 45%, #b98943 60%, #f3dfa8); background-size: 220% 100%; -webkit-background-clip: text; background-clip: text; color: transparent; animation: ieFoil 4s linear infinite; }
    .ie--prestige .ie-card small { color: #d8b978; }
    .ie--prestige .ie-glare { background: linear-gradient(105deg, transparent 30%, rgba(255, 226, 160, .45) 48%, transparent 62%); }
    @keyframes ieFoil { from { background-position: 160% 0; } to { background-position: -60% 0; } }

    /* Modern booklet */
    .ie-book { aspect-ratio: .72; cursor: pointer; perspective: 1600px; position: relative; width: calc(var(--ie-w) * .64); }
    .ie-book .ie-card { border-radius: 4px 14px 14px 4px; inset: 0; transform-origin: 50% 50%; }
    .ie-book .ie-card strong { -webkit-line-clamp: 3; }
    .ie-cover { inset: 0; position: absolute; transform-origin: 0 50%; transform-style: preserve-3d; z-index: 3; }
    .ie-cover-face { backface-visibility: hidden; -webkit-backface-visibility: hidden; border-radius: 4px 14px 14px 4px; inset: 0; position: absolute; }
    .ie-cover-front { align-items: center; background: radial-gradient(120% 90% at 0% 0%, color-mix(in srgb, var(--ie-accent) 60%, transparent), transparent 60%), var(--ie-primary); box-shadow: 0 30px 50px -20px rgba(0, 0, 0, .5), inset 7px 0 12px -6px rgba(0, 0, 0, .45); color: #fff; display: flex; flex-direction: column; justify-content: center; padding: 14%; text-align: center; }
    .ie-cover-front .ie-card-logo { background: rgba(255, 255, 255, .14); height: 42px; margin-bottom: 16%; width: 42px; }
    .ie-cover-front small { color: color-mix(in srgb, var(--ie-accent) 65%, #fff); font-size: .6rem; font-weight: 700; letter-spacing: .2em; text-transform: uppercase; }
    .ie-cover-front em { font-family: "Cormorant Garamond", Georgia, serif; font-size: 1.55rem; line-height: 1.12; margin-top: 8px; }
    .ie-cover-front .ie-cta { border: 1px solid rgba(255, 255, 255, .35); border-radius: 99px; font-size: .62rem; letter-spacing: .12em; margin-top: 16%; padding: 6px 13px; text-transform: uppercase; }
    .ie-cover-back { backface-visibility: visible; -webkit-backface-visibility: visible; background: linear-gradient(90deg, color-mix(in srgb, var(--ie-primary) 82%, #fff), color-mix(in srgb, var(--ie-primary) 92%, #fff)); border-radius: 14px 4px 4px 14px; transform: rotateY(180deg); }

    /* Sound toggle (stays after the animation) */
    .ie-sound { align-items: center; -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px); background: rgba(20, 20, 18, .85); border: 0; border-radius: 999px; box-shadow: 0 10px 24px -10px rgba(0, 0, 0, .5); color: #fff; display: inline-flex; font-size: .76rem; font-weight: 600; gap: 8px; height: 36px; left: 16px; padding: 0 13px; position: fixed; top: calc(14px + env(safe-area-inset-top)); z-index: 2001; }
    .ie-sound[hidden] { display: none; }
    .ie-sound.is-preview { left: 12px; position: absolute; top: 12px; z-index: 11; }
    .ie-eq { align-items: flex-end; display: inline-flex; gap: 2px; height: 12px; }
    .ie-eq span { background: #d8b978; border-radius: 1px; height: 30%; width: 3px; }
    .ie-sound.is-playing .ie-eq span { animation: ieEq .9s ease-in-out infinite; }
    .ie-sound.is-playing .ie-eq span:nth-child(2) { animation-delay: .2s; }
    .ie-sound.is-playing .ie-eq span:nth-child(3) { animation-delay: .4s; }
    @keyframes ieEq { 0%, 100% { height: 25%; } 50% { height: 100%; } }
    body.ie-lock { overflow: hidden; }
</style>
@endpush
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script>
window.InvitationExperience = window.InvitationExperience || (() => {
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let sharedCtx = null;
    const audioCtx = () => {
        const AC = window.AudioContext || window.webkitAudioContext;
        if (!AC) return null;
        sharedCtx = sharedCtx || new AC();
        if (sharedCtx.state === 'suspended') sharedCtx.resume();
        return sharedCtx;
    };

    // Royalty-free generative melody (Web Audio): soft pad + bell arpeggio, looping.
    function synthPlayer() {
        const ctx = audioCtx();
        if (!ctx) return null;
        const master = ctx.createGain(); master.gain.value = 0; master.connect(ctx.destination);
        const reverb = ctx.createConvolver();
        const len = ctx.sampleRate * 2.6, impulse = ctx.createBuffer(2, len, ctx.sampleRate);
        for (let c = 0; c < 2; c++) { const d = impulse.getChannelData(c); for (let i = 0; i < len; i++) d[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / len, 2.6); }
        reverb.buffer = impulse;
        const wet = ctx.createGain(); wet.gain.value = .45; reverb.connect(wet); wet.connect(master);
        const dry = ctx.createGain(); dry.gain.value = .7; dry.connect(master);
        const hz = (m) => 440 * Math.pow(2, (m - 69) / 12);
        const chords = [[60, 64, 67, 71], [57, 60, 64, 67], [53, 57, 60, 64], [55, 59, 62, 67]];
        const note = (midi, t, dur, type, gain) => {
            const o = ctx.createOscillator(), g = ctx.createGain();
            o.type = type; o.frequency.value = hz(midi);
            g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(gain, t + (dur < 2 ? .01 : .8)); g.gain.exponentialRampToValueAtTime(.0001, t + dur);
            o.connect(g); g.connect(dry); g.connect(reverb); o.start(t); o.stop(t + dur + .05);
        };
        let timer = null, bar = 0, next = 0;
        const schedule = () => {
            while (next < ctx.currentTime + 1.2) {
                const chord = chords[bar % chords.length];
                chord.forEach((m) => note(m - 12, next, 4.2, 'triangle', .035));
                [0, 1, 2, 3, 2, 1, 3, 2].forEach((idx, i) => note(chord[idx] + 12, next + i * .5, 1.6, 'sine', .05 + (i === 0 ? .02 : 0)));
                next += 4; bar++;
            }
        };
        return {
            play() { ctx.resume(); if (!timer) { next = ctx.currentTime + .1; schedule(); timer = setInterval(schedule, 400); } master.gain.cancelScheduledValues(ctx.currentTime); master.gain.linearRampToValueAtTime(.9, ctx.currentTime + 2); },
            pause() { master.gain.cancelScheduledValues(ctx.currentTime); master.gain.linearRampToValueAtTime(0, ctx.currentTime + .4); },
        };
    }

    function filePlayer(url) {
        const audio = new Audio(url); audio.loop = true; audio.preload = 'auto'; audio.volume = 0;
        let fade = null;
        const ramp = (to) => { clearInterval(fade); fade = setInterval(() => { const v = audio.volume + (to > audio.volume ? .05 : -.08); audio.volume = Math.max(0, Math.min(.75, v)); if ((to > 0 && audio.volume >= to) || (to === 0 && audio.volume <= 0)) { clearInterval(fade); if (!to) audio.pause(); } }, 80); };
        return { play() { audio.play().catch(() => {}); ramp(.75); }, pause() { ramp(0); } };
    }

    // Small synthesized sound effects: wax crack and paper whoosh.
    function sfx(kind) {
        const ctx = audioCtx();
        if (!ctx) return;
        const t = ctx.currentTime;
        const noise = (dur) => { const b = ctx.createBuffer(1, Math.ceil(ctx.sampleRate * dur), ctx.sampleRate); const d = b.getChannelData(0); for (let i = 0; i < d.length; i++) d[i] = Math.random() * 2 - 1; const s = ctx.createBufferSource(); s.buffer = b; return s; };
        if (kind === 'crack') {
            const n = noise(.12), f = ctx.createBiquadFilter(), g = ctx.createGain();
            f.type = 'highpass'; f.frequency.value = 1800; g.gain.setValueAtTime(.35, t); g.gain.exponentialRampToValueAtTime(.001, t + .12);
            n.connect(f); f.connect(g); g.connect(ctx.destination); n.start(t);
            const o = ctx.createOscillator(), og = ctx.createGain(); o.frequency.setValueAtTime(160, t); o.frequency.exponentialRampToValueAtTime(60, t + .15);
            og.gain.setValueAtTime(.25, t); og.gain.exponentialRampToValueAtTime(.001, t + .18); o.connect(og); og.connect(ctx.destination); o.start(t); o.stop(t + .2);
        } else {
            const n = noise(.9), f = ctx.createBiquadFilter(), g = ctx.createGain();
            f.type = 'bandpass'; f.Q.value = .8; f.frequency.setValueAtTime(400, t); f.frequency.exponentialRampToValueAtTime(2600, t + .7);
            g.gain.setValueAtTime(0, t); g.gain.linearRampToValueAtTime(.16, t + .25); g.gain.exponentialRampToValueAtTime(.001, t + .9);
            n.connect(f); f.connect(g); g.connect(ctx.destination); n.start(t);
        }
    }

    // Canvas particles: ambient floating dust + a burst on reveal.
    function particles(canvas, palette, mode) {
        const ctx = canvas.getContext('2d');
        let w = 0, h = 0, items = [], raf = null, running = false;
        const dpr = Math.min(2, window.devicePixelRatio || 1);
        const resize = () => { const r = canvas.getBoundingClientRect(); w = r.width; h = r.height; canvas.width = w * dpr; canvas.height = h * dpr; ctx.setTransform(dpr, 0, 0, dpr, 0, 0); };
        const rand = (a, b) => a + Math.random() * (b - a);
        const ambient = () => ({ kind: 'dust', x: rand(0, w), y: rand(0, h), r: rand(.6, 2.2), vx: rand(-.08, .08), vy: rand(-.25, -.05), a: rand(.2, .8), tw: rand(0, Math.PI * 2), color: palette[0] });
        const frame = () => {
            ctx.clearRect(0, 0, w, h);
            items = items.filter((p) => p.life === undefined || p.life > 0);
            for (const p of items) {
                if (p.kind === 'dust') {
                    p.x += p.vx; p.y += p.vy; p.tw += .04;
                    if (p.y < -5) { p.y = h + 5; p.x = rand(0, w); }
                    ctx.globalAlpha = p.a * (.55 + .45 * Math.sin(p.tw));
                    ctx.fillStyle = p.color; ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2); ctx.fill();
                } else {
                    p.vy += p.g; p.vx *= .985; p.vy *= .985; p.x += p.vx; p.y += p.vy; p.rot += p.vr; p.life -= 1;
                    ctx.globalAlpha = Math.max(0, Math.min(1, p.life / 40));
                    ctx.fillStyle = p.color;
                    if (p.kind === 'spark') { ctx.beginPath(); ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2); ctx.fill(); }
                    else { ctx.save(); ctx.translate(p.x, p.y); ctx.rotate(p.rot); ctx.scale(1, Math.cos(p.rot * 2)); ctx.fillRect(-p.r, -p.r / 2, p.r * 2, p.r); ctx.restore(); }
                }
            }
            ctx.globalAlpha = 1;
            raf = running ? requestAnimationFrame(frame) : null;
        };
        return {
            start() { resize(); items = Array.from({ length: Math.min(90, Math.round((w * h) / 9000)) }, ambient); running = true; if (!raf) frame(); },
            burst(x, y, count = 110) {
                for (let i = 0; i < count; i++) {
                    const angle = rand(-Math.PI * .95, -Math.PI * .05), speed = rand(3, 9.5);
                    items.push({ kind: mode === 'sparks' ? 'spark' : 'confetti', x, y, r: mode === 'sparks' ? rand(1, 2.6) : rand(3, 6), vx: Math.cos(angle) * speed * rand(.6, 1.2), vy: Math.sin(angle) * speed, g: mode === 'sparks' ? .07 : .16, rot: rand(0, 6), vr: rand(-.25, .25), life: rand(70, 130), color: palette[i % palette.length] });
                }
            },
            stop() { running = false; },
            resize,
        };
    }

    function init(root) {
        if (root.dataset.ieReady) return;
        root.dataset.ieReady = '1';
        const gsap = window.gsap;
        if (gsap) root.classList.add('has-js');
        const template = root.dataset.ieTemplate;
        const music = JSON.parse(root.dataset.ieMusic || 'null');
        const preview = root.classList.contains('is-preview');
        const sound = document.getElementById(root.dataset.ieSound);
        const $ = (s) => root.querySelector(s), $$ = (s) => [...root.querySelectorAll(s)];
        const style = getComputedStyle(root);
        const accent = style.getPropertyValue('--ie-accent').trim() || '#b98943';
        const primary = style.getPropertyValue('--ie-primary').trim() || '#171713';
        const palette = template === 'prestige' ? ['#f3dfa8', '#d8b978', '#ffffff', '#b98943'] : template === 'modern' ? [accent, primary, '#d8b978', '#ffffff'] : ['#d8b978', accent, '#ffffff', '#c79b52'];
        const fx = particles($('.ie-canvas'), palette, template === 'prestige' ? 'sparks' : 'confetti');
        let player = null, playing = false, opened = false, finished = false, idle = [], autoTimer = null;

        const setPlaying = (on) => {
            playing = on;
            if (!sound) return;
            sound.hidden = !music;
            sound.classList.toggle('is-playing', on);
            sound.querySelector('[data-ie-label]').textContent = on ? 'Couper le son' : 'Écouter la musique';
        };
        const play = () => { if (!music) return; player = player || (music.type === 'file' ? filePlayer(music.url) : synthPlayer()); if (!player) return; player.play(); setPlaying(true); };
        const stop = () => { player?.pause(); setPlaying(false); };
        sound?.addEventListener('click', () => (playing ? stop() : play()));

        const finish = () => {
            if (finished) return;
            finished = true;
            clearTimeout(autoTimer);
            const done = () => {
                root.classList.add('is-done');
                fx.stop();
                document.body.classList.remove('ie-lock');
                if (!preview) { try { localStorage.setItem(root.dataset.ieKey, '1'); } catch (e) {} root.hidden = true; }
            };
            if (gsap && !reduced) {
                gsap.to($('.ie-continue'), { autoAlpha: 0, duration: .25 });
                gsap.to(template === 'modern' ? $('.ie-book') : $('.ie-card'), { y: '-=40', autoAlpha: 0, duration: .6, ease: 'power2.in' });
                gsap.to(root, { autoAlpha: 0, duration: .7, delay: .35, ease: 'power1.inOut', onComplete: done });
            } else {
                done();
            }
        };

        const intro = () => {
            if (!gsap || reduced) return;
            fx.start();
            const tl = gsap.timeline({ defaults: { ease: 'power3.out' } });
            tl.from($('.ie-bg'), { autoAlpha: 0, duration: .6 });
            if (template === 'modern') {
                tl.from($$('.ie-shapes i'), { autoAlpha: 0, scale: .5, duration: 1.2, stagger: .08 }, .1)
                  .from($('.ie-book'), { y: 80, rotationY: -35, rotationX: 10, autoAlpha: 0, duration: 1.2, ease: 'back.out(1.4)' }, .2);
                $$('.ie-shapes i').forEach((s, i) => idle.push(gsap.to(s, { x: gsap.utils.random(-30, 30), y: gsap.utils.random(-30, 30), rotation: gsap.utils.random(-25, 25), duration: gsap.utils.random(4, 7), repeat: -1, yoyo: true, ease: 'sine.inOut', delay: i * .2 })));
                idle.push(gsap.to($('.ie-book'), { y: -8, rotationY: 6, duration: 2.6, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1.4 }));
            } else {
                tl.from($('.ie-env'), { y: -120, rotationX: 35, rotationZ: -6, scale: .85, autoAlpha: 0, duration: 1.3, ease: 'back.out(1.3)' }, .15)
                  .from($('.ie-shadow'), { scale: .4, autoAlpha: 0, duration: 1.1 }, .4)
                  .from($('.ie-seal'), { scale: 0, rotation: -90, duration: .9, ease: 'elastic.out(1, .55)' }, 1.05);
                if (template === 'prestige') tl.to($('.ie-rays'), { autoAlpha: .22, duration: 1.4 }, .3);
                idle.push(gsap.to($('.ie-env'), { y: -9, rotationZ: 1.2, rotationX: 4, duration: 2.8, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1.5 }));
                idle.push(gsap.to($('.ie-shadow'), { scale: .9, opacity: .7, duration: 2.8, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 1.5 }));
                idle.push(gsap.fromTo($('.ie-seal-ring'), { scale: 1, autoAlpha: .7 }, { scale: 1.9, autoAlpha: 0, duration: 1.6, repeat: -1, ease: 'power1.out', delay: 1.8 }));
                if (template === 'prestige') idle.push(gsap.to($('.ie-rays'), { rotation: 360, duration: 60, repeat: -1, ease: 'none' }));
            }
            tl.from($('.ie-hint'), { y: 12, autoAlpha: 0, duration: .7 }, 1.3)
              .from($('.ie-top'), { autoAlpha: 0, duration: .5 }, 1.2);
            idle.push(gsap.to($('.ie-hint'), { y: -3, autoAlpha: .6, duration: 1.2, repeat: -1, yoyo: true, ease: 'sine.inOut', delay: 2 }));
        };

        const revealContent = (tl, at, cardEl) => {
            tl.to(cardEl.querySelectorAll('.ie-ci'), { autoAlpha: 1, y: 0, duration: .6, stagger: .09, ease: 'power2.out' }, at)
              .fromTo(cardEl.querySelector('.ie-rule'), { scaleX: 0 }, { scaleX: 1, duration: .7, ease: 'power2.inOut' }, at + .35)
              .fromTo(cardEl.querySelector('.ie-glare'), { xPercent: -130 }, { xPercent: 130, duration: 1.2, ease: 'power2.inOut' }, at + .55);
        };

        const showContinue = (tl, at) => {
            tl.fromTo($('.ie-continue'), { autoAlpha: 0, y: 16 }, { autoAlpha: 1, y: 0, duration: .6, ease: 'power3.out' }, at)
              .call(() => { autoTimer = setTimeout(finish, 6500); }, null, at);
        };

        // Moves an element to the visual center of the overlay and scales it to targetWidth.
        const flyToCenter = (el, targetWidth, duration) => {
            const r = el.getBoundingClientRect(), rr = root.getBoundingClientRect();
            const scale = Math.min(1.7, targetWidth / r.width);
            gsap.to(el, {
                x: '+=' + (rr.left + rr.width / 2 - (r.left + r.width / 2)),
                y: '+=' + (rr.top + rr.height * .46 - (r.top + r.height / 2)),
                scale, rotationX: 0, duration, ease: 'power3.inOut',
            });
        };
        const burstAt = (el, count) => { const r = el.getBoundingClientRect(), rr = root.getBoundingClientRect(); fx.burst(r.left - rr.left + r.width / 2, r.top - rr.top + r.height * .45, count); };

        const open = () => {
            if (opened) return;
            opened = true;
            idle.forEach((t) => t.kill()); idle = [];
            play();
            if (navigator.vibrate) navigator.vibrate(18);
            if (!gsap || reduced) { finish(); return; }
            if (music) sfx('crack');
            const rr = root.getBoundingClientRect();
            const targetW = Math.min(rr.width * .84, 380);
            const tl = gsap.timeline();
            tl.to([$('.ie-hint'), $('.ie-top')], { autoAlpha: 0, duration: .25 }, 0);

            if (template === 'modern') {
                const book = $('.ie-book'), card = book.querySelector('.ie-card');
                gsap.set(card.querySelectorAll('.ie-ci'), { y: 10 });
                gsap.set($('.ie-cover-back'), { autoAlpha: 0 });
                tl.to(book, { scale: .95, duration: .12, ease: 'power2.out' }, 0)
                  .to(book, { scale: 1, xPercent: 50, rotationY: 0, rotationX: 0, y: 0, duration: 1.1, ease: 'power3.inOut' }, .12)
                  .to($('.ie-cover'), { rotationY: -180, duration: 1.3, ease: 'power3.inOut' }, .15)
                  .set($('.ie-cover-front'), { autoAlpha: 0 }, .8)
                  .set($('.ie-cover-back'), { autoAlpha: 1 }, .8)
                  .call(() => { if (music) sfx('whoosh'); }, null, .3)
                  .to($('.ie-flash'), { autoAlpha: .55, duration: .2, yoyo: true, repeat: 1 }, 1.0)
                  .call(() => burstAt(book, 130), null, 1.15)
                  .to($('.ie-cover'), { autoAlpha: 0, duration: .5 }, 1.3)
                  .to(book, { xPercent: 0, duration: .9, ease: 'power3.inOut' }, 1.35)
                  .call(() => gsap.to(book, { scale: Math.min(1.5, targetW / book.offsetWidth), duration: 1, ease: 'power3.out' }), null, 1.5)
                  .to($$('.ie-shapes i'), { autoAlpha: .35, duration: 1 }, 1.4);
                revealContent(tl, 1.9, card);
                showContinue(tl, 3.1);
                return;
            }

            const env = $('.ie-env'), card = $('.ie-card'), k = template === 'prestige' ? 1.25 : 1;
            gsap.set(card.querySelectorAll('.ie-ci'), { y: 10 });
            tl.to($('.ie-seal-ring'), { autoAlpha: 0, duration: .1 }, 0)
              .to($('.ie-seal'), { scale: 1.15, duration: .12, ease: 'power2.out' }, 0)
              .to($('.ie-seal-l'), { x: -34, y: 18, rotation: -38, autoAlpha: 0, duration: .55, ease: 'power2.out' }, .12)
              .to($('.ie-seal-r'), { x: 34, y: 22, rotation: 42, autoAlpha: 0, duration: .55, ease: 'power2.out' }, .12)
              .fromTo($$('.ie-frag'), { x: 0, y: 0, autoAlpha: 1, scale: 1 }, { x: () => gsap.utils.random(-70, 70), y: () => gsap.utils.random(-30, 60), rotation: () => gsap.utils.random(-180, 180), autoAlpha: 0, scale: .3, duration: .7, ease: 'power2.out', stagger: .01 }, .12)
              .to($('.ie-flash'), { autoAlpha: .6, duration: .12, yoyo: true, repeat: 1 }, .12)
              .to(env, { rotationX: 14, scale: 1.04, y: 10, duration: .5 * k, ease: 'power2.out' }, .1)
              .to($('.ie-flap'), { rotationX: 180, duration: 1 * k, ease: 'power2.inOut' }, .35)
              .set($('.ie-flap'), { zIndex: 1 }, .35 + .5 * k)
              .call(() => { if (music) sfx('whoosh'); }, null, .95 * k)
              .to(card, { yPercent: -72, duration: 1 * k, ease: 'power3.out' }, 1.0 * k)
              .set(card, { zIndex: 7, transformOrigin: '50% 50%' }, 1.85 * k)
              .to([$('.ie-env-back'), ...$$('.ie-fold'), $('.ie-flap')], { y: 140, autoAlpha: 0, duration: .9, ease: 'power2.in' }, 1.9 * k)
              .to($('.ie-shadow'), { autoAlpha: 0, duration: .6 }, 1.9 * k)
              .to(env, { rotationX: 0, y: 0, scale: 1, duration: .9, ease: 'power2.out' }, 1.9 * k)
              .call(() => flyToCenter(card, targetW, 1.1), null, 2.0 * k)
              .call(() => burstAt(card, template === 'prestige' ? 160 : 120), null, 2.55 * k)
              .to($('.ie-flash'), { autoAlpha: .4, duration: .2, yoyo: true, repeat: 1 }, 2.5 * k);
            if (template === 'prestige') tl.to($('.ie-rays'), { autoAlpha: 1, scale: 1.15, duration: 1.4, ease: 'power2.out' }, 2.3 * k);
            revealContent(tl, 2.9 * k, card);
            showContinue(tl, 3.9 * k);
        };

        $$('[data-ie-open]').forEach((el) => {
            el.addEventListener('click', open);
            el.addEventListener('keydown', (e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
        });
        $('[data-ie-skip]')?.addEventListener('click', finish);
        $('[data-ie-continue]')?.addEventListener('click', finish);
        window.addEventListener('resize', () => fx.resize());

        root.reset = () => {
            stop(); clearTimeout(autoTimer); idle.forEach((t) => t.kill()); idle = [];
            if (gsap) { gsap.killTweensOf([root, ...root.querySelectorAll('*')]); gsap.set([root, ...root.querySelectorAll('*')], { clearProps: 'all' }); }
            root.classList.remove('is-done'); root.hidden = false; opened = false; finished = false;
            intro();
        };

        let seen = false;
        try { seen = !preview && localStorage.getItem(root.dataset.ieKey) === '1'; } catch (e) {}
        if (!preview && (seen || reduced)) { root.hidden = true; setPlaying(false); return; }
        if (!preview) document.body.classList.add('ie-lock');
        setPlaying(false);
        if (sound && !preview) sound.hidden = true;
        if (!preview) intro();
    }

    const boot = () => document.querySelectorAll('[data-ie]').forEach(init);
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', boot) : boot();
    return { init, boot };
})();
</script>
@endpush
@endonce

<div id="{{ $uid }}" @class(['ie', 'ie--' . $template, 'is-preview' => $preview]) data-ie data-ie-template="{{ $template }}" data-ie-key="{{ $storageKey ?? $uid }}" data-ie-music='@json($experience['music'] ?? null)' data-ie-sound="{{ $uid }}-sound" style="--ie-primary: {{ $experience['primary'] ?? '#171713' }}; --ie-accent: {{ $experience['accent'] ?? '#b98943' }}" role="dialog" aria-modal="{{ $preview ? 'false' : 'true' }}" aria-label="Ouvrir votre invitation">
    <div class="ie-bg"></div>
    @if($template === 'prestige')<div class="ie-rays" aria-hidden="true"></div>@endif
    @if($template === 'modern')<div class="ie-shapes" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>@endif
    <div class="ie-vignette"></div>
    <canvas class="ie-canvas" aria-hidden="true"></canvas>
    <div class="ie-flash" aria-hidden="true"></div>
    <div class="ie-top"><button type="button" class="ie-skip" data-ie-skip>Passer <i class="bi bi-chevron-right"></i></button></div>

    <div class="ie-stage">
        @if($template === 'modern')
            <div class="ie-book" data-ie-open role="button" tabindex="0" aria-label="Ouvrir l’invitation">
                <div class="ie-card"><x-invitation.card-inner :experience="$experience" :initials="$initials" :guest="$guest" :title="$title" :date="$date" :place="$place" /></div>
                <div class="ie-cover">
                    <div class="ie-cover-face ie-cover-front">
                        <span class="ie-card-logo">@if($logo)<img src="{{ $logo }}" alt="">@else{{ $initials }}@endif</span>
                        <small>{{ $brand }}</small>
                        <em>Vous êtes invité(e)</em>
                        <span class="ie-cta">Ouvrir</span>
                    </div>
                    <div class="ie-cover-face ie-cover-back"></div>
                </div>
            </div>
        @else
            <div class="ie-scene">
                <div class="ie-env" data-ie-open role="button" tabindex="0" aria-label="Ouvrir l’enveloppe">
                    <div class="ie-env-back"></div>
                    <div class="ie-card"><x-invitation.card-inner :experience="$experience" :initials="$initials" :guest="$guest" :title="$title" :date="$date" :place="$place" /></div>
                    <div class="ie-fold ie-fold-l"></div>
                    <div class="ie-fold ie-fold-r"></div>
                    <div class="ie-fold ie-fold-b"></div>
                    <div class="ie-flap"><div class="ie-flap-face ie-flap-out"></div><div class="ie-flap-face ie-flap-in"></div></div>
                    <div class="ie-seal" aria-hidden="true">
                        <span class="ie-seal-ring"></span>
                        <span class="ie-seal-half ie-seal-l">@if($logo)<img src="{{ $logo }}" alt="">@else{{ $initials }}@endif</span>
                        <span class="ie-seal-half ie-seal-r">@if($logo)<img src="{{ $logo }}" alt="">@else{{ $initials }}@endif</span>
                        @for($i = 0; $i < 7; $i++)<span class="ie-frag"></span>@endfor
                    </div>
                </div>
                <div class="ie-shadow"></div>
            </div>
        @endif
        <p class="ie-hint"><i class="bi bi-hand-index"></i>{{ $template === 'modern' ? 'Touchez la carte pour l’ouvrir' : 'Touchez le cachet pour ouvrir' }}</p>
    </div>
    <button type="button" class="ie-continue" data-ie-continue>Découvrir mon invitation<i class="bi bi-arrow-right"></i></button>
</div>
<button type="button" id="{{ $uid }}-sound" @class(['ie-sound', 'is-preview' => $preview]) hidden aria-live="polite">
    <span class="ie-eq" aria-hidden="true"><span></span><span></span><span></span></span><span data-ie-label>Écouter la musique</span>
</button>
