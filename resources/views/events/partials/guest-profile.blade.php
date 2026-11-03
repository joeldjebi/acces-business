{{--
    Fiche invité partagée par le détail d'inscription et le détail de présence.
    Variables : $event, $registration, $context ('registration' | 'attendee')
--}}
@php
    $responseLabels = ['present' => 'Présent', 'peut_etre' => 'Peut-être', 'absent' => 'Absent', 'represente' => 'Représenté', 'en_attente' => 'En attente'];
    $responseTones = ['present' => 'success', 'peut_etre' => 'info', 'absent' => 'danger', 'represente' => 'gold', 'en_attente' => ''];
    $status = $registration->statut_reponse;
    $isRepresented = $status === 'represente';
    $representativeConfirmed = $registration->representant_statut === 'confirme';
    $representativeName = trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? ''));
    $guestName = $registration->nom_complet ?: ($registration->email ?: 'Invité');
    $canCheckIn = !$registration->checked_in_at && ($status === 'present' || $status === 'peut_etre' || ($isRepresented && $representativeConfirmed));
    $dateTime = fn ($date) => $date?->locale('fr')->translatedFormat('d M Y · H:i');
    $initials = collect(preg_split('/\s+/', trim($guestName)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
    $isAttendee = $context === 'attendee';
    $listRoute = $isAttendee ? route('events.confirmed-attendees', $event) : route('events.registrations', $event);

    // Parcours de l'invité : [libellé, fait ?, détail]
    $steps = [['Inscription', (bool) $registration->date_inscription, $dateTime($registration->date_inscription)]];
    $steps[] = ['Réponse', $status !== 'en_attente', $status === 'en_attente' ? 'En attente' : ($responseLabels[$status] ?? $status) . ($registration->date_reponse ? ' · ' . $registration->date_reponse->format('d/m') : '')];
    if ($status !== 'absent') {
        if ($isRepresented) {
            $steps[] = ['Représentant confirmé', $representativeConfirmed, $representativeConfirmed ? $dateTime($registration->representant_confirme_le) : 'En attente'];
        }
        $cardSent = $isRepresented ? $registration->representant_carte_envoyee : $registration->carte_envoyee;
        $steps[] = ['Carte envoyée', (bool) $cardSent, $cardSent ? ($isRepresented && $registration->representant_carte_envoyee_le ? $dateTime($registration->representant_carte_envoyee_le) : 'Par email') : 'Pas encore'];
        $steps[] = ['Arrivée', (bool) $registration->checked_in_at, $registration->checked_in_at ? $dateTime($registration->checked_in_at) : 'Pas encore'];
    }
    $currentStep = collect($steps)->search(fn ($step) => !$step[1]);

    // Envois liés à la représentation : [libellé, envoyé ?, date, erreur]
    $deliveries = [
        ['Lien de confirmation', $registration->representant_mail_envoye, $registration->representant_mail_envoye_le, $registration->representant_mail_erreur],
        ['Carte du représentant', $registration->representant_carte_envoyee, $registration->representant_carte_envoyee_le, $registration->representant_carte_erreur],
        ['Notification à l’invité', $registration->represente_notification_envoyee, $registration->represente_notification_envoyee_le, null],
    ];

    // Icône et ton de chaque type d'activité
    $activityStyles = [
        'registration_created' => ['bi-person-plus', ''], 'response_submitted' => ['bi-reply', 'info'],
        'email_sent' => ['bi-envelope', ''], 'reminder_sent' => ['bi-bell', ''], 'reminder_failed' => ['bi-bell-slash', 'danger'],
        'representative_link_sent' => ['bi-link-45deg', ''], 'representative_link_failed' => ['bi-link-45deg', 'danger'],
        'representative_confirmed' => ['bi-person-check', 'success'], 'qr_scanned' => ['bi-qr-code-scan', ''],
        'checked_in' => ['bi-door-open', 'success'], 'duplicate_checkin' => ['bi-exclamation-triangle', 'warning'],
        'checkin_rejected' => ['bi-x-circle', 'danger'], 'thank_you_sms_sent' => ['bi-chat-heart', ''], 'thank_you_sms_failed' => ['bi-chat', 'danger'],
    ];
    $activities = $registration->activities->sortByDesc('created_at');
@endphp

@push('styles')
<style>
    .guest { margin: 0 auto; max-width: 1080px; }
    .guest *, .guest *::before, .guest *::after { min-width: 0; }
    .crumbs-line { align-items: center; color: var(--app-muted); display: flex; flex-wrap: wrap; font-size: .82rem; gap: 6px; margin-bottom: 16px; }
    .crumbs-line a { color: var(--app-muted); text-decoration: none; }
    .crumbs-line a:hover { color: var(--app-ink); }
    .crumbs-line i { font-size: .62rem; opacity: .6; }

    /* Identité */
    .identity { align-items: center; display: grid; gap: 18px 22px; grid-template-columns: auto minmax(0, 1fr) auto; padding: 22px; }
    .identity-avatar { align-items: center; background: #8e8e93; border-radius: 50%; color: #fff; display: flex; font-size: 1.15rem; font-weight: 650; height: 64px; justify-content: center; letter-spacing: .02em; width: 64px; }
    .identity h1 { color: var(--app-ink); font-size: clamp(1.3rem, 2.2vw, 1.65rem); font-weight: 700; letter-spacing: -.025em; line-height: 1.2; margin: 0; overflow-wrap: anywhere; }
    .identity-role { color: var(--app-muted); font-size: .88rem; margin-top: 2px; }
    .identity-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
    .identity-contact { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-top: 12px; }
    .identity-contact a { align-items: center; color: var(--app-text); display: inline-flex; font-size: .85rem; gap: 7px; text-decoration: none; }
    .identity-contact a:hover { color: var(--app-ink); text-decoration: underline; }
    .identity-contact i { color: var(--app-muted); }
    .identity-actions { align-items: center; display: flex; flex-direction: column; gap: 8px; }
    .identity-actions .ui-btn, .identity-actions form { width: 100%; }

    /* Parcours */
    .journey { display: grid; grid-auto-columns: minmax(0, 1fr); grid-auto-flow: column; list-style: none; margin: 0; padding: 20px 22px 22px; }
    .journey li { position: relative; text-align: center; }
    .journey li::before { background: var(--app-line); content: ""; height: 2px; left: calc(-50% + 16px); position: absolute; right: calc(50% + 16px); top: 13px; }
    .journey li:first-child::before { display: none; }
    .journey li.is-done::before { background: #34c759; }
    .step-dot { align-items: center; background: var(--app-panel); border: 2px solid var(--app-line); border-radius: 50%; color: var(--app-muted); display: inline-flex; font-size: .78rem; font-weight: 700; height: 28px; justify-content: center; position: relative; width: 28px; }
    .is-done .step-dot { background: #34c759; border-color: #34c759; color: #fff; }
    .is-current .step-dot { border-color: var(--app-gold); color: var(--app-gold); }
    .step-label { color: var(--app-ink); display: block; font-size: .84rem; font-weight: 600; margin-top: 8px; }
    .step-detail { color: var(--app-muted); display: block; font-size: .76rem; margin-top: 1px; }
    li:not(.is-done):not(.is-current) .step-label { color: var(--app-muted); font-weight: 500; }

    .guest-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr); margin: 20px 0; }
    .stack { display: grid; gap: 20px; }
    .facts { padding-bottom: 6px; }
    .fact { align-items: baseline; border-top: 1px solid var(--app-line); display: grid; gap: 14px; grid-template-columns: 140px minmax(0, 1fr); padding: 11px 22px; }
    .fact:first-child { border-top: 0; }
    .fact-label { color: var(--app-muted); font-size: .82rem; }
    .fact-value { color: var(--app-ink); font-size: .88rem; overflow-wrap: anywhere; }
    .fact-value small { color: var(--app-muted); display: block; font-size: .78rem; }
    .fact-value .error { color: var(--app-red); }
    .fact-value a { color: var(--app-ink); }
    .entry-note { align-items: flex-start; background: var(--app-soft); border-radius: 12px; color: var(--app-text); display: flex; font-size: .86rem; gap: 10px; line-height: 1.5; margin: 0 22px 18px; padding: 12px 14px; }
    .entry-note i { color: var(--app-ink); font-size: 1rem; margin-top: 1px; }

    /* Historique */
    .timeline { list-style: none; margin: 0; padding: 4px 22px 20px; }
    .timeline li { display: grid; gap: 12px; grid-template-columns: 30px minmax(0, 1fr); padding-bottom: 16px; position: relative; }
    .timeline li::after { background: var(--app-line); bottom: 0; content: ""; left: 14.5px; position: absolute; top: 32px; width: 1px; }
    .timeline li:last-child { padding-bottom: 0; }
    .timeline li:last-child::after { display: none; }
    .tl-icon { align-items: center; background: var(--app-soft); border-radius: 50%; color: var(--app-ink); display: flex; font-size: .82rem; height: 30px; justify-content: center; width: 30px; }
    .tl-icon.success { background: rgba(52, 199, 89, .14); color: var(--app-green); }
    .tl-icon.danger { background: rgba(255, 59, 48, .12); color: var(--app-red); }
    .tl-icon.warning { background: rgba(255, 149, 0, .14); color: var(--app-orange); }
    .tl-icon.info { background: rgba(0, 122, 255, .12); color: var(--app-blue); }
    .tl-head { align-items: baseline; display: flex; flex-wrap: wrap; gap: 2px 10px; justify-content: space-between; padding-top: 5px; }
    .tl-title { color: var(--app-ink); font-size: .88rem; font-weight: 600; }
    .tl-time { color: var(--app-muted); font-size: .76rem; white-space: nowrap; }
    .tl-by { color: var(--app-muted); font-size: .78rem; }
    .tl-desc { color: var(--app-text); font-size: .85rem; margin-top: 3px; }

    @media (max-width: 991.98px) {
        .guest-grid { grid-template-columns: 1fr; }
        .identity { grid-template-columns: auto minmax(0, 1fr); }
        .identity-actions { flex-direction: row; flex-wrap: wrap; grid-column: 1 / -1; }
        .identity-actions .ui-btn, .identity-actions form { width: auto; }
    }
    @media (max-width: 575.98px) {
        .identity-avatar { font-size: 1rem; height: 52px; width: 52px; }
        .journey { grid-auto-flow: row; gap: 14px; }
        .journey li { align-items: center; display: grid; gap: 0 12px; grid-template-columns: 28px minmax(0, 1fr); text-align: left; }
        .journey li::before { display: none; }
        .step-label { margin-top: 0; }
        .step-detail { grid-column: 2; }
        .fact { gap: 2px; grid-template-columns: 1fr; }
    }
</style>
@endpush

<div class="guest">
    <nav class="crumbs-line" aria-label="Fil d’Ariane">
        <a href="{{ route('events.show', $event) }}">{{ \Illuminate\Support\Str::limit($event->titre, 40) }}</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ $listRoute }}">{{ $isAttendee ? 'Présences confirmées' : 'Inscriptions' }}</a>
        <i class="bi bi-chevron-right"></i>
        <span>{{ $guestName }}</span>
    </nav>

    <section class="ui-card identity">
        <span class="identity-avatar" aria-hidden="true">{{ $initials ?: '?' }}</span>
        <div>
            <h1>{{ $guestName }}</h1>
            @if($registration->fonction || $registration->entreprise)
                <div class="identity-role">{{ collect([$registration->fonction, $registration->entreprise])->filter()->implode(' · ') }}</div>
            @endif
            <div class="identity-tags">
                <span class="ui-pill dot {{ $responseTones[$status] ?? '' }}">{{ $responseLabels[$status] ?? ucfirst((string) $status) }}</span>
                @if($isRepresented)
                    <span class="ui-pill {{ $representativeConfirmed ? 'success' : 'warning' }}">Représentant {{ $representativeConfirmed ? 'confirmé' : 'en attente' }}</span>
                @endif
                @if($registration->checked_in_at)
                    <span class="ui-pill success"><i class="bi bi-door-open"></i>Arrivé à {{ $registration->checked_in_at->format('H:i') }}</span>
                @endif
            </div>
            @if($registration->email || $registration->telephone)
                <div class="identity-contact">
                    @if($registration->email)<a href="mailto:{{ $registration->email }}"><i class="bi bi-envelope"></i>{{ $registration->email }}</a>@endif
                    @if($registration->telephone)<a href="tel:{{ $registration->telephone }}"><i class="bi bi-telephone"></i>{{ $registration->telephone }}</a>@endif
                </div>
            @endif
        </div>
        <div class="identity-actions">
            @if($isAttendee && $canCheckIn)
                <form method="POST" action="{{ route('events.registrations.check-in', [$event, $registration]) }}"
                      data-confirm-title="Valider l’entrée ?" data-confirm="{{ $isRepresented ? $representativeName . ' (représentant de ' . $guestName . ')' : $guestName }} sera enregistré comme arrivé." data-confirm-button="Valider l’entrée" data-confirm-tone="primary">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-door-open"></i>Valider l’entrée</button>
                </form>
            @endif
            @if($isRepresented && !$representativeConfirmed)
                <form method="POST" action="{{ route('events.registrations.representative.resend', [$event, $registration]) }}"
                      data-confirm-title="Renvoyer le lien ?" data-confirm="Le représentant recevra à nouveau son lien de confirmation." data-confirm-button="Renvoyer" data-confirm-tone="primary">
                    @csrf
                    <button type="submit" class="ui-btn ui-btn-primary"><i class="bi bi-arrow-repeat"></i>Renvoyer le lien</button>
                </form>
            @endif
            <a href="{{ route('events.verify-qr', $registration->token_unique) }}" target="_blank" rel="noopener" class="ui-btn ui-btn-ghost"><i class="bi bi-qr-code"></i>Voir le QR</a>
        </div>
    </section>

    <section class="ui-card mt-3" aria-label="Parcours de l’invité">
        <ol class="journey">
            @foreach($steps as $i => [$label, $done, $detail])
                <li @class(['is-done' => $done, 'is-current' => $i === $currentStep])>
                    <span class="step-dot">@if($done)<i class="bi bi-check-lg"></i>@else{{ $i + 1 }}@endif</span>
                    <span class="step-label">{{ $label }}</span>
                    <span class="step-detail">{{ $detail }}</span>
                </li>
            @endforeach
        </ol>
    </section>

    <div class="guest-grid">
        <div class="stack">
            @if($isRepresented)
                <section class="ui-card">
                    <div class="ui-card-head"><div><h2>Représentant</h2><p>{{ $representativeConfirmed ? 'Présence confirmée' . ($registration->representant_confirme_le ? ' le ' . $dateTime($registration->representant_confirme_le) : '') : 'N’a pas encore confirmé sa venue' }}</p></div></div>
                    @if($isAttendee)
                        <div class="entry-note"><i class="bi bi-info-circle"></i><span>C’est <strong>{{ $representativeName ?: 'le représentant' }}</strong> qui se présentera à l’entrée, à la place de {{ $guestName }}.</span></div>
                    @endif
                    <div class="facts">
                        @foreach(array_filter(['Nom' => $representativeName, 'Fonction' => $registration->representant_fonction]) as $label => $value)
                            <div class="fact"><div class="fact-label">{{ $label }}</div><div class="fact-value">{{ $value }}</div></div>
                        @endforeach
                        @if($registration->representant_email)
                            <div class="fact"><div class="fact-label">Email</div><div class="fact-value"><a href="mailto:{{ $registration->representant_email }}">{{ $registration->representant_email }}</a></div></div>
                        @endif
                        @if($registration->representant_contact)
                            <div class="fact"><div class="fact-label">Contact</div><div class="fact-value"><a href="tel:{{ $registration->representant_contact }}">{{ $registration->representant_contact }}</a></div></div>
                        @endif
                        @foreach($deliveries as [$label, $sent, $sentAt, $error])
                            <div class="fact">
                                <div class="fact-label">{{ $label }}</div>
                                <div class="fact-value">
                                    {{ $sent ? 'Envoyé' : 'Non envoyé' }}@if($sent && $sentAt)<small>{{ $dateTime($sentAt) }}</small>@endif
                                    @if(!$sent && $error)<small class="error"><i class="bi bi-exclamation-circle me-1"></i>Échec de l’envoi</small>@endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="ui-card">
                <div class="ui-card-head"><div><h2>Historique</h2><p>{{ $activities->count() }} action(s) enregistrée(s)</p></div></div>
                @if($activities->isNotEmpty())
                    <ol class="timeline">
                        @foreach($activities as $activity)
                            @php
                                [$icon, $tone] = $activityStyles[$activity->type] ?? ['bi-dot', ''];
                            @endphp
                            <li>
                                <span class="tl-icon {{ $tone }}"><i class="bi {{ $icon }}"></i></span>
                                <div>
                                    <div class="tl-head">
                                        <span class="tl-title">{{ $activity->title }}</span>
                                        <time class="tl-time" datetime="{{ $activity->created_at->toIso8601String() }}" title="{{ $activity->created_at->format('d/m/Y H:i:s') }}">{{ $activity->created_at->locale('fr')->diffForHumans() }}</time>
                                    </div>
                                    <div class="tl-by">{{ $activity->actor?->name ?: ['public' => 'Invité', 'guest' => 'Invité', 'system' => 'Automatique'][$activity->actor_type] ?? ucfirst((string) $activity->actor_type) }}</div>
                                    @if($activity->description && !str_ends_with((string) $activity->type, '_failed'))<div class="tl-desc">{{ $activity->description }}</div>@endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="ui-empty py-4"><i class="bi bi-clock-history"></i>Aucune action enregistrée pour cet invité.</div>
                @endif
            </section>
        </div>

        <section class="ui-card">
            <div class="ui-card-head"><div><h2>Informations</h2></div></div>
            <div class="facts">
                <div class="fact"><div class="fact-label">Inscrit le</div><div class="fact-value">{{ $dateTime($registration->date_inscription) ?? '—' }}</div></div>
                <div class="fact"><div class="fact-label">Répondu le</div><div class="fact-value">{{ $dateTime($registration->date_reponse) ?? '—' }}</div></div>
                <div class="fact"><div class="fact-label">Carte d’invitation</div><div class="fact-value">{{ $registration->carte_envoyee ? 'Envoyée' : 'Non envoyée' }}</div></div>
                @if((int) $registration->reminder_count > 0)
                    <div class="fact"><div class="fact-label">Relances</div><div class="fact-value">{{ $registration->reminder_count }}@if($registration->last_reminder_sent_at)<small>Dernière le {{ $dateTime($registration->last_reminder_sent_at) }}</small>@endif</div></div>
                @endif
                <div class="fact">
                    <div class="fact-label">Entrée</div>
                    <div class="fact-value">
                        @if($registration->checked_in_at)
                            {{ $dateTime($registration->checked_in_at) }}
                            @if($registration->checkedInBy)<small>Validée par {{ $registration->checkedInBy->name }}</small>@endif
                            @if((int) $registration->checkin_count > 1)<small class="error">{{ $registration->checkin_count - 1 }} double(s) scan(s)</small>@endif
                        @else
                            Pas encore arrivé
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>
