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
        body { background:#fbf8f1; color:var(--text); font-family:Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin:0; }
        a { color:inherit; }
        .site { overflow:hidden; }
        .nav { align-items:center; background:rgba(251,248,241,.86); border-bottom:1px solid rgba(223,215,203,.7); backdrop-filter:blur(18px); display:flex; gap:18px; height:74px; justify-content:space-between; left:0; padding:0 clamp(18px,4vw,58px); position:sticky; right:0; top:0; z-index:20; }
        .brand { align-items:center; display:flex; gap:10px; font-weight:800; text-decoration:none; }
        .brand-mark { align-items:center; background:#171713; border-radius:14px; color:#fff; display:flex; height:40px; justify-content:center; width:40px; }
        .nav-links { align-items:center; display:flex; gap:18px; }
        .nav-links a { color:#4d473f; font-size:.92rem; text-decoration:none; }
        .nav-actions { align-items:center; display:flex; gap:10px; }
        .btn { align-items:center; border:1px solid var(--line); border-radius:999px; display:inline-flex; gap:8px; justify-content:center; min-height:44px; padding:0 17px; text-decoration:none; white-space:nowrap; }
        .btn.primary { background:var(--ink); border-color:var(--ink); color:#fff; }
        .btn.gold { background:var(--gold); border-color:var(--gold); color:#fff; }
        .section { padding:clamp(52px,8vw,96px) clamp(18px,4vw,58px); }
        .inner { margin:0 auto; max-width:1220px; }
        .hero { display:grid; gap:34px; grid-template-columns:minmax(0,1fr) minmax(380px,.88fr); align-items:center; padding-top:54px; }
        .eyebrow { color:#8a6128; font-size:.76rem; font-weight:800; letter-spacing:.16em; text-transform:uppercase; }
        h1 { color:var(--ink); font-size:clamp(2.65rem,6vw,5.6rem); letter-spacing:0; line-height:.95; margin:14px 0 18px; max-width:860px; }
        .lead { color:var(--muted); font-size:clamp(1.04rem,1.6vw,1.22rem); line-height:1.75; max-width:720px; }
        .hero-actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:28px; }
        .trust-row { display:flex; flex-wrap:wrap; gap:12px; margin-top:30px; }
        .trust-item { align-items:center; background:rgba(255,254,250,.78); border:1px solid var(--line); border-radius:999px; display:flex; gap:8px; min-height:38px; padding:0 13px; }
        .mock-shell { background:#171713; border-radius:30px; box-shadow:0 26px 80px rgba(39,33,25,.2); color:#fffaf1; overflow:hidden; padding:16px; }
        .mock-top { align-items:center; display:flex; justify-content:space-between; padding:8px 8px 16px; }
        .dots { display:flex; gap:6px; } .dots span { background:#fffaf1; border-radius:999px; height:8px; opacity:.42; width:8px; }
        .mock-grid { background:#fffefa; border-radius:22px; color:var(--ink); display:grid; gap:12px; grid-template-columns:1fr .74fr; padding:14px; }
        .mock-card { background:#f8f4ec; border:1px solid #e4dacc; border-radius:16px; padding:14px; }
        .metric-line { display:grid; gap:10px; grid-template-columns:repeat(3,1fr); margin-top:12px; }
        .metric-line div { background:#fff; border:1px solid #e4dacc; border-radius:13px; padding:12px; }
        .metric-line span, .mock-label { color:var(--muted); display:block; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; }
        .metric-line strong { display:block; font-size:1.45rem; margin-top:5px; }
        .qr-card { background:#171713; color:#fffaf1; min-height:246px; }
        .qr-grid { display:grid; gap:5px; grid-template-columns:repeat(5,1fr); margin:16px 0; max-width:146px; }
        .qr-grid span { aspect-ratio:1; background:#fffaf1; border-radius:3px; opacity:.95; }
        .qr-grid span:nth-child(3n) { opacity:.28; }
        .flow { display:grid; gap:10px; margin-top:12px; }
        .flow-row { align-items:center; background:#fff; border:1px solid #e4dacc; border-radius:13px; display:flex; gap:10px; padding:11px; }
        .flow-row i { color:var(--green); }
        .section-head { margin:0 auto 30px; max-width:820px; text-align:center; }
        .section-head h2 { color:var(--ink); font-size:clamp(2rem,4vw,3.55rem); line-height:1; margin:10px 0 12px; }
        .section-head p { color:var(--muted); font-size:1.03rem; line-height:1.7; margin:0; }
        .feature-grid { display:grid; gap:14px; grid-template-columns:repeat(4,minmax(0,1fr)); }
        .feature { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 44px rgba(39,33,25,.055); padding:20px; }
        .feature i { align-items:center; background:rgba(185,137,67,.12); border-radius:12px; color:#8a6128; display:flex; height:42px; justify-content:center; margin-bottom:15px; width:42px; }
        .feature h3 { color:var(--ink); font-size:1rem; margin:0 0 8px; }
        .feature p { color:var(--muted); line-height:1.65; margin:0; }
        .split { display:grid; gap:26px; grid-template-columns:1fr 1fr; align-items:center; }
        .panel { background:var(--panel); border:1px solid var(--line); border-radius:22px; box-shadow:0 18px 44px rgba(39,33,25,.055); padding:24px; }
        .panel h2 { color:var(--ink); font-size:clamp(1.8rem,3.2vw,3rem); line-height:1.02; margin:8px 0 14px; }
        .panel p { color:var(--muted); line-height:1.75; }
        .list { display:grid; gap:12px; margin-top:18px; }
        .list div { align-items:flex-start; display:flex; gap:10px; } .list i { color:var(--green); margin-top:3px; }
        .checkin-board { display:grid; gap:12px; }
        .scan-row { align-items:center; border:1px solid var(--line); border-radius:14px; display:flex; justify-content:space-between; padding:13px; }
        .scan-row strong { display:block; } .scan-row span { color:var(--muted); font-size:.86rem; }
        .badge { border-radius:999px; font-size:.78rem; font-weight:800; padding:7px 10px; }
        .badge.ok { background:rgba(46,123,101,.12); color:var(--green); } .badge.wait { background:rgba(185,137,67,.14); color:#8a6128; }
        .pricing { display:grid; gap:16px; grid-template-columns:repeat(3,1fr); }
        .price { background:var(--panel); border:1px solid var(--line); border-radius:20px; padding:24px; } .price.featured { border-color:rgba(185,137,67,.65); box-shadow:0 24px 60px rgba(185,137,67,.12); }
        .price h3 { margin:0 0 8px; } .price strong { color:var(--ink); display:block; font-size:2rem; margin:14px 0; }
        .faq { display:grid; gap:12px; grid-template-columns:1fr 1fr; }
        details { background:var(--panel); border:1px solid var(--line); border-radius:16px; padding:18px; } summary { cursor:pointer; font-weight:800; } details p { color:var(--muted); line-height:1.7; margin:12px 0 0; }
        .cta { background:#171713; border-radius:30px; color:#fffaf1; padding:clamp(30px,6vw,58px); text-align:center; } .cta h2 { color:#fffaf1; font-size:clamp(2rem,4vw,3.6rem); line-height:1; margin:0 0 14px; } .cta p { color:rgba(255,250,241,.72); line-height:1.7; margin:0 auto 24px; max-width:680px; }
        .footer { align-items:center; border-top:1px solid var(--line); color:var(--muted); display:flex; flex-wrap:wrap; gap:14px; justify-content:space-between; padding:26px clamp(18px,4vw,58px); }
        @media(max-width:1060px){ .hero,.split{grid-template-columns:1fr;} .feature-grid{grid-template-columns:repeat(2,1fr);} .pricing{grid-template-columns:1fr;} }
        @media(max-width:720px){ .nav-links{display:none;} .nav{height:auto; padding-bottom:12px; padding-top:12px;} .nav-actions{gap:6px;} .btn{min-height:40px; padding:0 12px;} .hero{padding-top:28px;} .mock-grid,.feature-grid,.faq{grid-template-columns:1fr;} .metric-line{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="site">
    <nav class="nav" aria-label="Navigation principale">
        <a href="{{ route('landing') }}" class="brand"><span class="brand-mark"><i class="bi bi-shield-check"></i></span><span>Accès Business</span></a>
        <div class="nav-links">
            <a href="#features">Fonctionnalités</a><a href="#checkin">Check-in</a><a href="#wallet">Wallet</a><a href="#pricing">Tarifs</a><a href="#faq">FAQ</a>
        </div>
        <div class="nav-actions"><a href="{{ route('client.login') }}" class="btn">Connexion</a><a href="{{ route('register') }}" class="btn primary">Créer un espace</a></div>
    </nav>

    <header class="section hero inner">
        <div>
            <div class="eyebrow">Plateforme invitations & accès</div>
            <h1>Gérez vos invitations événementielles avec précision.</h1>
            <p class="lead">Créez vos événements, envoyez des invitations personnalisées, collectez les réponses, gérez les représentants et validez les entrées par QR code depuis une console premium.</p>
            <div class="hero-actions"><a href="{{ route('register') }}" class="btn primary"><i class="bi bi-arrow-right"></i>Créer mon espace</a><a href="{{ route('client.login') }}" class="btn"><i class="bi bi-box-arrow-in-right"></i>Accéder à mon espace</a></div>
            <div class="trust-row"><div class="trust-item"><i class="bi bi-qr-code"></i>QR unique</div><div class="trust-item"><i class="bi bi-phone"></i>Apple & Google Wallet</div><div class="trust-item"><i class="bi bi-person-check"></i>Représentation tracée</div></div>
        </div>
        <div class="mock-shell" aria-label="Aperçu produit Accès Business">
            <div class="mock-top"><strong>Console événement</strong><div class="dots"><span></span><span></span><span></span></div></div>
            <div class="mock-grid">
                <div class="mock-card"><span class="mock-label">Cocktail Business Club</span><div class="metric-line"><div><span>Confirmés</span><strong>184</strong></div><div><span>Check-in</span><strong>92</strong></div><div><span>Représ.</span><strong>17</strong></div></div><div class="flow"><div class="flow-row"><i class="bi bi-envelope-check"></i>Invitation envoyée</div><div class="flow-row"><i class="bi bi-person-vcard"></i>Carte QR générée</div><div class="flow-row"><i class="bi bi-bell"></i>Relance planifiée</div></div></div>
                <div class="mock-card qr-card"><span class="mock-label" style="color:rgba(255,250,241,.58)">Carte d'accès</span><div class="qr-grid">@for($i=0;$i<25;$i++)<span></span>@endfor</div><strong>VIP-8Q4X</strong><p style="color:rgba(255,250,241,.68);line-height:1.55;margin:8px 0 0;">Présentez le QR code à l'entrée.</p></div>
            </div>
        </div>
    </header>

    <main>
        <section class="section" id="features"><div class="inner"><div class="section-head"><div class="eyebrow">Cycle complet</div><h2>Tout le parcours d’invitation dans un seul outil.</h2><p>Accès Business couvre la création de l’événement, la réponse de l’invité, les cartes, le check-in et l’analyse après événement.</p></div><div class="feature-grid">
            @foreach([
                ['bi-calendar2-event','Création événement','Public, privé ou sur invitation avec capacité, tarification et localisation.'],
                ['bi-envelope-paper','Invitations email','Liens personnalisés, OTP et carte PDF avec QR code.'],
                ['bi-person-arms-up','Représentation','Un invité indisponible peut désigner un représentant avec trace complète.'],
                ['bi-qr-code-scan','Check-in live','Scan QR, anti double entrée, heure d’arrivée et opérateur.'],
                ['bi-wallet2','Wallet mobile','Ajout dans Apple Wallet et Google Wallet depuis le mail.'],
                ['bi-bell','Relances automatiques','Chaque organisation choisit son heure quotidienne de relance.'],
                ['bi-chat-dots','SMS remerciement','Messages post-événement selon les crédits SMS disponibles.'],
                ['bi-graph-up-arrow','Stats & exports','Suivi des réponses, représentants, cartes, scans et présences.'],
            ] as $feature)<article class="feature"><i class="bi {{ $feature[0] }}"></i><h3>{{ $feature[1] }}</h3><p>{{ $feature[2] }}</p></article>@endforeach
        </div></div></section>

        <section class="section"><div class="inner split"><div class="panel"><div class="eyebrow">Représentation</div><h2>Quand un invité ne peut pas venir, l’organisation garde le contrôle.</h2><p>L’invité renseigne son représentant. Le représentant reçoit un lien, confirme sa présence, reçoit sa carte, et l’organisateur voit tout l’historique.</p><div class="list"><div><i class="bi bi-check2-circle"></i><span>Coordonnées du représentant: nom, fonction, contact, email.</span></div><div><i class="bi bi-check2-circle"></i><span>Notification à l’invité représenté après confirmation.</span></div><div><i class="bi bi-check2-circle"></i><span>Détail complet accessible dans le tableau des inscriptions.</span></div></div></div><div class="panel"><div class="scan-row"><div><strong>Aminata K.</strong><span>Invitée représentée par Eric N.</span></div><span class="badge ok">Confirmé</span></div><div class="scan-row"><div><strong>Eric N.</strong><span>Carte représentant envoyée</span></div><span class="badge ok">Wallet prêt</span></div><div class="scan-row"><div><strong>Organisateur</strong><span>Historique complet conservé</span></div><span class="badge wait">Trace</span></div></div></div></section>

        <section class="section" id="checkin"><div class="inner split"><div class="panel checkin-board"><div class="scan-row"><div><strong>Marie Koffi</strong><span>VIP · QR scanné à 18:42</span></div><span class="badge ok">Entrée validée</span></div><div class="scan-row"><div><strong>Jean B.</strong><span>Déjà scanné à 18:18</span></div><span class="badge wait">Double scan</span></div><div class="scan-row"><div><strong>Représentant</strong><span>Confirme pour Cabinet N.</span></div><span class="badge ok">Autorisé</span></div></div><div class="panel"><div class="eyebrow">Check-in live</div><h2>Une entrée fluide, contrôlée et mesurable.</h2><p>Les agents scannent le QR code, voient immédiatement le profil invité et valident l’entrée. Les doubles scans sont détectés et l’organisateur suit les arrivées en temps réel.</p></div></div></section>

        <section class="section" id="wallet"><div class="inner split"><div class="panel"><div class="eyebrow">Wallet</div><h2>Des cartes prêtes pour iPhone et Android.</h2><p>Les invités peuvent ajouter leur carte à Apple Wallet ou Google Wallet depuis le mail. Le QR reste accessible sans chercher une pièce jointe.</p></div><div class="panel"><div class="mock-card qr-card"><span class="mock-label" style="color:rgba(255,250,241,.58)">My Signal Wallet</span><h3 style="font-size:1.8rem;margin:18px 0 8px;">Carte invitation</h3><p style="color:rgba(255,250,241,.72);">QR code, date, lieu, code d’accès et lien de vérification.</p><div class="hero-actions"><span class="btn gold"><i class="bi bi-apple"></i>Apple Wallet</span><span class="btn"><i class="bi bi-google"></i>Google Wallet</span></div></div></div></div></section>

        <section class="section" id="pricing"><div class="inner"><div class="section-head"><div class="eyebrow">Plans</div><h2>Une offre claire pour chaque volume.</h2><p>Démarrez léger, puis augmentez vos quotas d’événements, invités, emails, SMS et utilisateurs selon votre activité.</p></div><div class="pricing"><article class="price"><h3>Starter</h3><p>Pour les petites équipes.</p><strong>Essentiel</strong><div class="list"><div><i class="bi bi-check2"></i>Événements publics et privés</div><div><i class="bi bi-check2"></i>Cartes PDF QR</div><div><i class="bi bi-check2"></i>Branding léger</div></div></article><article class="price featured"><h3>Business</h3><p>Pour agences et événements corporate.</p><strong>Premium</strong><div class="list"><div><i class="bi bi-check2"></i>Représentation complète</div><div><i class="bi bi-check2"></i>Check-in live</div><div><i class="bi bi-check2"></i>Relances et exports</div></div></article><article class="price"><h3>Enterprise</h3><p>Pour institutions et grands comptes.</p><strong>Sur mesure</strong><div class="list"><div><i class="bi bi-check2"></i>Quotas avancés</div><div><i class="bi bi-check2"></i>Wallet & SMS</div><div><i class="bi bi-check2"></i>Support prioritaire</div></div></article></div></div></section>

        <section class="section"><div class="inner"><div class="section-head"><div class="eyebrow">Sécurité</div><h2>Des accès maîtrisés, des traces lisibles.</h2><p>OTP, QR unique, rôles utilisateurs, séparation par organisation et journal d’activité pour chaque invité.</p></div></div></section>

        <section class="section" id="faq"><div class="inner"><div class="section-head"><div class="eyebrow">FAQ</div><h2>Questions fréquentes.</h2></div><div class="faq"><details><summary>Peut-on gérer des événements privés ?</summary><p>Oui. Les invités passent par un lien sécurisé et une vérification OTP avant de répondre.</p></details><details><summary>Un invité peut-il se faire représenter ?</summary><p>Oui. Il désigne un représentant, qui confirme ensuite sa présence via son propre lien.</p></details><details><summary>Le check-in évite-t-il les doubles entrées ?</summary><p>Oui. Un QR déjà scanné affiche immédiatement une alerte avec l’heure du premier check-in.</p></details><details><summary>Les cartes Wallet sont-elles possibles ?</summary><p>Oui, Apple Wallet et Google Wallet sont prévus dans le parcours invitation.</p></details></div></div></section>

        <section class="section"><div class="inner cta"><h2>Prêt à professionnaliser vos invitations ?</h2><p>Centralisez les confirmations, cartes, représentants, relances et check-in dans une expérience premium pour vos invités et vos équipes.</p><div class="hero-actions" style="justify-content:center"><a href="{{ route('register') }}" class="btn gold">Créer mon espace</a><a href="{{ route('client.login') }}" class="btn" style="color:#fffaf1;border-color:rgba(255,250,241,.24)">Connexion</a></div></div></section>
    </main>

    <footer class="footer"><strong>Accès Business</strong><span>Invitations, accès et présence événementielle.</span><span>{{ now()->year }}</span></footer>
</div>
</body>
</html>
