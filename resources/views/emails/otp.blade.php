<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code de vérification</title>
</head>
<body style="margin:0; padding:0; background:#f5f3ee; font-family:Arial, Helvetica, sans-serif; color:#2c2a25;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f5f3ee; padding:34px 14px;">
        <tr>
            <td align="center">
                <table role="presentation" width="560" cellspacing="0" cellpadding="0" border="0" style="max-width:560px; width:100%; background:#fffefa; border:1px solid #ded6c8; border-radius:22px; overflow:hidden;">
                    <tr>
                        <td style="background:#171713; padding:30px 34px;">
                            <p style="margin:0 0 10px; color:#d8b476; font-size:12px; letter-spacing:2px; text-transform:uppercase; font-weight:bold;">Accès sécurisé</p>
                            <h1 style="margin:0; color:#ffffff; font-size:28px; line-height:1.15; font-weight:500;">Code de vérification</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px; text-align:center;">
                            <p style="margin:0 0 18px; color:#625b51; font-size:16px; line-height:1.7;">Voici le code à utiliser pour accéder à :</p>
                            <p style="margin:0 0 28px; color:#171713; font-size:20px; line-height:1.35; font-weight:bold;">{{ $event->titre }}</p>
                            <div style="display:inline-block; background:#ffffff; border:1px solid #ded6c8; border-radius:18px; padding:20px 28px; color:#171713; font-family:'Courier New', monospace; font-size:36px; font-weight:bold; letter-spacing:8px;">
                                {{ $otpCode }}
                            </div>
                            <p style="margin:24px 0 0; color:#8a6128; font-size:14px; font-weight:bold;">Ce code est valide pendant 15 minutes.</p>
                            <p style="margin:14px 0 0; color:#746f65; font-size:13px; line-height:1.6;">Si vous n’êtes pas à l’origine de cette demande, vous pouvez ignorer cet email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
