@extends('layouts.app')

@section('title', 'Répondre à l\'invitation - ' . $event->titre)
@section('hide_errors', '1')

@php
    $eventVideoEmbed = \App\Support\EventMedia::videoEmbedUrl($event->video_url);
    $image = \App\Support\EventMedia::storageUrl($event->image);
    $start = $event->date_debut?->locale('fr');
    $time = fn ($value) => $value ? \Illuminate\Support\Str::substr($value, 0, 5) : null;
    $firstName = trim((string) ($inviteData['prenom'] ?? ''));
    $selected = old('reponse');
    $choices = [
        'present' => ['icon' => 'bi-check-lg', 'tone' => 'green', 'title' => 'Je serai présent(e)', 'text' => 'Je confirme ma venue.', 'cta' => 'Confirmer ma présence', 'hint' => 'Votre carte d’invitation avec QR code vous sera envoyée par email.'],
        'peut_etre' => ['icon' => 'bi-question-lg', 'tone' => 'blue', 'title' => 'Peut-être', 'text' => 'Je ne suis pas encore sûr(e).', 'cta' => 'Envoyer ma réponse', 'hint' => 'Vous recevrez quand même votre carte d’invitation par email.'],
        'absent' => ['icon' => 'bi-x-lg', 'tone' => 'red', 'title' => 'Je ne pourrai pas venir', 'text' => 'Je décline l’invitation.', 'cta' => 'Envoyer ma réponse', 'hint' => 'L’organisateur sera informé de votre absence.'],
        'represente' => ['icon' => 'bi-person-badge', 'tone' => 'gold', 'title' => 'Me faire représenter', 'text' => 'Une personne viendra à ma place.', 'cta' => 'Désigner mon représentant', 'hint' => 'Votre représentant recevra un lien pour confirmer sa venue.'],
    ];
@endphp

