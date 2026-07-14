@extends('layouts.app')

@section('title', 'Plans abonnement')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .plan-card { border-top:1px solid var(--line); padding:18px 0; }
    .plan-card:first-of-type { border-top:0; }
    .muted { color:var(--muted); }
    .form-control,.form-select { border:1px solid var(--line); border-radius:10px; min-height:42px; }
    textarea.form-control { min-height:104px; }
    .btn-dark { background:#171713; border:0; border-radius:10px; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; }
    .status-on { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-off { background:rgba(164,81,74,.13); color:#a4514a; }
</style>
@endpush

@section('content')
<div class="platform">
    <div class="d-flex justify-content-between align-items-end gap-3 mb-4">
        <div>
            <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">Plateforme</div>
            <h1 class="mb-1" style="font-weight:600;">Plans d'abonnement</h1>
            <p class="muted mb-0">Le propriétaire plateforme peut gérer l'offre commerciale sans abonnement ni restriction tenant.</p>
        </div>
        <a href="{{ route('platform.organizations') }}" class="btn btn-outline-dark">Organisations</a>
    </div>

    <section class="panel mb-3">
        <h2 class="h5 mb-3" style="font-weight:600;">Créer un plan</h2>
        <form method="POST" action="{{ route('platform.plans.store') }}" class="row g-3">
            @csrf
            @include('platform.partials.plan-fields', ['plan' => null])
            <div class="col-12">
                <button class="btn btn-dark" type="submit">
                    <i class="bi bi-plus-lg me-2"></i> Créer le plan
                </button>
            </div>
        </form>
    </section>

    <section class="panel">
        <h2 class="h5 mb-3" style="font-weight:600;">Plans existants</h2>
        @foreach($plans as $plan)
            <article class="plan-card">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                    <div>
                        <strong style="color:#171713;">{{ $plan->name }}</strong>
                        <span class="muted">· {{ $plan->slug }}</span>
                    </div>
                    <span class="status-pill {{ $plan->is_active ? 'status-on' : 'status-off' }}">
                        {{ $plan->is_active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('platform.plans.update', $plan) }}" class="row g-3">
                    @csrf
                    @method('PUT')
                    @include('platform.partials.plan-fields', ['plan' => $plan])
                    <div class="col-12">
                        <button class="btn btn-dark" type="submit">
                            <i class="bi bi-save me-2"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </article>
        @endforeach
    </section>
</div>
@endsection
