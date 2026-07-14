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
            </article>
        @endforeach

        <div class="mt-3">
            {{ $organizations->links() }}
        </div>
    </section>
</div>
@endsection
