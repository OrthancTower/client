<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Orthanc Client Enabled
    |--------------------------------------------------------------------------
    */
    'enabled' => env('ORTHANC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */
    'api_url' => env('ORTHANC_API_URL'),
    'api_token' => env('ORTHANC_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Default Channel
    |--------------------------------------------------------------------------
    | Default channel when none specified. Must exist in server config.
    |
    */
    'default_channel' => env('ORTHANC_DEFAULT_CHANNEL', 'system-config'),

    /*
    |--------------------------------------------------------------------------
    | Timeout Configuration
    |--------------------------------------------------------------------------
    */
    'timeout' => env('ORTHANC_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    */
    'retry' => [
        'enabled' => true,
        'times' => 3,
        'sleep' => 100,
        'base_ms' => 100,
        'cap_ms' => 2000,
        'jitter' => 'full',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    */
    'queue' => [
        'enabled' => env('ORTHANC_QUEUE_ENABLED', false),
        'connection' => env('ORTHANC_QUEUE_CONNECTION', 'redis'),
        'queue' => env('ORTHANC_QUEUE_NAME', 'orthanc-client'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    */
    'fallback' => [
        'log' => true,
        'throw_on_failure' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Configuration
    |--------------------------------------------------------------------------
    */
    'context' => [
        'app_name' => env('APP_NAME', 'Laravel App'),
        'app_url' => env('APP_URL'),
        'environment' => env('APP_ENV', 'production'),
        'include_user' => true,
        'include_email' => true,
        'include_name' => true,
        'include_ip' => true,
        'include_route' => true,
        'include_user_agent' => false,
        'sanitize_fields' => [],
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    */
    'auto_report_exceptions' => true,
    'override_exception_handler' => env('ORTHANC_CLIENT_OVERRIDE_HANDLER', false),

    /*
    |--------------------------------------------------------------------------
    | Exception Channels (mapeamento por severidade)
    |--------------------------------------------------------------------------
    */
    'exception_channels' => [
        'critical' => env('ORTHANC_CRITICAL_CHANNEL', 'system-config'),
        'security' => env('ORTHANC_SECURITY_CHANNEL', 'sting-alerts'),
        'warning' => env('ORTHANC_WARNING_CHANNEL', 'warnings'),
        'default' => env('ORTHANC_DEFAULT_CHANNEL', 'system-config'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    */
    'ignore_exceptions' => [],

    /*
    |--------------------------------------------------------------------------
    | Idempotency
    |--------------------------------------------------------------------------
    */
    'idempotency' => [
        'enabled' => false,
    ],
];
