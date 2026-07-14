@extends('layouts.app')

@section('title', 'Facturation')

@push('styles')
<style>
    .billing-page { --ink:#171713; --muted:#746f65; --line:#dfd7cb; --panel:#fffefa; --soft:#f8f4ec; --gold:#b98943; max-width:1320px; margin:0 auto; color:#2c2a25; }
    .billing-header { align-items:end; display:flex; justify-content:space-between; gap:20px; margin-bottom:22px; }
    .billing-kicker { color:var(--gold); font-size:.76rem; font-weight:600; letter-spacing:.12em; text-transform:uppercase; }
    .billing-title { color:var(--ink); font-size:clamp(1.7rem,2.5vw,2.45rem); font-weight:600; margin:6px 0; }
    .billing-copy,.muted { color:var(--muted); }
    .summary-grid { display:grid; gap:16px; grid-template-columns:repeat(3,minmax(0,1fr)); margin-bottom:18px; }
    .billing-card { background:var(--panel); border:1px solid var(--line); border-radius:18px; box-shadow:0 18px 42px rgba(39,33,25,.055); padding:22px; }
    .metric-label { color:var(--muted); display:block; font-size:.76rem; font-weight:600; letter-spacing:.09em; text-transform:uppercase; }
    .metric-value { color:var(--ink); display:block; font-size:1.55rem; font-weight:600; margin-top:8px; }
    .btn-saas { align-items:center; background:var(--ink); border:0; border-radius:10px; color:white; display:inline-flex; font-weight:600; gap:8px; justify-content:center; min-height:44px; padding:0 16px; text-decoration:none; }
    .btn-saas.secondary { background:var(--soft); border:1px solid var(--line); color:var(--ink); }
    .history-table { border-collapse:separate; border-spacing:0; width:100%; }
    .history-table th { color:var(--muted); font-size:.72rem; font-weight:600; letter-spacing:.08em; padding:0 12px 12px; text-transform:uppercase; white-space:nowrap; }
    .history-table td { border-top:1px solid var(--line); padding:14px 12px; vertical-align:middle; }
    .history-ref { color:var(--ink); font-weight:600; }
    .status-pill { border-radius:999px; display:inline-flex; justify-content:center; font-size:.78rem; font-weight:600; padding:6px 10px; white-space:nowrap; }
    .status-paid { background:rgba(46,123,101,.12); color:#2e7b65; }
    .status-pending { background:rgba(185,137,67,.14); color:#8a6128; }
    .status-draft { background:#f0ebe2; color:#746f65; }
    .empty-state { align-items:center; background:var(--soft); border:1px dashed var(--line); border-radius:16px; display:grid; gap:10px; justify-items:center; padding:42px 20px; text-align:center; }
    .empty-state i { color:var(--gold); font-size:2rem; }
    @media (max-width: 1000px) { .billing-header,.summary-grid { grid-template-columns:1fr; display:grid; } .history-table { min-width:860px; } }
</style>
@endpush

@section('content')
<div class="billing-page">
    <div class="billing-header">
        <div>
            <div class="billing-kicker">{{ $organization->name }} · abonnements</div>
            <h1 class="billing-title">Historique des abonnements</h1>
            <p class="billing-copy mb-0">Retrouvez les souscriptions et renouvellements validés pour votre organisation.</p>
        </div>
        <a href="{{ route('saas.plans') }}" class="btn-saas secondary">
            <i class="bi bi-gem"></i>
            Choisir ou renouveler
        </a>
    </div>

    <section class="summary-grid">
        <article class="billing-card">
            <span class="metric-label">Plan actuel</span>
            <strong class="metric-value">{{ $plan['name'] }}</strong>
            <div class="muted mt-2">{{ $cycle === 'yearly' ? 'Cycle annuel' : 'Cycle mensuel' }}</div>
        </article>
        <article class="billing-card">
            <span class="metric-label">Échéance</span>
            <strong class="metric-value">{{ optional($organization->subscription_ends_at)->format('d/m/Y') ?: 'Non définie' }}</strong>
            <div class="muted mt-2">Date de fin de la période active</div>
        </article>
        <article class="billing-card">
            <span class="metric-label">Paiements</span>
            <strong class="metric-value">{{ $invoices->count() }}</strong>
            <div class="muted mt-2">Dernières opérations enregistrées</div>
        </article>
    </section>

    <section class="billing-card">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <h2 class="h5 mb-1" style="font-weight:600;">Journal des abonnements</h2>
                <p class="muted mb-0">Chaque ligne correspond à un choix ou renouvellement de plan validé.</p>
            </div>
        </div>

        @if($invoices->isNotEmpty())
            <div class="table-responsive">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Plan</th>
                            <th>Cycle</th>
                            <th>Période</th>
                            <th>Opérateur</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            @php($metadata = $invoice->metadata ?? [])
                            <tr>
                                <td>
                                    <div class="history-ref">{{ $invoice->reference }}</div>
                                    <div class="muted">{{ optional($invoice->paid_at ?? $invoice->created_at)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <strong>{{ $metadata['plan_name'] ?? $invoice->description }}</strong>
                                    <div class="muted">{{ ($metadata['action'] ?? null) === 'renewal' ? 'Renouvellement' : 'Souscription' }}</div>
                                </td>
                                <td>{{ ($metadata['billing_cycle'] ?? $cycle) === 'yearly' ? 'Annuel' : 'Mensuel' }}</td>
                                <td>
                                    {{ optional($invoice->period_start)->format('d/m/Y') ?: '-' }}
                                    <span class="muted">→</span>
                                    {{ optional($invoice->period_end)->format('d/m/Y') ?: '-' }}
                                </td>
                                <td>
                                    {{ $metadata['payment_operator_label'] ?? '-' }}
                                    @if(!empty($metadata['payment_phone']))
                                        <div class="muted">{{ $metadata['payment_phone'] }}</div>
                                    @endif
                                </td>
                                <td>{{ \App\Support\SaasPlans::formatPrice($invoice->amount, $invoice->currency) }}</td>
                                <td><span class="status-pill status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <i class="bi bi-receipt"></i>
                <div>
                    <strong>Aucun abonnement enregistré</strong>
                    <div class="muted">Choisissez un plan pour créer la première ligne d’historique.</div>
                </div>
                <a href="{{ route('saas.plans') }}" class="btn-saas">
                    <i class="bi bi-arrow-right-circle"></i>
                    Voir les plans
                </a>
            </div>
        @endif
    </section>
</div>
@endsection
