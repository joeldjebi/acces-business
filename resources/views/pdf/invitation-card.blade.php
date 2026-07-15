<!DOCTYPE html>
<html lang="fr">
<head>
    @php
        $design = $cardDesign ?? [];
        $primary = $design['primary_color'] ?? '#171713';
        $accent = $design['accent_color'] ?? '#b98943';
        $brandName = $design['brand_name'] ?? 'Accès Business';
        $organizationLogo = $design['organization_logo'] ?? null;
        $signatureText = $design['signature_text'] ?? '';
        $signatureLogo = $design['signature_logo'] ?? null;
    @endphp
    <meta charset="UTF-8">
    <title>Carte d'Invitation - {{ $event->titre }}</title>
    <style>
        @page {
            margin: 7mm;
            size: A4 portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f3ee;
            color: #171713;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11px;
        }

        .card {
            height: 281mm;
            background: #fffefa;
            border: 1px solid #ded6c8;
            border-radius: 16px;
            overflow: hidden;
        }

        .header {
            background: {{ $primary }};
            color: #fff;
            padding: 11mm 13mm 10mm;
        }

        .brand-row {
            display: table;
            width: 100%;
            margin-bottom: 8mm;
        }

        .brand-cell,
        .logo-cell {
            display: table-cell;
            vertical-align: middle;
        }

        .brand-cell {
            color: #fff;
            font-size: 12px;
        }

        .logo-cell {
            text-align: right;
            width: 44mm;
        }

        .org-logo {
            background: #fff;
            border-radius: 10px;
            display: inline-block;
            max-height: 18mm;
            max-width: 38mm;
            padding: 3mm;
        }

        .kicker {
            color: {{ $accent }};
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 4mm;
        }

        .title {
            font-size: 23px;
            font-weight: normal;
            line-height: 1.16;
            margin: 0;
        }

        .body {
            padding: 10mm 13mm 8mm;
        }

        .guest {
            color: #746f65;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2mm;
        }

        .name {
            font-size: 17px;
            margin-bottom: 1.5mm;
        }

        .guest-meta {
            color: #746f65;
            font-size: 11px;
            margin-bottom: 6mm;
        }

        .details {
            border: 1px solid #e5ded2;
            border-radius: 12px;
            padding: 7mm;
            margin-bottom: 7mm;
        }

        .detail {
            margin-bottom: 3mm;
            line-height: 1.45;
        }

        .detail:last-child {
            margin-bottom: 0;
        }

        .label {
            color: #746f65;
            display: inline-block;
            width: 24mm;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        .qr-row {
            display: table;
            width: 100%;
            margin-bottom: 6mm;
        }

        .qr-box,
        .code-box {
            display: table-cell;
            vertical-align: middle;
        }

        .qr-box {
            width: 54mm;
            text-align: center;
        }

        .qr-frame {
            display: inline-block;
            border: 1px solid #ded6c8;
            border-radius: 12px;
            padding: 4mm;
            background: #fff;
        }

        .qr-frame img {
            width: 35mm;
            height: 35mm;
            display: block;
        }

        .code-box {
            padding-left: 8mm;
        }

        .code-label {
            color: #746f65;
            font-size: 10px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 3mm;
        }

        .code {
            background: #f8f4ec;
            border-radius: 10px;
            color: #171713;
            font-family: Courier, monospace;
            font-size: 10px;
            line-height: 1.5;
            padding: 4mm;
            word-break: break-all;
        }

        .signature {
            border-top: 1px solid #e5ded2;
            display: table;
            margin-top: 5mm;
            padding-top: 5mm;
            width: 100%;
        }

        .signature-text,
        .signature-mark {
            display: table-cell;
            vertical-align: middle;
        }

        .signature-text {
            color: #746f65;
            font-size: 10px;
            line-height: 1.55;
        }

        .signature-mark {
            text-align: right;
            width: 42mm;
        }

        .signature-mark img {
            max-height: 16mm;
            max-width: 38mm;
        }

        .notice {
            border-top: 1px solid #e5ded2;
            color: #746f65;
            font-size: 10px;
            line-height: 1.6;
            padding-top: 5mm;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="brand-row">
                <div class="brand-cell">{{ $brandName }}</div>
                <div class="logo-cell">
                    @if($organizationLogo)
                        <img class="org-logo" src="{{ $organizationLogo }}" alt="{{ $brandName }}">
                    @endif
                </div>
            </div>
            <div class="kicker">Carte d'invitation</div>
            <h1 class="title">{{ $event->titre }}</h1>
        </div>

        <div class="body">
            <div class="guest">Invité</div>
            <div class="name">{{ $registration->nom_complet ?: $registration->email }}</div>
            @if($registration->entreprise || $registration->fonction)
                <div class="guest-meta">
                    {{ $registration->fonction ?: '' }}{{ $registration->fonction && $registration->entreprise ? ' · ' : '' }}{{ $registration->entreprise ?: '' }}
                </div>
            @endif

            <div class="details">
                <div class="detail">
                    <span class="label">Date</span>
                    {{ $event->date_debut->format('d/m/Y') }}
                    @if($event->date_fin && $event->date_debut->format('Y-m-d') !== $event->date_fin->format('Y-m-d'))
                        - {{ $event->date_fin->format('d/m/Y') }}
                    @endif
                </div>
                <div class="detail">
                    <span class="label">Heure</span>
                    {{ $event->heure_debut }}{{ $event->heure_fin ? ' - ' . $event->heure_fin : '' }}
                </div>
                @if($event->lieu)
                    <div class="detail"><span class="label">Lieu</span>{{ $event->lieu }}</div>
                @endif
                @if($event->ville || $event->pays)
                    <div class="detail"><span class="label">Ville</span>{{ trim(($event->ville ?: '') . ' ' . ($event->pays ? ', ' . $event->pays : '')) }}</div>
                @endif
            </div>

            <div class="qr-row">
                <div class="qr-box">
                    <div class="qr-frame">
                        <img src="{{ $qrCodeImageUrl }}" alt="QR Code">
                    </div>
                </div>
                <div class="code-box">
                    <div class="code-label">Code d'accès</div>
                    <div class="code">{{ $registration->token_unique }}</div>
                </div>
            </div>

            <div class="notice">
                Présentez cette carte ou le QR code à l’entrée de l’événement.<br>
                Carte personnelle et non transférable.
            </div>

            @if($signatureText || $signatureLogo)
                <div class="signature">
                    <div class="signature-text">{{ $signatureText }}</div>
                    <div class="signature-mark">
                        @if($signatureLogo)
                            <img src="{{ $signatureLogo }}" alt="Signature">
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
