@extends('layouts.app')

@section('title', 'Facturation')

@use('App\Support\SaasPlans')

@php
    $endsAt = $organization->subscription_ends_at;
    $daysLeft = $endsAt ? (int) now()->startOfDay()->diffInDays($endsAt->copy()->startOfDay(), false) : null;
    $paid = $invoices->where('status', 'paid');
    $totalPaid = $paid->groupBy('currency')->map->sum('amount');
    $yearPaid = $paid->filter(fn ($i) => optional($i->paid_at ?? $i->created_at)->isCurrentYear())->sum('amount');
    $statusLabels = ['paid' => ['Payée', 'success'], 'pending' => ['En attente', 'warning'], 'draft' => ['Brouillon', ''], 'failed' => ['Échouée', 'danger'], 'cancelled' => ['Annulée', '']];
    $typeOf = fn ($invoice) => ($invoice->metadata['type'] ?? 'subscription') === 'communication_credits' ? 'credits' : 'subscription';
    $counts = $invoices->countBy(fn ($i) => $typeOf($i));
    $billingErrors = $errors->hasAny(['billing_name', 'billing_email', 'billing_address', 'billing_city', 'billing_country', 'tax_number', 'currency']);
@endphp

@push('styles')
<style>
    .billing-page { margin: 0 auto; max-width: 1320px; }
    .billing-page *, .billing-page *::before, .billing-page *::after { min-width: 0; }
    .billing-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 360px; }
    .hero-stat { background: radial-gradient(90% 140% at 0% 0%, rgba(185, 137, 67, .35), transparent 60%), #141412; border: 0; color: #fff; }
    .hero-stat .ui-stat-top { color: rgba(255, 255, 255, .6); }
    .hero-stat .ui-stat-top i { background: rgba(255, 255, 255, .1); color: #d8b978; }
    .hero-stat strong { color: #fff; }
    .hero-stat small { color: rgba(255, 255, 255, .55); }
    .type-tabs { background: rgba(23, 23, 19, .05); border-radius: 999px; display: inline-flex; gap: 2px; padding: 3px; }
    .type-tabs button { background: transparent; border: 0; border-radius: 999px; color: var(--app-muted); font-size: .8rem; font-weight: 600; padding: 6px 12px; }
    .type-tabs button.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 10px -4px rgba(23, 23, 19, .14); color: var(--app-ink); }
    .inv-icon { align-items: center; border-radius: 10px; display: flex; flex: 0 0 36px; font-size: .95rem; height: 36px; justify-content: center; width: 36px; }
    .inv-icon.subscription { background: var(--app-gold-soft); color: var(--app-gold-deep); }
    .inv-icon.credits { background: rgba(47, 109, 181, .1); color: #2f6db5; }
    .amount { color: var(--app-ink); font-variant-numeric: tabular-nums; font-weight: 650; white-space: nowrap; }
    .ref { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .74rem; }
    .side { display: grid; gap: 20px; position: sticky; top: 84px; }
    .info-saved { align-items: center; color: var(--app-green); display: flex; font-size: .78rem; gap: 6px; }
    tr[hidden] { display: none; }
    @media (max-width: 1099.98px) { .billing-grid { grid-template-columns: 1fr; } .side { position: static; } }
    @media (max-width: 767.98px) {
        .inv-table thead { display: none; }
        .inv-table tr { border-bottom: 1px solid var(--app-line); display: grid; gap: 6px; grid-template-columns: 1fr auto; padding: 14px 18px; }
        .inv-table tr[hidden] { display: none; }
        .inv-table td { border: 0 !important; padding: 0 !important; }
        .inv-table td.col-hide { display: none; }
        .inv-table td.col-amount { grid-column: 2; grid-row: 1; text-align: right; }
    }
</style>
@endpush

@section('content')
<div class="billing-page">
    <x-app.page-header kicker="Abonnement" title="Facturation" subtitle="Vos paiements, votre échéance et les informations qui figurent sur vos factures.">
        <x-slot:actions>
            <a href="{{ route('saas.plans') }}" class="ui-btn ui-btn-primary"><i class="bi bi-gem"></i>Changer de forfait</a>
        </x-slot:actions>
    </x-app.page-header>

    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

    <section class="ui-stats">
        <article class="ui-stat hero-stat">
            <div class="ui-stat-top">Forfait actuel<i class="bi bi-gem"></i></div>
            <strong>{{ $plan['name'] }}</strong>
            <small>{{ $cycle === 'yearly' ? 'Cycle annuel' : 'Cycle mensuel' }} · {{ SaasPlans::formatPrice(SaasPlans::price($plan, $cycle), $plan['currency']) }}</small>
        </article>
        <article class="ui-stat" style="--accent: {{ $daysLeft !== null && $daysLeft < 7 ? 'var(--app-red)' : 'var(--app-green)' }}">
            <div class="ui-stat-top">Prochaine échéance<i class="bi bi-calendar-check"></i></div>
            <strong>{{ $endsAt ? $endsAt->locale('fr')->translatedFormat('d M Y') : '—' }}</strong>
            <small>{{ $daysLeft === null ? 'Aucune période active' : ($daysLeft < 0 ? 'Expirée depuis ' . abs($daysLeft) . ' jour(s)' : $daysLeft . ' jour(s) restant(s)') }}</small>
        </article>
        <article class="ui-stat" style="--accent: var(--app-gold)">
            <div class="ui-stat-top">Payé cette année<i class="bi bi-cash-stack"></i></div>
            <strong>{{ number_format($yearPaid, 0, ',', ' ') }} <span style="font-size: .9rem; color: var(--app-muted)">{{ $plan['currency'] }}</span></strong>
            <small>{{ $paid->count() }} paiement(s) dans l’historique</small>
        </article>
        <article class="ui-stat" style="--accent: var(--app-blue)">
            <div class="ui-stat-top">Opérations<i class="bi bi-receipt"></i></div>
            <strong>{{ $invoices->count() }}</strong>
            <small>{{ $counts['subscription'] ?? 0 }} abonnement(s) · {{ $counts['credits'] ?? 0 }} achat(s) de crédits</small>
        </article>
    </section>

    <div class="billing-grid">
        <section class="ui-card" style="overflow: hidden">
            <div class="ui-card-head">
                <div><h2>Historique des paiements</h2><p>Les 12 dernières opérations de votre organisation.</p></div>
                @if($invoices->isNotEmpty())
                    <div class="type-tabs" role="group" aria-label="Type d’opération">
                        <button type="button" class="is-active" data-type="">Tout</button>
                        <button type="button" data-type="subscription">Abonnements</button>
                        <button type="button" data-type="credits">Crédits</button>
                    </div>
                @endif
            </div>
            @if($invoices->isNotEmpty())
                <div class="table-responsive">
                    <table class="ui-table inv-table">
                        <thead><tr><th>Opération</th><th>Période</th><th>Moyen de paiement</th><th>Statut</th><th class="text-end">Montant</th></tr></thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                @php
                                    $meta = $invoice->metadata ?? [];
                                    $type = $typeOf($invoice);
                                    [$statusLabel, $statusTone] = $statusLabels[$invoice->status] ?? [ucfirst($invoice->status), ''];
                                    $title = $type === 'credits'
                                        ? 'Crédits ' . strtoupper($meta['channel'] ?? '') . ' · ' . number_format((int) ($meta['quantity'] ?? 0), 0, ',', ' ')
                                        : ($meta['plan_name'] ?? $invoice->description) . ' · ' . (($meta['billing_cycle'] ?? $cycle) === 'yearly' ? 'annuel' : 'mensuel');
                                @endphp
                                <tr data-row-type="{{ $type }}">
                                    <td>
                                        <div class="ui-person">
                                            <span class="inv-icon {{ $type }}"><i class="bi {{ $type === 'credits' ? 'bi-chat-dots' : 'bi-gem' }}"></i></span>
                                            <div>
                                                <strong>{{ $title }}</strong>
                                                <small>{{ $type === 'credits' ? 'Achat de crédits' : ((($meta['action'] ?? null) === 'renewal') ? 'Renouvellement' : 'Souscription') }} · {{ optional($invoice->paid_at ?? $invoice->created_at)->format('d/m/Y H:i') }} · <span class="ref">{{ $invoice->reference }}</span></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="col-hide">{{ $invoice->period_start ? $invoice->period_start->format('d/m/Y') . ' → ' . optional($invoice->period_end)->format('d/m/Y') : '—' }}</td>
                                    <td class="col-hide">{{ $meta['payment_operator_label'] ?? '—' }}<span class="ui-sub">{{ $meta['payment_phone'] ?? '' }}</span></td>
                                    <td class="col-hide"><span class="ui-pill dot {{ $statusTone }}">{{ $statusLabel }}</span></td>
                                    <td class="text-end col-amount"><span class="amount">{{ SaasPlans::formatPrice((int) $invoice->amount, $invoice->currency) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="ui-empty">
                    <i class="bi bi-receipt"></i>
                    <strong>Aucun paiement enregistré</strong>Choisissez un forfait pour créer la première opération.
                    <div class="mt-3"><a href="{{ route('saas.plans') }}" class="ui-btn ui-btn-primary"><i class="bi bi-gem"></i>Voir les forfaits</a></div>
                </div>
            @endif
        </section>

        <aside class="side">
            <section class="ui-card">
                <div class="ui-card-head has-border">
                    <div><h2>Informations de facturation</h2><p>Elles apparaissent sur vos factures.</p></div>
                    @if(!empty($billing['billing_name']) && !$billingErrors)<span class="info-saved"><i class="bi bi-check-circle-fill"></i>Enregistrées</span>@endif
                </div>
                <form method="POST" action="{{ route('saas.billing.update') }}" class="ui-card-body" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" for="billing_name">Raison sociale <span class="text-danger">*</span></label>
                            <input class="form-control @error('billing_name') is-invalid @enderror" id="billing_name" name="billing_name" value="{{ old('billing_name', $billing['billing_name'] ?? $organization->name) }}" autocomplete="organization" required>
                            @error('billing_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="billing_email">Email de facturation <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('billing_email') is-invalid @enderror" id="billing_email" name="billing_email" value="{{ old('billing_email', $billing['billing_email'] ?? auth()->user()->email) }}" autocomplete="email" required>
                            @error('billing_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="billing_address">Adresse</label>
                            <input class="form-control @error('billing_address') is-invalid @enderror" id="billing_address" name="billing_address" value="{{ old('billing_address', $billing['billing_address'] ?? '') }}" autocomplete="street-address">
                            @error('billing_address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="billing_city">Ville</label>
                            <input class="form-control @error('billing_city') is-invalid @enderror" id="billing_city" name="billing_city" value="{{ old('billing_city', $billing['billing_city'] ?? '') }}">
                            @error('billing_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="billing_country">Pays</label>
                            <input class="form-control @error('billing_country') is-invalid @enderror" id="billing_country" name="billing_country" value="{{ old('billing_country', $billing['billing_country'] ?? '') }}">
                            @error('billing_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-7">
                            <label class="form-label" for="tax_number">N° fiscal / RCCM</label>
                            <input class="form-control @error('tax_number') is-invalid @enderror" id="tax_number" name="tax_number" value="{{ old('tax_number', $billing['tax_number'] ?? '') }}">
                            @error('tax_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-5">
                            <label class="form-label" for="currency">Devise <span class="text-danger">*</span></label>
                            <select class="form-select @error('currency') is-invalid @enderror" id="currency" name="currency" required>
                                @foreach(['XOF' => 'XOF', 'EUR' => 'EUR', 'USD' => 'USD'] as $code => $label)
                                    <option value="{{ $code }}" @selected(old('currency', $billing['currency'] ?? 'XOF') === $code)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <button type="submit" class="ui-btn ui-btn-primary w-100"><i class="bi bi-check2"></i>Enregistrer</button>
                        </div>
                    </div>
                </form>
            </section>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-type]').forEach((button) => button.addEventListener('click', () => {
        document.querySelectorAll('[data-type]').forEach((b) => b.classList.toggle('is-active', b === button));
        document.querySelectorAll('[data-row-type]').forEach((row) => row.hidden = button.dataset.type !== '' && row.dataset.rowType !== button.dataset.type);
    }));
</script>
@endpush
