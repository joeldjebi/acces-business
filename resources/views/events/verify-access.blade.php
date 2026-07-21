@extends('layouts.app')

@section('title', 'Accès invité - ' . $event->titre)

@push('styles')
<style>
    .guest-page {
        --ink: #171713;
        --muted: #746f65;
        --line: #ded6c8;
        --panel: #fffefa;
        --gold: #b98943;
        max-width: 980px;
        margin: 0 auto;
        min-height: calc(100vh - 150px);
        display: grid;
        place-items: center;
    }

    .guest-card {
        width: 100%;
        display: grid;
        grid-template-columns: minmax(0, .9fr) minmax(360px, 1fr);
        background: var(--panel);
        border: 1px solid rgba(222, 214, 200, .8);
        border-radius: 28px;
        box-shadow: 0 30px 80px rgba(39, 33, 25, .09);
        overflow: hidden;
    }

    .guest-aside {
        background: #171713;
        color: #fff;
        padding: 42px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        min-height: 520px;
    }

    .guest-image {
        aspect-ratio: 16 / 10;
        border-radius: 20px;
        margin-top: 24px;
        object-fit: cover;
        width: 100%;
    }

    .guest-video {
        border: 1px solid var(--line);
        border-radius: 18px;
        margin-bottom: 22px;
        overflow: hidden;
    }

    .guest-video iframe {
        aspect-ratio: 16 / 9;
        border: 0;
        width: 100%;
    }

    .guest-kicker {
        color: #d8b476;
        font-size: .76rem;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .guest-title {
        font-size: clamp(2rem, 4vw, 3.2rem);
        font-weight: 500;
        line-height: 1.05;
        margin: 16px 0;
    }

    .guest-meta {
        color: rgba(255, 255, 255, .72);
        display: grid;
        gap: 10px;
        font-size: .95rem;
    }

    .guest-body {
        padding: 42px;
    }

    .step {
        display: none;
    }

    .step.active {
        display: block;
    }

    .section-title {
        color: var(--ink);
        font-size: 1.55rem;
        font-weight: 500;
        margin-bottom: 10px;
    }

    .section-copy {
        color: var(--muted);
        line-height: 1.6;
        margin-bottom: 26px;
    }

    .form-control {
        border: 1px solid var(--line);
        border-radius: 16px;
        min-height: 50px;
        padding: 12px 15px;
    }

    .form-control:focus {
        border-color: rgba(185, 137, 67, .75);
        box-shadow: 0 0 0 .22rem rgba(185, 137, 67, .12);
    }

    .otp-input {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 10px;
        margin: 28px 0 18px;
    }

    .otp-input input {
        width: 100%;
        height: 58px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: #fff;
        color: var(--ink);
        text-align: center;
        font-size: 1.35rem;
        font-weight: 600;
    }

    .otp-input input:focus {
        border-color: var(--gold);
        outline: none;
        box-shadow: 0 0 0 .22rem rgba(185, 137, 67, .12);
    }

    .countdown {
        color: var(--gold);
        font-weight: 600;
        text-align: center;
        margin-bottom: 22px;
    }

    .guest-btn {
        align-items: center;
        background: var(--ink);
        border: 1px solid var(--ink);
        border-radius: 999px;
        color: #fff;
        display: inline-flex;
        gap: 9px;
        justify-content: center;
        min-height: 48px;
        padding: 0 20px;
        width: 100%;
    }

    .guest-btn:hover {
        background: #2c2a25;
        color: #fff;
    }

    .guest-btn.secondary {
        background: #f5f0e8;
        border-color: var(--line);
        color: var(--ink);
    }

    .alert {
        border-radius: 16px;
        margin-top: 18px;
    }

    @media (max-width: 860px) {
        .guest-card {
            grid-template-columns: 1fr;
        }

        .guest-aside {
            min-height: auto;
        }

        .guest-body,
        .guest-aside {
            padding: 28px;
        }
    }
</style>
@endpush

@section('content')
<div class="guest-page">
    @php($eventVideoEmbed = \App\Support\EventMedia::videoEmbedUrl($event->video_url))
    <div class="guest-card">
        <aside class="guest-aside">
            <div>
                <div class="guest-kicker">Invitation privée</div>
                <h1 class="guest-title">{{ $event->titre }}</h1>
                @if($event->image)
                    <img class="guest-image" src="{{ \App\Support\EventMedia::storageUrl($event->image) }}" alt="{{ $event->titre }}">
                @endif
            </div>
            <div class="guest-meta">
                @if($event->date_debut)
                    <span><i class="bi bi-calendar3 me-2"></i>{{ $event->date_debut->format('d/m/Y') }} @if($event->date_fin && $event->date_debut->format('Y-m-d') !== $event->date_fin->format('Y-m-d'))- {{ $event->date_fin->format('d/m/Y') }}@endif</span>
                @endif
                @if($event->heure_debut)
                    <span><i class="bi bi-clock me-2"></i>{{ $event->heure_debut }}{{ $event->heure_fin ? ' - ' . $event->heure_fin : '' }}</span>
                @endif
                @if($event->lieu || $event->ville)
                    <span><i class="bi bi-geo-alt me-2"></i>{{ $event->lieu ?: $event->ville }}</span>
                @endif
            </div>
        </aside>

        <main class="guest-body">
            @if($eventVideoEmbed)
                <div class="guest-video">
                    <iframe src="{{ $eventVideoEmbed }}" title="Vidéo de l'événement" allowfullscreen></iframe>
                </div>
            @elseif($event->video_url)
                <a href="{{ $event->video_url }}" target="_blank" class="guest-btn secondary mb-3">
                    <i class="bi bi-play-circle"></i> Voir la vidéo de l'événement
                </a>
            @endif

            <div class="step active" id="step-email">
                <h2 class="section-title">Vérifiez votre accès</h2>
                <p class="section-copy">Entrez l’adresse email ayant reçu l’invitation. Nous vous enverrons un code sécurisé pour continuer.</p>

                <form id="email-form">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Adresse email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ $accessLink->email_destinataire ?? '' }}" required>
                    </div>
                    <button type="submit" class="guest-btn">
                        <i class="bi bi-send"></i> Recevoir le code
                    </button>
                </form>
            </div>

            <div class="step" id="step-otp">
                <h2 class="section-title">Code de vérification</h2>
                <p class="section-copy">Saisissez le code à 6 chiffres reçu par email. Le code expire automatiquement après 15 minutes.</p>

                <form id="otp-form">
                    @csrf
                    <input type="hidden" id="otp-email" name="email">

                    <div class="otp-input">
                        <input type="text" maxlength="1" inputmode="numeric" id="otp-1" required>
                        <input type="text" maxlength="1" inputmode="numeric" id="otp-2" required>
                        <input type="text" maxlength="1" inputmode="numeric" id="otp-3" required>
                        <input type="text" maxlength="1" inputmode="numeric" id="otp-4" required>
                        <input type="text" maxlength="1" inputmode="numeric" id="otp-5" required>
                        <input type="text" maxlength="1" inputmode="numeric" id="otp-6" required>
                    </div>

                    <div class="countdown" id="countdown">15:00</div>

                    <button type="submit" class="guest-btn mb-3">
                        <i class="bi bi-check2-circle"></i> Valider l’accès
                    </button>
                    <button type="button" class="guest-btn secondary" id="resend-otp" style="display: none;">
                        <i class="bi bi-arrow-clockwise"></i> Renvoyer le code
                    </button>
                </form>
            </div>

            <div id="error-message" class="alert alert-danger" style="display: none;"></div>
        </main>
    </div>
