@extends('layouts.app')

@section('title', 'Invitations · ' . $event->titre)

@php
    $quota = $quota ?? \App\Support\SaasUsage::forOrganization($event->organization);
    $invitationLimit = $quota['limits']['invitations'] ?? null;
    $invitationsUsed = $quota['usage']['invitations'] ?? 0;
    $remainingInvitations = $invitationLimit === null ? null : max(0, (int) $invitationLimit - (int) $invitationsUsed);
    $canSendInvitations = $remainingInvitations === null || $remainingInvitations > 0;
    $quotaPct = $invitationLimit ? min(100, round($invitationsUsed / $invitationLimit * 100)) : 0;
    $statusFilters = ['' => 'Tous', 'envoye' => 'Envoyés', 'utilise' => 'Utilisés'];
    $hasFilters = collect(request()->only(['search', 'statut', 'date_from', 'date_to']))->filter()->isNotEmpty();
    $approvals = collect([
        !empty($cardDesign['allow_organization_logo'])
            ? ['ok' => true, 'icon' => 'bi-check2-circle', 'text' => 'Logo de l’organisation autorisé']
            : (!empty($cardDesign['organization_logo_blocked'])
                ? ['ok' => false, 'icon' => 'bi-shield-lock', 'text' => 'Logo en attente d’autorisation plateforme']
                : ['ok' => false, 'icon' => 'bi-info-circle', 'text' => 'Aucun logo d’organisation disponible']),
        !empty($cardDesign['allow_organization_branding'])
            ? ['ok' => true, 'icon' => 'bi-palette', 'text' => 'Couleurs de l’organisation autorisées']
            : (!empty($cardDesign['organization_branding_blocked'])
                ? ['ok' => false, 'icon' => 'bi-shield-lock', 'text' => 'Couleurs en attente d’autorisation plateforme']
                : null),
    ])->filter();
@endphp

