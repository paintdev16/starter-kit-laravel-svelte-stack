<?php

return [

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

    /*
    |--------------------------------------------------------------------------
    | Service Manager
    |--------------------------------------------------------------------------
    |
    | Controls how background services (e.g. Reverb) are managed from the UI.
    | Supported drivers: "local" (direct process), "systemd", "supervisor".
    |
    */

    'manager' => env('SERVICE_MANAGER', 'local'),

    'realtime_service' => env('REALTIME_SERVICE', 'reverb'),

    'systemd' => [
        'unit' => env('SYSTEMD_UNIT', 'reverb'),
        'directory' => env('SYSTEMD_DIRECTORY', '/etc/systemd/system'),
        'user' => env('SYSTEMD_USER', ''),
        'sudo' => filter_var(env('SYSTEMD_SUDO', false), FILTER_VALIDATE_BOOL),
    ],

    'supervisor' => [
        'program' => env('SUPERVISOR_PROGRAM', 'reverb'),
        'directory' => env('SUPERVISOR_CONFIG_DIR', '/etc/supervisor/conf.d'),
        'sudo' => filter_var(env('SUPERVISOR_SUDO', false), FILTER_VALIDATE_BOOL),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ],

];
