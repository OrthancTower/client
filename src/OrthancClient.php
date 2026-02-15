<?php

declare(strict_types=1);

namespace OrthancTower\Client;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OrthancTower\Client\Support\ContextBuilder;
use OrthancTower\Contracts\DTO\NotificationPayload;
use OrthancTower\Contracts\Enums\Channel;
use OrthancTower\Contracts\Enums\Level;

class OrthancClient
{
    protected ContextBuilder $contextBuilder;

    protected $sleeper = null;

    public function __construct()
    {
        $this->contextBuilder = new ContextBuilder;
    }

    /**
     * Send notification to server.
     */
    public function notify(Channel|string $channel, Level|string $level, string $message, array $context = []): bool
    {
        // LOG: Verificar se está habilitado
        Log::debug('Orthanc Client: notify() called', [
            'channel' => is_string($channel) ? $channel : $channel->value,
            'level' => is_string($level) ? $level : $level->value,
            'message' => substr($message, 0, 100),
            'is_enabled' => $this->isEnabled(),
        ]);

        if (! $this->isEnabled()) {
            Log::warning('Orthanc Client: disabled or misconfigured', [
                'enabled' => config('orthanc-client.enabled', true),
                'has_url' => ! empty(config('orthanc-client.api_url')),
                'has_token' => ! empty(config('orthanc-client.api_token')),
            ]);

            return false;
        }

        // Build context
        $context = $this->contextBuilder->build($context);

        $payload = new NotificationPayload(
            channel: is_string($channel) ? Channel::from($channel) : $channel,
            level: is_string($level) ? Level::from($level) : $level,
            message: $message,
            context: $context,
        );

        // LOG: Verificar se vai enfileirar
        $queueEnabled = config('orthanc-client.queue.enabled', false);
        Log::info('Orthanc Client: queue check', [
            'queue_enabled' => $queueEnabled,
        ]);

        // Queue or send immediately
        if ($queueEnabled) {
            Log::info('Orthanc Client: dispatching to queue');
            dispatch(new \OrthancTower\Client\Jobs\SendNotificationJob($payload->toArray()));

            return true;
        }

        Log::info('Orthanc Client: sending immediately');

        return $this->sendNow($payload);
    }

    /**
     * Send notification immediately.
     */
    public function sendNow(NotificationPayload|array $payload): bool
    {
        Log::info('Orthanc Client: sendNow() called', [
            'channel' => $payload instanceof NotificationPayload ? $payload->channel->value : ($payload['channel'] ?? null),
            'level' => $payload instanceof NotificationPayload ? $payload->level->value : ($payload['level'] ?? null),
        ]);

        $data = $payload instanceof NotificationPayload ? $payload->toArray() : $payload;

        try {
            $response = $this->makeRequest('/api/notify', $data);

            Log::info('Orthanc Client: request completed', [
                'success' => $response['success'] ?? false,
                'response' => $response,
            ]);

            return $response['success'] ?? false;
        } catch (\Throwable $e) {
            Log::error('Orthanc Client: sendNow() exception', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return $this->handleFailure($e, $data);
        }
    }

    /**
     * Send critical notification.
     */
    public function critical(Channel|string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, Level::Critical, $message, $context);
    }

