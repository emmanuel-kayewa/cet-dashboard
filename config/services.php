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

    'azure' => [
        'client_id' => env('AZURE_AD_CLIENT_ID'),
        'client_secret' => env('AZURE_AD_CLIENT_SECRET'),
        'redirect' => env('AZURE_AD_REDIRECT_URI', '/auth/azure/callback'),
        'tenant' => env('AZURE_AD_TENANT_ID', 'common'),
    ],

    'whatsapp' => [
        // WhatsApp Business Platform (Cloud API)
        // https://developers.facebook.com/docs/whatsapp/cloud-api/
        'token' => env('WHATSAPP_CLOUD_API_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID'),
        'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),
        'templates' => [
            'alert' => env('WHATSAPP_TEMPLATE_ALERT'),
            'weekly_digest' => env('WHATSAPP_TEMPLATE_WEEKLY_DIGEST'),
        ],
        'url_button' => [
            // Only enable if your approved templates include a URL button.
            'enabled' => env('WHATSAPP_URL_BUTTON_ENABLED', false),
            'index' => (int) env('WHATSAPP_URL_BUTTON_INDEX', 0),
            // For URL buttons, WhatsApp typically expects the dynamic URL suffix (path/query), not the full domain.
            'parameter' => env('WHATSAPP_URL_BUTTON_PARAMETER', '/dashboard'),
        ],
    ],

];
