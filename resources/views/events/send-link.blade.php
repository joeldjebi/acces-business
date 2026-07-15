@extends('layouts.app')

@section('title', 'Envoyer des liens d\'accès - ' . $event->titre)

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

    .invite-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 390px;
        gap: 20px;
        align-items: start;
    }

    .invite-panel {
        background: var(--panel);
        border: 1px solid rgba(222, 214, 200, .78);
        border-radius: 24px;
        box-shadow: 0 18px 46px rgba(39, 33, 25, .06);
        overflow: hidden;
    }

    .invite-panel.sticky {
        position: sticky;
        top: 90px;
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

    @media (max-width: 1100px) {
        .invite-grid {
            grid-template-columns: 1fr;
        }

        .invite-panel.sticky {
            position: static;
        }
    }

    @media (max-width: 860px) {
        .invite-head {
            align-items: flex-start;
            flex-direction: column;
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
        <a href="{{ route('events.show', $event) }}" class="invite-btn">
            <i class="bi bi-arrow-left"></i> Retour à l'événement
        </a>
    </header>

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }}">
                {{ session($key) }}
            </div>
        @endif
    @endforeach

    <div class="invite-grid">
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

        <aside class="invite-panel sticky">
            <div class="panel-head">
                <h2>Nouveau lien</h2>
                <p>Envoyez une invitation personnalisée à une ou plusieurs adresses.</p>
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

                    <button type="submit" class="invite-btn primary w-100 justify-content-center">
                        <i class="bi bi-send"></i> Envoyer les liens
                    </button>
                </form>
            </div>
        </aside>
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
</script>
@endpush
@endsection
