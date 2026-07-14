<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Carte d'Invitation - {{ $event->titre }}</title>
    <style>
        @page {
            margin: 10mm;
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
            font-size: 12px;
        }

        .card {
            height: 277mm;
            background: #fffefa;
            border: 1px solid #ded6c8;
            border-radius: 18px;
            overflow: hidden;
        }

        .header {
            background: #171713;
            color: #fff;
            padding: 16mm 17mm 14mm;
        }

        .kicker {
            color: #d8b476;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 7mm;
        }

        .title {
            font-size: 25px;
            font-weight: normal;
            line-height: 1.16;
            margin: 0;
        }

        .body {
            padding: 13mm 17mm 10mm;
        }

        .guest {
            color: #746f65;
            font-size: 11px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 3mm;
        }

        .name {
            font-size: 18px;
            margin-bottom: 10mm;
        }

        .details {
            border: 1px solid #e5ded2;
            border-radius: 14px;
            padding: 9mm;
            margin-bottom: 10mm;
        }

        .detail {
            margin-bottom: 4mm;
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
            margin-bottom: 9mm;
        }

        .qr-box,
        .code-box {
            display: table-cell;
            vertical-align: middle;
        }

        .qr-box {
            width: 62mm;
            text-align: center;
        }

        .qr-frame {
            display: inline-block;
            border: 1px solid #ded6c8;
            border-radius: 14px;
            padding: 5mm;
            background: #fff;
        }

        .qr-frame img {
            width: 39mm;
            height: 39mm;
            display: block;
        }

        .code-box {
            padding-left: 10mm;
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
            border-radius: 12px;
            color: #171713;
            font-family: Courier, monospace;
            font-size: 10px;
            line-height: 1.5;
            padding: 5mm;
            word-break: break-all;
        }

        .notice {
            border-top: 1px solid #e5ded2;
            color: #746f65;
            font-size: 10px;
            line-height: 1.6;
            padding-top: 7mm;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="kicker">Carte d'invitation</div>
            <h1 class="title">{{ $event->titre }}</h1>
        </div>

        <div class="body">
            <div class="guest">Invité</div>
            <div class="name">{{ $registration->nom_complet ?: $registration->email }}</div>

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
        </div>
    </div>
</body>
</html>
