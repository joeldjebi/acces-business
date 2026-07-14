@extends('layouts.app')

@section('title', 'Organisations')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; max-width:1480px; margin:0 auto; color:#2c2a25; }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .muted { color:var(--muted); }
    .form-control,.form-select { border:1px solid var(--line); border-radius:10px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:10px; }
    .btn-icon { align-items:center; display:inline-flex; height:38px; justify-content:center; width:42px; }
    .platform-table { border-collapse:separate; border-spacing:0; width:100%; }
    .platform-table th { color:var(--muted); font-size:.72rem; font-weight:600; letter-spacing:.08em; padding:0 10px 12px; text-transform:uppercase; white-space:nowrap; }
    .platform-table td { border-top:1px solid var(--line); padding:12px 10px; vertical-align:top; }
    .org-title { color:var(--ink); font-weight:600; min-width:180px; }
    .link-box { background:#f8f4ec; border:1px solid var(--line); border-radius:10px; color:#171713; font-size:.78rem; max-width:280px; padding:8px 10px; word-break:break-all; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; }
    .status-active,.status-trialing { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-suspended,.status-cancelled { background:rgba(164,81,74,.13); color:#a4514a; }
    .role-form { display:grid; gap:8px; grid-template-columns:minmax(150px,1fr) 150px 42px; margin-bottom:8px; }
    .modal-content { border:0; border-radius:18px; }
    .modal-header { border-bottom:1px solid var(--line); }
    @media (max-width: 1100px) { .role-form { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">Plateforme</div>
            <h1 class="mb-1" style="font-weight:600;">Organisations</h1>
            <p class="muted mb-0">Gestion des tenants, plans, statuts, liens d'accès et rôles client.</p>
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
                        <th>Plan / statut</th>
                        <th>Domaine</th>
                        <th>Liens client</th>
                        <th>Rôles</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $organization)
                        <tr>
                            <td>
                                <form id="organization-update-{{ $organization->id }}" method="POST" action="{{ route('platform.organizations.update', $organization) }}">
                                    @csrf
                                    @method('PUT')
                                </form>
                                <input form="organization-update-{{ $organization->id }}" class="form-control mb-2" name="name" value="{{ $organization->name }}" required>
                                <div class="muted">{{ $organization->users_count }} user(s) · {{ $organization->events_count }} événement(s)</div>
                                <div class="muted">{{ $organization->slug }}</div>
                            </td>
                            <td>
                                <select form="organization-update-{{ $organization->id }}" class="form-select mb-2" name="plan">
                                    @foreach($plans as $key => $plan)
                                        <option value="{{ $key }}" {{ $organization->plan === $key ? 'selected' : '' }}>{{ $plan['name'] }}</option>
                                    @endforeach
                                </select>
                                <select form="organization-update-{{ $organization->id }}" class="form-select mb-2" name="status">
                                    @foreach(['active', 'trialing', 'suspended', 'cancelled'] as $status)
                                        <option value="{{ $status }}" {{ $organization->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                                <span class="status-pill status-{{ $organization->status }}">{{ ucfirst($organization->status) }}</span>
                            </td>
                            <td>
                                <input form="organization-update-{{ $organization->id }}" class="form-control mb-2" name="domain" value="{{ $organization->domain }}" placeholder="Domaine">
                                <input form="organization-update-{{ $organization->id }}" type="date" class="form-control" name="subscription_ends_at" value="{{ optional($organization->subscription_ends_at)->format('Y-m-d') }}">
                            </td>
                            <td>
                                <div class="mb-2">
                                    <div class="muted mb-1">Register</div>
                                    <div class="link-box">
                                        @if($organization->onboarding_token)
                                            {{ route('client.register', $organization->onboarding_token) }}
                                        @else
                                            Onboarding utilisé ou non généré.
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="muted mb-1">Login</div>
                                    <div class="link-box">{{ route('client.login.organization', $organization) }}</div>
                                </div>
                            </td>
                            <td>
                                @forelse($organization->users as $clientUser)
                                    <form method="POST" action="{{ route('platform.organizations.users.role', [$organization, $clientUser]) }}" class="role-form">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <div class="org-title">{{ $clientUser->name }}</div>
                                            <div class="muted">{{ $clientUser->email }}</div>
                                        </div>
                                        <select class="form-select" name="role">
                                            @foreach(['super_admin' => 'Admin principal', 'admin' => 'Admin', 'manager' => 'Manager', 'moderateur' => 'Modérateur'] as $role => $label)
                                                <option value="{{ $role }}" {{ $clientUser->role === $role ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn btn-dark btn-icon" type="submit" title="Enregistrer le rôle"><i class="bi bi-save"></i></button>
                                    </form>
                                @empty
                                    <div class="muted">Aucun utilisateur client.</div>
                                @endforelse
                            </td>
                            <td>
                                <button form="organization-update-{{ $organization->id }}" class="btn btn-dark btn-icon mb-2" type="submit" title="Enregistrer l'organisation">
                                    <i class="bi bi-save"></i>
                                </button>
                                <form method="POST" action="{{ route('platform.organizations.onboarding-link', $organization) }}">
                                    @csrf
                                    <button class="btn btn-outline-dark btn-icon" type="submit" title="Regénérer le lien register">
                                        <i class="bi bi-arrow-clockwise"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="muted text-center py-4">Aucune organisation trouvée.</td>
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
