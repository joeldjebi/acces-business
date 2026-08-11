<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation du représentant</title>
</head>
<body style="margin:0; padding:0; background:#f5f3ee; font-family:Arial, Helvetica, sans-serif; color:#2c2a25;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f3ee; padding:34px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; width:100%; background:#fffefa; border:1px solid #ded6c8; border-radius:22px; overflow:hidden;">
                    <tr>
                        <td style="background:#171713; padding:34px;">
                            <p style="margin:0 0 12px; color:#d8b476; font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:bold;">Représentation confirmée</p>
                            <h1 style="margin:0; color:#ffffff; font-size:30px; line-height:1.12; font-weight:500;">{{ $event->titre }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px;">
                            <p style="margin:0 0 18px; font-size:16px; line-height:1.7;">Bonjour {{ $inviteName }},</p>
                            <p style="margin:0 0 22px; color:#625b51; font-size:16px; line-height:1.7;">Votre représentant <strong>{{ $representativeName }}</strong> a confirmé sa présence à votre place.</p>
                            <div style="background:#f8f4ec; border-left:4px solid #b98943; border-radius:12px; padding:16px 18px; color:#5f5549; line-height:1.6;">
                                @if($cardSent)
                                    Sa carte d’invitation lui a été envoyée par email.
                                @else
                                    Sa présence est confirmée, mais l’envoi de sa carte par email a échoué. L’organisateur garde la trace de cette erreur.
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8f4ec; padding:22px 34px; color:#746f65; font-size:13px; line-height:1.6; text-align:center;">Vous recevez ce message parce que vous avez demandé à être représenté(e).</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