@push('styles')
<style>
    .invite-page { margin: 0 auto; max-width: 1320px; }
    .invite-page *, .invite-page *::before, .invite-page *::after { min-width: 0; }
    .invite-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 340px; }
    .side { display: grid; gap: 20px; position: sticky; top: 84px; }

    .status-tabs { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 2px; padding: 3px; }
    .status-tabs a { border-radius: 999px; color: var(--app-muted); font-size: .8rem; font-weight: 600; padding: 7px 13px; text-decoration: none; }
    .status-tabs a.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }

    /* Invitation card preview */
    .card-preview { border-radius: 22px; box-shadow: 0 30px 60px -30px rgba(23, 23, 19, .45); overflow: hidden; }
    .cp-cover { background: radial-gradient(100% 120% at 0% 0%, color-mix(in srgb, var(--preview-accent) 45%, transparent), transparent 60%), var(--preview-primary); color: #fff; padding: 20px; }
    .cp-brand { align-items: center; display: flex; font-size: .78rem; gap: 10px; justify-content: space-between; opacity: .92; }
    .cp-logo { align-items: center; background: #fff; border-radius: 10px; display: flex; height: 36px; justify-content: center; overflow: hidden; width: 64px; }
    .cp-logo img { max-height: 100%; max-width: 100%; object-fit: contain; padding: 5px; }
    .cp-kicker { color: var(--preview-accent); font-size: .64rem; font-weight: 700; letter-spacing: .14em; margin-top: 18px; text-transform: uppercase; }
    .cp-title { font-size: 1.2rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.2; margin: 6px 0 0; }
    .cp-body { background: #fff; padding: 18px 20px; }
    .cp-rows { display: grid; gap: 10px; }
    .cp-rows span { color: var(--app-muted); display: block; font-size: .64rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
    .cp-rows div { color: var(--app-ink); font-size: .84rem; font-weight: 500; }
    .cp-qr { align-items: center; border-top: 1px dashed var(--app-line); display: flex; gap: 14px; margin-top: 16px; padding-top: 16px; }
    .cp-qr-box { align-items: center; background: var(--app-bg); border-radius: 12px; color: var(--app-ink); display: flex; flex: 0 0 64px; font-size: 2rem; height: 64px; justify-content: center; }
    .cp-qr small { color: var(--app-muted); display: block; font-size: .7rem; }
    .cp-qr strong { color: var(--app-ink); font-size: .82rem; letter-spacing: .04em; }
    .cp-signature { align-items: center; border-top: 1px solid var(--app-line); color: var(--app-muted); display: flex; font-size: .76rem; gap: 10px; justify-content: space-between; margin-top: 14px; padding-top: 12px; }
    .cp-signature img { max-height: 32px; max-width: 100px; }
    .approval { align-items: center; display: flex; font-size: .82rem; gap: 10px; padding: 10px 0; }
    .approval + .approval { border-top: 1px solid var(--app-line); }
    .approval i { align-items: center; border-radius: 8px; display: flex; flex: 0 0 26px; height: 26px; justify-content: center; }
    .approval.ok i { background: rgba(46, 123, 101, .1); color: var(--app-green); }
    .approval.ko i { background: var(--app-gold-soft); color: var(--app-gold-deep); }
    .quota-big { align-items: baseline; display: flex; gap: 6px; }
    .quota-big strong { color: var(--app-ink); font-size: 1.6rem; font-weight: 700; letter-spacing: -.04em; }
    .quota-big span { color: var(--app-muted); font-size: .84rem; }

    /* Modal */
    .modal-head { align-items: flex-start; display: flex; gap: 16px; justify-content: space-between; padding: 22px 24px 6px; }
    .modal-head h2 { color: var(--app-ink); font-size: 1.2rem; font-weight: 700; letter-spacing: -.02em; margin: 0; }
    .modal-head p { color: var(--app-muted); font-size: .86rem; margin: 4px 0 0; }
    .modal-body-x { padding: 16px 24px 24px; }
    .method-toggle { background: rgba(23, 23, 19, .05); border-radius: 999px; display: grid; gap: 3px; grid-template-columns: 1fr 1fr; margin-bottom: 20px; padding: 4px; }
    .method-toggle .btn { border: 0; border-radius: 999px; color: var(--app-muted); font-size: .86rem; font-weight: 600; height: 38px; }
    .method-toggle .btn.active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }
    .upload-section { background: #fbfaf7; border: 1.5px dashed rgba(23, 23, 19, .18); border-radius: 18px; cursor: pointer; padding: 28px 20px; text-align: center; transition: border-color .2s, background .2s; }
    .upload-section:hover, .upload-section.dragover { background: #fff; border-color: var(--app-gold); }
    .upload-section i { color: var(--app-gold); font-size: 1.8rem; }
    .upload-section strong { color: var(--app-ink); display: block; margin-top: 6px; }

    .links-table td:first-child { max-width: 300px; }
    @media (max-width: 1439.98px) { .links-table .col-fn { display: none; } }
    @media (max-width: 1099.98px) { .invite-grid { grid-template-columns: 1fr; } .side { position: static; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); } }
    @media (max-width: 767.98px) {
        .links-table thead { display: none; }
        .links-table tr { border-bottom: 1px solid var(--app-line); display: grid; gap: 6px; grid-template-columns: 1fr auto; padding: 14px 18px; }
        .links-table td { border: 0 !important; padding: 0 !important; }
        .links-table td.col-meta { grid-column: 1; }
        .links-table td.col-status { grid-column: 2; grid-row: 1; text-align: right; }
        .links-table td.col-hide { display: none; }
    }
</style>
@endpush

@section('content')
<div class="invite-page">
    <x-app.event-header :event="$event" active="send-link">
        <x-slot:actions>
            <button type="button" class="ui-btn ui-btn-primary" data-bs-toggle="modal" data-bs-target="#sendLinkModal" @disabled(!$canSendInvitations)>
                <i class="bi bi-send"></i>Nouvelle invitation
            </button>
        </x-slot:actions>
    </x-app.event-header>

    @if(session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="invite-grid">
        <section class="ui-card" style="overflow: hidden">
            <div class="ui-card-head">
                <div>
                    <h2>Historique des invitations</h2>
                    <p>{{ $links->total() }} lien(s) personnel(s) généré(s) pour cet événement.</p>
                </div>
                <nav class="status-tabs" aria-label="Filtrer par statut">
                    @foreach($statusFilters as $value => $label)
                        <a href="{{ route('events.send-link', array_merge([$event], array_filter(array_merge(request()->except(['statut', 'page']), ['statut' => $value])))) }}" @class(['is-active' => (string) request('statut', '') === (string) $value])>{{ $label }}</a>
                    @endforeach
                </nav>
            </div>
            <form method="GET" action="{{ route('events.send-link', $event) }}" class="ui-toolbar px-4 pb-3">
                @if(request('statut'))<input type="hidden" name="statut" value="{{ request('statut') }}">@endif
                <label class="ui-search mb-0">
                    <i class="bi bi-search"></i>
                    <input type="search" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher un email" aria-label="Rechercher un email">
                </label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}" aria-label="Envoyé à partir du">
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}" aria-label="Envoyé jusqu’au">
                <button type="submit" class="ui-btn ui-btn-primary ui-btn-sm">Filtrer</button>
                @if($hasFilters)<a href="{{ route('events.send-link', $event) }}" class="ui-btn ui-btn-soft ui-btn-sm" title="Réinitialiser"><i class="bi bi-arrow-counterclockwise"></i></a>@endif
            </form>

            @if($links->count() > 0)
                <div class="table-responsive">
                    <table class="ui-table links-table">
                        <thead><tr><th>Invité</th><th class="col-fn">Fonction</th><th>Envoyé le</th><th>Statut</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @foreach($links as $link)
                                @php $name = trim(($link->prenom ?? '') . ' ' . ($link->nom ?? '')); @endphp
                                <tr>
                                    <td class="col-meta">
                                        <div class="ui-person">
                                            <span class="ui-avatar">{{ strtoupper(mb_substr($name ?: $link->email_destinataire, 0, 1)) }}</span>
                                            <div><strong>{{ $name ?: $link->email_destinataire }}</strong><small>{{ $link->email_destinataire }}{{ $link->telephone ? ' · ' . $link->telephone : '' }}</small></div>
                                        </div>
                                    </td>
                                    <td class="col-hide col-fn">{{ $link->fonction ?: '—' }}<span class="ui-sub">{{ $link->entreprise }}</span></td>
                                    <td class="col-hide">{{ optional($link->envoye_le)->format('d/m/Y') }}<span class="ui-sub">{{ optional($link->envoye_le)->format('H:i') }}</span></td>
                                    <td class="col-status">
                                        @if($link->est_utilise)
                                            <span class="ui-pill dot success" title="Utilisé le {{ $link->utilise_le?->format('d/m/Y H:i') }}">Utilisé</span>
                                            <span class="ui-sub mt-1">{{ $link->utilise_le?->format('d/m H:i') }}</span>
                                        @else
                                            <span class="ui-pill dot warning">Envoyé</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="ui-actions">
                                            <button type="button" class="ui-icon-btn" title="Copier le lien" data-copy="{{ $link->access_url }}"><i class="bi bi-copy"></i></button>
                                            <a href="{{ $link->access_url }}" target="_blank" rel="noopener" class="ui-icon-btn" title="Ouvrir le lien"><i class="bi bi-box-arrow-up-right"></i></a>
                                            <form method="POST" action="{{ route('events.send-link.destroy', [$event, $link]) }}" onsubmit="return confirm('Supprimer cette invitation ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ui-icon-btn danger" title="Supprimer l'invitation"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($links->hasPages())
                    <div class="ui-pager">{{ $links->links('pagination::bootstrap-5') }}</div>
                @endif
            @else
                <div class="ui-empty">
                    <i class="bi bi-envelope-paper"></i>
                    @if($hasFilters)
                        <strong>Aucun résultat</strong>Aucune invitation ne correspond à ces critères.
                    @else
                        <strong>Aucune invitation envoyée</strong>Invitez vos premiers convives par email ou en important un fichier CSV.
                        @if($canSendInvitations)<div class="mt-3"><button type="button" class="ui-btn ui-btn-primary" data-bs-toggle="modal" data-bs-target="#sendLinkModal"><i class="bi bi-send"></i>Nouvelle invitation</button></div>@endif
                    @endif
                </div>
            @endif
        </section>

        <aside class="side">
            <section class="ui-card">
                <div class="ui-card-head"><div><h2>Forfait invitations</h2><p>Consommation de votre organisation.</p></div></div>
                <div class="ui-card-body pt-0">
                    @if($invitationLimit === null)
                        <div class="quota-big"><strong>∞</strong><span>Invitations illimitées</span></div>
                    @else
                        <div class="quota-big"><strong>{{ number_format((int) $remainingInvitations, 0, ',', ' ') }}</strong><span>restante(s) sur {{ number_format((int) $invitationLimit, 0, ',', ' ') }}</span></div>
                        <div class="ui-meter mt-2" style="--accent: {{ $quotaPct >= 90 ? 'var(--app-red)' : ($quotaPct >= 70 ? 'var(--app-gold)' : 'var(--app-ink)') }}"><span style="width: {{ $quotaPct }}%"></span></div>
                        @unless($canSendInvitations)<p class="text-danger small mt-2 mb-0">Limite atteinte : passez à un forfait supérieur pour continuer.</p>@endunless
                    @endif
                </div>
            </section>

            <section class="ui-card">
                <div class="ui-card-head"><div><h2>Carte d’invitation</h2><p>Envoyée après confirmation de présence.</p></div></div>
                <div class="ui-card-body pt-0">
                    <article class="card-preview" style="--preview-primary: {{ $cardDesign['primary_color'] ?? '#171713' }}; --preview-accent: {{ $cardDesign['accent_color'] ?? '#b98943' }};">
                        <div class="cp-cover">
                            <div class="cp-brand">
                                <span>{{ $cardDesign['brand_name'] ?? 'Accès Business' }}</span>
                                @if(!empty($cardDesign['organization_logo']))<span class="cp-logo"><img src="{{ $cardDesign['organization_logo'] }}" alt="{{ $cardDesign['brand_name'] ?? 'Logo organisation' }}"></span>@endif
                            </div>
                            <div class="cp-kicker">Invitation</div>
                            <h3 class="cp-title">{{ $event->titre }}</h3>
                        </div>
                        <div class="cp-body">
                            <div class="cp-rows">
                                <div><span>Invité</span>Exemple Invité · Fonction</div>
                                <div><span>Date & heure</span>{{ optional($event->date_debut)->format('d/m/Y') }} · {{ \Illuminate\Support\Str::substr($event->heure_debut, 0, 5) }}{{ $event->heure_fin ? ' – ' . \Illuminate\Support\Str::substr($event->heure_fin, 0, 5) : '' }}</div>
                                <div><span>Lieu</span>{{ $event->lieu ?: $event->ville ?: 'Lieu à confirmer' }}</div>
                            </div>
                            <div class="cp-qr">
                                <span class="cp-qr-box"><i class="bi bi-qr-code"></i></span>
                                <div><small>Code d’accès</small><strong>INVITATION-EXEMPLE</strong></div>
                            </div>
                            @if(!empty($cardDesign['signature_text']) || !empty($cardDesign['signature_logo']))
                                <div class="cp-signature">
                                    <span>{{ $cardDesign['signature_text'] }}</span>
                                    @if(!empty($cardDesign['signature_logo']))<img src="{{ $cardDesign['signature_logo'] }}" alt="Signature plateforme">@endif
                                </div>
                            @endif
                        </div>
                    </article>
                    <div class="mt-3">
                        @foreach($approvals as $approval)
                            <div @class(['approval', 'ok' => $approval['ok'], 'ko' => !$approval['ok']])><i class="bi {{ $approval['icon'] }}"></i>{{ $approval['text'] }}</div>
                        @endforeach
                    </div>
                    <p class="text-muted small mb-0 mt-2">La signature, le logo et les couleurs sont validés par l’administration de la plateforme.</p>
                </div>
            </section>
        </aside>
    </div>

    <div class="modal fade" id="sendLinkModal" tabindex="-1" aria-labelledby="sendLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-head">
                    <div>
                        <h2 id="sendLinkModalLabel">Nouvelle invitation</h2>
                        <p>Envoyez un lien personnel à une adresse, ou importez une liste au format CSV.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body-x">
                    <form method="POST" action="{{ route('events.send-link.store', $event) }}" enctype="multipart/form-data" id="sendLinkForm">
                        @csrf

                        <div class="method-toggle">
                            <button type="button" class="btn active" id="btnManual" onclick="switchInputMethod('manual')"><i class="bi bi-person-plus me-1"></i>Un invité</button>
                            <button type="button" class="btn" id="btnCSV" onclick="switchInputMethod('csv')"><i class="bi bi-file-earmark-spreadsheet me-1"></i>Import CSV</button>
                        </div>

                        <div id="manualInput" class="input-method">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="invite@exemple.com">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="nom" class="form-label">Nom</label>
                                    <input class="form-control" id="nom" name="nom" value="{{ old('nom') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="prenom" class="form-label">Prénoms</label>
                                    <input class="form-control" id="prenom" name="prenom" value="{{ old('prenom') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="telephone" class="form-label">Téléphone</label>
                                    <input class="form-control" id="telephone" name="telephone" value="{{ old('telephone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="entreprise" class="form-label">Entreprise</label>
                                    <input class="form-control" id="entreprise" name="entreprise" value="{{ old('entreprise') }}">
                                </div>
                                <div class="col-12">
                                    <label for="fonction" class="form-label">Fonction</label>
                                    <input class="form-control" id="fonction" name="fonction" value="{{ old('fonction') }}">
                                </div>
                            </div>
                        </div>

                        <div id="csvInput" class="input-method" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="csv_file" class="form-label mb-0">Fichier CSV</label>
                                <a href="{{ route('events.download-csv-template') }}" class="small fw-semibold text-decoration-none"><i class="bi bi-download me-1"></i>Télécharger le modèle</a>
                            </div>
                            <div class="upload-section" id="uploadArea" onclick="document.getElementById('csv_file').click()">
                                <i class="bi bi-cloud-arrow-up"></i>
                                <strong>Déposez votre fichier ici ou cliquez pour parcourir</strong>
                                <p class="text-muted small mb-0 mt-1">Colonnes : email, nom, prenom, telephone, entreprise, fonction.</p>
                            </div>
                            <input type="file" class="form-control d-none @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv,.txt" onchange="handleFileSelect(this)">
                            @error('csv_file')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            <div id="fileInfo" class="mt-2" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between rounded-3 border px-3 py-2">
                                    <span><i class="bi bi-file-earmark-check text-success me-2"></i><span id="fileName"></span></span>
                                    <button type="button" class="ui-icon-btn danger" onclick="clearFile()" aria-label="Retirer le fichier"><i class="bi bi-x-lg"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label for="message" class="form-label">Message personnalisé <span class="text-muted fw-normal">(facultatif)</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="3" placeholder="Votre message sera ajouté dans l’email.">{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="ui-btn ui-btn-primary w-100 mt-4" @disabled(!$canSendInvitations)>
                            <i class="bi bi-send"></i>Envoyer l’invitation
                        </button>
                        @if(!$canSendInvitations)
                            <div class="alert alert-warning mt-3 mb-0">Limite d’invitations atteinte. Passez à un forfait supérieur pour continuer.</div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentMethod = 'manual';

    function switchInputMethod(method) {
        currentMethod = method;
        document.getElementById('btnManual').classList.toggle('active', method === 'manual');
        document.getElementById('btnCSV').classList.toggle('active', method === 'csv');
        document.getElementById('manualInput').style.display = method === 'manual' ? 'block' : 'none';
        document.getElementById('csvInput').style.display = method === 'csv' ? 'block' : 'none';
    }

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            document.getElementById('fileName').textContent = input.files[0].name;
            document.getElementById('fileInfo').style.display = 'block';
            document.getElementById('uploadArea').classList.add('dragover');
        }
    }

    function clearFile() {
        document.getElementById('csv_file').value = '';
        document.getElementById('fileInfo').style.display = 'none';
        document.getElementById('uploadArea').classList.remove('dragover');
    }

    const uploadArea = document.getElementById('uploadArea');
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, e => { e.preventDefault(); e.stopPropagation(); }, false);
    });
    ['dragenter', 'dragover'].forEach(eventName => uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false));
    ['dragleave', 'drop'].forEach(eventName => uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false));
    uploadArea.addEventListener('drop', e => {
        const fileInput = document.getElementById('csv_file');
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(fileInput);
    });

    document.getElementById('sendLinkForm').addEventListener('submit', function (e) {
        if (currentMethod === 'manual' && !document.getElementById('email').value.trim()) {
            e.preventDefault();
            alert('Veuillez entrer un email.');
        }
    });

    document.querySelectorAll('[data-copy]').forEach(button => {
        button.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(button.dataset.copy);
                const icon = button.querySelector('i');
                icon.className = 'bi bi-check2';
                setTimeout(() => icon.className = 'bi bi-copy', 1500);
            } catch (e) {
                window.prompt('Copiez le lien :', button.dataset.copy);
            }
        });
    });

    @if($errors->any())
        @if($errors->has('csv_file')) switchInputMethod('csv'); @endif
        new bootstrap.Modal(document.getElementById('sendLinkModal')).show();
    @endif
</script>
@endpush
@endsection
