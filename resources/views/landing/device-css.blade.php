{{-- Phone mockup + journey screens styles, shared by the landing and the auth pages. --}}
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
