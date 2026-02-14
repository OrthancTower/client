<?php

declare(strict_types=1);

namespace G80st\OrthancClient\Support;

class ContextBuilder
{
    /**
     * Build notification context.
     */
    public function build(array $context = []): array
    {
        $config = config('orthanc-client.context', []);

        // App information
        $context['app'] = [
            'name' => $config['app_name'] ?? config('app.name'),
            'environment' => $config['environment'] ?? app()->environment(),
            'url' => $config['app_url'] ?? config('app.url'),
        ];

        // Request context
        if ($request = request()) {
            if ($config['include_route'] ?? true) {
                $context['route'] = $request->method().' '.$request->path();
            }

            if ($config['include_ip'] ?? true) {
                $context['ip'] = $request->ip();
            }

            if ($config['include_user_agent'] ?? false) {
                $context['user_agent'] = $request->userAgent();
            }
        }

        // User context
        if (($config['include_user'] ?? true) && auth()->check()) {
            $user = auth()->user();
            $context['user'] = [
                'id' => $user->id,
                'email' => $user->email ?? null,
                'name' => $user->name ?? null,
            ];
        }

        // Timestamp
        $context['timestamp'] = now()->toIso8601String();

        return $context;
    }

    /**
     * Build exception context.
     */
    public function buildFromException(\Throwable $exception, array $additionalContext = []): array
    {
        $context = $this->build($additionalContext);

        $context['exception'] = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
        ];

        // Add trace for critical exceptions
        if ($this->isCritical($exception)) {
            $context['exception']['trace'] = $this->formatTrace($exception);
        }

        return $context;
    }

    /**
     * Check if exception is critical.
     */
    protected function isCritical(\Throwable $exception): bool
    {
        $criticalExceptions = [
            \PDOException::class,
            \Illuminate\Database\QueryException::class,
            \RuntimeException::class,
            \ErrorException::class,
        ];

        foreach ($criticalExceptions as $class) {
            if ($exception instanceof $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format exception trace.
     */
    protected function formatTrace(\Throwable $exception): array
    {
        return collect($exception->getTrace())
            ->take(10)
            ->map(function ($trace) {
                return [
                    'file' => $trace['file'] ?? 'unknown',
                    'line' => $trace['line'] ?? 0,
                    'function' => $trace['function'] ?? 'unknown',
                    'class' => $trace['class'] ?? null,
                ];
            })
            ->toArray();
    }
}
