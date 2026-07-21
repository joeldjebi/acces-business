@extends('layouts.app')

@section('title', 'Crédits messages')

@push('styles')
<style>
    .comm-page { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; --green:#2e7b65; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .comm-header { align-items:flex-end; display:flex; gap:18px; justify-content:space-between; margin-bottom:22px; }
    .comm-kicker { color:var(--gold); font-size:.76rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; }
    .comm-title { color:var(--ink); font-size:clamp(1.8rem,3vw,2.6rem); font-weight:600; margin:6px 0; }
    .muted { color:var(--muted); }
    .panel,.credit-card { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:20px; }
    .credit-grid { display:grid; gap:14px; grid-template-columns:repeat(3,minmax(0,1fr)); margin-bottom:18px; }
    .credit-card span { color:var(--muted); display:block; font-size:.75rem; font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
    .credit-card strong { color:var(--ink); display:block; font-size:2rem; font-weight:600; margin-top:8px; }
    .purchase-grid { display:grid; gap:18px; grid-template-columns:minmax(0,1fr) minmax(340px,.6fr); align-items:start; }
    .package-card { border:1px solid var(--line); border-radius:16px; padding:16px; }
    .package-card + .package-card { margin-top:12px; }
    .form-control,.form-select { border:1px solid var(--line); border-radius:12px; min-height:42px; }
    .btn-dark { background:#171713; border:0; border-radius:12px; }
    .history-table { min-width:760px; width:100%; }
    .history-table th { color:var(--muted); font-size:.72rem; letter-spacing:.08em; padding:0 10px 10px; text-transform:uppercase; }
    .history-table td { border-top:1px solid var(--line); padding:12px 10px; }
    @media(max-width:1000px){ .comm-header,.credit-grid,.purchase-grid{grid-template-columns:1fr;display:grid;align-items:start;} }
</style>
@endpush

@section('content')
<div class="comm-page">
    <header class="comm-header">
        <div>
            <div class="comm-kicker">{{ $organization->name }} · communications</div>
            <h1 class="comm-title">Crédits messages</h1>
            <p class="muted mb-0">Achetez des crédits SMS, WhatsApp et email pour envoyer des messages de remerciements après vos événements.</p>
        </div>
    </header>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }}">{{ session($key) }}</div>
        @endif
    @endforeach

    <section class="credit-grid">
        @foreach($channels as $channel => $label)
            @php($balance = $balances[$channel] ?? null)
            <article class="credit-card">
                <span>{{ $label }}</span>
                <strong>{{ number_format($balance?->remaining ?? 0, 0, ',', ' ') }}</strong>
                <div class="muted">{{ number_format($balance?->purchased ?? 0, 0, ',', ' ') }} acheté(s) · {{ number_format($balance?->used ?? 0, 0, ',', ' ') }} utilisé(s)</div>
            </article>
        @endforeach
    </section>

    <div class="purchase-grid">
        <section class="panel">
            <h2 class="h5 mb-3">Acheter des crédits</h2>
            @forelse($packages as $package)
                <div class="package-card">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <strong>{{ $package->name }}</strong>
                            <div class="muted">{{ $channels[$package->channel] ?? strtoupper($package->channel) }} · minimum {{ number_format($package->minimum_quantity, 0, ',', ' ') }}</div>
                        </div>
                        <div><strong>{{ number_format($package->unit_price, 0, ',', ' ') }} {{ $package->currency }}</strong><div class="muted">/ unité</div></div>
                    </div>

                    <form method="POST" action="{{ route('saas.communications.purchase') }}" class="row g-2 align-items-end">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <div class="col-md-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" class="form-control" name="quantity" min="{{ $package->minimum_quantity }}" value="{{ $package->minimum_quantity }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Opérateur</label>
                            <select class="form-select" name="payment_operator" required>
                                <option value="">Choisir</option>
                                @foreach($paymentOperators as $key => $operator)
                                    <option value="{{ $key }}">{{ $operator }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Numéro</label>
                            <input class="form-control" name="payment_phone" placeholder="+225..." required>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button class="btn btn-dark" type="submit">Acheter</button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="muted">Aucun package actif. Contactez le super admin plateforme.</div>
            @endforelse
        </section>

        <section class="panel">
            <h2 class="h5 mb-3">Derniers achats</h2>
            <div class="table-responsive">
                <table class="history-table">
                    <thead><tr><th>Date</th><th>Canal</th><th>Quantité</th><th>Montant</th></tr></thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            @php($meta = $invoice->metadata ?? [])
                            <tr>
                                <td>{{ optional($invoice->paid_at ?? $invoice->created_at)->format('d/m/Y') }}</td>
                                <td>{{ $channels[$meta['channel'] ?? ''] ?? '-' }}</td>
                                <td>{{ number_format((int) ($meta['quantity'] ?? 0), 0, ',', ' ') }}</td>
                                <td>{{ number_format($invoice->amount, 0, ',', ' ') }} {{ $invoice->currency }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="muted text-center py-4">Aucun achat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
@endsection