    /**
     * Send error notification.
     */
    public function error(Channel|string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, Level::Error, $message, $context);
    }

    /**
     * Send warning notification.
     */
    public function warning(Channel|string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, Level::Warning, $message, $context);
    }

    /**
     * Send info notification.
     */
    public function info(Channel|string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, Level::Info, $message, $context);
    }

    /**
     * Send success notification.
     */
    public function success(Channel|string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, Level::Notice, $message, $context);
    }

    /**
     * Send debug notification.
     */
    public function debug(Channel|string $channel, string $message, array $context = []): bool
    {
        return $this->notify($channel, Level::Debug, $message, $context);
    }

    /**
     * Test connection to server.
     */
    public function testConnection(): bool
    {
        try {
            $response = $this->makeRequest('/api/health', [], 'GET');

            if (! is_array($response)) {
                return false;
            }
            if (($response['status'] ?? null) === 'ok') {
                return true;
            }
            if (($response['success'] ?? null) === true) {
                return true;
            }

            return false;
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
            $response = $this->makeRequest('/api/channels', [], 'GET');

            $channels = $response['channels'] ?? $response['data'] ?? [];

            return is_array($channels) ? $channels : [];
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

        Log::info('Orthanc Client: makeRequest() starting', [
            'url' => $url,
            'method' => $method,
            'has_token' => ! empty($token),
            'token_prefix' => $token ? substr($token, 0, 15).'...' : null,
        ]);

        if (! $token) {
            throw new \RuntimeException('Orthanc API token not configured');
        }

        $times = (int) config('orthanc-client.retry.times', 3);
        $enabled = (bool) config('orthanc-client.retry.enabled', true);
        $base = (int) config('orthanc-client.retry.base_ms', 100);
        $cap = (int) config('orthanc-client.retry.cap_ms', 2000);
        $jitter = (string) config('orthanc-client.retry.jitter', 'full');

        $attempt = 0;
        $lastError = null;
        $nonRetriable = false;

        while (true) {
            try {
                Log::debug('Orthanc Client: attempt', [
                    'attempt' => $attempt + 1,
                    'max_attempts' => $times,
                ]);

                $headers = [];
                if (config('orthanc-client.idempotency.enabled', false)) {
                    $keyBase = json_encode([
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'payload' => $data,
                    ]);
                    $headers['X-Idempotency-Key'] = hash('sha256', (string) $keyBase);
                }

                $req = Http::withToken($token)
                    ->withHeaders($headers)
                    ->timeout(config('orthanc-client.timeout', 10))
                    ->acceptJson();

                Log::info('Orthanc Client: sending HTTP request', [
                    'url' => $url,
                    'headers_count' => count($headers),
                    'payload_keys' => array_keys($data),
                ]);

                $response = $method === 'GET'
                    ? $req->get($url, $data)
                    : $req->post($url, $data);

                Log::info('Orthanc Client: HTTP response received', [
                    'status' => $response->status(),
                    'successful' => $response->successful(),
                    'body_preview' => substr($response->body(), 0, 200),
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                $status = $response->status();
                // Don't retry client errors except 429 (rate limit)
                if ($status >= 400 && $status < 500 && $status !== 429) {
                    $nonRetriable = true;
                    $lastError = new \RuntimeException("Orthanc server returned {$status}: {$response->body()}");
                    Log::error('Orthanc Client: non-retriable error', [
                        'status' => $status,
                        'body' => $response->body(),
                    ]);
                } else {
                    $lastError = new \RuntimeException("Orthanc server returned {$status}: {$response->body()}");
                    Log::warning('Orthanc Client: retriable error', [
                        'status' => $status,
                    ]);
                }
            } catch (\Throwable $e) {
                $lastError = $e;
                Log::error('Orthanc Client: HTTP exception', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }

            if ($nonRetriable) {
                throw $lastError ?? new \RuntimeException('Orthanc request failed');
            }

            if (! $enabled || $attempt >= ($times - 1)) {
                Log::error('Orthanc Client: max retries reached', [
                    'attempt' => $attempt,
                    'max' => $times,
                ]);
                throw $lastError ?? new \RuntimeException('Orthanc request failed');
            }

            $delay = $this->computeBackoffMs($attempt, $base, $cap, $jitter);
            if (isset($response) && in_array($response->status(), [429, 503], true)) {
                $retryAfter = $response->header('Retry-After');
                if (is_string($retryAfter) && ctype_digit($retryAfter)) {
                    $delay = (int) $retryAfter * 1000;
                }
            }

            Log::info('Orthanc Client: retrying', [
                'delay_ms' => $delay,
                'next_attempt' => $attempt + 2,
            ]);

            $this->sleepMs($delay);
            $attempt++;
        }
    }

    protected function computeBackoffMs(int $attempt, int $baseMs, int $capMs, string $jitter): int
    {
        $exp = (int) min($capMs, $baseMs * (2 ** $attempt));
        if ($jitter === 'equal') {
            return intdiv($exp, 2) + random_int(0, max(1, intdiv($exp, 2)));
        }
        if ($jitter === 'full') {
            return random_int(0, max(1, $exp));
        }

        return max(1, $exp);
    }

    protected function sleepMs(int $ms): void
    {
        $fn = $this->sleeper;
        if (is_callable($fn)) {
            $fn(max(0, $ms));

            return;
        }
        usleep(max(0, $ms) * 1000);
    }

    public function setSleeper(callable $sleeper): void
    {
        $this->sleeper = $sleeper;
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
