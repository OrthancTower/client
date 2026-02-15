<?php

declare(strict_types=1);

namespace OrthancTower\Client\Exceptions;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use OrthancTower\Client\Facades\Orthanc;
use OrthancTower\Client\Support\ContextBuilder;
use OrthancTower\Contracts\Enums\Channel;
use OrthancTower\Contracts\Enums\Level;
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
    protected function getExceptionLevel(Throwable $exception): Level
    {
        $criticalExceptions = config('orthanc-client.exceptions.critical', []);
        foreach ($criticalExceptions as $class) {
            if ($exception instanceof $class) {
                return Level::Critical;
            }
        }

        $warningExceptions = config('orthanc-client.exceptions.warning', []);
        foreach ($warningExceptions as $class) {
            if ($exception instanceof $class) {
                return Level::Warning;
            }
        }

        return Level::Error;
    }

    /**
     * Get default channel for exception.
     */
    protected function getExceptionChannel(Throwable $exception): Channel
    {
        $channels = config('orthanc-client.exception_channels', []);
        if ($this->getExceptionLevel($exception) === Level::Critical) {
            return isset($channels['critical']) ? (Channel::tryFrom($channels['critical']) ?? Channel::System) : Channel::System;
        }

        $securityExceptions = config('orthanc-client.exceptions.security', []);
        foreach ($securityExceptions as $class) {
            if ($exception instanceof $class) {
                return isset($channels['security']) ? (Channel::tryFrom($channels['security']) ?? Channel::Security) : Channel::Security;
            }
        }

        return isset($channels['default']) ? (Channel::tryFrom($channels['default']) ?? Channel::System) : Channel::System;
    }
}
