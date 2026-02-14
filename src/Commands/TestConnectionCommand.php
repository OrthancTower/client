<?php

declare(strict_types=1);

namespace OrthancTower\Client\Commands;

use OrthancTower\Client\Facades\Orthanc;

use Illuminate\Console\Command;

class TestConnectionCommand extends Command
{
    protected $signature = 'orthanc:test-connection {--show-token-partial}';

    protected $description = 'Test connection to Orthanc server';

    public function handle(): int
    {
        $this->info('🧪 Testing connection to Orthanc server...');
        $this->newLine();

        // Check configuration
        if (! config('orthanc-client.api_url')) {
            $this->error('❌ ORTHANC_API_URL not configured!');

            return 1;
        }

        if (! config('orthanc-client.api_token')) {
            $this->error('❌ ORTHANC_API_TOKEN not configured!');

            return 1;
        }

        $this->line('Configuration:');
        $this->line('  API URL: '.config('orthanc-client.api_url'));
        if ($this->option('show-token-partial')) {
            $token = (string) config('orthanc-client.api_token');
            $masked = strlen($token) > 10
                ? substr($token, 0, 6).'***'.substr($token, -4)
                : '***';
            $this->line('  Token: '.$masked);
        } else {
            $this->line('  Token: ✅ Configured');
        }
        $this->newLine();

        // Test connection
        try {
            if (Orthanc::testConnection()) {
                $this->info('✅ Connection successful!');
                $this->newLine();

                // Get available channels
                $channels = Orthanc::getChannels();

                if (! empty($channels) && is_array($channels)) {
                    $this->line('Available channels:');
                    foreach ($channels as $channel) {
                        $name = is_array($channel) ? ($channel['name'] ?? (string) ($channel['id'] ?? 'unknown')) : (string) $channel;
                        $levels = is_array($channel) ? ($channel['allowed_levels'] ?? ($channel['levels'] ?? '')) : '';
                        $this->line($levels ? "  • {$name} ({$levels})" : "  • {$name}");
                    }
                }

                return 0;
            }

            $this->error('❌ Connection failed!');

            return 1;
        } catch (\Throwable $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return 1;
        }
    }
}
