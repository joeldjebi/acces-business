@extends('layouts.app')

@section('title', 'Organisations')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .muted { color:var(--muted); }
    .form-control,.form-select { border:1px solid var(--line); border-radius:10px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:10px; }
    .platform-table { border-collapse:separate; border-spacing:0; width:100%; }
    .platform-table th { color:var(--muted); font-size:.72rem; font-weight:600; letter-spacing:.08em; padding:0 12px 12px; text-transform:uppercase; white-space:nowrap; }
    .platform-table td { border-top:1px solid var(--line); padding:14px 12px; vertical-align:middle; }
    .org-title { color:var(--ink); font-weight:600; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; white-space:nowrap; }
    .status-active,.status-trialing { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-suspended,.status-cancelled { background:rgba(164,81,74,.13); color:#a4514a; }
    .modal-content { border:0; border-radius:18px; }
    .modal-header { border-bottom:1px solid var(--line); }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">Plateforme</div>
            <h1 class="mb-1" style="font-weight:600;">Organisations</h1>
            <p class="muted mb-0">Liste synthétique des clients. Ouvrez les détails pour les événements, utilisateurs, facturation et référentiels.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-dark">Dashboard</a>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createOrganizationModal">
                <i class="bi bi-plus-lg me-2"></i> Créer un client
            </button>
        </div>
    </div>

    <section class="panel mb-3">
        <form class="row g-3 align-items-end">
            <div class="col-lg-7">
                <label class="form-label">Recherche</label>
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Nom, slug ou domaine">
            </div>
            <div class="col-lg-3">
                <label class="form-label">Statut</label>
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    @foreach(['active', 'trialing', 'suspended', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <button class="btn btn-dark w-100" type="submit"><i class="bi bi-search me-2"></i> Filtrer</button>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="table-responsive">
            <table class="platform-table">
                <thead>
                    <tr>
                        <th>Organisation</th>
                        <th>Plan</th>
                        <th>Statut</th>
                        <th>Utilisateurs</th>
                        <th>Événements</th>
                        <th>Création</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $organization)
                        <tr>
                            <td>
                                <div class="org-title">{{ $organization->name }}</div>
                                <div class="muted">{{ $organization->slug }}</div>
                                @if($organization->domain)
                                    <div class="muted">{{ $organization->domain }}</div>
                                @endif
                            </td>
                            <td>{{ ucfirst($organization->plan) }}</td>
                            <td><span class="status-pill status-{{ $organization->status }}">{{ ucfirst($organization->status) }}</span></td>
                            <td>{{ $organization->users_count }}</td>
                            <td>{{ $organization->events_count }}</td>
                            <td>{{ optional($organization->created_at)->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('platform.organizations.show', $organization) }}" class="btn btn-dark btn-sm">
                                    <i class="bi bi-layout-text-window-reverse me-1"></i> Détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="muted text-center py-4">Aucune organisation trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $organizations->links() }}
        </div>
    </section>
</div>

<div class="modal fade" id="createOrganizationModal" tabindex="-1" aria-labelledby="createOrganizationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="text-uppercase" style="color:#b98943;font-size:.72rem;font-weight:600;letter-spacing:.12em;">Nouveau client</div>
                    <h2 class="modal-title h5" id="createOrganizationModalLabel" style="font-weight:600;">Créer une organisation</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="{{ route('platform.organizations.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-6">
                            <label class="form-label">Organisation / entreprise / agence</label>
                            <input class="form-control" name="name" value="{{ old('name') }}" required placeholder="Nom du client">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Slug</label>
                            <input class="form-control" name="slug" value="{{ old('slug') }}" placeholder="auto si vide">
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Plan</label>
                            <select class="form-select" name="plan">
                                @foreach($plans as $key => $plan)
                                    <option value="{{ $key }}">{{ $plan['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6">
                            <label class="form-label">Domaine</label>
                            <input class="form-control" name="domain" value="{{ old('domain') }}" placeholder="optionnel">
                        </div>
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
