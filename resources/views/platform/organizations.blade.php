@extends('layouts.app')

@section('title', 'Organisations')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .org-card { border-top:1px solid var(--line); padding:18px 0; }
    .org-card:first-of-type { border-top:0; }
    .org-title { color:var(--ink); font-weight:600; }
    .muted { color:var(--muted); }
    .form-control,.form-select { border:1px solid var(--line); border-radius:10px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:10px; }
    .link-box { background:#f8f4ec; border:1px solid var(--line); border-radius:10px; color:#171713; font-size:.84rem; padding:10px 12px; word-break:break-all; }
    .user-role-row { align-items:end; display:grid; gap:10px; grid-template-columns:minmax(0,1fr) 160px 52px; margin-top:10px; }
    @media (max-width: 900px) { .user-role-row { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">Plateforme</div>
            <h1 class="mb-1" style="font-weight:600;">Organisations</h1>
            <p class="muted mb-0">Gestion des tenants, plans, statuts et domaines.</p>
        </div>
        <a href="{{ route('platform.dashboard') }}" class="btn btn-outline-dark">Dashboard plateforme</a>
    </div>

    <section class="panel mb-3">
        <h2 class="h5 mb-3" style="font-weight:600;">Créer un client</h2>
        <form method="POST" action="{{ route('platform.organizations.store') }}" class="row g-3 align-items-end mb-4">
            @csrf
            <div class="col-lg-3">
                <label class="form-label">Organisation / entreprise / agence</label>
                <input class="form-control" name="name" value="{{ old('name') }}" required placeholder="Nom du client">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Slug</label>
                <input class="form-control" name="slug" value="{{ old('slug') }}" placeholder="auto si vide">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Plan</label>
                <select class="form-select" name="plan">
                    @foreach($plans as $key => $plan)
                        <option value="{{ $key }}">{{ $plan['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Domaine</label>
                <input class="form-control" name="domain" value="{{ old('domain') }}" placeholder="optionnel">
            </div>
            <div class="col-lg-2">
                <button class="btn btn-dark w-100" type="submit">
                    <i class="bi bi-plus-lg me-2"></i> Créer
                </button>
            </div>
        </form>

        <hr>

        <form class="row g-3">
            <div class="col-md-8">
                <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Rechercher nom, slug ou domaine">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="status">
                    <option value="">Tous les statuts</option>
                    @foreach(['active', 'trialing', 'suspended', 'cancelled'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-dark w-100" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </form>
    </section>

    <section class="panel">
        @foreach($organizations as $organization)
            <article class="org-card">
                <form method="POST" action="{{ route('platform.organizations.update', $organization) }}" class="row g-3 align-items-end">
                    @csrf
                    @method('PUT')
                    <div class="col-lg-3">
                        <label class="form-label">Organisation</label>
                        <input class="form-control" name="name" value="{{ $organization->name }}" required>
                        <div class="muted mt-1">{{ $organization->users_count }} user(s) · {{ $organization->events_count }} événement(s)</div>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Plan</label>
                        <select class="form-select" name="plan">
                            @foreach($plans as $key => $plan)
                                <option value="{{ $key }}" {{ $organization->plan === $key ? 'selected' : '' }}>{{ $plan['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Statut</label>
                        <select class="form-select" name="status">
                            @foreach(['active', 'trialing', 'suspended', 'cancelled'] as $status)
                                <option value="{{ $status }}" {{ $organization->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Domaine</label>
                        <input class="form-control" name="domain" value="{{ $organization->domain }}">
                    </div>
                    <div class="col-lg-2">
                        <label class="form-label">Fin abonnement</label>
                        <input type="date" class="form-control" name="subscription_ends_at" value="{{ optional($organization->subscription_ends_at)->format('Y-m-d') }}">
                    </div>
                    <div class="col-lg-1">
                        <button class="btn btn-dark w-100" type="submit"><i class="bi bi-save"></i></button>
                    </div>
                </form>

                <div class="row g-3 mt-2">
                    <div class="col-lg-6">
                        <label class="form-label">Lien register client</label>
                        <div class="link-box">
                            @if($organization->onboarding_token)
                                {{ route('client.register', $organization->onboarding_token) }}
                            @else
                                Onboarding déjà utilisé ou non généré.
                            @endif
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label">Lien login client</label>
                        <div class="link-box">{{ route('login', ['organization' => $organization->slug]) }}</div>
                    </div>
                    <div class="col-lg-1 d-flex align-items-end">
                        <form method="POST" action="{{ route('platform.organizations.onboarding-link', $organization) }}" class="w-100">
                            @csrf
                            <button class="btn btn-outline-dark w-100" type="submit" title="Regénérer le lien register">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Rôles et permissions du client</label>
                    @forelse($organization->users as $clientUser)
                        <form method="POST" action="{{ route('platform.organizations.users.role', [$organization, $clientUser]) }}" class="user-role-row">
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
                            <button class="btn btn-dark" type="submit"><i class="bi bi-save"></i></button>
                        </form>
                    @empty
                        <div class="muted">Aucun utilisateur client. Envoyez le lien register au client pour créer le premier admin.</div>
                    @endforelse
                </div>
            </article>
        @endforeach

        <div class="mt-3">
            {{ $organizations->links() }}
        </div>
    </section>
</div>
@endsection
