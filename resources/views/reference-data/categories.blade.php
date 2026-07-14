@extends('layouts.app')

@section('title', 'Catégories d\'événement')

@push('styles')
<style>
    .ref-page {
        --ink: #171713;
        --text: #2c2a25;
        --muted: #746f65;
        --line: #dfd7cb;
        --panel: #fffefa;
        --panel-soft: #f8f4ec;
        --gold: #b98943;
        --green: #2e7b65;
        --red: #a4514a;
        --shadow: 0 18px 45px rgba(39, 33, 25, 0.08);
        color: var(--text);
        max-width: 1180px;
        margin: 0 auto;
    }

    .ref-head {
        align-items: flex-start;
        display: flex;
        gap: 20px;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .ref-kicker {
        color: var(--gold);
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .ref-title {
        color: var(--ink);
        font-size: clamp(1.7rem, 2.5vw, 2.45rem);
        font-weight: 600;
        line-height: 1.08;
        margin: 6px 0 0;
    }

    .ref-copy {
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.6;
        margin: 10px 0 0;
        max-width: 680px;
    }

    .ref-grid {
        display: grid;
        gap: 18px;
        grid-template-columns: minmax(280px, 0.55fr) minmax(0, 1fr);
        align-items: start;
    }

    .ref-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 8px;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .ref-card-head {
        border-bottom: 1px solid var(--line);
        padding: 16px 18px;
    }

    .ref-card-head h2 {
        color: var(--ink);
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
    }

    .ref-card-head p {
        color: var(--muted);
        font-size: 0.8rem;
        margin: 4px 0 0;
    }

    .ref-form {
        display: grid;
        gap: 12px;
        padding: 18px;
    }

    .ref-label {
        color: var(--muted);
        font-size: 0.78rem;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .ref-input {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--ink);
        min-height: 42px;
        padding: 10px 12px;
        width: 100%;
    }

    .ref-btn {
        align-items: center;
        border: 1px solid transparent;
        border-radius: 8px;
        display: inline-flex;
        gap: 8px;
        justify-content: center;
        min-height: 42px;
        padding: 0 14px;
        text-decoration: none;
    }

    .ref-btn.primary {
        background: var(--ink);
        color: #fff;
    }

    .ref-btn.ghost {
        background: #fff;
        border-color: var(--line);
        color: var(--ink);
    }

    .ref-list-row {
        align-items: center;
        border-bottom: 1px solid var(--line);
        display: grid;
        gap: 12px;
        grid-template-columns: minmax(0, 1fr) 110px 108px 98px;
        padding: 14px 18px;
    }

    .ref-list-row:last-child {
        border-bottom: 0;
    }

    .ref-list-head {
        background: var(--panel-soft);
        color: var(--muted);
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .status-pill {
        border-radius: 999px;
        display: inline-flex;
        font-size: 0.76rem;
        justify-content: center;
        min-height: 28px;
        padding: 6px 10px;
        width: fit-content;
    }

    .status-pill.active {
        background: rgba(46, 123, 101, 0.12);
        color: var(--green);
    }

    .status-pill.inactive {
        background: rgba(164, 81, 74, 0.13);
        color: var(--red);
    }

    .row-actions {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
    }

    .icon-btn {
        align-items: center;
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 8px;
        color: var(--ink);
        display: inline-flex;
        height: 36px;
        justify-content: center;
        width: 36px;
    }

    .icon-btn.danger {
        color: var(--red);
    }

    .empty-state {
        color: var(--muted);
        padding: 34px 18px;
        text-align: center;
    }

    @media (max-width: 920px) {
        .ref-grid {
            grid-template-columns: 1fr;
        }

        .ref-list-row,
        .ref-list-head {
            grid-template-columns: 1fr;
        }

        .ref-list-head {
            display: none;
        }

        .row-actions {
            justify-content: flex-start;
        }
    }
</style>
@endpush

@section('content')
<div class="ref-page">
    <div class="ref-head">
        <div>
            <div class="ref-kicker">Référentiels</div>
            <h1 class="ref-title">Catégories d'événement</h1>
            <p class="ref-copy">Créez, renommez et activez les catégories utilisées lors de la création des événements.</p>
        </div>
    </div>

    <div class="ref-grid">
        <section class="ref-card">
            <div class="ref-card-head">
                <h2>Nouvelle catégorie</h2>
                <p>Ajoutez une catégorie visible dans le formulaire événement.</p>
            </div>
            <form action="{{ route('categories.store') }}" method="POST" class="ref-form">
                @csrf
                <div>
                    <label for="libelle" class="ref-label">Libellé</label>
                    <input id="libelle" name="libelle" class="ref-input @error('libelle') is-invalid @enderror" value="{{ old('libelle') }}" maxlength="100" required>
                    @error('libelle')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="ref-btn primary">
                    <i class="bi bi-plus-circle"></i>
                    Créer la catégorie
                </button>
            </form>
        </section>

        <section class="ref-card">
            <div class="ref-card-head">
                <h2>Liste des catégories</h2>
                <p>{{ $categories->count() }} catégorie(s) enregistrée(s).</p>
            </div>

            <div class="ref-list-row ref-list-head">
                <div>Libellé</div>
                <div>Statut</div>
                <div>Événements</div>
                <div class="text-end">Actions</div>
            </div>

            @forelse($categories as $category)
                <form id="update-category-{{ $category->id }}" action="{{ route('categories.update', $category) }}" method="POST">
                    @csrf
                    @method('PUT')
                </form>
                <div class="ref-list-row">
                    <div>
                        <input form="update-category-{{ $category->id }}" name="libelle" class="ref-input" value="{{ old('libelle', $category->libelle) }}" maxlength="100" required>
                    </div>
                    <div>
                        <label class="status-pill {{ $category->statut ? 'active' : 'inactive' }}">
                            <input form="update-category-{{ $category->id }}" type="checkbox" name="statut" value="1" class="me-2" {{ $category->statut ? 'checked' : '' }}>
                            {{ $category->statut ? 'Actif' : 'Inactif' }}
                        </label>
                    </div>
                    <div>{{ $category->events_count }}</div>
                    <div class="row-actions">
                        <button form="update-category-{{ $category->id }}" type="submit" class="icon-btn" title="Enregistrer">
                            <i class="bi bi-check2"></i>
                        </button>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-btn danger" title="Supprimer" {{ $category->events_count > 0 ? 'disabled' : '' }}>
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty-state">Aucune catégorie enregistrée.</div>
            @endforelse
        </section>
    </div>
</div>
@endsection
