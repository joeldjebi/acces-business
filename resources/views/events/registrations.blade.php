@extends('layouts.app')

@section('title', 'Inscriptions · ' . $event->titre)

@php
    $presents = $stats['present'] ?? 0;
    $peutEtre = $stats['peut_etre'] ?? 0;
    $absents = $stats['absent'] ?? 0;
    $representes = $stats['represente'] ?? 0;
    $total = $stats['total'] ?? $registrations->total();
    $pct = fn ($value) => $total ? round($value / $total * 100) : 0;

    $responseLabels = ['present' => 'Présent', 'peut_etre' => 'Peut-être', 'absent' => 'Absent', 'represente' => 'Représenté', 'en_attente' => 'En attente'];
    $responseTones = ['present' => 'success', 'peut_etre' => 'info', 'absent' => 'danger', 'represente' => 'gold', 'en_attente' => ''];
    $audienceLabels = ['all' => 'Tout le monde', 'present' => 'Accepté', 'absent' => 'Refusé', 'peut_etre' => 'Peut-être', 'represente' => 'Représenté'];
    $broadcastStatus = ['pending' => ['En attente', ''], 'processing' => ['En cours', 'info'], 'completed' => ['Terminé', 'success'], 'failed' => ['Échec', 'danger']];
    $repStatus = function ($registration) {
        return match ($registration->representant_statut) {
            'confirme' => ['Confirmé le ' . ($registration->representant_confirme_le?->format('d/m/Y H:i') ?: '—'), 'success'],
            'mail_envoye' => ['Lien envoyé le ' . ($registration->representant_mail_envoye_le?->format('d/m/Y H:i') ?: '—') . ', en attente', 'warning'],
            'mail_echec' => ['Envoi échoué · ' . ($registration->representant_mail_erreur ?: 'erreur inconnue'), 'danger'],
            default => ['En attente d’envoi', ''],
        };
    };
    $advancedActive = collect(request()->only(['representative_status', 'card']))->filter()->isNotEmpty();
    $openComposer = $errors->hasAny(['channel', 'audience', 'subject', 'message', 'attachment']) || old('message');
@endphp

