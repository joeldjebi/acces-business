@extends('layouts.app')

@section('title', 'Envoyer des liens d\'accès - ' . $event->titre)

@php
    $quota = $quota ?? \App\Support\SaasUsage::forOrganization($event->organization);
    $invitationLimit = $quota['limits']['invitations'] ?? null;
    $invitationsUsed = $quota['usage']['invitations'] ?? 0;
    $remainingInvitations = $invitationLimit === null ? null : max(0, (int) $invitationLimit - (int) $invitationsUsed);
    $canSendInvitations = $remainingInvitations === null || $remainingInvitations > 0;
@endphp

@push('styles')
<style>
    .invite-admin {
        --ink: #171713;
        --muted: #746f65;
        --line: #ded6c8;
        --panel: #fffefa;
        --gold: #b98943;
        --green: #2e7b65;
        --red: #a4514a;
        max-width: 1380px;
        margin: 0 auto;
    }

    .invite-head {
        align-items: flex-end;
        display: flex;
        gap: 20px;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .invite-kicker {
        color: var(--gold);
        font-size: .74rem;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .invite-title {
        color: var(--ink);
        font-size: clamp(1.8rem, 3vw, 2.8rem);
        font-weight: 500;
        line-height: 1.08;
        margin: 6px 0 8px;
    }

    .invite-copy {
        color: var(--muted);
        margin: 0;
        max-width: 720px;
    }

    .invite-btn {
        align-items: center;
        border-radius: 999px;
        display: inline-flex;
        gap: 8px;
        min-height: 42px;
        padding: 0 16px;
        text-decoration: none;
        border: 1px solid var(--line);
        background: #fff;
        color: var(--ink);
        white-space: nowrap;
    }

    .invite-btn.primary {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }

    .invite-panel {
        background: var(--panel);
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 24px;
        box-shadow: 0 18px 46px rgba(39, 33, 25, .06);
        overflow: hidden;
    }

    .modal-content.invite-panel {
        border: 1px solid rgba(222, 214, 200, .78);
    }

    .panel-head {
        border-bottom: 1px solid rgba(222, 214, 200, .7);
        padding: 18px 20px;
    }

    .panel-head h2 {
        color: var(--ink);
        font-size: 1.04rem;
        font-weight: 600;
        margin: 0;
    }

    .panel-head p {
        color: var(--muted);
        font-size: .84rem;
        margin: 4px 0 0;
    }

    .panel-body {
        padding: 20px;
    }

    .form-control,
    .form-select {
        border: 1px solid var(--line);
        border-radius: 14px;
        min-height: 42px;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: rgba(185, 137, 67, .75);
        box-shadow: 0 0 0 .22rem rgba(185, 137, 67, .12);
    }

    .method-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 16px;
    }

    .method-toggle .btn {
        border-radius: 999px;
        border-color: var(--line);
    }

    .method-toggle .btn.active {
        background: var(--ink);
        border-color: var(--ink);
        color: #fff;
    }

    .upload-section {
        border: 1px dashed rgba(185, 137, 67, .45);
        border-radius: 18px;
        padding: 22px;
        text-align: center;
        cursor: pointer;
        background: rgba(185, 137, 67, .06);
    }

    .upload-section.dragover {
        background: rgba(185, 137, 67, .12);
    }

    .link-row {
        align-items: center;
        border-bottom: 1px solid rgba(222, 214, 200, .7);
        display: grid;
        gap: 14px;
        grid-template-columns: minmax(220px, 1.2fr) minmax(170px, .8fr) 130px 130px 98px 92px;
        padding: 15px 20px;
    }

    .link-row.head {
        background: #f8f4ec;
        color: var(--muted);
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .07em;
        text-transform: uppercase;
    }

    .link-row:last-child {
        border-bottom: 0;
    }

    .email-cell {
        color: var(--ink);
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .muted-cell {
        color: var(--muted);
        font-size: .86rem;
    }

    .badge-status {
        border-radius: 999px;
        display: inline-flex;
        font-size: .76rem;
        font-weight: 600;
        padding: 6px 10px;
    }

    .badge-status.envoye {
        background: rgba(185, 137, 67, .14);
        color: #8a6128;
    }

    .badge-status.utilise {
        background: rgba(46, 123, 101, .12);
        color: var(--green);
    }

    .icon-btn {
        align-items: center;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        color: var(--ink);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        text-decoration: none;
        width: 36px;
    }

    .empty-state {
        color: var(--muted);
        padding: 38px 20px;
        text-align: center;
    }

    .card-preview-grid {
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(0, 1fr) 320px;
        margin-bottom: 18px;
    }

    .invitation-preview {
        background: #fffefa;
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 22px;
        box-shadow: 0 18px 46px rgba(39, 33, 25, .06);
        overflow: hidden;
    }

    .preview-cover {
        background: var(--preview-primary, var(--ink));
        color: #fff;
        padding: 24px;
    }

    .preview-brand {
        align-items: center;
        display: flex;
        gap: 14px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .preview-brand-name {
        font-size: .9rem;
        opacity: .9;
    }

    .preview-logo {
        align-items: center;
        background: #fff;
        border-radius: 12px;
        display: flex;
        height: 48px;
        justify-content: center;
        overflow: hidden;
        width: 86px;
    }

    .preview-logo img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        padding: 7px;
    }

    .preview-kicker {
        color: var(--preview-accent, var(--gold));
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
    }

    .preview-title {
        font-size: 1.35rem;
        font-weight: 500;
        line-height: 1.18;
        margin: 8px 0 0;
    }

    .preview-body {
        padding: 22px 24px;
    }

    .preview-detail {
        border: 1px solid var(--line);
        border-radius: 16px;
        display: grid;
        gap: 10px;
        padding: 14px;
    }

    .preview-detail span {
        color: var(--muted);
        display: block;
        font-size: .72rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .preview-qr {
        align-items: center;
        display: flex;
        gap: 16px;
        margin-top: 16px;
    }

    .preview-qr-box {
        align-items: center;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 16px;
        color: var(--muted);
        display: flex;
        flex: 0 0 86px;
        height: 86px;
        justify-content: center;
    }

    .preview-signature {
        align-items: center;
        border-top: 1px solid var(--line);
        display: flex;
        gap: 14px;
        justify-content: space-between;
        margin-top: 16px;
        padding-top: 14px;
    }

    .preview-signature img {
        max-height: 42px;
        max-width: 120px;
    }

    .approval-note {
        background: rgba(185, 137, 67, .1);
        border: 1px solid rgba(185, 137, 67, .28);
        border-radius: 18px;
        color: #7a5727;
        padding: 16px;
    }

    @media (max-width: 860px) {
        .invite-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .card-preview-grid {
            grid-template-columns: 1fr;
        }

        .link-row,
        .link-row.head {
            grid-template-columns: 1fr;
        }

        .link-row.head {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="invite-admin">
    <header class="invite-head">
        <div>
            <div class="invite-kicker">Invitations privées</div>
            <h1 class="invite-title">Envoyer des liens d’accès</h1>
            <p class="invite-copy">{{ $event->titre }}</p>
        </div>
        <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button type="button" class="invite-btn primary" data-bs-toggle="modal" data-bs-target="#sendLinkModal">
                <i class="bi bi-send"></i> Nouveau lien
            </button>
            <a href="{{ route('events.show', $event) }}" class="invite-btn">
                <i class="bi bi-arrow-left"></i> Retour à l'événement
            </a>
        </div>
    </header>

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }}">
                {{ session($key) }}
            </div>
        @endif
    @endforeach

    <section class="card-preview-grid">
        <article class="invitation-preview" style="--preview-primary: {{ $cardDesign['primary_color'] ?? '#171713' }}; --preview-accent: {{ $cardDesign['accent_color'] ?? '#b98943' }};">
            <div class="preview-cover">
                <div class="preview-brand">
                    <div class="preview-brand-name">{{ $cardDesign['brand_name'] ?? 'Accès Business' }}</div>
                    @if(!empty($cardDesign['organization_logo']))
                        <div class="preview-logo">
                            <img src="{{ $cardDesign['organization_logo'] }}" alt="{{ $cardDesign['brand_name'] ?? 'Logo organisation' }}">
                        </div>
                    @endif
                </div>
                <div class="preview-kicker">Modèle carte d’invitation</div>
                <h2 class="preview-title">{{ $event->titre }}</h2>
            </div>
            <div class="preview-body">
                <div class="preview-detail">
                    <div>
                        <span>Invité</span>
                        Exemple Invité · Fonction · Entreprise
                    </div>
                    <div>
                        <span>Date & heure</span>
                        {{ optional($event->date_debut)->format('d/m/Y') }} · {{ $event->heure_debut }}{{ $event->heure_fin ? ' - ' . $event->heure_fin : '' }}
                    </div>
                    <div>
                        <span>Lieu</span>
                        {{ $event->lieu ?: $event->ville ?: 'Lieu à confirmer' }}
                    </div>
                </div>
                <div class="preview-qr">
                    <div class="preview-qr-box"><i class="bi bi-qr-code" style="font-size:2.4rem;"></i></div>
                    <div>
                        <span class="muted-cell d-block mb-1">Code d’accès</span>
                        <strong>INVITATION-EXEMPLE</strong>
                    </div>
                </div>
                @if(!empty($cardDesign['signature_text']) || !empty($cardDesign['signature_logo']))
                    <div class="preview-signature">
                        <span class="muted-cell">{{ $cardDesign['signature_text'] }}</span>
                        @if(!empty($cardDesign['signature_logo']))
                            <img src="{{ $cardDesign['signature_logo'] }}" alt="Signature plateforme">
                        @endif
                    </div>
                @endif
            </div>
        </article>

        <aside class="invite-panel">
            <div class="panel-head">
                <h2>Contrôle du modèle</h2>
                <p>Ce visuel sera utilisé après confirmation de présence.</p>
            </div>
            <div class="panel-body">
                @if(!empty($cardDesign['allow_organization_logo']))
                    <div class="approval-note mb-3" style="background:rgba(46,123,101,.1);border-color:rgba(46,123,101,.22);color:#2e7b65;">
                        <i class="bi bi-check2-circle me-1"></i> Logo organisation autorisé par le SA.
                    </div>
                @elseif(!empty($cardDesign['organization_logo_blocked']))
                    <div class="approval-note mb-3">
                        <i class="bi bi-shield-lock me-1"></i> Le logo organisation existe, mais il n’est pas encore autorisé par le SA.
                    </div>
                @else
                    <div class="approval-note mb-3">
                        <i class="bi bi-info-circle me-1"></i> Aucun logo organisation n’est disponible pour cette carte.
                    </div>
                @endif
                @if(!empty($cardDesign['allow_organization_branding']))
                    <div class="approval-note mb-3" style="background:rgba(46,123,101,.1);border-color:rgba(46,123,101,.22);color:#2e7b65;">
                        <i class="bi bi-palette me-1"></i> Couleurs organisation autorisées par le SA.
                    </div>
                @elseif(!empty($cardDesign['organization_branding_blocked']))
                    <div class="approval-note mb-3">
                        <i class="bi bi-shield-lock me-1"></i> Les couleurs organisation existent, mais elles ne sont pas encore autorisées par le SA.
                    </div>
                @endif
                <p class="muted-cell mb-0">
                    La signature, le logo et l’utilisation des couleurs organisation sont contrôlés par le super admin plateforme.
                </p>
                <div class="approval-note mt-3" style="{{ $canSendInvitations ? 'background:rgba(46,123,101,.1);border-color:rgba(46,123,101,.22);color:#2e7b65;' : '' }}">
                    <i class="bi {{ $canSendInvitations ? 'bi-send-check' : 'bi-lock' }} me-1"></i>
                    @if($invitationLimit === null)
                        Invitations illimitées sur votre forfait.
                    @else
                        {{ number_format($invitationsUsed, 0, ',', ' ') }} / {{ number_format((int) $invitationLimit, 0, ',', ' ') }} invitation(s) utilisées.
                        Il reste {{ number_format((int) $remainingInvitations, 0, ',', ' ') }} invitation(s).
                    @endif
                </div>
            </div>
        </aside>
    </section>

    <div class="invite-panel">
            <div class="panel-head">
                <h2>Historique des liens</h2>
                <p>{{ $links->total() }} lien(s) généré(s) pour cet événement.</p>
            </div>

            <div class="panel-body">
                <form method="GET" action="{{ route('events.send-link', $event) }}">
                    <div class="row g-2">
                        <div class="col-lg-4">
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Rechercher un email">
                        </div>
                        <div class="col-lg-3">
                            <select class="form-select" id="statut" name="statut">
                                <option value="">Tous les statuts</option>
                                <option value="envoye" {{ request('statut') === 'envoye' ? 'selected' : '' }}>Envoyé</option>
                                <option value="utilise" {{ request('statut') === 'utilise' ? 'selected' : '' }}>Utilisé</option>
                            </select>
                        </div>
                        <div class="col-lg-2">
                            <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-lg-2">
                            <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-lg-1 d-grid">
                            <button type="submit" class="invite-btn primary px-0">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @if($links->count() > 0)
                <div class="link-row head">
                    <div>Invité</div>
                    <div>Fonction</div>
                    <div>Envoyé</div>
                    <div>Utilisé</div>
                    <div>Statut</div>
                    <div class="text-end">Actions</div>
                </div>
                @foreach($links as $link)
                    <div class="link-row">
                        <div>
                            <div class="email-cell">{{ trim(($link->prenom ?? '') . ' ' . ($link->nom ?? '')) ?: $link->email_destinataire }}</div>
                            <div class="muted-cell">{{ $link->email_destinataire }}</div>
                            @if($link->telephone)
                                <div class="muted-cell">{{ $link->telephone }}</div>
                            @endif
                        </div>
                        <div class="muted-cell">
                            {{ $link->fonction ?: '-' }}
                            @if($link->entreprise)
                                <div>{{ $link->entreprise }}</div>
                            @endif
                        </div>
                        <div class="muted-cell">{{ optional($link->envoye_le)->format('d/m/Y H:i') }}</div>
                        <div class="muted-cell">{{ $link->utilise_le ? $link->utilise_le->format('d/m/Y H:i') : 'Non utilisé' }}</div>
                        <div>
                            @if($link->est_utilise)
                                <span class="badge-status utilise">Utilisé</span>
                            @else
                                <span class="badge-status envoye">Envoyé</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ $link->access_url }}" target="_blank" class="icon-btn" title="Ouvrir le lien">
                                <i class="bi bi-link-45deg"></i>
                            </a>
                            <form method="POST" action="{{ route('events.send-link.destroy', [$event, $link]) }}" onsubmit="return confirm('Supprimer cette invitation ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn" title="Supprimer l'invitation">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
                @if($links->hasPages())
                    <div class="panel-body d-flex justify-content-center">
                        {{ $links->links() }}
                    </div>
                @endif
            @else
                <div class="empty-state">
                    <i class="bi bi-envelope-paper d-block mb-2" style="font-size: 2rem;"></i>
                    Aucun lien envoyé pour le moment.
                </div>
            @endif
    </div>

    <div class="modal fade" id="sendLinkModal" tabindex="-1" aria-labelledby="sendLinkModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content invite-panel">
            <div class="panel-head">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <h2 id="sendLinkModalLabel">Nouveau lien</h2>
                        <p>Envoyez une invitation personnalisée à une ou plusieurs adresses.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
            </div>
            <div class="panel-body">
                <form method="POST" action="{{ route('events.send-link.store', $event) }}" enctype="multipart/form-data" id="sendLinkForm">
                    @csrf

                    <div class="method-toggle">
                        <button type="button" class="btn active" id="btnManual" onclick="switchInputMethod('manual')">
                            <i class="bi bi-keyboard me-1"></i>Manuel
                        </button>
                        <button type="button" class="btn" id="btnCSV" onclick="switchInputMethod('csv')">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                        </button>
                    </div>

                    <div id="manualInput" class="input-method">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="invite@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-2">
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
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="csv_file" class="form-label mb-0">Fichier CSV</label>
                                <a href="{{ route('events.download-csv-template') }}" class="small text-decoration-none">Modèle CSV</a>
                            </div>
                            <div class="upload-section" id="uploadArea" onclick="document.getElementById('csv_file').click()">
                                <i class="bi bi-cloud-upload d-block mb-2" style="font-size: 1.6rem; color: var(--gold);"></i>
                                <strong>Sélectionner un fichier</strong>
                                <p class="text-muted small mb-0 mt-1">Colonnes: email, nom, prenom, telephone, entreprise, fonction.</p>
                            </div>
                            <input type="file" class="form-control d-none @error('csv_file') is-invalid @enderror" id="csv_file" name="csv_file" accept=".csv,.txt" onchange="handleFileSelect(this)">
                            <div id="fileInfo" class="mt-2" style="display: none;">
                                <div class="alert alert-light border d-flex align-items-center justify-content-between mb-0">
                                    <span><i class="bi bi-file-earmark-check me-2"></i><span id="fileName"></span></span>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearFile()"><i class="bi bi-x"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Message personnalisé</label>
                        <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="4" placeholder="Votre message sera ajouté dans l’email.">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="invite-btn primary w-100 justify-content-center" {{ !$canSendInvitations ? 'disabled' : '' }}>
                        <i class="bi bi-send"></i> Envoyer les liens
                    </button>
                    @if(!$canSendInvitations)
                        <div class="alert alert-warning mt-3 mb-0">
                            Limite d’invitations atteinte. Passez à un forfait supérieur pour continuer.
                        </div>
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
            const file = input.files[0];
            document.getElementById('fileName').textContent = file.name;
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
        uploadArea.addEventListener(eventName, function(e) {
            e.preventDefault();
            e.stopPropagation();
        }, false);
    });

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
    });

    uploadArea.addEventListener('drop', (e) => {
        const fileInput = document.getElementById('csv_file');
        fileInput.files = e.dataTransfer.files;
        handleFileSelect(fileInput);
    });

    document.getElementById('sendLinkForm').addEventListener('submit', function(e) {
        if (currentMethod === 'manual' && !document.getElementById('email').value.trim()) {
            e.preventDefault();
            alert('Veuillez entrer un email.');
        }
    });

    @if($errors->any())
        new bootstrap.Modal(document.getElementById('sendLinkModal')).show();
    @endif
</script>
@endpush
@endsection
