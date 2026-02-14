<?php

declare(strict_types=1);

namespace G80st\OrthancClient;

use G80st\OrthancClient\Support\ContextBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrthancClient
{
    protected ContextBuilder $contextBuilder;

    public function __construct()
    {
        $this->contextBuilder = new ContextBuilder();
    }

    /**
     * Send notification to server.
     */
    public function notify(string $channel, string $level, string $message, array $context = []): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        // Build context
        $context = $this->contextBuilder->build($context);

        $payload = [
            'channel' => $channel,
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        // Queue or send immediately
        if (config('orthanc-client.queue.enabled', false)) {
            dispatch(new \G80st\OrthancClient\Jobs\SendNotificationJob($payload));

            return true;
        }

        return $this->sendNow($payload);
    }

    /**
     * Send notification immediately.
     */
    public function sendNow(array $payload): bool
    {
        try {
            $response = $this->makeRequest('/api/v1/notify', $payload);

            return $response['success'] ?? false;
        } catch (\Throwable $e) {
            return $this->handleFailure($e, $payload);
        }
    }

    /**
     * Send critical notification.
     */
    public function critical(string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, 'critical', $message, $context);
    }

    /**
     * Send error notification.
     */
    public function error(string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, 'error', $message, $context);
    }

    /**
     * Send warning notification.
     */
    public function warning(string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, 'warning', $message, $context);
    }

    /**
     * Send info notification.
     */
    public function info(string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, 'info', $message, $context);
    }

    /**
     * Send success notification.
     */
    public function success(string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, 'success', $message, $context);
    }

    /**
     * Send debug notification.
     */
    public function debug(string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, 'debug', $message, $context);
    }

    /**
     * Test connection to server.
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('/api/v1/health', [], 'GET');

            return $response['status'] === 'ok';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get available channels from server.
     */
    public function getChannels(): array
    {
        try {
            $response = $this->makeRequest('/api/v1/channels', [], 'GET');

            return $response['channels'] ?? [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Make HTTP request to server.
     */
    protected function makeRequest(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        $url = rtrim(config('orthanc-client.api_url'), '/').$endpoint;
        $token = config('orthanc-client.api_token');

        if (! $token) {
            throw new \RuntimeException('Orthanc API token not configured');
        }

        $request = Http::withToken($token)
            ->timeout(config('orthanc-client.timeout', 10))
            ->acceptJson();

        // Retry configuration
        if (config('orthanc-client.retry.enabled', true)) {
            $request->retry(
                config('orthanc-client.retry.times', 3),
                config('orthanc-client.retry.sleep', 100)
            );
        }

        $response = $method === 'GET'
            ? $request->get($url, $data)
            : $request->post($url, $data);

        if (! $response->successful()) {
            throw new \RuntimeException(
                "Orthanc server returned {$response->status()}: {$response->body()}"
            );
        }

        return $response->json();
    }

    /**
     * Handle request failure.
     */
    protected function handleFailure(\Throwable $e, array $payload): bool
    {
        if (config('orthanc-client.fallback.log', true)) {
            Log::error('Orthanc client failed to send notification', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);
        }

        if (config('orthanc-client.fallback.throw_on_failure', false)) {
            throw $e;
        }

        return false;
    }

    /**
     * Check if client is enabled.
     */
    protected function isEnabled(): bool
    {
        return config('orthanc-client.enabled', true)
            && config('orthanc-client.api_url')
            && config('orthanc-client.api_token');
    }
}