@push('styles')
<style>
    .reg-page { margin: 0 auto; max-width: 1400px; }
    .reg-page *, .reg-page *::before, .reg-page *::after { min-width: 0; }

    .composer { margin-bottom: 20px; overflow: hidden; }
    .composer-head { align-items: center; background: radial-gradient(80% 140% at 0% 0%, rgba(185, 137, 67, .35), transparent 60%), #171713; color: #fff; cursor: pointer; display: flex; gap: 16px; justify-content: space-between; padding: 18px 22px; }
    .composer-head h2 { color: #fff; font-size: 1rem; font-weight: 650; margin: 0; }
    .composer-head p { color: rgba(255, 255, 255, .6); font-size: .8rem; margin: 3px 0 0; }
    .composer-credits { text-align: right; }
    .composer-credits small { color: rgba(255, 255, 255, .55); display: block; font-size: .72rem; }
    .composer-credits strong { color: #d8b978; font-size: 1.3rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.03em; }
    .composer-toggle { align-items: center; background: rgba(255, 255, 255, .1); border-radius: 50%; display: flex; flex: 0 0 34px; height: 34px; justify-content: center; transition: transform .4s var(--app-ease); }
    .composer-head[aria-expanded="true"] .composer-toggle { transform: rotate(180deg); }
    .channel-choice { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 3px; padding: 4px; }
    .channel-choice input { opacity: 0; pointer-events: none; position: absolute; }
    .channel-choice label { border-radius: 999px; color: var(--app-muted); cursor: pointer; font-size: .86rem; font-weight: 600; margin: 0; padding: 8px 16px; transition: background .2s, color .2s; }
    .channel-choice input:checked + label { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }
    .channel-choice input:focus-visible + label { box-shadow: 0 0 0 4px var(--app-gold-soft); }
    .history { border-top: 1px solid var(--app-line); margin-top: 22px; padding-top: 18px; }
    .history h3 { color: var(--app-ink); font-size: .86rem; font-weight: 650; margin-bottom: 8px; }

    .filters-bar { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; justify-content: space-between; padding: 16px 22px; }
    .resp-tabs { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 2px; max-width: 100%; overflow-x: auto; padding: 3px; scrollbar-width: none; }
    .resp-tabs a { border-radius: 999px; color: var(--app-muted); font-size: .8rem; font-weight: 600; padding: 7px 12px; text-decoration: none; white-space: nowrap; }
    .resp-tabs a.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }
    .rep-box { background: var(--app-bg); border-radius: 12px; font-size: .78rem; line-height: 1.5; margin-top: 8px; max-width: 320px; padding: 10px 12px; white-space: normal; }
    .rep-box strong { color: var(--app-ink); display: block; font-size: .82rem; }
    .rep-box .rep-line { align-items: flex-start; color: var(--app-muted); display: flex; gap: 6px; margin-top: 3px; }
    .rep-box .rep-line i { margin-top: 2px; }
    .rep-ok { color: var(--app-green) !important; }
    .rep-ko { color: var(--app-red) !important; }

    @media (max-width: 991.98px) {
        .reg-table thead { display: none; }
        .reg-table tr { border-bottom: 1px solid var(--app-line); display: grid; gap: 8px; grid-template-columns: 1fr auto; padding: 14px 18px; }
        .reg-table td { border: 0 !important; padding: 0 !important; }
        .reg-table td.col-response { grid-column: 1 / -1; }
        .reg-table td.col-hide { display: none; }
        .reg-table td.col-actions { grid-column: 2; grid-row: 1; }
    }
</style>
@endpush

@section('content')
<div class="reg-page">
    <x-app.event-header :event="$event" active="registrations" />

    <section class="ui-stats">
        @foreach([
            ['label' => 'Total', 'value' => $total, 'icon' => 'bi-people', 'accent' => 'var(--app-ink)', 'pct' => null],
            ['label' => 'Présents', 'value' => $presents, 'icon' => 'bi-check-circle', 'accent' => 'var(--app-green)', 'pct' => $pct($presents)],
            ['label' => 'Peut-être', 'value' => $peutEtre, 'icon' => 'bi-question-circle', 'accent' => 'var(--app-blue)', 'pct' => $pct($peutEtre)],
            ['label' => 'Absents', 'value' => $absents, 'icon' => 'bi-x-circle', 'accent' => 'var(--app-red)', 'pct' => $pct($absents)],
            ['label' => 'Représentés', 'value' => $representes, 'icon' => 'bi-person-badge', 'accent' => 'var(--app-gold)', 'pct' => $pct($representes)],
        ] as $stat)
            <article class="ui-stat" style="--accent: {{ $stat['accent'] }}">
                <div class="ui-stat-top">{{ $stat['label'] }}<i class="bi {{ $stat['icon'] }}"></i></div>
                <strong>{{ number_format($stat['value'], 0, ',', ' ') }}</strong>
                @if($stat['pct'] !== null)
                    <small>{{ $stat['pct'] }}% des inscrits</small>
                    <div class="ui-meter"><span style="width: {{ $stat['pct'] }}%"></span></div>
                @else
                    <small>Réponses enregistrées</small>
                @endif
            </article>
        @endforeach
    </section>

    <section class="ui-card composer">
        <div class="composer-head" data-bs-toggle="collapse" data-bs-target="#composerBody" role="button" aria-expanded="{{ $openComposer ? 'true' : 'false' }}" aria-controls="composerBody">
            <div class="d-flex align-items-center gap-3">
                <span class="composer-toggle"><i class="bi bi-chevron-down"></i></span>
                <div>
                    <h2>Envoyer une communication</h2>
                    <p>Email ou SMS ciblé selon la réponse des inscrits.</p>
                </div>
            </div>
            <div class="composer-credits"><small>Crédits SMS</small><strong>{{ number_format($smsBalance->remaining, 0, ',', ' ') }}</strong></div>
        </div>
        <div @class(['collapse', 'show' => $openComposer]) id="composerBody">
            <div class="ui-card-body pt-4">
                <form method="POST" action="{{ route('events.registrations.communications.send', $event) }}" enctype="multipart/form-data" id="bulkCommunicationForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <span class="form-label d-block">Canal</span>
                            <div class="channel-choice">
                                <input type="radio" name="channel" id="channelEmail" value="email" @checked(old('channel', 'email') === 'email')>
                                <label for="channelEmail"><i class="bi bi-envelope me-1"></i>Email</label>
                                <input type="radio" name="channel" id="channelSms" value="sms" @checked(old('channel') === 'sms')>
                                <label for="channelSms"><i class="bi bi-chat-dots me-1"></i>SMS</label>
                            </div>
                            @error('channel')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-4">
                            <label for="communicationAudience" class="form-label">Destinataires</label>
                            <select class="form-select" id="communicationAudience" name="audience" required>
                                <option value="all" @selected(old('audience') === 'all')>Tout le monde</option>
                                <option value="present" @selected(old('audience') === 'present')>Ceux qui ont accepté</option>
                                <option value="absent" @selected(old('audience') === 'absent')>Ceux qui ont refusé</option>
                                <option value="peut_etre" @selected(old('audience') === 'peut_etre')>Ceux qui ne savent pas encore</option>
                                <option value="represente" @selected(old('audience') === 'represente')>Ceux qui se font représenter</option>
                            </select>
                            @error('audience')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-4" id="communicationSubjectGroup">
                            <label for="communicationSubject" class="form-label">Objet de l’email</label>
                            <input type="text" class="form-control" id="communicationSubject" name="subject" value="{{ old('subject') }}" maxlength="255">
                            @error('subject')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label for="communicationMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="communicationMessage" name="message" rows="4" maxlength="2000" required>{{ old('message') }}</textarea>
                            <div class="d-flex justify-content-end"><small class="text-muted mt-1" id="messageCount">0 / 2000</small></div>
                            @error('message')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-8">
                            <label for="communicationAttachment" class="form-label">Fichier <span class="text-muted fw-normal">(facultatif)</span></label>
                            <input type="file" class="form-control" id="communicationAttachment" name="attachment" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <div class="form-text" id="attachmentHelp">Le fichier sera joint à l’email. Taille maximale : 10 Mo.</div>
                            @error('attachment')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-lg-4 d-flex align-items-end justify-content-lg-end">
                            <button type="submit" class="ui-btn ui-btn-primary" onclick="return confirm('Confirmer la création de cette campagne ?');"><i class="bi bi-send"></i>Envoyer la campagne</button>
                        </div>
                    </div>
                </form>

                @if($broadcasts->isNotEmpty())
                    <div class="history">
                        <h3>Campagnes récentes</h3>
                        <div class="table-responsive">
                            <table class="ui-table">
                                <thead><tr><th>Date</th><th>Canal</th><th>Cible</th><th>Résultat</th><th>Statut</th></tr></thead>
                                <tbody>
                                    @foreach($broadcasts as $broadcast)
                                        @php [$bLabel, $bTone] = $broadcastStatus[$broadcast->status] ?? [$broadcast->status, '']; @endphp
                                        <tr>
                                            <td>{{ $broadcast->created_at->format('d/m/Y H:i') }}</td>
                                            <td><span class="ui-pill"><i class="bi {{ $broadcast->channel === 'sms' ? 'bi-chat-dots' : 'bi-envelope' }}"></i>{{ strtoupper($broadcast->channel) }}</span></td>
                                            <td>{{ $audienceLabels[$broadcast->audience] ?? $broadcast->audience }}</td>
                                            <td>{{ $broadcast->sent_count }} envoyé(s) · {{ $broadcast->failed_count }} échec(s) / {{ $broadcast->recipient_count }}</td>
                                            <td><span class="ui-pill dot {{ $bTone }}">{{ $bLabel }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    <section class="ui-card" style="overflow: hidden">
        <div class="filters-bar">
            <nav class="resp-tabs" aria-label="Filtrer par réponse">
                @foreach(['' => 'Toutes'] + $responseLabels as $value => $label)
                    <a href="{{ route('events.registrations', array_merge([$event], array_filter(array_merge(request()->except(['response', 'page']), ['response' => $value])))) }}" @class(['is-active' => (string) request('response', '') === (string) $value])>{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" class="ui-toolbar" style="flex: 1 1 420px; justify-content: flex-end">
                @if(request('response'))<input type="hidden" name="response" value="{{ request('response') }}">@endif
                <label class="ui-search mb-0" style="max-width: 280px">
                    <i class="bi bi-search"></i>
                    <input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nom, email, téléphone…" aria-label="Rechercher">
                </label>
                <select name="representative_status" class="form-select" aria-label="Statut du représentant">
                    <option value="">Représentant</option>
                    <option value="en_attente" @selected(request('representative_status') === 'en_attente')>En attente</option>
                    <option value="mail_envoye" @selected(request('representative_status') === 'mail_envoye')>Mail envoyé</option>
                    <option value="mail_echec" @selected(request('representative_status') === 'mail_echec')>Mail échoué</option>
                    <option value="confirme" @selected(request('representative_status') === 'confirme')>Confirmé</option>
                </select>
                <select name="card" class="form-select" aria-label="Carte">
                    <option value="">Carte</option>
                    <option value="sent" @selected(request('card') === 'sent')>Envoyée</option>
                    <option value="not_sent" @selected(request('card') === 'not_sent')>Non envoyée</option>
                </select>
                <select name="per_page" class="form-select" aria-label="Par page">
                    @foreach([10, 20, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((int) request('per_page', 20) === $size)>{{ $size }} / page</option>
                    @endforeach
                </select>
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">Filtrer</button>
                @if(collect(request()->only(['search', 'response', 'representative_status', 'card']))->filter()->isNotEmpty())
                    <a href="{{ route('events.registrations', $event) }}" class="ui-btn ui-btn-soft ui-btn-sm" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </form>
        </div>

        @if($registrations->count())
            <div class="table-responsive">
                <table class="ui-table reg-table">
                    <thead><tr><th>Invité</th><th>Organisation</th><th>Réponse</th><th>Inscrit le</th><th>Carte</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        @foreach($registrations as $registration)
                            <tr>
                                <td>
                                    <div class="ui-person">
                                        <span class="ui-avatar">{{ strtoupper(mb_substr($registration->prenom ?: $registration->nom ?: $registration->email, 0, 1)) }}</span>
                                        <div><strong>{{ $registration->nom_complet ?: '—' }}</strong><small>{{ $registration->email }}{{ $registration->telephone ? ' · ' . $registration->telephone : '' }}</small></div>
                                    </div>
                                </td>
                                <td class="col-hide">{{ $registration->entreprise ?: '—' }}<span class="ui-sub">{{ $registration->fonction }}</span></td>
                                <td class="col-response">
                                    <span class="ui-pill dot {{ $responseTones[$registration->statut_reponse] ?? '' }}">{{ $responseLabels[$registration->statut_reponse] ?? 'En attente' }}</span>
                                    @if($registration->statut_reponse === 'represente')
                                        @php [$repLabel, $repTone] = $repStatus($registration); @endphp
                                        <div class="rep-box">
                                            <strong><i class="bi bi-person-badge me-1"></i>{{ trim(($registration->representant_prenoms ?? '') . ' ' . ($registration->representant_nom ?? '')) ?: 'Représentant' }}</strong>
                                            <div class="rep-line"><i class="bi bi-briefcase"></i>{{ $registration->representant_fonction ?: '—' }} · {{ $registration->representant_contact ?: '—' }}</div>
                                            <div class="rep-line"><i class="bi bi-envelope"></i>{{ $registration->representant_email ?: '—' }}</div>
                                            <div @class(['rep-line', 'rep-ok' => $repTone === 'success', 'rep-ko' => $repTone === 'danger'])><i class="bi bi-link-45deg"></i>{{ $repLabel }}</div>
                                            <div @class(['rep-line', 'rep-ok' => $registration->representant_carte_envoyee, 'rep-ko' => !$registration->representant_carte_envoyee && $registration->representant_carte_erreur])><i class="bi bi-postcard"></i>
                                                Carte : @if($registration->representant_carte_envoyee) envoyée le {{ $registration->representant_carte_envoyee_le?->format('d/m/Y H:i') ?: '—' }} @elseif($registration->representant_carte_erreur) non envoyée · {{ $registration->representant_carte_erreur }} @else non envoyée @endif
                                            </div>
                                            <div @class(['rep-line', 'rep-ok' => $registration->represente_notification_envoyee, 'rep-ko' => !$registration->represente_notification_envoyee && $registration->represente_notification_erreur])><i class="bi bi-bell"></i>
                                                Invité notifié : @if($registration->represente_notification_envoyee) le {{ $registration->represente_notification_envoyee_le?->format('d/m/Y H:i') ?: '—' }} @elseif($registration->represente_notification_erreur) non · {{ $registration->represente_notification_erreur }} @else non @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                                <td class="col-hide">{{ $registration->date_inscription->format('d/m/Y') }}<span class="ui-sub">{{ $registration->date_inscription->format('H:i') }}</span></td>
                                <td class="col-hide">
                                    @if($registration->carte_envoyee)
                                        <span class="ui-pill success"><i class="bi bi-check2"></i>Envoyée</span>
                                    @else
                                        <span class="ui-pill">Non envoyée</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    <div class="ui-actions">
                                        <a href="{{ route('events.verify-qr', $registration->token_unique) }}" target="_blank" class="ui-icon-btn" title="Voir le QR code"><i class="bi bi-qr-code"></i></a>
                                        @if($registration->statut_reponse === 'represente' && $registration->representant_statut !== 'confirme')
                                            <form method="POST" action="{{ route('events.registrations.representative.resend', [$event, $registration]) }}">
                                                @csrf
                                                <button type="submit" class="ui-icon-btn" title="Renvoyer le lien au représentant"><i class="bi bi-envelope-arrow-up"></i></button>
                                            </form>
                                        @endif
                                        <a href="{{ route('events.registrations.show', [$event, $registration]) }}" class="ui-btn ui-btn-soft ui-btn-sm">Détail <i class="bi bi-chevron-right"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($registrations->hasPages())
                <div class="ui-pager">{{ $registrations->links('pagination::bootstrap-5') }}</div>
            @endif
        @else
            <div class="ui-empty">
                <i class="bi bi-inbox"></i>
                <strong>Aucune inscription</strong>
                {{ collect(request()->only(['search', 'response', 'representative_status', 'card']))->filter()->isNotEmpty() ? 'Aucun inscrit ne correspond à ces critères.' : 'Les réponses de vos invités apparaîtront ici.' }}
            </div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const email = document.getElementById('channelEmail');
        const sms = document.getElementById('channelSms');
        const subjectGroup = document.getElementById('communicationSubjectGroup');
        const subject = document.getElementById('communicationSubject');
        const attachmentHelp = document.getElementById('attachmentHelp');
        const message = document.getElementById('communicationMessage');
        const counter = document.getElementById('messageCount');

        function updateCommunicationChannel() {
            const isEmail = email.checked;
            subjectGroup.hidden = !isEmail;
            subject.required = isEmail;
            attachmentHelp.textContent = isEmail
                ? 'Le fichier sera joint à l’email. Taille maximale : 10 Mo.'
                : 'Un lien sécurisé vers le fichier sera ajouté automatiquement au SMS. Taille maximale : 10 Mo.';
        }

        const updateCount = () => counter.textContent = `${message.value.length} / 2000`;

        email.addEventListener('change', updateCommunicationChannel);
        sms.addEventListener('change', updateCommunicationChannel);
        message.addEventListener('input', updateCount);
        updateCommunicationChannel();
        updateCount();
    })();
</script>
@endpush
