<?php

declare(strict_types=1);

namespace G80st\OrthancClient\Commands;

use G80st\OrthancClient\Facades\Orthanc;
use Illuminate\Console\Command;

class TestConnectionCommand extends Command
{
    protected $signature = 'orthanc:test-connection';

    protected $description = 'Test connection to Orthanc server';

    public function handle(): int
    {
        $this->info('🧪 Testing connection to Orthanc server...');
        $this->newLine();

        // Check configuration
        if (! config('orthanc-client.api_url')) {
            $this->error('❌ ORTHANC_API_URL not configured!');

            return self::FAILURE;
        }

        if (! config('orthanc-client.api_token')) {
            $this->error('❌ ORTHANC_API_TOKEN not configured!');

            return self::FAILURE;
        }

        $this->line('Configuration:');
        $this->line('  API URL: '.config('orthanc-client.api_url'));
        $this->line('  Token: '.substr(config('orthanc-client.api_token'), 0, 20).'...');
        $this->newLine();

        // Test connection
        try {
            if (Orthanc::testConnection()) {
                $this->info('✅ Connection successful!');
                $this->newLine();

                // Get available channels
                $channels = Orthanc::getChannels();

                if (! empty($channels)) {
                    $this->line('Available channels:');
                    foreach ($channels as $channel) {
                        $this->line("  • {$channel['name']} ({$channel['allowed_levels']})");
                    }
                }

                return self::SUCCESS;
            }

            $this->error('❌ Connection failed!');

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
