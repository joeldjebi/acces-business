@extends('layouts.app')

@section('title', 'Répondre à l\'invitation - ' . $event->titre)

@push('styles')
<style>
    .rsvp-page {
        --ink: #171713;
        --muted: #746f65;
        --line: #ded6c8;
        --panel: #fffefa;
        --gold: #b98943;
        max-width: 1060px;
        margin: 0 auto;
    }

    .rsvp-hero {
        background: #171713;
        color: #fff;
        border-radius: 28px;
        padding: 38px;
        margin-bottom: 22px;
        box-shadow: 0 24px 70px rgba(39, 33, 25, .12);
    }

    .rsvp-kicker {
        color: #d8b476;
        font-size: .76rem;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .rsvp-title {
        font-size: clamp(2rem, 4vw, 3.4rem);
        font-weight: 500;
        line-height: 1.04;
        margin: 12px 0 18px;
    }

    .rsvp-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 22px;
        color: rgba(255, 255, 255, .72);
    }

    .rsvp-card {
        background: var(--panel);
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 24px;
        box-shadow: 0 20px 55px rgba(39, 33, 25, .07);
        padding: 28px;
    }

    .rsvp-note {
        background: rgba(185, 137, 67, .1);
        border: 1px solid rgba(185, 137, 67, .22);
        border-radius: 18px;
        color: #725322;
        padding: 15px 18px;
        margin-bottom: 24px;
    }

    .response-buttons {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin: 18px 0 28px;
    }

    .response-btn {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 22px;
        cursor: pointer;
        min-height: 166px;
        padding: 24px;
        transition: all .2s ease;
    }

    .response-btn:hover {
        border-color: rgba(185, 137, 67, .55);
        transform: translateY(-2px);
    }

    .response-btn.selected {
        border-color: var(--gold);
        box-shadow: 0 16px 36px rgba(185, 137, 67, .16);
    }

    .response-btn i {
        font-size: 2rem;
        margin-bottom: 16px;
    }

    .response-btn h3 {
        color: var(--ink);
        font-size: 1.05rem;
        font-weight: 600;
        margin: 0 0 8px;
    }

    .response-btn p {
        color: var(--muted);
        line-height: 1.45;
        margin: 0;
    }

    .form-section {
        display: none;
        border-top: 1px solid rgba(222, 214, 200, .72);
        padding-top: 24px;
    }

    .form-section.active {
        display: block;
    }

    .form-control {
        border: 1px solid var(--line);
        border-radius: 16px;
        min-height: 48px;
        padding: 12px 14px;
    }

    .form-control:focus {
        border-color: rgba(185, 137, 67, .75);
        box-shadow: 0 0 0 .22rem rgba(185, 137, 67, .12);
    }

    .submit-btn {
        background: var(--ink);
        border: 1px solid var(--ink);
        border-radius: 999px;
        color: #fff;
        min-height: 50px;
        padding: 0 22px;
    }

    .submit-btn:hover {
        background: #2c2a25;
        color: #fff;
    }

    @media (max-width: 820px) {
        .response-buttons {
            grid-template-columns: 1fr;
        }

        .rsvp-hero,
        .rsvp-card {
            border-radius: 22px;
            padding: 24px;
        }
    }
</style>
@endpush

@section('content')
<div class="rsvp-page">
    <section class="rsvp-hero">
        <div class="rsvp-kicker">Réponse à l’invitation</div>
        <h1 class="rsvp-title">{{ $event->titre }}</h1>
        <div class="rsvp-meta">
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
    </section>

    <section class="rsvp-card">
        <div class="rsvp-note">
            <i class="bi bi-shield-check me-2"></i>
            Après confirmation ou “peut-être”, votre carte d’invitation avec QR code sera envoyée par email.
        </div>

        <form id="response-form" method="POST" action="{{ route('events.submit-response', $event) }}">
            @csrf
            <input type="hidden" name="reponse" id="reponse-input" required>

            <h2 class="h5 mb-3">Votre réponse</h2>
            <div class="response-buttons">
                <div class="response-btn present" data-value="present">
                    <i class="bi bi-check-circle" style="color: #2e7b65;"></i>
                    <h3>Je serai présent(e)</h3>
                    <p>Votre présence est confirmée.</p>
                </div>
                <div class="response-btn peut-etre" data-value="peut_etre">
                    <i class="bi bi-question-circle" style="color: #b98943;"></i>
                    <h3>Peut-être</h3>
                    <p>Vous recevrez quand même votre carte.</p>
                </div>
                <div class="response-btn absent" data-value="absent">
                    <i class="bi bi-x-circle" style="color: #a4514a;"></i>
                    <h3>Je ne pourrai pas venir</h3>
                    <p>Votre absence sera enregistrée.</p>
                </div>
            </div>

            <div class="form-section" id="info-form">
                <h2 class="h5 mb-3">Vos informations</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nom" class="form-label">Nom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="prenom" class="form-label">Prénom <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telephone" class="form-label">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" value="{{ old('telephone') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="entreprise" class="form-label">Entreprise</label>
                        <input type="text" class="form-control" id="entreprise" name="entreprise" value="{{ old('entreprise') }}">
                    </div>
                </div>

                <button type="submit" class="submit-btn mt-4">
                    <i class="bi bi-check2-circle me-2"></i>Enregistrer ma réponse
                </button>
            </div>
        </form>
    </section>
</div>

@push('scripts')
<script>
    const responseButtons = document.querySelectorAll('.response-btn');
    const reponseInput = document.getElementById('reponse-input');
    const infoForm = document.getElementById('info-form');

    responseButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            responseButtons.forEach(b => b.classList.remove('selected'));
            this.classList.add('selected');
            reponseInput.value = this.dataset.value;
            infoForm.classList.add('active');
            infoForm.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
</script>
@endpush
@endsection
