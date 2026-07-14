<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation</title>
</head>
<body style="margin:0; padding:0; background:#f5f3ee; font-family:Arial, Helvetica, sans-serif; color:#2c2a25;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f3ee; padding:34px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background:#fffefa; border:1px solid #ded6c8; border-radius:22px; overflow:hidden;">
                    <tr>
                        <td style="background:#171713; padding:34px 34px 30px;">
                            <p style="margin:0 0 12px; color:#d8b476; font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:bold;">Invitation privée</p>
                            <h1 style="margin:0; color:#ffffff; font-size:30px; line-height:1.12; font-weight:500;">{{ $event->titre }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px;">
                            <p style="margin:0 0 18px; font-size:16px; line-height:1.7;">Bonjour,</p>
                            <p style="margin:0 0 24px; color:#625b51; font-size:16px; line-height:1.7;">Vous êtes invité(e) à accéder à cet événement. Veuillez utiliser le lien sécurisé ci-dessous pour vérifier votre email et indiquer votre présence.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#ffffff; border:1px solid #e5ded2; border-radius:18px; margin:24px 0;">
                                <tr>
                                    <td style="padding:22px;">
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

                            @if($message)
                                <div style="background:#f8f4ec; border-left:4px solid #b98943; border-radius:12px; padding:16px 18px; margin:24px 0; color:#5f5549; line-height:1.6;">
                                    {{ $message }}
                                </div>
                            @endif

                            <div style="text-align:center; margin:30px 0;">
                                <a href="{{ $accessLink->access_url }}" style="display:inline-block; background:#171713; color:#ffffff; text-decoration:none; padding:15px 28px; border-radius:999px; font-weight:bold;">Accéder à l’invitation</a>
                            </div>

                            <p style="margin:22px 0 8px; color:#746f65; font-size:13px;">Si le bouton ne fonctionne pas, copiez ce lien :</p>
                            <p style="margin:0; color:#8a6128; font-size:13px; word-break:break-all;">{{ $accessLink->access_url }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8f4ec; padding:22px 34px; color:#746f65; font-size:13px; line-height:1.6; text-align:center;">
                            Invitation envoyée par {{ $senderName ?? 'l’équipe' }}. Ce lien est personnel.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
