@extends('layouts.app')

@section('title', 'Plans abonnement')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; max-width:1480px; margin:0 auto; color:#2c2a25; }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .muted { color:var(--muted); }
    .form-control,.form-select { border:1px solid var(--line); border-radius:10px; min-height:42px; }
    textarea.form-control { min-height:96px; }
    .btn-dark { background:#171713; border:0; border-radius:10px; }
    .btn-icon { align-items:center; display:inline-flex; height:38px; justify-content:center; width:42px; }
    .platform-table { border-collapse:separate; border-spacing:0; width:100%; }
    .platform-table th { color:var(--muted); font-size:.72rem; font-weight:600; letter-spacing:.08em; padding:0 10px 12px; text-transform:uppercase; white-space:nowrap; }
    .platform-table td { border-top:1px solid var(--line); padding:12px 10px; vertical-align:top; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; white-space:nowrap; }
    .status-on { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-off { background:rgba(164,81,74,.13); color:#a4514a; }
    .modal-content { border:0; border-radius:18px; }
    .modal-header { border-bottom:1px solid var(--line); }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">Plateforme</div>
            <h1 class="mb-1" style="font-weight:600;">Plans d'abonnement</h1>
            <p class="muted mb-0">Gestion des prix, limites et fonctionnalités disponibles pour les clients.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('platform.organizations') }}" class="btn btn-outline-dark">Organisations</a>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                <i class="bi bi-plus-lg me-2"></i> Créer un plan
            </button>
        </div>
    </div>

    <section class="panel">
        <div class="table-responsive">
            <table class="platform-table">
                <thead>
                    <tr>
                        <th>Plan</th>
                        <th>Prix</th>
                        <th>Limites</th>
                        <th>Fonctionnalités</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td>
                                <form id="plan-update-{{ $plan->id }}" method="POST" action="{{ route('platform.plans.update', $plan) }}">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <input form="plan-update-{{ $plan->id }}" class="form-control mb-2" name="name" value="{{ $plan->name }}" required>
                                <input form="plan-update-{{ $plan->id }}" class="form-control mb-2" name="slug" value="{{ $plan->slug }}" required>
                                <input form="plan-update-{{ $plan->id }}" class="form-control" name="tagline" value="{{ $plan->tagline }}" placeholder="Promesse">
                            </td>
                            <td style="min-width:180px;">
                                <input form="plan-update-{{ $plan->id }}" type="number" class="form-control mb-2" name="monthly_price" value="{{ $plan->monthly_price }}" min="0" required>
                                <input form="plan-update-{{ $plan->id }}" type="number" class="form-control mb-2" name="yearly_price" value="{{ $plan->yearly_price }}" min="0" required>
                                <input form="plan-update-{{ $plan->id }}" class="form-control" name="currency" value="{{ $plan->currency }}" required>
                            </td>
                            <td style="min-width:180px;">
                                <input form="plan-update-{{ $plan->id }}" type="number" class="form-control mb-2" name="events_limit" value="{{ $plan->events_limit }}" min="1" placeholder="Événements illimités">
                                <input form="plan-update-{{ $plan->id }}" type="number" class="form-control mb-2" name="users_limit" value="{{ $plan->users_limit }}" min="1" placeholder="Utilisateurs illimités">
                                <input form="plan-update-{{ $plan->id }}" type="number" class="form-control" name="invitations_limit" value="{{ $plan->invitations_limit }}" min="1" placeholder="Invitations illimitées">
                            </td>
                            <td style="min-width:280px;">
                                <textarea form="plan-update-{{ $plan->id }}" class="form-control" name="features_text">{{ implode("\n", $plan->features ?? []) }}</textarea>
                            </td>
                            <td>
                                <input form="plan-update-{{ $plan->id }}" type="number" class="form-control mb-2" name="sort_order" value="{{ $plan->sort_order }}" min="0">
                                <input form="plan-update-{{ $plan->id }}" type="hidden" name="is_active" value="0">
                                <div class="form-check mb-2">
                                    <input form="plan-update-{{ $plan->id }}" class="form-check-input" type="checkbox" name="is_active" value="1" id="plan-active-{{ $plan->id }}" {{ $plan->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="plan-active-{{ $plan->id }}">Actif</label>
                                </div>
                                <span class="status-pill {{ $plan->is_active ? 'status-on' : 'status-off' }}">
                                    {{ $plan->is_active ? 'Disponible' : 'Masqué' }}
                                </span>
                            </td>
                            <td>
                                <button form="plan-update-{{ $plan->id }}" class="btn btn-dark btn-icon" type="submit" title="Enregistrer">
                                    <i class="bi bi-save"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted text-center py-4">Aucun plan configuré.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="text-uppercase" style="color:#b98943;font-size:.72rem;font-weight:600;letter-spacing:.12em;">Nouvelle offre</div>
                    <h2 class="modal-title h5" id="createPlanModalLabel" style="font-weight:600;">Créer un plan d'abonnement</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="{{ route('platform.plans.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        @include('platform.partials.plan-fields', ['plan' => null])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Annuler</button>
                    <button class="btn btn-dark" type="submit"><i class="bi bi-plus-lg me-2"></i> Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
