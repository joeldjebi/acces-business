<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mailjet' => [
        'api_key_public' => env('MAILJET_API_KEY_PUBLIC'),
        'api_key_private' => env('MAILJET_API_KEY_PRIVATE'),
        'from_email' => env('MAILJET_FROM_EMAIL', env('MAIL_FROM_ADDRESS')),
        'from_name' => env('MAILJET_FROM_NAME', env('APP_NAME')),
    ],

    'mailjet_sms' => [
        'token' => env('MAILJET_SMS_TOKEN'),
        'sender' => env('MAILJET_SMS_SENDER', env('APP_NAME', 'My Signal')),
    ],

    'apple_wallet' => [
        'pass_type_identifier' => env('APPLE_PASS_TYPE_IDENTIFIER'),
        'team_identifier' => env('APPLE_TEAM_IDENTIFIER'),
        'organization_name' => env('APPLE_WALLET_ORGANIZATION_NAME', env('APP_NAME')),
        'description' => env('APPLE_WALLET_DESCRIPTION', 'Carte d’invitation'),
        'logo_text' => env('APPLE_WALLET_LOGO_TEXT', env('APP_NAME')),
        'foreground_color' => env('APPLE_WALLET_FOREGROUND_COLOR', 'rgb(255, 255, 255)'),
        'background_color' => env('APPLE_WALLET_BACKGROUND_COLOR', 'rgb(201, 162, 39)'),
        'label_color' => env('APPLE_WALLET_LABEL_COLOR', 'rgb(255, 255, 255)'),
        'certificate_path' => env('APPLE_PASS_CERT_PATH'),
        'key_path' => env('APPLE_PASS_KEY_PATH'),
        'key_password' => env('APPLE_PASS_KEY_PASSWORD'),
        'wwdr_path' => env('APPLE_WWDR_CERT_PATH'),
        'asset_path' => env('APPLE_PASS_ASSET_PATH', 'resources/wallet/apple'),
    ],

    'google_wallet' => [
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID'),
        'class_id' => env('GOOGLE_WALLET_CLASS_ID'),
        'service_account_path' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_PATH'),
        'background_color' => env('GOOGLE_WALLET_BACKGROUND_COLOR', '#C9A227'),
        'logo_url' => env('GOOGLE_WALLET_LOGO_URL'),
        'watermark_url' => env('GOOGLE_WALLET_WATERMARK_URL'),
    ],

];
