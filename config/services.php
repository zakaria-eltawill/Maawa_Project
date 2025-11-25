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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'fcm' => [
        'project_id' => env('FCM_PROJECT_ID', 'maawa-project'),
        'service_account' => [
            'type' => env('FCM_SA_TYPE', 'service_account'),
            'project_id' => env('FCM_SA_PROJECT_ID'),
            'private_key_id' => env('FCM_SA_PRIVATE_KEY_ID'),
            'private_key' => env('FCM_SA_PRIVATE_KEY'),
            'client_email' => env('FCM_SA_CLIENT_EMAIL'),
            'client_id' => env('FCM_SA_CLIENT_ID'),
            'auth_uri' => env('FCM_SA_AUTH_URI', 'https://accounts.google.com/o/oauth2/auth'),
            'token_uri' => env('FCM_SA_TOKEN_URI', 'https://oauth2.googleapis.com/token'),
        ],
        // Legacy support (deprecated)
        'server_key' => env('FCM_SERVER_KEY'),
    ],

];
