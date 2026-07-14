@extends('layouts.app')

@section('title', 'Facturation')

@push('styles')
<style>
    .billing-page { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .billing-grid { display:grid; grid-template-columns:minmax(0, .9fr) minmax(0, 1.25fr); gap:18px; align-items:start; }
    .billing-card { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:22px; }
    .billing-title { color:var(--ink); font-size:clamp(1.7rem,2.5vw,2.45rem); font-weight:600; margin:0 0 8px; }
    .billing-copy, .muted { color:var(--muted); }
    .plan-line { align-items:center; display:flex; justify-content:space-between; gap:16px; border-bottom:1px solid var(--line); padding-bottom:18px; margin-bottom:18px; }
    .plan-line strong { color:var(--ink); display:block; font-size:1.2rem; font-weight:600; }
    .badge-soft { background:rgba(185,137,67,.12); border-radius:999px; color:#8a6128; font-weight:600; padding:7px 11px; }
    .form-stack { display:grid; gap:14px; }
    .form-label { color:var(--ink); font-weight:600; font-size:.86rem; }
    .form-control, .form-select { border:1px solid var(--line); border-radius:10px; min-height:44px; }
    .btn-saas { background:var(--ink); border:0; border-radius:10px; color:white; font-weight:600; min-height:44px; padding:0 16px; }
    .invoice-table { width:100%; }
    .invoice-row { align-items:center; border-bottom:1px solid var(--line); display:grid; gap:14px; grid-template-columns:1fr 120px 110px 100px; padding:14px 0; }
    .invoice-row:last-child { border-bottom:0; }
    .invoice-ref { color:var(--ink); font-weight:600; }
    .status-pill { border-radius:999px; display:inline-flex; justify-content:center; font-size:.78rem; font-weight:600; padding:6px 10px; }
    .status-pending { background:rgba(185,137,67,.14); color:#8a6128; }
    .status-paid { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-draft { background:#f0ebe2; color:#746f65; }
    @media (max-width: 1000px) { .billing-grid, .invoice-row { grid-template-columns:1fr; } }
</style>
@endpush

@section('content')
<div class="billing-page">
    <div class="mb-4">
        <div class="text-uppercase" style="color:#b98943;font-size:.76rem;font-weight:600;letter-spacing:.12em;">{{ $organization->name }} · abonnement</div>
        <h1 class="billing-title">Facturation</h1>
        <p class="billing-copy mb-0">Centralisez les informations de facturation et le suivi des factures de votre organisation.</p>
    </div>

    <div class="billing-grid">
        <section class="billing-card">
            <div class="plan-line">
                <div>
                    <span class="muted">Plan actuel</span>
                    <strong>{{ $plan['name'] }}</strong>
                </div>
                <span class="badge-soft">{{ $cycle === 'yearly' ? 'Annuel' : 'Mensuel' }}</span>
            </div>
            <p class="muted mb-3">
                Prochaine échéance:
                <strong style="color:#171713;">{{ optional($organization->subscription_ends_at)->format('d/m/Y') ?: 'Non définie' }}</strong>
            </p>
            <a href="{{ route('saas.plans') }}" class="btn btn-outline-dark w-100">
                <i class="bi bi-gem me-2"></i> Modifier le plan
            </a>
        </section>

        <section class="billing-card">
            <h2 class="h5 mb-3" style="font-weight:600;">Informations de facturation</h2>
            <form method="POST" action="{{ route('saas.billing.update') }}" class="form-stack">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom facturation</label>
                        <input class="form-control" name="billing_name" value="{{ old('billing_name', $billing['billing_name'] ?? $organization->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email facturation</label>
                        <input type="email" class="form-control" name="billing_email" value="{{ old('billing_email', $billing['billing_email'] ?? auth()->user()->email) }}" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Adresse</label>
                        <input class="form-control" name="billing_address" value="{{ old('billing_address', $billing['billing_address'] ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ville</label>
                        <input class="form-control" name="billing_city" value="{{ old('billing_city', $billing['billing_city'] ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Pays</label>
                        <input class="form-control" name="billing_country" value="{{ old('billing_country', $billing['billing_country'] ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Devise</label>
                        <select class="form-select" name="currency">
                            @foreach(['XOF', 'EUR', 'USD'] as $currency)
                                <option value="{{ $currency }}" {{ old('currency', $billing['currency'] ?? 'XOF') === $currency ? 'selected' : '' }}>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">N° fiscal / RCCM</label>
                        <input class="form-control" name="tax_number" value="{{ old('tax_number', $billing['tax_number'] ?? '') }}">
                    </div>
                </div>
                <button class="btn-saas mt-2" type="submit">
                    <i class="bi bi-save me-2"></i> Enregistrer
                </button>
            </form>
        </section>

        <section class="billing-card" style="grid-column:1 / -1;">
            <h2 class="h5 mb-1" style="font-weight:600;">Historique des factures</h2>
            <p class="muted mb-3">Les factures générées lors des changements de plan apparaissent ici.</p>
            @if($invoices->isNotEmpty())
                <div class="invoice-table">
                    @foreach($invoices as $invoice)
                        <div class="invoice-row">
                            <div>
                                <div class="invoice-ref">{{ $invoice->reference }}</div>
                                <div class="muted">{{ $invoice->description }}</div>
                            </div>
                            <div>{{ \App\Support\SaasPlans::formatPrice($invoice->amount, $invoice->currency) }}</div>
                            <div class="muted">{{ optional($invoice->due_at)->format('d/m/Y') ?: '-' }}</div>
                            <div><span class="status-pill status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="muted">Aucune facture générée pour le moment.</div>
            @endif
        </section>
    </div>
</div>
@endsection
