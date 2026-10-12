@extends('layouts.app')

@section('title', 'Plans SaaS')
@section('hide_errors', '1')

@use('App\Support\SaasPlans')
@use('App\Support\SaasUsage')

@php
    $currentPlan = $organization->plan ?? 'starter';
    $current = SaasPlans::get($currentPlan);
    $usage = $quota['usage'] ?? ['events' => 0, 'users' => 0, 'invitations' => 0];
    $endsAt = $organization->subscription_ends_at;
    $daysLeft = $endsAt ? (int) now()->startOfDay()->diffInDays($endsAt->copy()->startOfDay(), false) : null;
    $planKeys = array_keys($plans);
    $featuredKey = $planKeys[1] ?? $planKeys[0] ?? null;
    $maxSaving = collect($plans)->map(fn ($p) => $p['monthly_price'] > 0 ? round((1 - $p['yearly_price'] / ($p['monthly_price'] * 12)) * 100) : 0)->max();
    $modalToOpen = old('plan');
    $operatorIcons = ['orange_money' => 'bi-phone', 'mtn_money' => 'bi-phone', 'moov_money' => 'bi-phone', 'wave' => 'bi-water', 'card' => 'bi-credit-card-2-front', 'bank_transfer' => 'bi-bank'];
    $resources = ['events' => ['Événements', 'bi-calendar2-week'], 'users' => ['Utilisateurs', 'bi-people'], 'invitations' => ['Invitations', 'bi-send']];
@endphp

