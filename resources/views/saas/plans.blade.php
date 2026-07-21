@extends('layouts.app')

@section('title', 'Plans SaaS')

@php
    $currentPlan = $organization->plan ?? 'starter';
@endphp

@push('styles')
<style>
    .saas-page {
        --ink: #171713;
        --muted: #746f65;
        --line: #dfd7cb;
        --panel: #fffefa;
        --soft: #f8f4ec;
        --gold: #b98943;
        max-width: 1320px;
        margin: 0 auto;
        color: #2c2a25;
    }

    .subscription-modal {
        --ink: #171713;
        --muted: #746f65;
        --line: #dfd7cb;
        --panel: #fffefa;
        --soft: #f8f4ec;
        --gold: #b98943;
    }

    .saas-header {
        align-items: end;
        display: flex;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 22px;
    }

    .saas-kicker {
        color: var(--gold);
        font-size: .76rem;
        font-weight: 600;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .saas-title {
        color: var(--ink);
        font-size: clamp(1.7rem, 2.5vw, 2.5rem);
        font-weight: 600;
        margin: 6px 0;
    }

    .saas-copy {
        color: var(--muted);
        margin: 0;
        max-width: 720px;
    }

    .plan-grid {
        display: grid;
        gap: 16px;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .plan-card {
        background: var(--panel);
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 18px 42px rgba(39, 33, 25, .055);
        display: flex;
        flex-direction: column;
        min-height: 100%;
        padding: 22px;
    }

    .plan-card.active {
        border-color: rgba(185, 137, 67, .75);
        box-shadow: 0 22px 54px rgba(126, 89, 36, .14);
    }

    .plan-top {
        align-items: center;
        display: flex;
        justify-content: space-between;
        gap: 12px;
    }

    .plan-name {
        color: var(--ink);
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0;
    }

    .plan-badge {
        background: rgba(185, 137, 67, .12);
        border-radius: 999px;
        color: #8a6128;
        font-size: .74rem;
        font-weight: 600;
        padding: 6px 10px;
    }

    .plan-price {
        color: var(--ink);
        font-size: 2rem;
        font-weight: 600;
        margin-top: 18px;
    }

    .plan-price span {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 500;
    }

    .plan-tagline {
        color: var(--muted);
        min-height: 48px;
        margin: 10px 0 18px;
    }

    .feature-list {
        display: grid;
        gap: 10px;
        margin: 0 0 20px;
        padding: 0;
        list-style: none;
    }

    .feature-list li {
        align-items: flex-start;
        color: #3a362f;
        display: flex;
        gap: 9px;
        font-size: .92rem;
    }

    .feature-list i {
        color: var(--gold);
        margin-top: 2px;
    }

    .plan-form {
        margin-top: auto;
    }

    .cycle-select {
        border: 1px solid var(--line);
        border-radius: 10px;
        margin-bottom: 12px;
        padding: 10px 12px;
        width: 100%;
    }

    .modal-content {
        border: 0;
        border-radius: 18px;
    }

    .modal-header,
    .modal-footer {
        border-color: var(--line);
    }

    .payment-summary {
        background: var(--soft);
        border: 1px solid var(--line);
        border-radius: 14px;
        display: grid;
        gap: 8px;
        margin-bottom: 16px;
        padding: 14px;
    }

    .payment-summary strong {
        color: var(--ink);
        font-size: 1.35rem;
        font-weight: 600;
    }

    .saas-btn {
        align-items: center;
        background: var(--ink);
        border: 0;
        border-radius: 10px;
        color: white;
        display: inline-flex;
        font-weight: 600;
        gap: 8px;
        justify-content: center;
        min-height: 44px;
        padding: 0 16px;
        text-decoration: none;
        width: 100%;
    }

    .saas-btn.secondary {
        background: var(--soft);
        border: 1px solid var(--line);
        color: var(--ink);
    }

    @media (max-width: 1100px) {
        .plan-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="saas-page">
    <div class="saas-header">
        <div>
            <div class="saas-kicker">{{ $organization->name }} · abonnement</div>
            <h1 class="saas-title">Plans SaaS</h1>
            <p class="saas-copy">Choisissez le niveau qui correspond au volume d'événements, d'utilisateurs et d'invitations de votre organisation.</p>
        </div>
        <a href="{{ route('saas.billing') }}" class="saas-btn secondary" style="width: auto;">
            <i class="bi bi-receipt"></i>
            Facturation
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    @foreach(['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }}">{{ session($key) }}</div>
        @endif
    @endforeach

    <div class="plan-grid">
        @foreach($plans as $key => $plan)
            @php($planCanCoverUsage = \App\Support\SaasUsage::planCanCoverCurrentUsage($organization, $plan))
            <article class="plan-card {{ $currentPlan === $key ? 'active' : '' }}">
                <div class="plan-top">
                    <h2 class="plan-name">{{ $plan['name'] }}</h2>
                    @if($currentPlan === $key)
                        <span class="plan-badge">Plan actuel</span>
                    @endif
                </div>

                <div class="plan-price">
                    {{ \App\Support\SaasPlans::formatPrice($plan['monthly_price'], $plan['currency']) }}
                    <span>/ mois</span>
                </div>
                <p class="plan-tagline">{{ $plan['tagline'] }}</p>

                <ul class="feature-list">
                    <li><i class="bi bi-calendar2-check"></i> {{ $plan['limits']['events'] ? $plan['limits']['events'] . ' événements' : 'Événements illimités' }}</li>
                    <li><i class="bi bi-people"></i> {{ $plan['limits']['users'] ? $plan['limits']['users'] . ' utilisateurs' : 'Utilisateurs illimités' }}</li>
                    <li><i class="bi bi-send"></i> {{ $plan['limits']['invitations'] ? number_format($plan['limits']['invitations'], 0, ',', ' ') . ' invitations' : 'Invitations illimitées' }}</li>
                    @foreach($plan['features'] as $feature)
                        <li><i class="bi bi-check2"></i> {{ $feature }}</li>
                    @endforeach
                </ul>

                <div class="plan-form">
                    @if($planCanCoverUsage)
                        <button type="button" class="saas-btn" data-bs-toggle="modal" data-bs-target="#planPaymentModal{{ $loop->index }}">
                            <i class="bi bi-arrow-right-circle"></i>
                            {{ $currentPlan === $key ? 'Renouveler ce plan' : 'Choisir ce plan' }}
                        </button>
                    @else
                        <button type="button" class="saas-btn" disabled style="opacity:.55;cursor:not-allowed;">
                            <i class="bi bi-lock"></i>
                            Usage actuel trop élevé
                        </button>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</div>

@foreach($plans as $key => $plan)
    @php($defaultAmount = \App\Support\SaasPlans::price($plan, $cycle))
    <div class="modal fade subscription-modal" id="planPaymentModal{{ $loop->index }}" tabindex="-1" aria-labelledby="planPaymentModalLabel{{ $loop->index }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('saas.plans.update') }}" class="subscription-payment-form">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">

                    <div class="modal-header">
                        <div>
                            <div class="saas-kicker">{{ $currentPlan === $key ? 'Renouvellement' : 'Souscription' }}</div>
                            <h2 class="modal-title h5 mb-0" id="planPaymentModalLabel{{ $loop->index }}" style="font-weight:600;">{{ $plan['name'] }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>

                    <div class="modal-body">
                        <div class="payment-summary">
                            <span class="muted">Montant à valider</span>
                            <strong class="payment-amount-label">{{ \App\Support\SaasPlans::formatPrice($defaultAmount, $plan['currency']) }}</strong>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cycle</label>
                            <select name="billing_cycle" class="form-select payment-cycle-select">
                                <option value="monthly"
                                        data-amount="{{ $plan['monthly_price'] }}"
                                        data-label="{{ \App\Support\SaasPlans::formatPrice($plan['monthly_price'], $plan['currency']) }}"
                                        {{ $cycle === 'monthly' ? 'selected' : '' }}>
                                    Mensuel · {{ \App\Support\SaasPlans::formatPrice($plan['monthly_price'], $plan['currency']) }}
                                </option>
                                <option value="yearly"
                                        data-amount="{{ $plan['yearly_price'] }}"
                                        data-label="{{ \App\Support\SaasPlans::formatPrice($plan['yearly_price'], $plan['currency']) }}"
                                        {{ $cycle === 'yearly' ? 'selected' : '' }}>
                                    Annuel · {{ \App\Support\SaasPlans::formatPrice($plan['yearly_price'], $plan['currency']) }}
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Opérateur</label>
                            <select name="payment_operator" class="form-select" required>
                                <option value="">Sélectionner un opérateur</option>
                                @foreach($paymentOperators as $operatorKey => $operatorLabel)
                                    <option value="{{ $operatorKey }}">{{ $operatorLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Numéro de paiement</label>
                            <input class="form-control" name="payment_phone" placeholder="Ex: +225 07 00 00 00 00" required>
                        </div>

                        <div>
                            <label class="form-label">Montant</label>
                            <input type="number" class="form-control payment-amount-input" name="amount" value="{{ $defaultAmount }}" min="1" required>
                            <div class="form-text">Le montant doit correspondre au prix du cycle sélectionné.</div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="saas-btn" style="width:auto;">
                            <i class="bi bi-check2-circle"></i>
                            Valider
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    document.querySelectorAll('.subscription-payment-form').forEach((form) => {
        const cycleSelect = form.querySelector('.payment-cycle-select');
        const amountInput = form.querySelector('.payment-amount-input');
        const amountLabel = form.querySelector('.payment-amount-label');

        const syncAmount = () => {
            const selectedOption = cycleSelect.options[cycleSelect.selectedIndex];
            amountInput.value = selectedOption.dataset.amount;
            amountLabel.textContent = selectedOption.dataset.label;
        };

        cycleSelect.addEventListener('change', syncAmount);
        syncAmount();
    });
</script>
@endpush
@endsection
