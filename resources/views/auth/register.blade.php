<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Créer un espace client - {{ config('app.name', 'Accès Business') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background:
                linear-gradient(180deg, rgba(255,255,255,.82), rgba(245,243,238,.96)),
                #f5f3ee;
            color: #2c2a25;
            font-family: 'Jost', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .register-shell {
            display: grid;
            grid-template-columns: minmax(0, .92fr) minmax(420px, .72fr);
            min-height: 100vh;
        }

        .register-story {
            background:
                linear-gradient(180deg, rgba(23,23,19,.78), rgba(23,23,19,.9)),
                url('https://images.unsplash.com/photo-1511578314322-379afb476865?auto=format&fit=crop&w=1400&q=80') center/cover;
            color: #fffaf1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(32px, 5vw, 70px);
        }

        .brand-mark {
            align-items: center;
            display: inline-flex;
            gap: 12px;
            font-size: .95rem;
            font-weight: 600;
        }

        .brand-icon {
            align-items: center;
            background: #c59a55;
            border-radius: 12px;
            color: #171713;
            display: inline-flex;
            height: 42px;
            justify-content: center;
            width: 42px;
        }

        .story-copy {
            max-width: 680px;
        }

        .eyebrow {
            color: #d8b978;
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .13em;
            margin-bottom: 14px;
            text-transform: uppercase;
        }

        .story-copy h1 {
            font-size: clamp(2.2rem, 5vw, 4.9rem);
            font-weight: 600;
            letter-spacing: 0;
            line-height: .98;
            margin: 0;
            max-width: 760px;
        }

        .story-copy p {
            color: rgba(255,250,241,.74);
            font-size: 1.04rem;
            line-height: 1.7;
            margin: 22px 0 0;
            max-width: 620px;
        }

        .story-strip {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .story-stat {
            border-top: 1px solid rgba(216,185,120,.42);
            padding-top: 14px;
        }

        .story-stat strong,
        .story-stat span {
            display: block;
        }

        .story-stat strong {
            color: #d8b978;
            font-size: 1rem;
            font-weight: 600;
        }

        .story-stat span {
            color: rgba(255,250,241,.62);
            font-size: .8rem;
            margin-top: 4px;
        }

        .register-panel {
            align-items: center;
            display: flex;
            padding: clamp(24px, 4vw, 58px);
        }

        .form-card {
            background: #fffefa;
            border: 1px solid #dfd7cb;
            border-radius: 22px;
            box-shadow: 0 22px 58px rgba(39,33,25,.08);
            margin: 0 auto;
            max-width: 560px;
            padding: clamp(24px, 4vw, 38px);
            width: 100%;
        }

        .form-kicker {
            color: #b98943;
            font-size: .74rem;
            font-weight: 600;
            letter-spacing: .12em;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .form-card h2 {
            color: #171713;
            font-size: 1.72rem;
            font-weight: 600;
            margin: 0 0 8px;
        }

        .form-card .intro {
            color: #746f65;
            line-height: 1.55;
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            color: #343028;
            font-size: .84rem;
            font-weight: 600;
            margin-bottom: 7px;
        }

        .field-wrap {
            position: relative;
        }

        .form-control {
            background: #fbf8f1;
            border: 1px solid #dfd7cb;
            border-radius: 12px;
            color: #171713;
            min-height: 48px;
            padding: 12px 42px 12px 14px;
        }

        .form-control:focus {
            background: #fffefa;
            border-color: #b98943;
            box-shadow: 0 0 0 4px rgba(185,137,67,.12);
        }

        .input-icon {
            color: #9b8f7c;
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
        }

        .btn-register {
            align-items: center;
            background: #171713;
            border: 0;
            border-radius: 12px;
            color: #fff;
            display: inline-flex;
            font-weight: 600;
            gap: 8px;
            justify-content: center;
            min-height: 48px;
            width: 100%;
        }

        .btn-register:hover {
            background: #2c2a25;
            color: #fff;
        }

        .secondary-link {
            align-items: center;
            border: 1px solid #dfd7cb;
            border-radius: 12px;
            color: #171713;
            display: inline-flex;
            font-weight: 600;
            gap: 8px;
            justify-content: center;
            min-height: 46px;
            text-decoration: none;
            width: 100%;
        }

        .secondary-link:hover {
            background: #f8f4ec;
            color: #171713;
        }

        .divider {
            align-items: center;
            color: #9b8f7c;
            display: flex;
            font-size: .85rem;
            gap: 12px;
            margin: 18px 0;
        }

        .divider::before,
        .divider::after {
            background: #dfd7cb;
            content: '';
            flex: 1;
            height: 1px;
        }

        .password-strength {
            color: #746f65;
            font-size: .82rem;
            margin-top: 7px;
        }

        .alert {
            border-radius: 14px;
        }

        @media (max-width: 992px) {
            .register-shell {
                grid-template-columns: 1fr;
            }

            .register-story {
                gap: 48px;
                min-height: 470px;
            }

            .story-strip {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <main class="register-shell">
        <section class="register-story">
            <div class="brand-mark">
                <span class="brand-icon"><i class="bi bi-shield-check"></i></span>
                Accès Business
            </div>

            <div class="story-copy">
                <div class="eyebrow">{{ isset($clientOrganization) ? 'Activation client' : 'Onboarding SaaS' }}</div>
                <h1>{{ isset($clientOrganization) ? 'Activez votre espace.' : 'Créez votre espace client.' }}</h1>
                <p>
                    @isset($clientOrganization)
                        Votre organisation {{ $clientOrganization->name }} a été préparée. Créez le compte administrateur principal pour ouvrir la console.
                    @else
                        Lancez une organisation, une entreprise ou une agence événementielle avec son propre espace isolé, ses équipes et ses invitations.
                    @endisset
                </p>
            </div>

            <div class="story-strip">
                <div class="story-stat">
                    <strong>Tenant isolé</strong>
                    <span>Données séparées par organisation</span>
                </div>
                <div class="story-stat">
                    <strong>14 jours</strong>
                    <span>Essai inclus au démarrage</span>
                </div>
                <div class="story-stat">
                    <strong>Premium</strong>
                    <span>Invitations, OTP et cartes</span>
                </div>
            </div>
        </section>

        <section class="register-panel">
            <div class="form-card">
                <div class="form-kicker">{{ isset($clientOrganization) ? 'Compte administrateur' : 'Self-service client' }}</div>
                <h2>{{ isset($clientOrganization) ? 'Finaliser l’accès' : 'Créer une organisation' }}</h2>
                <p class="intro">
                    {{ isset($clientOrganization) ? 'Définissez le premier compte administrateur de votre espace.' : 'Renseignez les informations principales. Vous pourrez personnaliser le branding et le plan ensuite.' }}
                </p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    @isset($clientOrganization)
                        <input type="hidden" name="onboarding_token" value="{{ $onboardingToken }}">
                    @endisset

                    <div class="form-group">
                        <label for="organization_name" class="form-label">Organisation</label>
                        <div class="field-wrap">
                            <input type="text"
                                   class="form-control @error('organization_name') is-invalid @enderror"
                                   id="organization_name"
                                   name="organization_name"
                                   value="{{ old('organization_name', $clientOrganization->name ?? '') }}"
                                   {{ isset($clientOrganization) ? 'readonly' : 'required' }}
                                   autofocus
                                   placeholder="Nom de l'organisation">
                            <i class="bi bi-building input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="name" class="form-label">Nom complet</label>
                        <div class="field-wrap">
                            <input type="text"
                                   class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   placeholder="Votre nom">
                            <i class="bi bi-person input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email professionnel</label>
                        <div class="field-wrap">
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   placeholder="nom@entreprise.com">
                            <i class="bi bi-at input-icon"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Mot de passe</label>
                        <div class="field-wrap">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   required
                                   placeholder="Minimum 8 caractères"
                                   minlength="8">
                            <i class="bi bi-lock input-icon"></i>
                        </div>
                        <div class="password-strength">
                            <i class="bi bi-shield-check me-1"></i>
                            Minimum 8 caractères
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation" class="form-label">Confirmation</label>
                        <div class="field-wrap">
                            <input type="password"
                                   class="form-control"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   required
                                   placeholder="Confirmez le mot de passe"
                                   minlength="8">
                            <i class="bi bi-key input-icon"></i>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register">
                        <i class="bi bi-arrow-right-circle"></i>
                        {{ isset($clientOrganization) ? 'Activer mon espace' : 'Créer mon espace client' }}
                    </button>
                </form>

                <div class="divider">Déjà inscrit</div>
                <a href="{{ route('client.login') }}" class="secondary-link">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Connexion client
                </a>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
