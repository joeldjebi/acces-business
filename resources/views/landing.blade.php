<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès Business - Invitations événementielles premium</title>
    <meta name="description" content="Créez vos événements, envoyez des invitations QR, gérez les réponses, représentants, wallets et check-in live.">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --ink:#171713; --text:#2c2a25; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; --green:#2e7b65; --red:#a4514a; --blue:#315f83; }
        * { box-sizing:border-box; min-width:0; }
        html { scroll-behavior:smooth; }
        body { background:#fbf8f1; color:var(--text); font-family:Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin:0; }
        a { color:inherit; }
        img { display:block; max-width:100%; }
        .site { overflow:hidden; position:relative; }
        .nav { align-items:center; background:rgba(251,248,241,.88); border-bottom:1px solid rgba(223,215,203,.72); backdrop-filter:blur(18px); display:flex; gap:18px; min-height:68px; justify-content:space-between; left:0; padding:10px clamp(18px,4vw,52px); position:sticky; right:0; top:0; z-index:20; }
        .brand { align-items:center; display:flex; gap:10px; font-weight:800; text-decoration:none; }
        .brand-mark { align-items:center; background:#171713; border-radius:13px; color:#fff; display:flex; height:38px; justify-content:center; width:38px; }
        .nav-links { align-items:center; display:flex; gap:16px; }
        .nav-links a { color:#4d473f; font-size:.9rem; text-decoration:none; }
        .nav-actions { align-items:center; display:flex; gap:9px; }
        .btn { align-items:center; border:1px solid var(--line); border-radius:999px; display:inline-flex; gap:8px; justify-content:center; min-height:42px; padding:0 16px; text-decoration:none; white-space:nowrap; transition:transform .2s ease, box-shadow .2s ease, background .2s ease; }
        .btn:hover { transform:translateY(-2px); }
        .btn.primary { background:var(--ink); border-color:var(--ink); color:#fff; box-shadow:0 14px 30px rgba(23,23,19,.16); }
        .btn.gold { background:var(--gold); border-color:var(--gold); color:#fff; }
        .section { padding:clamp(38px,6vw,72px) clamp(18px,4vw,52px); }
        .inner { margin:0 auto; max-width:1220px; }
        .hero { display:grid; gap:28px; grid-template-columns:minmax(0,.92fr) minmax(390px,1fr); align-items:center; padding-top:34px; padding-bottom:42px; }
        .eyebrow { color:#8a6128; font-size:.72rem; font-weight:800; letter-spacing:.15em; text-transform:uppercase; }
        h1 { color:var(--ink); font-size:clamp(2.25rem,5vw,4.55rem); letter-spacing:0; line-height:.97; margin:12px 0 14px; max-width:760px; }
        .lead { color:var(--muted); font-size:clamp(1rem,1.35vw,1.12rem); line-height:1.65; max-width:690px; }
        .hero-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:22px; }
        .trust-row { display:flex; flex-wrap:wrap; gap:9px; margin-top:22px; }
        .trust-item { align-items:center; background:rgba(255,254,250,.8); border:1px solid var(--line); border-radius:999px; display:flex; gap:8px; min-height:36px; padding:0 12px; }
        .hero-visual { border:1px solid rgba(223,215,203,.7); border-radius:28px; box-shadow:0 30px 80px rgba(39,33,25,.18); isolation:isolate; min-height:520px; overflow:hidden; position:relative; }
        .hero-visual img { height:100%; inset:0; object-fit:cover; position:absolute; width:100%; transform:scale(1.04); }
        .hero-visual::after { background:linear-gradient(90deg, rgba(251,248,241,.76), rgba(251,248,241,.08) 42%, rgba(23,23,19,.12)); content:""; inset:0; position:absolute; z-index:1; }
        .floating-card { background:rgba(255,254,250,.88); border:1px solid rgba(223,215,203,.9); border-radius:18px; box-shadow:0 18px 44px rgba(39,33,25,.12); color:var(--ink); position:absolute; z-index:2; }
        .hero-stats { bottom:18px; left:18px; padding:14px; width:min(420px,calc(100% - 36px)); }
        .metric-line { display:grid; gap:10px; grid-template-columns:repeat(3,1fr); }
        .metric-line span, .mock-label { color:var(--muted); display:block; font-size:.7rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .metric-line strong { display:block; font-size:1.38rem; margin-top:4px; }
        .scan-float { right:18px; top:18px; padding:13px 14px; width:190px; }
        .scan-float strong { display:block; } .scan-float span { color:var(--green); font-size:.84rem; font-weight:700; }
        .section-head { margin:0 auto 24px; max-width:760px; text-align:center; }
        .section-head h2 { color:var(--ink); font-size:clamp(1.7rem,3.2vw,2.8rem); line-height:1.03; margin:8px 0 10px; }
        .section-head p { color:var(--muted); font-size:1rem; line-height:1.62; margin:0; }
        .feature-grid { display:grid; gap:12px; grid-template-columns:repeat(4,minmax(0,1fr)); }
        .feature { background:var(--panel); border:1px solid var(--line); border-radius:16px; box-shadow:0 16px 38px rgba(39,33,25,.052); padding:18px; transition:transform .24s ease, box-shadow .24s ease; }
        .feature:hover { box-shadow:0 22px 50px rgba(39,33,25,.095); transform:translateY(-5px); }
        .feature i { align-items:center; background:rgba(185,137,67,.12); border-radius:12px; color:#8a6128; display:flex; height:40px; justify-content:center; margin-bottom:12px; width:40px; }
        .feature h3 { color:var(--ink); font-size:.98rem; margin:0 0 7px; }
        .feature p { color:var(--muted); font-size:.94rem; line-height:1.58; margin:0; }
        .split { display:grid; gap:22px; grid-template-columns:1fr 1fr; align-items:center; }
        .panel { background:var(--panel); border:1px solid var(--line); border-radius:20px; box-shadow:0 16px 38px rgba(39,33,25,.052); padding:22px; }
        .panel h2 { color:var(--ink); font-size:clamp(1.55rem,2.7vw,2.45rem); line-height:1.03; margin:8px 0 12px; }
        .panel p { color:var(--muted); line-height:1.64; }
        .visual-panel { overflow:hidden; padding:0; position:relative; }
        .visual-panel img { aspect-ratio:4/3; height:100%; object-fit:cover; width:100%; }
        .visual-caption { background:rgba(23,23,19,.86); border-radius:16px; bottom:14px; color:#fffaf1; left:14px; padding:13px 14px; position:absolute; right:14px; }
        .visual-caption span { color:rgba(255,250,241,.66); display:block; font-size:.84rem; margin-top:3px; }
        .list { display:grid; gap:10px; margin-top:16px; }
        .list div { align-items:flex-start; display:flex; gap:9px; } .list i { color:var(--green); margin-top:3px; }
        .checkin-board { display:grid; gap:10px; }
        .scan-row { align-items:center; border:1px solid var(--line); border-radius:14px; display:flex; justify-content:space-between; padding:12px; transition:transform .22s ease; }
        .scan-row:hover { transform:translateX(4px); }
        .scan-row strong { display:block; } .scan-row span { color:var(--muted); font-size:.84rem; }
        .badge { border-radius:999px; font-size:.76rem; font-weight:800; padding:7px 10px; }
        .badge.ok { background:rgba(46,123,101,.12); color:var(--green); } .badge.wait { background:rgba(185,137,67,.14); color:#8a6128; }
        .pricing { display:grid; gap:14px; grid-template-columns:repeat(3,1fr); }
        .price { background:var(--panel); border:1px solid var(--line); border-radius:18px; padding:21px; transition:transform .24s ease; } .price:hover { transform:translateY(-4px); } .price.featured { border-color:rgba(185,137,67,.65); box-shadow:0 22px 54px rgba(185,137,67,.12); }
        .price h3 { margin:0 0 7px; } .price strong { color:var(--ink); display:block; font-size:1.8rem; margin:12px 0; }
        .security-band { background:#171713; color:#fffaf1; overflow:hidden; position:relative; }
        .security-band .section-head h2 { color:#fffaf1; } .security-band .section-head p { color:rgba(255,250,241,.72); }
        .security-grid { display:grid; gap:12px; grid-template-columns:repeat(4,1fr); }
        .security-grid div { background:rgba(255,250,241,.07); border:1px solid rgba(255,250,241,.12); border-radius:16px; padding:17px; }
        .security-grid i { color:#d5ad62; display:block; font-size:1.35rem; margin-bottom:8px; }
        .faq { display:grid; gap:10px; grid-template-columns:1fr 1fr; }
        details { background:var(--panel); border:1px solid var(--line); border-radius:15px; padding:16px; } summary { cursor:pointer; font-weight:800; } details p { color:var(--muted); line-height:1.62; margin:10px 0 0; }
        .cta { background:#171713; border-radius:28px; color:#fffaf1; padding:clamp(28px,5vw,50px); text-align:center; } .cta h2 { color:#fffaf1; font-size:clamp(1.85rem,3.4vw,3rem); line-height:1; margin:0 0 12px; } .cta p { color:rgba(255,250,241,.72); line-height:1.62; margin:0 auto 20px; max-width:660px; }
        .footer { align-items:center; border-top:1px solid var(--line); color:var(--muted); display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; padding:22px clamp(18px,4vw,52px); }
        .reveal { opacity:0; transform:translateY(22px); transition:opacity .62s ease, transform .62s ease; }
        .reveal.is-visible { opacity:1; transform:none; }
        .floaty { animation:floaty 5.8s ease-in-out infinite; }
        @keyframes floaty { 0%,100%{ transform:translateY(0);} 50%{ transform:translateY(-10px);} }
        @media(max-width:1060px){ .hero,.split{grid-template-columns:1fr;} .hero-visual{min-height:430px;} .feature-grid,.security-grid{grid-template-columns:repeat(2,1fr);} .pricing{grid-template-columns:1fr;} }
        @media(max-width:720px){ .nav-links{display:none;} .nav{align-items:flex-start; flex-direction:column;} .nav-actions{width:100%;} .nav-actions .btn{flex:1;} .hero{padding-top:24px;} .feature-grid,.faq,.security-grid{grid-template-columns:1fr;} .metric-line{grid-template-columns:1fr;} h1{font-size:2.35rem;} .section{padding-block:34px;} .scan-float{left:14px; right:auto; top:14px;} }
    </style>
</head>
<body>
<div class="site">
    <nav class="nav" aria-label="Navigation principale">
        <a href="{{ route('landing') }}" class="brand"><span class="brand-mark"><i class="bi bi-shield-check"></i></span><span>Accès Business</span></a>
        <div class="nav-links"><a href="#features">Fonctionnalités</a><a href="#checkin">Check-in</a><a href="#wallet">Wallet</a><a href="#pricing">Tarifs</a><a href="#faq">FAQ</a></div>
        <div class="nav-actions"><a href="{{ route('client.login') }}" class="btn">Connexion</a><a href="{{ route('register') }}" class="btn primary">Créer un espace</a></div>
    </nav>

    <header class="section hero inner">
        <div class="reveal">
            <div class="eyebrow">Plateforme invitations & accès</div>
            <h1>Gérez vos invitations événementielles avec précision.</h1>
            <p class="lead">Créez vos événements, envoyez des invitations personnalisées, collectez les réponses, gérez les représentants et validez les entrées par QR code depuis une console premium.</p>
            <div class="hero-actions"><a href="{{ route('register') }}" class="btn primary"><i class="bi bi-arrow-right"></i>Créer mon espace</a><a href="{{ route('client.login') }}" class="btn"><i class="bi bi-box-arrow-in-right"></i>Accéder à mon espace</a></div>
            <div class="trust-row"><div class="trust-item"><i class="bi bi-qr-code"></i>QR unique</div><div class="trust-item"><i class="bi bi-phone"></i>Apple & Google Wallet</div><div class="trust-item"><i class="bi bi-person-check"></i>Représentation tracée</div></div>
        </div>
        <div class="hero-visual reveal" data-parallax="0.12">
            <img src="{{ asset('images/landing/event-checkin-hero.png') }}" alt="Check-in QR à l'entrée d'un événement premium">
            <div class="floating-card scan-float floaty"><strong>QR validé</strong><span><i class="bi bi-check-circle"></i> Entrée autorisée</span></div>
            <div class="floating-card hero-stats"><div class="metric-line"><div><span>Confirmés</span><strong>184</strong></div><div><span>Check-in</span><strong>92</strong></div><div><span>Représ.</span><strong>17</strong></div></div></div>
        </div>
    </header>

    <main>
        <section class="section" id="features"><div class="inner"><div class="section-head reveal"><div class="eyebrow">Cycle complet</div><h2>Tout le parcours d’invitation dans un seul outil.</h2><p>Accès Business couvre la création de l’événement, la réponse de l’invité, les cartes, le check-in et l’analyse après événement.</p></div><div class="feature-grid">
            @foreach([
                ['bi-calendar2-event','Création événement','Public, privé ou sur invitation avec capacité, tarification et localisation.'],
                ['bi-envelope-paper','Invitations email','Liens personnalisés, OTP et carte PDF avec QR code.'],
                ['bi-person-arms-up','Représentation','Un invité indisponible peut désigner un représentant avec trace complète.'],
                ['bi-qr-code-scan','Check-in live','Scan QR, anti double entrée, heure d’arrivée et opérateur.'],
                ['bi-wallet2','Wallet mobile','Ajout dans Apple Wallet et Google Wallet depuis le mail.'],
                ['bi-bell','Relances automatiques','Chaque organisation choisit son heure quotidienne de relance.'],
                ['bi-chat-dots','SMS remerciement','Messages post-événement selon les crédits SMS disponibles.'],
                ['bi-graph-up-arrow','Stats & exports','Suivi des réponses, représentants, cartes, scans et présences.'],
            ] as $feature)<article class="feature reveal"><i class="bi {{ $feature[0] }}"></i><h3>{{ $feature[1] }}</h3><p>{{ $feature[2] }}</p></article>@endforeach
        </div></div></section>

        <section class="section"><div class="inner split"><div class="panel reveal"><div class="eyebrow">Représentation</div><h2>Quand un invité ne peut pas venir, l’organisation garde le contrôle.</h2><p>L’invité renseigne son représentant. Le représentant reçoit un lien, confirme sa présence, reçoit sa carte, et l’organisateur voit tout l’historique.</p><div class="list"><div><i class="bi bi-check2-circle"></i><span>Coordonnées du représentant: nom, fonction, contact, email.</span></div><div><i class="bi bi-check2-circle"></i><span>Notification à l’invité représenté après confirmation.</span></div><div><i class="bi bi-check2-circle"></i><span>Détail complet accessible dans le tableau des inscriptions.</span></div></div></div><div class="panel reveal"><div class="scan-row"><div><strong>Aminata K.</strong><span>Invitée représentée par Eric N.</span></div><span class="badge ok">Confirmé</span></div><div class="scan-row"><div><strong>Eric N.</strong><span>Carte représentant envoyée</span></div><span class="badge ok">Wallet prêt</span></div><div class="scan-row"><div><strong>Organisateur</strong><span>Historique complet conservé</span></div><span class="badge wait">Trace</span></div></div></div></section>

        <section class="section" id="checkin"><div class="inner split"><div class="panel checkin-board reveal"><div class="scan-row"><div><strong>Marie Koffi</strong><span>VIP · QR scanné à 18:42</span></div><span class="badge ok">Entrée validée</span></div><div class="scan-row"><div><strong>Jean B.</strong><span>Déjà scanné à 18:18</span></div><span class="badge wait">Double scan</span></div><div class="scan-row"><div><strong>Représentant</strong><span>Confirme pour Cabinet N.</span></div><span class="badge ok">Autorisé</span></div></div><div class="panel reveal"><div class="eyebrow">Check-in live</div><h2>Une entrée fluide, contrôlée et mesurable.</h2><p>Les agents scannent le QR code, voient immédiatement le profil invité et valident l’entrée. Les doubles scans sont détectés et l’organisateur suit les arrivées en temps réel.</p></div></div></section>

        <section class="section" id="wallet"><div class="inner split"><div class="panel reveal"><div class="eyebrow">Wallet</div><h2>Des cartes prêtes pour iPhone et Android.</h2><p>Les invités peuvent ajouter leur carte à Apple Wallet ou Google Wallet depuis le mail. Le QR reste accessible sans chercher une pièce jointe.</p><div class="hero-actions"><span class="btn gold"><i class="bi bi-apple"></i>Apple Wallet</span><span class="btn"><i class="bi bi-google"></i>Google Wallet</span></div></div><div class="panel visual-panel reveal" data-parallax="0.08"><img src="{{ asset('images/landing/wallet-invitation.png') }}" alt="Carte d'invitation dans un wallet mobile"><div class="visual-caption"><strong>Invitation toujours à portée de main</strong><span>QR, date, lieu et accès mobile.</span></div></div></div></section>

        <section class="section" id="pricing"><div class="inner"><div class="section-head reveal"><div class="eyebrow">Plans</div><h2>Une offre claire pour chaque volume.</h2><p>Démarrez léger, puis augmentez vos quotas d’événements, invités, emails, SMS et utilisateurs selon votre activité.</p></div><div class="pricing"><article class="price reveal"><h3>Starter</h3><p>Pour les petites équipes.</p><strong>Essentiel</strong><div class="list"><div><i class="bi bi-check2"></i>Événements publics et privés</div><div><i class="bi bi-check2"></i>Cartes PDF QR</div><div><i class="bi bi-check2"></i>Branding léger</div></div></article><article class="price featured reveal"><h3>Business</h3><p>Pour agences et événements corporate.</p><strong>Premium</strong><div class="list"><div><i class="bi bi-check2"></i>Représentation complète</div><div><i class="bi bi-check2"></i>Check-in live</div><div><i class="bi bi-check2"></i>Relances et exports</div></div></article><article class="price reveal"><h3>Enterprise</h3><p>Pour institutions et grands comptes.</p><strong>Sur mesure</strong><div class="list"><div><i class="bi bi-check2"></i>Quotas avancés</div><div><i class="bi bi-check2"></i>Wallet & SMS</div><div><i class="bi bi-check2"></i>Support prioritaire</div></div></article></div></div></section>

        <section class="section security-band"><div class="inner"><div class="section-head reveal"><div class="eyebrow">Sécurité</div><h2>Des accès maîtrisés, des traces lisibles.</h2><p>OTP, QR unique, rôles utilisateurs, séparation par organisation et journal d’activité pour chaque invité.</p></div><div class="security-grid"><div class="reveal"><i class="bi bi-key"></i><strong>OTP privé</strong></div><div class="reveal"><i class="bi bi-upc-scan"></i><strong>QR unique</strong></div><div class="reveal"><i class="bi bi-people"></i><strong>Rôles</strong></div><div class="reveal"><i class="bi bi-clock-history"></i><strong>Historique</strong></div></div></div></section>

        <section class="section" id="faq"><div class="inner"><div class="section-head reveal"><div class="eyebrow">FAQ</div><h2>Questions fréquentes.</h2></div><div class="faq"><details class="reveal"><summary>Peut-on gérer des événements privés ?</summary><p>Oui. Les invités passent par un lien sécurisé et une vérification OTP avant de répondre.</p></details><details class="reveal"><summary>Un invité peut-il se faire représenter ?</summary><p>Oui. Il désigne un représentant, qui confirme ensuite sa présence via son propre lien.</p></details><details class="reveal"><summary>Le check-in évite-t-il les doubles entrées ?</summary><p>Oui. Un QR déjà scanné affiche immédiatement une alerte avec l’heure du premier check-in.</p></details><details class="reveal"><summary>Les cartes Wallet sont-elles possibles ?</summary><p>Oui, Apple Wallet et Google Wallet sont prévus dans le parcours invitation.</p></details></div></div></section>

        <section class="section"><div class="inner cta reveal"><h2>Prêt à professionnaliser vos invitations ?</h2><p>Centralisez les confirmations, cartes, représentants, relances et check-in dans une expérience premium pour vos invités et vos équipes.</p><div class="hero-actions" style="justify-content:center"><a href="{{ route('register') }}" class="btn gold">Créer mon espace</a><a href="{{ route('client.login') }}" class="btn" style="color:#fffaf1;border-color:rgba(255,250,241,.24)">Connexion</a></div></div></section>
    </main>

    <footer class="footer"><strong>Accès Business</strong><span>Invitations, accès et présence événementielle.</span><span>{{ now()->year }}</span></footer>
</div>
<script>
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.14 });

    document.querySelectorAll('.reveal').forEach((el) => revealObserver.observe(el));

    const parallaxItems = [...document.querySelectorAll('[data-parallax]')];
    const moveParallax = () => {
        const viewportCenter = window.innerHeight / 2;
        parallaxItems.forEach((item) => {
            const speed = parseFloat(item.dataset.parallax || '0');
            const rect = item.getBoundingClientRect();
            const delta = (rect.top + rect.height / 2 - viewportCenter) * speed;
            item.style.transform = `translate3d(0, ${Math.max(-34, Math.min(34, -delta))}px, 0)`;
        });
    };
    moveParallax();
    window.addEventListener('scroll', () => requestAnimationFrame(moveParallax), { passive: true });
    window.addEventListener('resize', moveParallax);
</script>
</body>
</html>