@push('styles')
<style>
    .rsvp { margin: 0 auto; max-width: 640px; padding: 20px 0 120px; }
    .rsvp *, .rsvp *::before, .rsvp *::after { min-width: 0; }

    /* Invitation card */
    .invite { background: #141412; border-radius: 26px; box-shadow: 0 30px 60px -30px rgba(23, 23, 19, .55); color: #fff; overflow: hidden; position: relative; }
    .invite-cover { aspect-ratio: 16 / 9; background: radial-gradient(100% 120% at 0% 0%, rgba(185, 137, 67, .5), transparent 60%), #141412; position: relative; }
    .invite-cover img { height: 100%; inset: 0; object-fit: cover; position: absolute; width: 100%; }
    .invite-cover::after { background: linear-gradient(180deg, rgba(20, 20, 18, .1) 30%, #141412); content: ""; inset: 0; position: absolute; }
    .invite-cover.is-empty { aspect-ratio: auto; height: 36px; }
    .invite-body { margin-top: -64px; padding: 0 22px 22px; position: relative; z-index: 1; }
    .invite-cover.is-empty + .invite-body { margin-top: 0; }
    .invite-kicker { align-items: center; color: #d8b978; display: inline-flex; font-size: .74rem; font-weight: 700; gap: 8px; letter-spacing: .1em; text-transform: uppercase; }
    .invite h1 { color: #fff; font-size: clamp(1.6rem, 6vw, 2.2rem); font-weight: 700; letter-spacing: -.035em; line-height: 1.1; margin: 8px 0 16px; overflow-wrap: anywhere; }
    .invite-facts { display: grid; gap: 10px; }
    .invite-fact { align-items: center; display: flex; font-size: .92rem; gap: 12px; }
    .invite-fact i { align-items: center; background: rgba(255, 255, 255, .08); border-radius: 10px; color: #d8b978; display: flex; flex: 0 0 34px; height: 34px; justify-content: center; }
    .invite-fact small { color: rgba(255, 255, 255, .55); display: block; font-size: .74rem; }
    .invite-links { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .invite-links a { align-items: center; background: rgba(255, 255, 255, .1); border-radius: 999px; color: #fff; display: inline-flex; font-size: .82rem; font-weight: 500; gap: 6px; padding: 8px 13px; text-decoration: none; }
    .invite-links a:hover { background: rgba(255, 255, 255, .16); color: #fff; }
    .invite-about { border-top: 1px solid rgba(255, 255, 255, .1); margin-top: 16px; padding-top: 14px; }
    .invite-about summary { color: rgba(255, 255, 255, .85); cursor: pointer; font-size: .86rem; font-weight: 600; list-style: none; }
    .invite-about summary::-webkit-details-marker { display: none; }
    .invite-about summary i { transition: transform .3s var(--app-ease); }
    .invite-about[open] summary i { transform: rotate(180deg); }
    .invite-about p { color: rgba(255, 255, 255, .7); font-size: .9rem; line-height: 1.65; margin: 10px 0 0; }
    .video { border-radius: 18px; margin-top: 14px; overflow: hidden; }

    /* Steps */
    .step { margin-top: 26px; }
    .step-head { margin: 0 4px 12px; }
    .step-head small { color: var(--app-gold-deep); display: block; font-size: .74rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .step-head h2 { color: var(--app-ink); font-size: 1.3rem; font-weight: 700; letter-spacing: -.025em; margin: 4px 0 0; }
    .step-head p { color: var(--app-muted); font-size: .9rem; margin: 4px 0 0; }
    .verified { align-items: center; background: rgba(46, 123, 101, .1); border-radius: 999px; color: var(--app-green); display: inline-flex; font-size: .78rem; font-weight: 600; gap: 6px; margin-top: 8px; max-width: 100%; padding: 5px 11px; }
    .verified span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    .choices { display: grid; gap: 10px; }
    .choice { cursor: pointer; display: block; margin: 0; position: relative; -webkit-tap-highlight-color: transparent; }
    .choice input { opacity: 0; pointer-events: none; position: absolute; }
    .choice-body { align-items: center; background: var(--app-panel); border: 1.5px solid var(--app-line); border-radius: 18px; box-shadow: var(--app-shadow); display: flex; gap: 14px; min-height: 72px; padding: 14px 16px; transition: border-color .2s, box-shadow .25s, transform .2s var(--app-ease); }
    .choice:active .choice-body { transform: scale(.985); }
    .choice-icon { align-items: center; background: color-mix(in srgb, var(--tone) 12%, transparent); border-radius: 14px; color: var(--tone); display: flex; flex: 0 0 44px; font-size: 1.25rem; height: 44px; justify-content: center; transition: background .25s, color .25s; }
    .choice-text { flex: 1; }
    .choice-text strong { color: var(--app-ink); display: block; font-size: 1rem; font-weight: 650; letter-spacing: -.01em; }
    .choice-text span { color: var(--app-muted); display: block; font-size: .84rem; margin-top: 2px; }
    .choice-radio { border: 2px solid rgba(23, 23, 19, .2); border-radius: 50%; flex: 0 0 22px; height: 22px; position: relative; transition: border-color .2s; }
    .choice-radio::after { background: var(--tone); border-radius: 50%; content: ""; inset: 3px; position: absolute; transform: scale(0); transition: transform .3s var(--app-ease); }
    .choice input:checked + .choice-body { border-color: var(--tone); box-shadow: 0 0 0 4px color-mix(in srgb, var(--tone) 14%, transparent), 0 14px 30px -18px rgba(23, 23, 19, .35); }
    .choice input:checked + .choice-body .choice-icon { background: var(--tone); color: #fff; }
    .choice input:checked + .choice-body .choice-radio { border-color: var(--tone); }
    .choice input:checked + .choice-body .choice-radio::after { transform: scale(1); }
    .choice input:focus-visible + .choice-body { box-shadow: 0 0 0 4px var(--app-gold-soft); }
    .tone-green { --tone: var(--app-green); }
    .tone-blue { --tone: var(--app-blue); }
    .tone-red { --tone: var(--app-red); }
    .tone-gold { --tone: var(--app-gold); }

    .reveal-step { display: none; }
    .reveal-step.is-visible { animation: stepIn .5s var(--app-ease); display: block; }
    @keyframes stepIn { from { opacity: 0; transform: translateY(12px); } }

    .panel { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 20px; box-shadow: var(--app-shadow); padding: 18px; }
    .panel + .panel { margin-top: 12px; }
    .panel-title { align-items: center; color: var(--app-ink); display: flex; font-size: .95rem; font-weight: 650; gap: 8px; margin-bottom: 14px; }
    .panel-title i { color: var(--app-gold); }
    .panel-note { color: var(--app-muted); font-size: .82rem; line-height: 1.5; margin: -6px 0 14px; }
    .rep-panel { background: #fbf6ec; border-color: rgba(185, 137, 67, .25); }
    .field { margin-bottom: 14px; }
    .field:last-child { margin-bottom: 0; }
    .field label { color: var(--app-ink); display: block; font-size: .82rem; font-weight: 600; margin-bottom: 6px; }
    .field label .opt { color: var(--app-muted); font-weight: 400; }
    .rsvp .form-control { background: #fbfaf7; border: 1px solid rgba(23, 23, 19, .12); border-radius: 14px; color: var(--app-ink); font-size: 16px; min-height: 50px; padding: 12px 14px; }
    .rsvp .form-control:focus { background: #fff; }
    .rsvp .rep-panel .form-control { background: #fff; }
    .fields-2 { display: grid; gap: 0 12px; grid-template-columns: 1fr 1fr; }
    .more-toggle { align-items: center; background: none; border: 0; color: var(--app-gold-deep); display: inline-flex; font-size: .86rem; font-weight: 600; gap: 6px; padding: 4px 0; }
    .more-toggle i { transition: transform .3s var(--app-ease); }
    .more-toggle[aria-expanded="true"] i { transform: rotate(180deg); }

    /* Sticky submit */
    .submit-bar { backdrop-filter: saturate(180%) blur(18px); -webkit-backdrop-filter: saturate(180%) blur(18px); background: color-mix(in srgb, var(--app-bg) 86%, transparent); border-top: 1px solid var(--app-line); bottom: 0; left: 0; padding: 12px 16px calc(12px + env(safe-area-inset-bottom)); position: fixed; right: 0; transform: translateY(110%); transition: transform .5s var(--app-ease); z-index: 1030; }
    .submit-bar.is-visible { transform: none; }
    .submit-inner { margin: 0 auto; max-width: 640px; }
    .submit-hint { align-items: center; color: var(--app-muted); display: flex; font-size: .78rem; gap: 6px; justify-content: center; margin-bottom: 8px; text-align: center; }
    .submit-btn { align-items: center; background: var(--app-ink); border: 0; border-radius: 16px; box-shadow: 0 14px 28px -14px rgba(23, 23, 19, .7); color: #fff; display: flex; font-size: 1rem; font-weight: 650; gap: 8px; height: 54px; justify-content: center; letter-spacing: -.01em; transition: transform .2s var(--app-ease), opacity .2s; width: 100%; }
    .submit-btn:active { transform: scale(.98); }
    .submit-btn:disabled { opacity: .6; }
    .submit-btn .spinner-border { height: 1.1rem; width: 1.1rem; }

    @media (min-width: 640px) {
        .rsvp { padding-top: 32px; }
        .choices { grid-template-columns: 1fr 1fr; }
        .choice-body { align-items: flex-start; flex-direction: column; gap: 12px; min-height: 150px; padding: 18px; }
        .choice-radio { flex: none; height: 22px; position: absolute; right: 16px; top: 16px; width: 22px; }
        .choice-icon { flex: none; height: 44px; width: 44px; }
        .invite-body { padding: 0 28px 26px; }
    }
    @media (max-width: 380px) { .fields-2 { grid-template-columns: 1fr; } }
    @media (prefers-reduced-motion: reduce) { .reveal-step.is-visible { animation: none; } .submit-bar { transition: none; } }
</style>
@endpush

@section('content')
@if($experience ?? null)
    <x-invitation.envelope
        :experience="$experience"
        :title="$event->titre"
        :date="$start ? \Illuminate\Support\Str::ucfirst($start->translatedFormat('l d F Y')) . ($time($event->heure_debut) ? ' · ' . $time($event->heure_debut) : '') : null"
        :place="$event->lieu ?: $event->ville"
        :guest="trim(($inviteData['prenom'] ?? '') . ' ' . ($inviteData['nom'] ?? '')) ?: null"
        :storage-key="'ie-opened-' . $event->id . '-' . md5($verifiedEmail)" />
@endif
<div class="rsvp">
    <article class="invite">
        <div @class(['invite-cover', 'is-empty' => !$image])>
            @if($image)<img src="{{ $image }}" alt="">@endif
        </div>
        <div class="invite-body">
            <span class="invite-kicker"><i class="bi bi-envelope-paper"></i>Vous êtes invité(e)</span>
            <h1>{{ $event->titre }}</h1>
            <div class="invite-facts">
                @if($start)
                    <div class="invite-fact"><i class="bi bi-calendar3"></i><div>{{ \Illuminate\Support\Str::ucfirst($start->translatedFormat('l d F Y')) }}@if($event->date_fin && !$event->date_fin->isSameDay($event->date_debut))<small>jusqu’au {{ $event->date_fin->locale('fr')->translatedFormat('l d F Y') }}</small>@endif</div></div>
                @endif
                @if($time($event->heure_debut))
                    <div class="invite-fact"><i class="bi bi-clock"></i><div>{{ $time($event->heure_debut) }}{{ $time($event->heure_fin) ? ' – ' . $time($event->heure_fin) : '' }}</div></div>
                @endif
                @if($event->lieu || $event->ville)
                    <div class="invite-fact"><i class="bi bi-geo-alt"></i><div>{{ $event->lieu ?: $event->ville }}@if($event->lieu && $event->ville)<small>{{ $event->ville }}{{ $event->pays ? ', ' . $event->pays : '' }}</small>@endif</div></div>
                @endif
            </div>
            @if($event->lien_google_map || $event->programme_pdf || ($event->video_url && !$eventVideoEmbed))
                <div class="invite-links">
                    @if($event->lien_google_map)<a href="{{ $event->lien_google_map }}" target="_blank" rel="noopener"><i class="bi bi-map"></i>Itinéraire</a>@endif
                    @if($event->programme_pdf)<a href="{{ \App\Support\EventMedia::storageUrl($event->programme_pdf) }}" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i>Programme</a>@endif
                    @if($event->video_url && !$eventVideoEmbed)<a href="{{ $event->video_url }}" target="_blank" rel="noopener"><i class="bi bi-play-circle"></i>Vidéo</a>@endif
                </div>
            @endif
            @if($event->description)
                <details class="invite-about">
                    <summary>À propos de l’événement <i class="bi bi-chevron-down ms-1"></i></summary>
                    <p>{!! nl2br(e($event->description)) !!}</p>
                </details>
            @endif
        </div>
    </article>

    @if($eventVideoEmbed)
        <div class="video ratio ratio-16x9"><iframe src="{{ $eventVideoEmbed }}" title="Vidéo de l'événement" allowfullscreen loading="lazy"></iframe></div>
    @endif

    <form id="response-form" method="POST" action="{{ route('events.submit-response', $event) }}" novalidate>
        @csrf

        <section class="step">
            <div class="step-head">
                <small>Étape 1 sur 2</small>
                <h2>{{ $firstName ? 'Bonjour ' . $firstName . ', serez-vous des nôtres ?' : 'Serez-vous des nôtres ?' }}</h2>
                <span class="verified"><i class="bi bi-patch-check-fill"></i><span>{{ $verifiedEmail }}</span></span>
            </div>
            <div class="choices" role="radiogroup" aria-label="Votre réponse">
                @foreach($choices as $value => $choice)
                    <label class="choice tone-{{ $choice['tone'] }}">
                        <input type="radio" name="reponse" value="{{ $value }}" data-cta="{{ $choice['cta'] }}" data-hint="{{ $choice['hint'] }}" @checked($selected === $value) required>
                        <span class="choice-body">
                            <span class="choice-icon"><i class="bi {{ $choice['icon'] }}"></i></span>
                            <span class="choice-text"><strong>{{ $choice['title'] }}</strong><span>{{ $choice['text'] }}</span></span>
                            <span class="choice-radio" aria-hidden="true"></span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('reponse')<div class="text-danger small mt-2 ms-1">{{ $message }}</div>@enderror
        </section>

        <section @class(['step', 'reveal-step', 'is-visible' => $selected]) id="info-form">
            <div class="step-head">
                <small>Étape 2 sur 2</small>
                <h2>Vérifiez vos informations</h2>
                <p>Elles apparaîtront sur votre carte d’invitation.</p>
            </div>

            <div class="panel">
                <div class="fields-2">
                    <div class="field">
                        <label for="prenom">Prénom</label>
                        <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom', $inviteData['prenom'] ?? '') }}" autocomplete="given-name" required>
                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $inviteData['nom'] ?? '') }}" autocomplete="family-name" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="field">
                    <label for="telephone">Téléphone <span class="opt">(facultatif)</span></label>
                    <input type="tel" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $inviteData['telephone'] ?? '') }}" autocomplete="tel" inputmode="tel">
                    @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                @php $proOpen = old('entreprise', $inviteData['entreprise'] ?? '') || old('fonction', $inviteData['fonction'] ?? '') || $errors->hasAny(['entreprise', 'fonction']); @endphp
                <button type="button" class="more-toggle" data-bs-toggle="collapse" data-bs-target="#proFields" aria-expanded="{{ $proOpen ? 'true' : 'false' }}" aria-controls="proFields">
                    Informations professionnelles <i class="bi bi-chevron-down"></i>
                </button>
                <div @class(['collapse', 'show' => $proOpen]) id="proFields">
                    <div class="fields-2 pt-3">
                        <div class="field">
                            <label for="entreprise">Entreprise</label>
                            <input type="text" class="form-control @error('entreprise') is-invalid @enderror" id="entreprise" name="entreprise" value="{{ old('entreprise', $inviteData['entreprise'] ?? '') }}" autocomplete="organization">
                            @error('entreprise')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="field">
                            <label for="fonction">Fonction</label>
                            <input type="text" class="form-control @error('fonction') is-invalid @enderror" id="fonction" name="fonction" value="{{ old('fonction', $inviteData['fonction'] ?? '') }}" autocomplete="organization-title">
                            @error('fonction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div @class(['panel', 'rep-panel', 'reveal-step', 'is-visible' => $selected === 'represente']) id="representative-form">
                <div class="panel-title"><i class="bi bi-person-badge"></i>Votre représentant</div>
                <p class="panel-note">Il recevra un lien pour confirmer sa venue, puis sa propre carte. L’organisateur verra qui vous représente.</p>
                <div class="fields-2">
                    <div class="field">
                        <label for="representant_prenoms">Prénoms</label>
                        <input type="text" class="form-control representative-required @error('representant_prenoms') is-invalid @enderror" id="representant_prenoms" name="representant_prenoms" value="{{ old('representant_prenoms') }}" autocomplete="off">
                        @error('representant_prenoms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="field">
                        <label for="representant_nom">Nom</label>
                        <input type="text" class="form-control representative-required @error('representant_nom') is-invalid @enderror" id="representant_nom" name="representant_nom" value="{{ old('representant_nom') }}" autocomplete="off">
                        @error('representant_nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="field">
                    <label for="representant_fonction">Fonction</label>
                    <input type="text" class="form-control representative-required @error('representant_fonction') is-invalid @enderror" id="representant_fonction" name="representant_fonction" value="{{ old('representant_fonction') }}" autocomplete="off">
                    @error('representant_fonction')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="representant_email">Email</label>
                    <input type="email" class="form-control representative-required @error('representant_email') is-invalid @enderror" id="representant_email" name="representant_email" value="{{ old('representant_email') }}" autocomplete="off" inputmode="email" autocapitalize="off">
                    @error('representant_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="field">
                    <label for="representant_contact">Téléphone</label>
                    <input type="tel" class="form-control representative-required @error('representant_contact') is-invalid @enderror" id="representant_contact" name="representant_contact" value="{{ old('representant_contact') }}" autocomplete="off" inputmode="tel">
                    @error('representant_contact')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </section>

        <div @class(['submit-bar', 'is-visible' => $selected]) id="submitBar">
            <div class="submit-inner">
                <div class="submit-hint"><i class="bi bi-info-circle"></i><span id="submitHint">{{ $selected ? $choices[$selected]['hint'] ?? '' : '' }}</span></div>
                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="bi bi-check2-circle"></i><span id="submitLabel">{{ $selected ? $choices[$selected]['cta'] ?? 'Envoyer ma réponse' : 'Envoyer ma réponse' }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    (() => {
        const form = document.getElementById('response-form');
        const choices = form.querySelectorAll('input[name="reponse"]');
        const infoForm = document.getElementById('info-form');
        const representativeForm = document.getElementById('representative-form');
        const representativeFields = form.querySelectorAll('.representative-required');
        const submitBar = document.getElementById('submitBar');
        const submitBtn = document.getElementById('submitBtn');
        const submitLabel = document.getElementById('submitLabel');
        const submitHint = document.getElementById('submitHint');
        let firstChoice = !form.querySelector('input[name="reponse"]:checked');

        function sync(input) {
            const isRepresented = input?.value === 'represente';
            representativeForm.classList.toggle('is-visible', isRepresented);
            representativeFields.forEach(field => field.required = isRepresented);
            if (!input) return;
            infoForm.classList.add('is-visible');
            submitBar.classList.add('is-visible');
            submitLabel.textContent = input.dataset.cta;
            submitHint.textContent = input.dataset.hint;
        }

        choices.forEach(input => input.addEventListener('change', () => {
            sync(input);
            // Bring the next step into view the first time a choice is made.
            if (firstChoice || input.value === 'represente') {
                firstChoice = false;
                setTimeout(() => (input.value === 'represente' ? representativeForm : infoForm)
                    .scrollIntoView({ behavior: 'smooth', block: 'start' }), 120);
            }
        }));

        form.addEventListener('submit', (event) => {
            const invalid = [...form.querySelectorAll('input[required]')].find(field => field.type !== 'radio' && !field.value.trim()
                || (field.type === 'email' && field.value && !field.checkValidity()));
            if (!form.querySelector('input[name="reponse"]:checked')) {
                event.preventDefault();
                form.querySelector('.choices').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (invalid) {
                event.preventDefault();
                invalid.classList.add('is-invalid');
                invalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => invalid.focus({ preventScroll: true }), 300);
                return;
            }
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border" role="status" aria-hidden="true"></span><span>Envoi en cours…</span>';
        });

        form.querySelectorAll('.form-control').forEach(field => field.addEventListener('input', () => field.classList.remove('is-invalid')));

        sync(form.querySelector('input[name="reponse"]:checked'));

        // After a server-side validation error, show the first invalid field.
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.closest('.collapse')?.classList.add('show');
            setTimeout(() => firstInvalid.scrollIntoView({ block: 'center' }), 200);
        }
    })();
</script>
@endpush
@endsection
