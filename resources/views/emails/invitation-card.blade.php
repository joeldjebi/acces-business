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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre carte d'invitation</title>
</head>
<body style="margin:0; padding:0; background:#f5f3ee; font-family:Arial, Helvetica, sans-serif; color:#2c2a25;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f3ee; padding:34px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background:#fffefa; border:1px solid #ded6c8; border-radius:22px; overflow:hidden;">
                    <tr>
                        <td style="background:{{ $primary }}; padding:30px 34px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:18px;">
                                <tr>
                                    <td style="color:#ffffff; font-size:13px;">{{ $brandName }}</td>
                                    <td align="right">
                                        @if($organizationLogo)
                                            <img src="{{ $organizationLogo }}" alt="{{ $brandName }}" style="max-width:120px; max-height:48px; background:#ffffff; border-radius:12px; padding:8px;">
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 12px; color:{{ $accent }}; font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:bold;">Carte d’invitation</p>
                            <h1 style="margin:0; color:#ffffff; font-size:30px; line-height:1.12; font-weight:500;">{{ $event->titre }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px;">
                            <p style="margin:0 0 20px; font-size:16px; line-height:1.7;">Bonjour <strong>{{ $registration->nom_complet ?: $registration->email }}</strong>,</p>
                            @if($registration->entreprise || $registration->fonction)
                                <p style="margin:-10px 0 20px; color:#746f65; font-size:14px;">
                                    {{ $registration->fonction ?: '' }}{{ $registration->fonction && $registration->entreprise ? ' · ' : '' }}{{ $registration->entreprise ?: '' }}
                                </p>
                            @endif
                            <p style="margin:0 0 24px; color:#625b51; font-size:16px; line-height:1.7;">Votre invitation est confirmée. Présentez le QR code ci-dessous à l’entrée de l’événement.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff; border:1px solid #e5ded2; border-radius:18px;">
                                <tr>
                                    <td style="padding:22px; vertical-align:top;">
                                        @if($event->date_debut)
                                            <p style="margin:0 0 10px; color:#625b51; font-size:14px;"><strong style="color:#171713;">Date</strong> · {{ $event->date_debut->format('d/m/Y') }} @if($event->date_fin && $event->date_debut->format('Y-m-d') !== $event->date_fin->format('Y-m-d'))- {{ $event->date_fin->format('d/m/Y') }}@endif</p>
                                        @endif
                                        @if($event->heure_debut)
                                            <p style="margin:0 0 10px; color:#625b51; font-size:14px;"><strong style="color:#171713;">Heure</strong> · {{ $event->heure_debut }}{{ $event->heure_fin ? ' - ' . $event->heure_fin : '' }}</p>
                                        @endif
                                        @if($event->lieu || $event->ville)
                                            <p style="margin:0; color:#625b51; font-size:14px;"><strong style="color:#171713;">Lieu</strong> · {{ $event->lieu ?: $event->ville }}</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <div style="text-align:center; margin:30px 0;">
                                <div style="display:inline-block; background:#ffffff; border:1px solid #ded6c8; border-radius:20px; padding:16px;">
                                    <img src="{{ $qrCodeImageUrl }}" alt="QR Code" style="display:block; width:190px; height:190px;">
                                </div>
                                <p style="margin:12px 0 0; color:#746f65; font-size:12px; letter-spacing:1px; text-transform:uppercase;">QR code d’accès</p>
                            </div>

                            <div style="background:#f8f4ec; border-radius:16px; padding:16px; text-align:center;">
                                <p style="margin:0 0 8px; color:#746f65; font-size:12px; letter-spacing:1px; text-transform:uppercase;">Code d’accès</p>
                                <p style="margin:0; color:#171713; font-family:'Courier New', monospace; font-size:15px; letter-spacing:1px; word-break:break-all;">{{ $registration->token_unique }}</p>
                            </div>

                            <div style="text-align:center; margin:28px 0 0;">
                                <a href="{{ route('invitation.download', ['token' => $registration->token_unique]) }}" style="display:inline-block; background:{{ $primary }}; color:#ffffff; text-decoration:none; padding:14px 24px; border-radius:999px; font-weight:bold;">Télécharger la carte PDF</a>
                            </div>
                        </td>
                    </tr>
                    @if($signatureText || $signatureLogo)
                        <tr>
                            <td style="background:#fffefa; border-top:1px solid #e5ded2; padding:18px 34px;">
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                    <tr>
                                        <td style="color:#746f65; font-size:13px; line-height:1.6;">{{ $signatureText }}</td>
                                        <td align="right" style="width:150px;">
                                            @if($signatureLogo)
                                                <img src="{{ $signatureLogo }}" alt="Signature" style="max-width:130px; max-height:50px;">
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="background:#f8f4ec; padding:22px 34px; color:#746f65; font-size:13px; line-height:1.6; text-align:center;">
                            Cette invitation est personnelle et non transférable.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
