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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // ***** START: ADDED TWILIO CONFIGURATION *****
    'twilio' => [
        'sid' => env('TWILIO_SID'),          // Reads TWILIO_SID from .env
        'token' => env('TWILIO_TOKEN'),      // Reads TWILIO_TOKEN from .env
        'from' => env('TWILIO_FROM'),        // Reads TWILIO_FROM from .env (your Twilio number)
        // If you were using a Messaging Service SID instead of a specific number, you'd use this:
        // 'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),
    ],
    // ***** END: ADDED TWILIO CONFIGURATION *****

];