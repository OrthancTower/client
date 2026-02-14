<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Orthanc Client Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable Orthanc client globally.
    |
    */

    'enabled' => env('ORTHANC_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Orthanc server API URL and authentication token.
    |
    */

    'api_url' => env('ORTHANC_API_URL'),
    
    'api_token' => env('ORTHANC_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Timeout Configuration
    |--------------------------------------------------------------------------
    |
    | HTTP request timeout in seconds.
    |
    */

    'timeout' => env('ORTHANC_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Retry failed requests automatically.
    |
    */

    'retry' => [
        'enabled' => true,
        'times' => 3,
        'sleep' => 100, // milliseconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Queue notifications to avoid blocking requests.
    |
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
    |
    | What to do when server is unreachable.
    |
    */

    'fallback' => [
        'log' => true, // Always log to Laravel log
        'throw_on_failure' => false, // Don't throw exceptions on failure
    ],

    /*
    |--------------------------------------------------------------------------
    | Context Configuration
    |--------------------------------------------------------------------------
    |
    | Automatically include context in notifications.
    |
    */

    'context' => [
        'app_name' => env('APP_NAME', 'Laravel App'),
        'app_url' => env('APP_URL'),
        'environment' => env('APP_ENV', 'production'),
        'include_user' => true,
        'include_ip' => true,
        'include_route' => true,
        'include_user_agent' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exception Handling
    |--------------------------------------------------------------------------
    |
    | Automatically send exceptions to Orthanc server.
    |
    */

    'auto_report_exceptions' => true,

    /*
    |--------------------------------------------------------------------------
    | Ignored Exceptions
    |--------------------------------------------------------------------------
    |
    | Don't send these exceptions to server.
    |
    */

    'ignore_exceptions' => [
        // Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
    ],

];