</div>

@push('scripts')
<script>
    let countdownInterval;
    let timeLeft = 900;

    document.getElementById('email-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const errorDiv = document.getElementById('error-message');
        errorDiv.style.display = 'none';

        try {
            const response = await fetch('{{ route("events.request-otp", $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email })
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok || !data.success) {
                errorDiv.textContent = data.message || 'Impossible d’envoyer le code. Vérifiez votre email puis réessayez.';
                errorDiv.style.display = 'block';
                return;
            }

            document.getElementById('step-email').classList.remove('active');
            document.getElementById('step-otp').classList.add('active');
            document.getElementById('otp-email').value = email;
            startCountdown();
            document.getElementById('otp-1').focus();
        } catch (error) {
            errorDiv.textContent = 'Une erreur est survenue. Veuillez réessayer.';
            errorDiv.style.display = 'block';
        }
    });

    const otpInputs = document.querySelectorAll('.otp-input input');
    otpInputs.forEach((input, index) => {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
            if (this.value.length === 1 && index < otpInputs.length - 1) {
                otpInputs[index + 1].focus();
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                otpInputs[index - 1].focus();
            }
        });
    });

    document.getElementById('otp-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const otpCode = Array.from(otpInputs).map(input => input.value).join('');
        const email = document.getElementById('otp-email').value;
        const errorDiv = document.getElementById('error-message');
        errorDiv.style.display = 'none';

        if (otpCode.length !== 6) {
            errorDiv.textContent = 'Veuillez entrer le code complet à 6 chiffres.';
            errorDiv.style.display = 'block';
            return;
        }

        try {
            const response = await fetch('{{ route("events.verify-otp", $event) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, otp_code: otpCode })
            });

            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                window.location.href = data.redirect;
                return;
            }

            errorDiv.textContent = data.message || 'Code invalide ou expiré.';
            errorDiv.style.display = 'block';
            otpInputs.forEach(input => input.value = '');
            otpInputs[0].focus();
        } catch (error) {
            errorDiv.textContent = 'Une erreur est survenue. Veuillez réessayer.';
            errorDiv.style.display = 'block';
        }
    });

    function startCountdown() {
        clearInterval(countdownInterval);
        timeLeft = 900;
        updateCountdown();
        countdownInterval = setInterval(() => {
            timeLeft--;
            updateCountdown();

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                document.getElementById('resend-otp').style.display = 'inline-flex';
            }
        }, 1000);
    }

    function updateCountdown() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        document.getElementById('countdown').textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
    }

    document.getElementById('resend-otp').addEventListener('click', function() {
        document.getElementById('email-form').dispatchEvent(new Event('submit'));
    });
</script>
@endpush
@endsection
