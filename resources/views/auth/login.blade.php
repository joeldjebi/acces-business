<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Accès Business - {{ config('app.name', 'Laravel') }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #181713;
            --ink-soft: #4c473d;
            --paper: #f5f1e9;
            --panel: #fffcf6;
            --line: rgba(24, 23, 19, 0.12);
            --gold: #b98943;
            --gold-deep: #8d642a;
            --danger: #a73b35;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: var(--ink);
            background:
                linear-gradient(120deg, rgba(255, 252, 246, 0.94), rgba(245, 241, 233, 0.9)),
                radial-gradient(circle at 18% 12%, rgba(185, 137, 67, 0.18), transparent 30%),
                #ede6da;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 18px;
        }

        .auth-shell {
            width: min(1060px, 100%);
            min-height: 640px;
            display: grid;
            grid-template-columns: minmax(0, 0.92fr) minmax(360px, 0.68fr);
            border: 1px solid var(--line);
            background: rgba(255, 252, 246, 0.78);
            box-shadow: 0 28px 80px rgba(24, 23, 19, 0.16);
            overflow: hidden;
        }

        .brand-panel {
            position: relative;
            min-height: 640px;
            padding: 52px;
            background:
                linear-gradient(rgba(24, 23, 19, 0.72), rgba(24, 23, 19, 0.88)),
                url('https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=1400&q=80') center/cover;
            color: #fffaf1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            inset: 22px;
            border: 1px solid rgba(255, 250, 241, 0.18);
            pointer-events: none;
        }

        .brand-top,
        .brand-copy,
        .brand-status {
            position: relative;
            z-index: 1;
        }

        .brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(255, 250, 241, 0.76);
        }

        .brand-mark-icon {
            width: 42px;
            height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--gold);
            color: var(--ink);
            font-size: 1.25rem;
        }

        .brand-copy {
            max-width: 560px;
        }

        .eyebrow {
            margin-bottom: 16px;
            color: rgba(255, 250, 241, 0.66);
            font-size: 0.82rem;
            font-weight: 700;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        .brand-copy h1 {
            max-width: 620px;
            margin-bottom: 18px;
            font-size: clamp(2.6rem, 5vw, 4.8rem);
            line-height: 0.96;
            font-weight: 800;
            letter-spacing: 0;
        }

        .brand-copy p {
            max-width: 470px;
            margin: 0;
            color: rgba(255, 250, 241, 0.76);
            font-size: 1.02rem;
            line-height: 1.8;
        }

        .brand-status {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            max-width: 580px;
        }

        .status-item {
            border-top: 1px solid rgba(255, 250, 241, 0.22);
            padding-top: 14px;
        }

        .status-value {
            display: block;
            margin-bottom: 5px;
            color: #fffaf1;
            font-size: 0.95rem;
            font-weight: 700;
        }

        .status-label {
            color: rgba(255, 250, 241, 0.58);
            font-size: 0.78rem;
        }

        .form-panel {
            background: var(--panel);
            padding: 56px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid var(--line);
        }

        .access-badge {
            width: fit-content;
            margin-bottom: 28px;
            padding: 8px 12px;
            border: 1px solid rgba(185, 137, 67, 0.35);
            color: var(--gold-deep);
            background: rgba(185, 137, 67, 0.08);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .auth-header {
            margin-bottom: 34px;
        }

        .auth-header h2 {
            margin-bottom: 10px;
            font-size: clamp(2rem, 3vw, 2.65rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0;
        }

        .auth-header p {
            margin: 0;
            color: var(--ink-soft);
            font-size: 0.98rem;
            line-height: 1.7;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 9px;
            color: var(--ink);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }

        .field-wrap {
            position: relative;
        }

        .form-control {
            min-height: 52px;
            border: 1px solid var(--line);
            border-radius: 0;
            padding: 14px 44px 14px 15px;
            color: var(--ink);
            background: #fffaf2;
            font-size: 0.98rem;
            box-shadow: none;
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
        }

        .form-control::placeholder {
            color: rgba(76, 71, 61, 0.46);
        }

        .form-control:focus {
            border-color: rgba(185, 137, 67, 0.72);
            background: #fffdf9;
            box-shadow: 0 0 0 4px rgba(185, 137, 67, 0.12);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .input-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(76, 71, 61, 0.48);
            pointer-events: none;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 8px 0 24px;
            color: var(--ink-soft);
            font-size: 0.94rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            border-color: rgba(24, 23, 19, 0.24);
            border-radius: 0;
            box-shadow: none;
        }

        .form-check-input:checked {
            background-color: var(--ink);
            border-color: var(--ink);
        }

        .btn-login,
        .btn-register {
            min-height: 54px;
            width: 100%;
            border-radius: 0;
            font-weight: 800;
            letter-spacing: 0.02em;
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }

        .btn-login {
            border: 1px solid var(--ink);
            background: var(--ink);
            color: #fffaf1;
            box-shadow: 0 14px 32px rgba(24, 23, 19, 0.18);
        }

        .btn-login:hover {
            transform: translateY(-1px);
            border-color: #000;
            background: #000;
            color: #fffaf1;
            box-shadow: 0 18px 42px rgba(24, 23, 19, 0.22);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 26px 0;
            color: rgba(76, 71, 61, 0.58);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .divider::before,
        .divider::after {
            content: '';
            height: 1px;
            flex: 1;
            background: var(--line);
        }

        .btn-register {
            border: 1px solid rgba(24, 23, 19, 0.24);
            background: transparent;
            color: var(--ink);
        }

        .btn-register:hover {
            border-color: var(--gold);
            background: rgba(185, 137, 67, 0.1);
            color: var(--ink);
        }

        .alert {
            margin-bottom: 24px;
            border: 1px solid rgba(167, 59, 53, 0.24);
            border-radius: 0;
            background: rgba(167, 59, 53, 0.08);
            color: var(--danger);
            padding: 14px 16px;
            font-weight: 600;
        }

        .invalid-feedback {
            margin-top: 8px;
            color: var(--danger);
            font-size: 0.86rem;
            font-weight: 600;
        }

        @media (max-width: 920px) {
            body {
                align-items: flex-start;
            }

            .auth-shell {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .brand-panel {
                min-height: 430px;
                padding: 40px;
            }

            .form-panel {
                border-left: none;
                border-top: 1px solid var(--line);
                padding: 42px 34px;
            }
        }

        @media (max-width: 560px) {
            body {
                padding: 14px;
            }

            .brand-panel {
                min-height: 360px;
                padding: 28px;
            }

            .brand-panel::after {
                inset: 14px;
            }

            .brand-status {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .status-item {
                padding-top: 10px;
            }

            .form-panel {
                padding: 34px 22px;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell" aria-label="Accès Business">
        <section class="brand-panel">
            <div class="brand-top">
                <span class="brand-mark">
                    <span class="brand-mark-icon"><i class="bi bi-shield-check"></i></span>
                    Accès Business
                </span>
            </div>

            <div class="brand-copy">
                <div class="eyebrow">{{ ($authContext ?? 'client') === 'platform' ? 'SA plateforme' : (isset($clientOrganization) ? 'Espace client' : 'Console client') }}</div>
                <h1>{{ ($authContext ?? 'client') === 'platform' ? 'Pilotage global Accès Business.' : (isset($clientOrganization) ? $clientOrganization->name : 'Votre espace événementiel.') }}</h1>
                <p>
                    @if(($authContext ?? 'client') === 'platform')
                        Connectez-vous à la console de supervision des organisations, plans et accès clients.
                    @elseif(isset($clientOrganization))
                        Connectez-vous à l'espace de votre organisation pour gérer vos événements, invitations et accès.
                    @else
                        Connectez-vous à votre espace client ou créez une organisation pour démarrer.
                    @endif
                </p>
            </div>

            <div class="brand-status" aria-label="Garanties d'accès">
                <div class="status-item">
                    <span class="status-value">Contrôle</span>
                    <span class="status-label">Comptes vérifiés</span>
                </div>
                <div class="status-item">
                    <span class="status-value">Traçabilité</span>
                    <span class="status-label">Actions centralisées</span>
                </div>
                <div class="status-item">
                    <span class="status-value">Confidentiel</span>
                    <span class="status-label">Accès restreint</span>
                </div>
            </div>
        </section>

        <section class="form-panel">
            <div class="access-badge">Session sécurisée</div>

            <div class="auth-header">
                <h2>Connexion</h2>
                <p>{{ ($authContext ?? 'client') === 'platform' ? 'Accès réservé au super admin plateforme.' : 'Accès client sécurisé.' }}</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ ($authContext ?? 'client') === 'platform' ? route('platform.login.store') : route('client.login.store') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="bi bi-envelope"></i>
                        Adresse email
                    </label>
                    <div class="field-wrap">
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               id="email"
                               name="email"
                               value="{{ old('email') }}"
                               required
                               autofocus
                               placeholder="nom@entreprise.com">
                        <i class="bi bi-at input-icon"></i>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="bi bi-lock"></i>
                        Mot de passe
                    </label>
                    <div class="field-wrap">
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               id="password"
                               name="password"
                               required
                               placeholder="Votre mot de passe">
                        <i class="bi bi-key input-icon"></i>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Maintenir ma session active
                    </label>
                </div>

            <button type="submit" class="btn btn-login">
                    <i class="bi bi-arrow-right-circle me-2"></i>
                    {{ ($authContext ?? 'client') === 'platform' ? 'Accéder à la plateforme' : 'Accéder à mon espace client' }}
                </button>
            </form>

            @if(!$hasUsers)
            <div class="divider">
                <span>Initialisation</span>
            </div>
            <a href="{{ route('register') }}" class="btn btn-register">
                <i class="bi bi-person-plus me-2"></i>
                Créer le compte Super Admin
            </a>
            @endif

            @if(($authContext ?? 'client') === 'platform')
                <div class="divider">
                    <span>Client</span>
                </div>
                <a href="{{ route('client.login') }}" class="btn btn-register">
                    <i class="bi bi-building me-2"></i>
                    Connexion client
                </a>
            @else
                <div class="divider">
                    <span>Client</span>
                </div>
                <a href="{{ route('client.register.self') }}" class="btn btn-register">
                    <i class="bi bi-building-add me-2"></i>
                    Créer mon espace client
                </a>
            @endif
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
