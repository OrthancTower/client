# 🔌 Orthanc Client

Lightweight HTTP client for sending notifications to Orthanc server.

## Features

- 🚀 **Simple API**: Same interface as server (`Orthanc::critical()`, etc)
- 🔄 **Automatic Retry**: Retries failed requests
- 📦 **Queue Support**: Optional queueing for non-blocking notifications
- 🎯 **Auto Exception Reporting**: Automatically captures and sends exceptions
- 🛡️ **Fallback**: Logs locally if server is unreachable
- 📊 **Rich Context**: Automatically includes app, user, IP, route, etc
- ⚡ **Lightweight**: No heavy dependencies

## Installation

### 1. Install via Composer

```bash
composer require g80st/orthanc-client
```

### 2. Publish Configuration

```bash
php artisan vendor:publish --tag=orthanc-client-config
```

### 3. Configure Environment

Add to `.env`:

```env
ORTHANC_ENABLED=true
ORTHANC_API_URL=https://orthanc.yourcompany.com
ORTHANC_API_TOKEN=orthanc_app123_abc...

# Optional
ORTHANC_TIMEOUT=10
ORTHANC_QUEUE_ENABLED=false
```

### 4. Update Exception Handler

```php
// app/Exceptions/Handler.php
namespace App\Exceptions;

use G80st\OrthancClient\Exceptions\OrthancClientExceptionHandler;

class Handler extends OrthancClientExceptionHandler
{
    // Your custom exception handling
}
```

## Usage

### Basic Notifications

```php
use G80st\OrthancClient\Facades\Orthanc;

// Critical
Orthanc::critical('gondor-alerts', 'Database connection lost!');

// Error
Orthanc::error('critical-errors', 'Payment processing failed', [
    'order_id' => 12345,
    'amount' => 199.99,
]);

// Warning
Orthanc::warning('warnings', 'API rate limit approaching');

// Success
Orthanc::success('deploy-success', 'Deployment completed', [
    'version' => '1.2.3',
    'duration' => '2m 15s',
]);

// Info
Orthanc::info('the-palantir', 'Backup started');

// Debug
Orthanc::debug('tests', 'Debug information');
```

### Automatic Exception Handling

All exceptions are automatically captured and sent to Orthanc server (if enabled).

Just extend `OrthancClientExceptionHandler` in your `app/Exceptions/Handler.php`.

### Manual Exception Reporting

```php
try {
    // Your code
} catch (\Exception $e) {
    Orthanc::error('critical-errors', 'Custom error occurred', [
        'exception' => $e,
        'custom_data' => 'value',
    ]);
    
    throw $e;
}
```

### Custom Context

```php
Orthanc::critical('gondor-alerts', 'High CPU usage', [
    'cpu' => '95%',
    'memory' => '80%',
    'server' => 'web-01',
    'data' => [
        'Load Average' => '4.5',
        'Processes' => 250,
    ],
]);
```

### Testing Connection

```bash
php artisan orthanc:test-connection
```

### View Status

```bash
php artisan orthanc:status
```

## Configuration

### Context

Control what context is automatically included:

```php
// config/orthanc-client.php
'context' => [
    'app_name' => env('APP_NAME', 'Laravel App'),
    'include_user' => true,      // Include authenticated user
    'include_ip' => true,         // Include client IP
    'include_route' => true,      // Include route/path
    'include_user_agent' => false, // Include user agent
],
```

### Retry

```php
'retry' => [
    'enabled' => true,
    'times' => 3,        // Retry 3 times
    'sleep' => 100,      // Wait 100ms between retries
],
```

### Queue

```php
'queue' => [
    'enabled' => false,
    'connection' => 'redis',
    'queue' => 'orthanc-client',
],
```

Enable queue to avoid blocking HTTP requests:

```env
ORTHANC_QUEUE_ENABLED=true
```

Then run queue worker:

```bash
php artisan queue:work --queue=orthanc-client
```

### Fallback

```php
'fallback' => [
    'log' => true,               // Log to Laravel log if server fails
    'throw_on_failure' => false, // Don't throw exceptions on failure
],
```

### Ignore Exceptions

Don't send certain exceptions to server:

```php
'ignore_exceptions' => [
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
],
```

## API Methods

```php
// Send notification
Orthanc::notify($channel, $level, $message, $context = []);

// Shortcuts
Orthanc::critical($channel, $message, $context = []);
Orthanc::error($channel, $message, $context = []);
Orthanc::warning($channel, $message, $context = []);
Orthanc::info($channel, $message, $context = []);
Orthanc::success($channel, $message, $context = []);
Orthanc::debug($channel, $message, $context = []);

// Test connection
Orthanc::testConnection(): bool;

// Get available channels
Orthanc::getChannels(): array;
```

## Examples

### Deployment Notification

```php
Orthanc::success('deploy-success', 'v1.2.3 deployed successfully', [
    'version' => '1.2.3',
    'environment' => 'production',
    'duration' => '2m 15s',
    'deployed_by' => auth()->user()->name,
]);
```

### Payment Failure

```php
Orthanc::error('critical-errors', 'Payment gateway error', [
    'title' => 'Payment Failed',
    'gateway' => 'Stripe',
    'amount' => 199.99,
    'customer_id' => 12345,
    'error_code' => 'card_declined',
]);
```

### High Resource Usage

```php
if (memory_get_peak_usage(true) > 256 * 1024 * 1024) {
    Orthanc::warning('system-metrics', 'High memory usage detected', [
        'memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB',
        'limit' => ini_get('memory_limit'),
    ]);
}
```

### Inventory Alert

```php
if ($product->stock < $product->reorder_point) {
    Orthanc::warning('warnings', 'Low inventory alert', [
        'product' => $product->name,
        'sku' => $product->sku,
        'current_stock' => $product->stock,
        'reorder_point' => $product->reorder_point,
    ]);
}
```

## Troubleshooting

### Notifications Not Sending

1. Check configuration: `php artisan orthanc:status`
2. Test connection: `php artisan orthanc:test-connection`
3. Check Laravel logs: `storage/logs/laravel.log`

### Connection Errors

- Verify `ORTHANC_API_URL` is correct
- Verify `ORTHANC_API_TOKEN` is valid
- Check network connectivity to server
- Check firewall rules

### Queue Not Processing

```bash
# Make sure queue worker is running
php artisan queue:work --queue=orthanc-client

# Or disable queue
ORTHANC_QUEUE_ENABLED=false
```

## Requirements

- PHP 8.2+
- Laravel 12.x

## License

Proprietary - Internal use only

## Support

For issues or questions, contact your Orthanc server administrator.
# client
