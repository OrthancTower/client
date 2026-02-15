<?php

declare(strict_types=1);

namespace OrthancTower\Client\Exceptions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use OrthancTower\Client\Facades\Orthanc;
use OrthancTower\Client\Support\ContextBuilder;
use Throwable;

class OrthancClientExceptionHandler extends ExceptionHandler
{
    protected ContextBuilder $contextBuilder;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->contextBuilder = new ContextBuilder;
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
            Log::error('Orthanc client failed to report exception', [
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
        $criticalExceptions = config('orthanc-client.exceptions.critical', []);
        foreach ($criticalExceptions as $class) {
            if ($exception instanceof $class) {
                return 'critical';
            }
        }

        $warningExceptions = config('orthanc-client.exceptions.warning', []);
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
        $channels = config('orthanc-client.exception_channels', []);
        if ($this->getExceptionLevel($exception) === 'critical') {
            return $channels['critical'] ?? 'critical-errors';
        }

        $securityExceptions = config('orthanc-client.exceptions.security', []);
        foreach ($securityExceptions as $class) {
            if ($exception instanceof $class) {
                return $channels['security'] ?? 'sting-alerts';
            }
        }

        return $channels['default'] ?? 'critical-errors';
    }
}
