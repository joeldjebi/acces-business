@extends('layouts.app')

@section('title', 'Crédits messages')
@section('hide_errors', '1')

@php
    $reminderEnabled = (bool) old('enabled', $reminderSettings['enabled'] ?? true);
    $reminderTime = old('time', $reminderSettings['time'] ?? '08:00');
    $channelIcons = ['sms' => 'bi-chat-dots', 'whatsapp' => 'bi-whatsapp', 'email' => 'bi-envelope'];
    $operatorIcons = ['orange_money' => 'bi-phone', 'mtn_money' => 'bi-phone', 'moov_money' => 'bi-phone', 'wave' => 'bi-water', 'card' => 'bi-credit-card-2-front', 'bank_transfer' => 'bi-bank'];
    $selectedPackage = old('package_id', $packages->first()?->id);
    $purchaseErrors = $errors->hasAny(['package_id', 'quantity', 'payment_operator', 'payment_phone']);
    $reminderErrors = $errors->hasAny(['time', 'enabled']);
@endphp

@push('styles')
<style>
    .comm-page { margin: 0 auto; max-width: 1240px; }
    .comm-page *, .comm-page *::before, .comm-page *::after { min-width: 0; }
    .balances { display: grid; gap: 16px; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-bottom: 20px; }
    .balance { padding: 18px 20px; }
    .balance-top { align-items: center; display: flex; justify-content: space-between; }
    .balance-top span { align-items: center; color: var(--app-muted); display: flex; font-size: .82rem; font-weight: 500; gap: 8px; }
    .balance-top span i { align-items: center; background: rgba(23, 23, 19, .05); border-radius: 8px; color: var(--app-ink); display: inline-flex; height: 28px; justify-content: center; width: 28px; }
    .balance strong { color: var(--app-ink); display: block; font-size: 2rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.045em; line-height: 1; margin-top: 14px; }
    .balance strong small { color: var(--app-muted); font-size: .85rem; font-weight: 500; letter-spacing: 0; margin-left: 4px; }
    .balance .ui-meter { margin-top: 12px; }
    .balance-foot { color: var(--app-muted); display: flex; font-size: .76rem; justify-content: space-between; margin-top: 8px; }
    .balance.is-empty strong { color: rgba(23, 23, 19, .3); }

    .comm-grid { align-items: start; display: grid; gap: 20px; grid-template-columns: minmax(0, 1fr) 380px; }
    .stack { display: grid; gap: 20px; }

    .reminder { align-items: center; display: grid; gap: 16px; grid-template-columns: minmax(0, 1fr) auto; }
    .reminder-icon { align-items: center; background: var(--app-gold-soft); border-radius: 12px; color: var(--app-gold-deep); display: flex; flex: 0 0 42px; font-size: 1.1rem; height: 42px; justify-content: center; width: 42px; }
    .reminder-controls { align-items: center; display: flex; flex-wrap: wrap; gap: 10px; }
    .reminder-controls .form-control { width: 120px; }
    .reminder-state { font-size: .78rem; font-weight: 600; }

    .packages { display: grid; gap: 10px; }
    .pkg { cursor: pointer; margin: 0; position: relative; }
    .pkg input { opacity: 0; pointer-events: none; position: absolute; }
    .pkg-body { align-items: center; border: 1px solid rgba(23, 23, 19, .13); border-radius: 14px; display: flex; gap: 14px; padding: 14px 16px; transition: border-color .2s, box-shadow .2s; }
    .pkg input:checked + .pkg-body { border-color: var(--app-ink); box-shadow: 0 0 0 3px rgba(23, 23, 19, .06); }
    .pkg input:focus-visible + .pkg-body { box-shadow: 0 0 0 3px var(--app-gold-soft); }
    .pkg-icon { align-items: center; background: rgba(23, 23, 19, .05); border-radius: 10px; color: var(--app-ink); display: flex; flex: 0 0 38px; font-size: 1rem; height: 38px; justify-content: center; width: 38px; }
    .pkg-body strong { color: var(--app-ink); display: block; font-size: .92rem; font-weight: 650; }
    .pkg-body small { color: var(--app-muted); font-size: .78rem; }
    .pkg-price { margin-left: auto; text-align: right; white-space: nowrap; }
    .pkg-price strong { font-variant-numeric: tabular-nums; }

    .qty { align-items: stretch; display: flex; }
    .qty button { background: #fff; border: 1px solid rgba(23, 23, 19, .13); color: var(--app-ink); font-size: 1rem; width: 40px; }
    .qty button:first-child { border-radius: 10px 0 0 10px; }
    .qty button:last-child { border-radius: 0 10px 10px 0; }
    .qty .form-control { border-left: 0; border-radius: 0; border-right: 0; font-variant-numeric: tabular-nums; text-align: center; }
    .presets { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .presets button { background: rgba(23, 23, 19, .05); border: 0; border-radius: 99px; color: var(--app-ink); font-size: .74rem; font-weight: 600; padding: 4px 10px; }
    .presets button:hover { background: rgba(23, 23, 19, .1); }
    .ops { display: grid; gap: 8px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .op { cursor: pointer; margin: 0; position: relative; }
    .op input { opacity: 0; pointer-events: none; position: absolute; }
    .op span { align-items: center; border: 1px solid rgba(23, 23, 19, .13); border-radius: 10px; color: var(--app-text); display: flex; flex-direction: column; font-size: .74rem; font-weight: 600; gap: 4px; padding: 9px 6px; text-align: center; transition: border-color .2s, box-shadow .2s; }
    .op span i { color: var(--app-gold-deep); font-size: .95rem; }
    .op input:checked + span { border-color: var(--app-ink); box-shadow: 0 0 0 3px rgba(23, 23, 19, .06); color: var(--app-ink); }
    .total { align-items: center; background: #141412; border-radius: 14px; color: #fff; display: flex; justify-content: space-between; margin-top: 18px; padding: 14px 16px; }
    .total span { color: rgba(255, 255, 255, .6); font-size: .8rem; }
    .total strong { font-size: 1.35rem; font-variant-numeric: tabular-nums; font-weight: 700; letter-spacing: -.03em; }
    .pay-note { color: var(--app-muted); font-size: .74rem; line-height: 1.45; margin-top: 10px; }

    @media (max-width: 1099.98px) { .comm-grid { grid-template-columns: 1fr; } }
    @media (max-width: 767.98px) { .balances { grid-template-columns: 1fr; } .reminder { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="comm-page">
    <x-app.page-header kicker="Communications" title="Crédits messages" subtitle="Rechargez vos crédits et planifiez les relances automatiques de vos invités." />

    @if(session('warning'))<div class="alert alert-warning">{{ session('warning') }}</div>@endif

    <section class="balances">
        @foreach($channels as $channel => $label)
            @php
                $balance = $balances[$channel] ?? null;
                $purchased = (int) ($balance?->purchased ?? 0);
                $used = (int) ($balance?->used ?? 0);
                $remaining = (int) ($balance?->remaining ?? 0);
                $pctUsed = $purchased ? min(100, round($used / $purchased * 100)) : 0;
            @endphp
            <article @class(['ui-card', 'balance', 'is-empty' => !$remaining]) style="--accent: {{ $pctUsed >= 90 ? 'var(--app-red)' : 'var(--app-ink)' }}">
                <div class="balance-top"><span><i class="bi {{ $channelIcons[$channel] ?? 'bi-chat' }}"></i>{{ $label }}</span>@if($remaining && $purchased && $remaining / $purchased < .1)<span class="ui-pill danger">Solde bas</span>@endif</div>
                <strong>{{ number_format($remaining, 0, ',', ' ') }}<small>crédit(s)</small></strong>
                <div class="ui-meter"><span style="width: {{ $pctUsed }}%"></span></div>
                <div class="balance-foot"><span>{{ number_format($used, 0, ',', ' ') }} utilisé(s)</span><span>{{ number_format($purchased, 0, ',', ' ') }} acheté(s)</span></div>
            </article>
        @endforeach
    </section>

    <div class="comm-grid">
        <div class="stack">
            <section class="ui-card">
                <div class="ui-card-body">
                    <form method="POST" action="{{ route('saas.communications.reminders.update') }}" class="reminder" novalidate>
                        @csrf
                        @method('PUT')
                        <div class="d-flex gap-3 align-items-start">
                            <span class="reminder-icon"><i class="bi bi-bell"></i></span>
                            <div>
                                <h2 class="h6 mb-1" style="font-weight: 650">Relances automatiques</h2>
                                <p class="text-muted small mb-1">Chaque jour à l’heure choisie, les invités qui n’ont pas encore répondu reçoivent un rappel.</p>
                                <span class="reminder-state {{ $reminderEnabled ? 'text-success' : 'text-muted' }}" data-reminder-state>{{ $reminderEnabled ? 'Activées · tous les jours à ' . $reminderTime : 'Désactivées' }}</span>
                            </div>
                        </div>
                        <div class="reminder-controls">
                            <div class="form-check form-switch m-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="reminder_enabled" name="enabled" value="1" @checked($reminderEnabled) aria-label="Activer les relances">
                            </div>
                            <input id="reminder_time" type="time" name="time" class="form-control @error('time') is-invalid @enderror" value="{{ $reminderTime }}" required aria-label="Heure des relances">
                            <button class="ui-btn ui-btn-primary ui-btn-sm" type="submit">Enregistrer</button>
                        </div>
                        @if($reminderErrors)<div class="text-danger small">{{ $errors->first('time') ?: $errors->first('enabled') }}</div>@endif
                    </form>
                </div>
            </section>

            <section class="ui-card" style="overflow: hidden">
                <div class="ui-card-head"><div><h2>Historique des achats</h2><p>Les 10 derniers rechargements.</p></div></div>
                @if($invoices->isNotEmpty())
                    <div class="table-responsive">
                        <table class="ui-table">
                            <thead><tr><th>Date</th><th>Canal</th><th>Quantité</th><th>Paiement</th><th class="text-end">Montant</th></tr></thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                    @php $meta = $invoice->metadata ?? []; @endphp
                                    <tr>
                                        <td>{{ optional($invoice->paid_at ?? $invoice->created_at)->format('d/m/Y') }}<span class="ui-sub">{{ optional($invoice->paid_at ?? $invoice->created_at)->format('H:i') }}</span></td>
                                        <td><span class="ui-pill"><i class="bi {{ $channelIcons[$meta['channel'] ?? ''] ?? 'bi-chat' }}"></i>{{ $channels[$meta['channel'] ?? ''] ?? '—' }}</span></td>
                                        <td style="font-variant-numeric: tabular-nums">{{ number_format((int) ($meta['quantity'] ?? 0), 0, ',', ' ') }}</td>
                                        <td>{{ $meta['payment_operator_label'] ?? '—' }}</td>
                                        <td class="text-end fw-semibold" style="font-variant-numeric: tabular-nums">{{ number_format($invoice->amount, 0, ',', ' ') }} {{ $invoice->currency }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="ui-empty"><i class="bi bi-receipt"></i><strong>Aucun achat</strong>Vos rechargements apparaîtront ici.</div>
                @endif
            </section>
        </div>

        <aside class="ui-card">
            <div class="ui-card-head has-border"><div><h2>Recharger des crédits</h2><p>Choisissez un package et une quantité.</p></div></div>
            @if($packages->isNotEmpty())
                <form method="POST" action="{{ route('saas.communications.purchase') }}" class="ui-card-body" id="purchaseForm" novalidate>
                    @csrf
                    @if($purchaseErrors)<div class="alert alert-danger py-2 small">{{ $errors->first() }}</div>@endif
                    <div class="packages mb-3">
                        @foreach($packages as $package)
                            <label class="pkg">
                                <input type="radio" name="package_id" value="{{ $package->id }}" data-price="{{ $package->unit_price }}" data-min="{{ $package->minimum_quantity }}" data-currency="{{ $package->currency }}" @checked((string) $selectedPackage === (string) $package->id)>
                                <span class="pkg-body">
                                    <span class="pkg-icon"><i class="bi {{ $channelIcons[$package->channel] ?? 'bi-chat' }}"></i></span>
                                    <span><strong>{{ $package->name }}</strong><small>{{ $channels[$package->channel] ?? strtoupper($package->channel) }} · min. {{ number_format($package->minimum_quantity, 0, ',', ' ') }}</small></span>
                                    <span class="pkg-price"><strong>{{ number_format($package->unit_price, 0, ',', ' ') }} {{ $package->currency }}</strong><small class="d-block">/ crédit</small></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="quantity">Quantité</label>
                        <div class="qty">
                            <button type="button" data-step="-1" aria-label="Diminuer">−</button>
                            <input type="number" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity', $packages->firstWhere('id', $selectedPackage)?->minimum_quantity ?? 100) }}" min="1" step="1" inputmode="numeric" required>
                            <button type="button" data-step="1" aria-label="Augmenter">+</button>
                        </div>
                        <div class="presets" data-presets></div>
                        <div class="form-text" data-min-hint></div>
                    </div>
                    <div class="mb-3">
                        <span class="form-label d-block">Moyen de paiement</span>
                        <div class="ops">
                            @foreach($paymentOperators as $key => $operator)
                                <label class="op"><input type="radio" name="payment_operator" value="{{ $key }}" @checked(old('payment_operator') === $key) required><span><i class="bi {{ $operatorIcons[$key] ?? 'bi-wallet2' }}"></i>{{ $operator }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="form-label" for="payment_phone">Numéro ou référence de paiement</label>
                        <input class="form-control @error('payment_phone') is-invalid @enderror" id="payment_phone" name="payment_phone" value="{{ old('payment_phone') }}" placeholder="+225 07 00 00 00 00" inputmode="tel" required>
                    </div>
                    <div class="total"><span>Total à régler</span><strong data-total>—</strong></div>
                    <button type="submit" class="ui-btn ui-btn-gold w-100 mt-3"><i class="bi bi-lightning-charge"></i>Acheter les crédits</button>
                    <p class="pay-note"><i class="bi bi-info-circle me-1"></i>Le règlement est enregistré manuellement : aucune somme n’est débitée automatiquement depuis cette page. Les crédits sont ajoutés immédiatement à votre solde.</p>
                </form>
            @else
                <div class="ui-empty"><i class="bi bi-box"></i><strong>Aucun package disponible</strong>Contactez l’administration de la plateforme.</div>
            @endif
        </aside>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const toggle = document.getElementById('reminder_enabled');
        const time = document.getElementById('reminder_time');
        const state = document.querySelector('[data-reminder-state]');
        const syncReminder = () => {
            time.disabled = !toggle.checked;
            state.textContent = toggle.checked ? `Activées · tous les jours à ${time.value || '08:00'}` : 'Désactivées';
            state.className = 'reminder-state ' + (toggle.checked ? 'text-success' : 'text-muted');
        };
        toggle?.addEventListener('change', syncReminder);
        time?.addEventListener('input', syncReminder);
        // A disabled input is not submitted: re-enable it right before submit so the required time is always sent.
        time?.closest('form').addEventListener('submit', () => { time.disabled = false; });
        if (toggle) syncReminder();

        const form = document.getElementById('purchaseForm');
        if (!form) return;
        const qty = form.querySelector('#quantity');
        const totalEl = form.querySelector('[data-total]');
        const hint = form.querySelector('[data-min-hint]');
        const presets = form.querySelector('[data-presets]');
        const fmt = new Intl.NumberFormat('fr-FR');
        const selected = () => form.querySelector('input[name="package_id"]:checked');

        const refresh = () => {
            const pkg = selected();
            if (!pkg) { totalEl.textContent = '—'; return; }
            const min = Number(pkg.dataset.min) || 1;
            qty.min = min;
            const value = Math.max(0, Number(qty.value) || 0);
            totalEl.textContent = `${fmt.format(value * Number(pkg.dataset.price))} ${pkg.dataset.currency}`;
            hint.textContent = value < min ? `Minimum ${fmt.format(min)} crédits pour ce package.` : '';
            hint.classList.toggle('text-danger', value < min);
            presets.replaceChildren(...[1, 5, 10].map((factor) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.textContent = fmt.format(min * factor);
                b.addEventListener('click', () => { qty.value = min * factor; refresh(); });
                return b;
            }));
        };

        form.querySelectorAll('[data-step]').forEach((b) => b.addEventListener('click', () => {
            const step = Number(selected()?.dataset.min) >= 100 ? 50 : 10;
            qty.value = Math.max(Number(qty.min) || 1, (Number(qty.value) || 0) + Number(b.dataset.step) * step);
            refresh();
        }));
        form.querySelectorAll('input[name="package_id"]').forEach((input) => input.addEventListener('change', () => {
            if (Number(qty.value) < Number(input.dataset.min)) qty.value = input.dataset.min;
            refresh();
        }));
        qty.addEventListener('input', refresh);
        form.addEventListener('submit', (event) => {
            const phone = form.querySelector('#payment_phone');
            const operator = form.querySelector('input[name="payment_operator"]:checked');
            const tooFew = Number(qty.value) < Number(qty.min);
            phone.classList.toggle('is-invalid', !phone.value.trim());
            qty.classList.toggle('is-invalid', tooFew);
            if (!selected() || !operator || !phone.value.trim() || tooFew) {
                event.preventDefault();
                if (!operator) form.querySelector('.ops').scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (!confirm(`Confirmer l’achat de ${fmt.format(qty.value)} crédits pour ${totalEl.textContent} ?`)) { event.preventDefault(); return; }
            form.querySelector('[type="submit"]').disabled = true;
        });
        refresh();
    })();
</script>
@endpush