@push('styles')
<style>
    .plans-page { margin: 0 auto; max-width: 1240px; }
    .plans-page *, .plans-page *::before, .plans-page *::after { min-width: 0; }

    .current { background: radial-gradient(90% 140% at 0% 0%, rgba(185, 137, 67, .35), transparent 60%), #141412; border-radius: 22px; color: #fff; display: grid; gap: 24px; grid-template-columns: minmax(0, 1fr) minmax(0, 1.4fr); margin-bottom: 28px; padding: 24px 26px; }
    .current small { color: #d8b978; display: block; font-size: .72rem; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; }
    .current h2 { color: #fff; font-size: 1.7rem; font-weight: 700; letter-spacing: -.035em; margin: 6px 0 4px; }
    .current p { color: rgba(255, 255, 255, .6); font-size: .86rem; margin: 0; }
    .current .status { align-items: center; background: rgba(48, 209, 88, .14); border-radius: 99px; color: #5fd38a; display: inline-flex; font-size: .74rem; font-weight: 600; gap: 6px; margin-top: 14px; padding: 4px 10px; }
    .current .status.is-late { background: rgba(255, 159, 10, .16); color: #ffb340; }
    .current-usage { display: grid; gap: 14px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .cu { background: rgba(255, 255, 255, .05); border: 1px solid rgba(255, 255, 255, .08); border-radius: 16px; padding: 14px; }
    .cu span { align-items: center; color: rgba(255, 255, 255, .6); display: flex; font-size: .76rem; gap: 6px; }
    .cu strong { display: block; white-space: nowrap; font-size: 1.3rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.03em; margin: 8px 0; }
    .cu strong small { color: rgba(255, 255, 255, .45); font-size: .85rem; font-weight: 500; }
    .cu-meter { background: rgba(255, 255, 255, .1); border-radius: 99px; height: 5px; overflow: hidden; }
    .cu-meter span { background: #d8b978; border-radius: inherit; display: block; height: 100%; }

    .cycle { align-items: center; display: flex; flex-direction: column; gap: 10px; margin-bottom: 22px; }
    .cycle-toggle { background: rgba(23, 23, 19, .06); border-radius: 999px; display: inline-flex; gap: 2px; padding: 4px; }
    .cycle-toggle button { align-items: center; background: transparent; border: 0; border-radius: 999px; color: var(--app-muted); display: inline-flex; font-size: .86rem; font-weight: 600; gap: 8px; padding: 8px 18px; transition: background .25s, color .25s; }
    .cycle-toggle button.is-active { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .08), 0 4px 12px -4px rgba(23, 23, 19, .16); color: var(--app-ink); }
    .save-badge { background: rgba(31, 138, 95, .12); border-radius: 99px; color: #1f8a5f; font-size: .7rem; font-weight: 700; padding: 2px 8px; }

    .plan-grid { align-items: stretch; display: grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); }
    .plan { background: var(--app-panel); border: 1px solid var(--app-line); border-radius: 22px; box-shadow: var(--app-shadow); display: flex; flex-direction: column; padding: 26px 24px; position: relative; transition: transform .5s var(--app-ease), box-shadow .5s var(--app-ease); }
    .plan:hover { box-shadow: 0 1px 2px rgba(23, 23, 19, .04), 0 30px 50px -28px rgba(23, 23, 19, .32); transform: translateY(-3px); }
    .plan.is-featured { background: #141412; border-color: transparent; color: #fff; }
    .plan.is-featured::before { background: conic-gradient(from var(--angle), transparent 0 60%, #d8b978, #b98943, transparent 90%); border-radius: inherit; content: ""; inset: -1px; -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0); -webkit-mask-composite: xor; mask-composite: exclude; padding: 1.5px; pointer-events: none; position: absolute; animation: spin 6s linear infinite; }
    @property --angle { syntax: "<angle>"; initial-value: 0deg; inherits: false; }
    @keyframes spin { to { --angle: 360deg; } }
    .plan.is-current { box-shadow: 0 0 0 2px var(--app-gold), var(--app-shadow); }
    .plan-tags { display: flex; gap: 6px; position: absolute; right: 18px; top: 18px; }
    .plan-name { color: var(--app-ink); font-size: 1.05rem; font-weight: 650; margin: 0; }
    .plan.is-featured .plan-name, .plan.is-featured .plan-price { color: #fff; }
    .plan-tagline { color: var(--app-muted); font-size: .86rem; line-height: 1.5; margin: 6px 0 0; min-height: 40px; }
    .plan.is-featured .plan-tagline, .plan.is-featured .plan-sub { color: rgba(255, 255, 255, .6); }
    .plan-price { align-items: baseline; color: var(--app-ink); display: flex; font-size: 2rem; font-variant-numeric: tabular-nums; font-weight: 700; gap: 6px; letter-spacing: -.045em; margin-top: 22px; }
    .plan-price small { color: var(--app-muted); font-size: .86rem; font-weight: 500; letter-spacing: 0; }
    .plan-sub { color: var(--app-muted); font-size: .78rem; min-height: 18px; }
    .plan ul { border-top: 1px solid var(--app-line); display: grid; gap: 10px; list-style: none; margin: 20px 0 24px; padding: 20px 0 0; }
    .plan.is-featured ul { border-color: rgba(255, 255, 255, .1); }
    .plan li { align-items: flex-start; display: flex; font-size: .88rem; gap: 10px; line-height: 1.4; }
    .plan li i { color: var(--app-gold); margin-top: 1px; }
    .plan.is-featured li i { color: #d8b978; }
    .plan li.is-limit strong { font-variant-numeric: tabular-nums; }
    .plan .ui-btn { margin-top: auto; width: 100%; }
    .plan.is-featured .ui-btn-ghost { background: rgba(255, 255, 255, .1); border-color: transparent; color: #fff; }
    .blocked { color: var(--app-red); font-size: .76rem; line-height: 1.4; margin-top: 8px; text-align: center; }
    .plans-note { color: var(--app-muted); font-size: .8rem; margin-top: 18px; text-align: center; }

    /* Payment modal */
    .pay-head { align-items: flex-start; display: flex; gap: 16px; justify-content: space-between; padding: 22px 24px 4px; }
    .pay-head small { color: var(--app-gold-deep); display: block; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .pay-head h2 { color: var(--app-ink); font-size: 1.25rem; font-weight: 700; letter-spacing: -.02em; margin: 4px 0 0; }
    .pay-body { padding: 16px 24px 24px; }
    .pay-total { align-items: center; background: var(--app-bg); border-radius: 16px; display: flex; justify-content: space-between; margin-bottom: 18px; padding: 14px 16px; }
    .pay-total span { color: var(--app-muted); font-size: .8rem; }
    .pay-total strong { color: var(--app-ink); font-size: 1.4rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.03em; }
    .seg { background: rgba(23, 23, 19, .06); border-radius: 12px; display: grid; gap: 3px; grid-template-columns: 1fr 1fr; padding: 3px; }
    .seg input { opacity: 0; pointer-events: none; position: absolute; }
    .seg label { border-radius: 9px; color: var(--app-muted); cursor: pointer; font-size: .84rem; font-weight: 600; margin: 0; padding: 8px; text-align: center; }
    .seg input:checked + label { background: var(--app-panel); box-shadow: 0 1px 2px rgba(23, 23, 19, .1); color: var(--app-ink); }
    .ops { display: grid; gap: 8px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .op { cursor: pointer; margin: 0; position: relative; }
    .op input { opacity: 0; pointer-events: none; position: absolute; }
    .op span { align-items: center; border: 1px solid rgba(23, 23, 19, .13); border-radius: 10px; color: var(--app-text); display: flex; flex-direction: column; font-size: .76rem; font-weight: 600; gap: 4px; padding: 10px 6px; text-align: center; transition: border-color .2s, box-shadow .2s; }
    .op span i { color: var(--app-gold-deep); font-size: 1rem; }
    .op input:checked + span { border-color: var(--app-ink); box-shadow: 0 0 0 3px rgba(23, 23, 19, .06); color: var(--app-ink); }
    .op input:focus-visible + span { box-shadow: 0 0 0 3px var(--app-gold-soft); }
    .pay-note { align-items: flex-start; color: var(--app-muted); display: flex; font-size: .76rem; gap: 8px; line-height: 1.45; margin-top: 14px; }

    @media (max-width: 991.98px) { .current { grid-template-columns: 1fr; } }
    @media (max-width: 575.98px) { .current-usage { grid-template-columns: 1fr; } .ops { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (prefers-reduced-motion: reduce) { .plan.is-featured::before { animation: none; } }
</style>
@endpush

@section('content')
<div class="plans-page">
    <x-app.page-header kicker="Abonnement" title="Choisissez votre forfait" subtitle="Adaptez vos volumes d’événements, d’utilisateurs et d’invitations à votre activité.">
        <x-slot:actions>
            <a href="{{ route('saas.billing') }}" class="ui-btn ui-btn-ghost"><i class="bi bi-receipt"></i>Facturation</a>
        </x-slot:actions>
    </x-app.page-header>

    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

    <section class="current">
        <div>
            <small>Forfait actuel</small>
            <h2>{{ $current['name'] }}</h2>
            <p>{{ ($organization->settings['billing_cycle'] ?? 'monthly') === 'yearly' ? 'Facturation annuelle' : 'Facturation mensuelle' }}@if($endsAt) · jusqu’au {{ $endsAt->locale('fr')->translatedFormat('d F Y') }}@endif</p>
            @if($daysLeft !== null)
                <span @class(['status', 'is-late' => $daysLeft < 7])><i class="bi {{ $daysLeft < 0 ? 'bi-exclamation-triangle' : 'bi-check-circle' }}"></i>{{ $daysLeft < 0 ? 'Expiré depuis ' . abs($daysLeft) . ' jour(s)' : ($daysLeft === 0 ? 'Expire aujourd’hui' : $daysLeft . ' jour(s) restant(s)') }}</span>
            @else
                <span class="status"><i class="bi bi-check-circle"></i>{{ ucfirst($organization->status ?? 'actif') }}</span>
            @endif
        </div>
        <div class="current-usage">
            @foreach($resources as $key => [$label, $icon])
                @php $limit = $current['limits'][$key] ?? null; $used = (int) ($usage[$key] ?? 0); $pct = $limit ? min(100, round($used / $limit * 100)) : 100; @endphp
                <div class="cu">
                    <span><i class="bi {{ $icon }}"></i>{{ $label }}</span>
                    <strong>{{ number_format($used, 0, ',', ' ') }} <small>/ {{ $limit ? number_format($limit, 0, ',', ' ') : '∞' }}</small></strong>
                    <div class="cu-meter"><span style="width: {{ $pct }}%; {{ $limit && $pct >= 90 ? 'background:#ff8a7a' : '' }}"></span></div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="cycle">
        <div class="cycle-toggle" role="group" aria-label="Cycle de facturation">
            <button type="button" data-cycle="monthly" @class(['is-active' => $cycle !== 'yearly'])>Mensuel</button>
            <button type="button" data-cycle="yearly" @class(['is-active' => $cycle === 'yearly'])>Annuel @if($maxSaving > 0)<span class="save-badge">jusqu’à −{{ $maxSaving }}%</span>@endif</button>
        </div>
    </div>

    <div class="plan-grid">
        @foreach($plans as $key => $plan)
            @php
                $canCover = SaasUsage::planCanCoverCurrentUsage($organization, $plan);
                $isCurrent = $currentPlan === $key;
                $isFeatured = $key === $featuredKey && !$isCurrent;
                $saving = $plan['monthly_price'] > 0 ? round((1 - $plan['yearly_price'] / ($plan['monthly_price'] * 12)) * 100) : 0;
            @endphp
            <article @class(['plan', 'is-featured' => $isFeatured, 'is-current' => $isCurrent])>
                <div class="plan-tags">
                    @if($isCurrent)<span class="ui-pill gold">Plan actuel</span>@elseif($isFeatured)<span class="ui-pill" style="background: rgba(216,185,120,.18); color: #d8b978">Recommandé</span>@endif
                </div>
                <h2 class="plan-name">{{ $plan['name'] }}</h2>
                <p class="plan-tagline">{{ $plan['tagline'] }}</p>
                <div class="plan-price">
                    <span data-price-monthly="{{ SaasPlans::formatPrice($plan['monthly_price'], $plan['currency']) }}" data-price-yearly="{{ SaasPlans::formatPrice($plan['yearly_price'], $plan['currency']) }}">{{ SaasPlans::formatPrice(SaasPlans::price($plan, $cycle), $plan['currency']) }}</span>
                    <small data-unit-monthly="/ mois" data-unit-yearly="/ an">{{ $cycle === 'yearly' ? '/ an' : '/ mois' }}</small>
                </div>
                <div class="plan-sub" data-sub-monthly="{{ SaasPlans::formatPrice($plan['yearly_price'], $plan['currency']) }} par an en annuel" data-sub-yearly="soit {{ SaasPlans::formatPrice((int) round($plan['yearly_price'] / 12), $plan['currency']) }} / mois{{ $saving > 0 ? ' · −' . $saving . '%' : '' }}">
                    {{ $cycle === 'yearly' ? 'soit ' . SaasPlans::formatPrice((int) round($plan['yearly_price'] / 12), $plan['currency']) . ' / mois' . ($saving > 0 ? ' · −' . $saving . '%' : '') : SaasPlans::formatPrice($plan['yearly_price'], $plan['currency']) . ' par an en annuel' }}
                </div>
                <ul>
                    @foreach($resources as $res => [$label, $icon])
                        <li class="is-limit"><i class="bi {{ $icon }}"></i><span><strong>{{ $plan['limits'][$res] ? number_format($plan['limits'][$res], 0, ',', ' ') : 'Illimité' }}</strong> {{ \Illuminate\Support\Str::lower($label) }}</span></li>
                    @endforeach
                    @foreach($plan['features'] as $feature)
                        <li><i class="bi bi-check2"></i>{{ $feature }}</li>
                    @endforeach
                    @if(SaasPlans::allows($plan, 'custom_music'))
                        <li><i class="bi bi-music-note-beamed"></i>Musique d’invitation personnalisée</li>
                    @endif
                </ul>
                @if($canCover)
                    <button type="button" @class(['ui-btn', 'ui-btn-gold' => $isFeatured, 'ui-btn-primary' => $isCurrent, 'ui-btn-ghost' => !$isFeatured && !$isCurrent]) data-bs-toggle="modal" data-bs-target="#planModal-{{ $key }}">
                        {{ $isCurrent ? 'Renouveler' : 'Choisir ' . $plan['name'] }}
                    </button>
                @else
                    <button type="button" class="ui-btn ui-btn-ghost" disabled><i class="bi bi-lock"></i>Indisponible</button>
                    <div class="blocked">{{ SaasUsage::planCoverageMessage($organization, $plan) }}</div>
                @endif
            </article>
        @endforeach
    </div>
    <p class="plans-note"><i class="bi bi-shield-check me-1"></i>Prix en {{ $current['currency'] }}. Votre nouvelle période démarre à la fin de la période en cours.</p>
</div>

@foreach($plans as $key => $plan)
    @php
        $selectedCycle = $modalToOpen === $key ? old('billing_cycle', $cycle) : $cycle;
        $planErrors = $modalToOpen === $key ? $errors->getBag('default') : new \Illuminate\Support\MessageBag();
    @endphp
    <div class="modal fade" id="planModal-{{ $key }}" tabindex="-1" aria-labelledby="planModalLabel-{{ $key }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('saas.plans.update') }}" class="plan-form" novalidate>
                    @csrf
                    <input type="hidden" name="plan" value="{{ $key }}">
                    <input type="hidden" name="amount" value="{{ SaasPlans::price($plan, $selectedCycle) }}" data-amount>
                    <div class="pay-head">
                        <div>
                            <small>{{ $currentPlan === $key ? 'Renouvellement' : 'Souscription' }}</small>
                            <h2 id="planModalLabel-{{ $key }}">Forfait {{ $plan['name'] }}</h2>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="pay-body">
                        @if($planErrors->any())
                            <div class="alert alert-danger py-2 small">{{ $planErrors->first() }}</div>
                        @endif
                        <div class="mb-3">
                            <span class="form-label d-block">Cycle</span>
                            <div class="seg">
                                <input type="radio" name="billing_cycle" id="cycle-m-{{ $key }}" value="monthly" data-price="{{ $plan['monthly_price'] }}" data-label="{{ SaasPlans::formatPrice($plan['monthly_price'], $plan['currency']) }}" @checked($selectedCycle !== 'yearly')>
                                <label for="cycle-m-{{ $key }}">Mensuel</label>
                                <input type="radio" name="billing_cycle" id="cycle-y-{{ $key }}" value="yearly" data-price="{{ $plan['yearly_price'] }}" data-label="{{ SaasPlans::formatPrice($plan['yearly_price'], $plan['currency']) }}" @checked($selectedCycle === 'yearly')>
                                <label for="cycle-y-{{ $key }}">Annuel</label>
                            </div>
                        </div>
                        <div class="pay-total"><span>Montant à régler</span><strong data-amount-label>{{ SaasPlans::formatPrice(SaasPlans::price($plan, $selectedCycle), $plan['currency']) }}</strong></div>
                        <div class="mb-3">
                            <span class="form-label d-block">Moyen de paiement</span>
                            <div class="ops">
                                @foreach($paymentOperators as $operatorKey => $operatorLabel)
                                    <label class="op">
                                        <input type="radio" name="payment_operator" value="{{ $operatorKey }}" @checked($modalToOpen === $key && old('payment_operator') === $operatorKey) required>
                                        <span><i class="bi {{ $operatorIcons[$operatorKey] ?? 'bi-wallet2' }}"></i>{{ $operatorLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @if($planErrors->has('payment_operator'))<div class="invalid-feedback d-block">{{ $planErrors->first('payment_operator') }}</div>@endif
                        </div>
                        <div class="mb-1">
                            <label class="form-label" for="phone-{{ $key }}">Numéro ou référence de paiement</label>
                            <input @class(['form-control', 'is-invalid' => $planErrors->has('payment_phone')]) id="phone-{{ $key }}" name="payment_phone" value="{{ $modalToOpen === $key ? old('payment_phone') : '' }}" placeholder="+225 07 00 00 00 00" inputmode="tel" required>
                            @if($planErrors->has('payment_phone'))<div class="invalid-feedback">{{ $planErrors->first('payment_phone') }}</div>@endif
                        </div>
                        <div class="pay-note"><i class="bi bi-info-circle"></i><span>Le règlement est enregistré manuellement : aucune somme n’est débitée automatiquement depuis cette page. L’opération apparaîtra dans votre historique de facturation.</span></div>
                        <div class="d-flex gap-2 mt-4">
                            <button type="button" class="ui-btn ui-btn-soft" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="ui-btn ui-btn-primary flex-fill"><i class="bi bi-check2-circle"></i>Valider le forfait</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
    (() => {
        document.querySelectorAll('.plan-form').forEach((form) => {
            const amount = form.querySelector('[data-amount]');
            const label = form.querySelector('[data-amount-label]');
            form.querySelectorAll('input[name="billing_cycle"]').forEach((input) => input.addEventListener('change', () => {
                amount.value = input.dataset.price;
                label.textContent = input.dataset.label;
            }));
            form.addEventListener('submit', (event) => {
                const phone = form.querySelector('[name="payment_phone"]');
                const operator = form.querySelector('[name="payment_operator"]:checked');
                phone.classList.toggle('is-invalid', !phone.value.trim());
                if (!operator || !phone.value.trim()) {
                    event.preventDefault();
                    form.querySelector('.ops').classList.toggle('text-danger', !operator);
                    (!operator ? form.querySelector('.ops input') : phone).focus();
                    return;
                }
                form.querySelector('[type="submit"]').disabled = true;
            });
        });

        // Monthly / yearly toggle on the plan cards (display only; the cycle is confirmed in the modal).
        const setCycle = (cycle) => {
            document.querySelectorAll('[data-cycle]').forEach((b) => b.classList.toggle('is-active', b.dataset.cycle === cycle));
            document.querySelectorAll('[data-price-monthly]').forEach((el) => el.textContent = el.dataset[cycle === 'yearly' ? 'priceYearly' : 'priceMonthly']);
            document.querySelectorAll('[data-unit-monthly]').forEach((el) => el.textContent = el.dataset[cycle === 'yearly' ? 'unitYearly' : 'unitMonthly']);
            document.querySelectorAll('[data-sub-monthly]').forEach((el) => el.textContent = el.dataset[cycle === 'yearly' ? 'subYearly' : 'subMonthly']);
            document.querySelectorAll(`.plan-form input[value="${cycle}"][name="billing_cycle"]`).forEach((input) => { input.checked = true; input.dispatchEvent(new Event('change')); });
        };
        document.querySelectorAll('[data-cycle]').forEach((b) => b.addEventListener('click', () => setCycle(b.dataset.cycle)));

        @if($modalToOpen && isset($plans[$modalToOpen]))
            new bootstrap.Modal(document.getElementById('planModal-{{ $modalToOpen }}')).show();
        @endif
    })();
</script>
@endpush
@endsection
