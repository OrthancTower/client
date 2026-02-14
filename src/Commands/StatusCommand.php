<?php

declare(strict_types=1);

namespace G80st\OrthancClient\Commands;

use Illuminate\Console\Command;

class StatusCommand extends Command
{
    protected $signature = 'orthanc:status';

    protected $description = 'Display Orthanc client configuration status';

    public function handle(): int
    {
        $this->info('🗼 Orthanc Client Status');
        $this->newLine();

        // General
        $this->line('<fg=cyan>Configuration</>');
        $this->line('─────────────────────────────────────');

        $enabled = config('orthanc-client.enabled') ? '✅ Enabled' : '❌ Disabled';
        $this->line("Status: {$enabled}");

        $apiUrl = config('orthanc-client.api_url') ?: '❌ Not configured';
        $this->line("API URL: {$apiUrl}");

        $apiToken = config('orthanc-client.api_token')
            ? '✅ Configured ('.substr(config('orthanc-client.api_token'), 0, 20).'...)'
            : '❌ Not configured';
        $this->line("API Token: {$apiToken}");

        $timeout = config('orthanc-client.timeout', 10);
        $this->line("Timeout: {$timeout}s");

        $this->newLine();

        // Retry
        $this->line('<fg=cyan>Retry Configuration</>');
        $this->line('─────────────────────────────────────');

        $retryEnabled = config('orthanc-client.retry.enabled') ? '✅ Enabled' : '❌ Disabled';
        $this->line("Status: {$retryEnabled}");

        if (config('orthanc-client.retry.enabled')) {
            $this->line('  Times: '.config('orthanc-client.retry.times'));
            $this->line('  Sleep: '.config('orthanc-client.retry.sleep').'ms');
        }

        $this->newLine();

        // Queue
        $this->line('<fg=cyan>Queue Configuration</>');
        $this->line('─────────────────────────────────────');

        $queueEnabled = config('orthanc-client.queue.enabled') ? '✅ Enabled' : '❌ Disabled';
        $this->line("Status: {$queueEnabled}");

        if (config('orthanc-client.queue.enabled')) {
            $this->line('  Connection: '.config('orthanc-client.queue.connection'));
            $this->line('  Queue: '.config('orthanc-client.queue.queue'));
        }

        $this->newLine();

        // Context
        $this->line('<fg=cyan>Context Configuration</>');
        $this->line('─────────────────────────────────────');

        $this->line('App Name: '.config('orthanc-client.context.app_name'));
        $this->line('Environment: '.config('orthanc-client.context.environment'));
        $this->line('Include User: '.(config('orthanc-client.context.include_user') ? '✅' : '❌'));
        $this->line('Include IP: '.(config('orthanc-client.context.include_ip') ? '✅' : '❌'));
        $this->line('Include Route: '.(config('orthanc-client.context.include_route') ? '✅' : '❌'));

        $this->newLine();

        // Exception Handling
        $this->line('<fg=cyan>Exception Handling</>');
        $this->line('─────────────────────────────────────');

        $autoReport = config('orthanc-client.auto_report_exceptions') ? '✅ Enabled' : '❌ Disabled';
        $this->line("Auto Report: {$autoReport}");

        $ignored = config('orthanc-client.ignore_exceptions', []);
        if (! empty($ignored)) {
            $this->line('Ignored Exceptions: '.count($ignored));
        }

        return self::SUCCESS;
    }
}
