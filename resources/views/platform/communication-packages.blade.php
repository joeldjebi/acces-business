@extends('layouts.app')

@section('title', 'Packages messages')

@push('styles')
<style>
    .platform { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .head { display:flex; justify-content:space-between; gap:18px; align-items:flex-end; margin-bottom:20px; }
    .kicker { color:var(--gold); font-size:.76rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; }
    .title { color:var(--ink); font-size:clamp(1.8rem,3vw,2.6rem); font-weight:600; margin:6px 0; }
    .muted { color:var(--muted); }
    .panel { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .grid { display:grid; gap:18px; grid-template-columns:360px minmax(0,1fr); align-items:start; }
    .form-control,.form-select { border:1px solid var(--line); border-radius:12px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:12px; }
    .table-wrap { overflow:auto; }
    .simple-table { border-collapse:separate; border-spacing:0; min-width:820px; width:100%; }
    .simple-table th { color:var(--muted); font-size:.72rem; letter-spacing:.08em; padding:0 10px 10px; text-transform:uppercase; }
    .simple-table td { border-top:1px solid var(--line); padding:12px 10px; vertical-align:middle; }
    .status-pill { border-radius:999px; display:inline-flex; font-size:.78rem; font-weight:600; padding:6px 10px; }
    .status-on { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-off { background:rgba(185,137,67,.14); color:#8a6128; }
    @media(max-width:1000px){ .head,.grid{grid-template-columns:1fr;display:grid;align-items:start;} }
</style>
@endpush

@section('content')
<div class="platform">
    <header class="head">
        <div>
            <div class="kicker">Super admin plateforme</div>
            <h1 class="title">Packages messages</h1>
            <p class="muted mb-0">Définissez le prix unitaire d’un SMS, email ou message WhatsApp, ainsi que la quantité minimale achetable.</p>
        </div>
    </header>

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }}">{{ session($key) }}</div>
        @endif
    @endforeach

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="grid">
        <section class="panel">
            <h2 class="h5 mb-3">Nouveau package</h2>
            <form method="POST" action="{{ route('platform.communication-packages.store') }}" class="row g-3">
                @csrf
                @include('platform.partials.communication-package-fields', ['package' => null, 'channels' => $channels])
                <div class="col-12">
                    <button class="btn btn-dark w-100" type="submit">
                        <i class="bi bi-plus-circle me-2"></i> Créer
                    </button>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="table-wrap">
                <table class="simple-table">
                    <thead>
                        <tr>
                            <th>Package</th>
                            <th>Canal</th>
                            <th>Prix unité</th>
                            <th>Minimum</th>
                            <th>Statut</th>
                            <th>Modifier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                            <tr>
                                <td><strong>{{ $package->name }}</strong></td>
                                <td>{{ $channels[$package->channel] ?? strtoupper($package->channel) }}</td>
                                <td>{{ number_format($package->unit_price, 0, ',', ' ') }} {{ $package->currency }}</td>
                                <td>{{ number_format($package->minimum_quantity, 0, ',', ' ') }}</td>
                                <td><span class="status-pill {{ $package->is_active ? 'status-on' : 'status-off' }}">{{ $package->is_active ? 'Actif' : 'Inactif' }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('platform.communication-packages.update', $package) }}" class="row g-2">
                                        @csrf
                                        @method('PUT')
                                        @include('platform.partials.communication-package-fields', ['package' => $package, 'channels' => $channels])
                                        <div class="col-12">
                                            <button class="btn btn-dark btn-sm" type="submit"><i class="bi bi-save me-1"></i> Enregistrer</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center muted py-4">Aucun package créé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
