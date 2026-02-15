<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Feature;

use Illuminate\Support\Facades\Http;
use OrthancTower\Client\Tests\TestCase;

class CommandsTest extends TestCase
{
    public function test_test_connection_masks_token_by_default_and_lists_channels()
    {
        config([
            'orthanc-client.api_url' => 'https://orthanc.test',
            'orthanc-client.api_token' => 'secret-token-1234567890',
        ]);

        Http::fake([
            'https://orthanc.test/api/health' => Http::response(['status' => 'ok'], 200),
            'https://orthanc.test/api/channels' => Http::response(['channels' => [
                ['name' => 'critical-errors', 'allowed_levels' => 'critical,error'],
                ['name' => 'general', 'allowed_levels' => 'info,success'],
            ]], 200),
        ]);

        $this->artisan('orthanc:test-connection')
            ->expectsOutput('Configuration:')
            ->expectsOutput('  API URL: https://orthanc.test')
            ->expectsOutput('  Token: ✅ Configured')
            ->expectsOutput('✅ Connection successful!')
            ->expectsOutput('Available channels:')
            ->expectsOutput('  • critical-errors (critical,error)')
            ->expectsOutput('  • general (info,success)')
            ->assertExitCode(0);
    }
}
