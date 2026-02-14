<?php

declare(strict_types=1);

namespace G80st\OrthancClient\Exceptions;

use G80st\OrthancClient\Facades\Orthanc;
use G80st\OrthancClient\Support\ContextBuilder;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class OrthancClientExceptionHandler extends ExceptionHandler
{
    protected ContextBuilder $contextBuilder;

    public function __construct($container)
    {
        parent::__construct($container);
        $this->contextBuilder = new ContextBuilder();
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        parent::report($e);

        // Send to Orthanc if enabled
        if ($this->shouldReportToOrthanc($e)) {
            $this->reportToOrthanc($e);
        }
    }

    /**
     * Check if exception should be reported to Orthanc.
     */
    protected function shouldReportToOrthanc(Throwable $exception): bool
    {
        if (! config('orthanc-client.auto_report_exceptions', true)) {
            return false;
        }

        if (! config('orthanc-client.enabled', true)) {
            return false;
        }

        // Check ignored exceptions
        $ignoredExceptions = config('orthanc-client.ignore_exceptions', []);

        foreach ($ignoredExceptions as $ignoredException) {
            if ($exception instanceof $ignoredException) {
                return false;
            }
        }

        return true;
    }

    /**
     * Report exception to Orthanc server.
     */
    protected function reportToOrthanc(Throwable $exception): void
    {
        try {
            $level = $this->getExceptionLevel($exception);
            $channel = $this->getExceptionChannel($exception);
            $context = $this->contextBuilder->buildFromException($exception);

            Orthanc::notify(
                $channel,
                $level,
                $exception->getMessage(),
                $context
            );
        } catch (\Throwable $e) {
            // Don't let Orthanc errors crash the app
            \Log::error('Orthanc client failed to report exception', [
                'error' => $e->getMessage(),
                'original_exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Get severity level for exception.
     */
    protected function getExceptionLevel(Throwable $exception): string
    {
        $criticalExceptions = [
            \PDOException::class,
            \Illuminate\Database\QueryException::class,
            \RuntimeException::class,
        ];

        foreach ($criticalExceptions as $class) {
            if ($exception instanceof $class) {
                return 'critical';
            }
        }

        $warningExceptions = [
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        ];

        foreach ($warningExceptions as $class) {
            if ($exception instanceof $class) {
                return 'warning';
            }
        }

        return 'error';
    }

    /**
     * Get default channel for exception.
     */
    protected function getExceptionChannel(Throwable $exception): string
    {
        // Critical exceptions
        if ($this->getExceptionLevel($exception) === 'critical') {
            return 'critical-errors';
        }

        // Security exceptions
        $securityExceptions = [
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Illuminate\Auth\AuthenticationException::class,
        ];

        foreach ($securityExceptions as $class) {
            if ($exception instanceof $class) {
                return 'sting-alerts';
            }
        }

        // Default
        return 'critical-errors';
    }
}
