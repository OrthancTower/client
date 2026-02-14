<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Feature;

use Illuminate\Support\Facades\Http;
use OrthancTower\Client\Facades\Orthanc;
use OrthancTower\Client\Tests\TestCase;

class RetryTest extends TestCase
{
    public function test_sendnow_succeeds_after_retries_when_enabled()
    {
        config([
            'orthanc-client.enabled' => true,
            'orthanc-client.api_url' => 'https://orthanc.test',
            'orthanc-client.api_token' => 'token',
            'orthanc-client.retry.enabled' => true,
            'orthanc-client.retry.times' => 3,
            'orthanc-client.retry.base_ms' => 1,
            'orthanc-client.retry.cap_ms' => 5,
            'orthanc-client.retry.jitter' => 'full',
            'orthanc-client.queue.enabled' => false,
        ]);

        Http::fake([
            'https://orthanc.test/api/v1/notify' => Http::sequence()
                ->push([], 500)
                ->push([], 502)
                ->push(['success' => true], 200),
        ]);

        $this->assertTrue(Orthanc::notify('general', 'info', 'message', []));
        Http::assertSentCount(3);
    }

    public function test_sendnow_fails_without_retries_when_disabled()
    {
        config([
            'orthanc-client.enabled' => true,
            'orthanc-client.api_url' => 'https://orthanc.test',
            'orthanc-client.api_token' => 'token',
            'orthanc-client.retry.enabled' => false,
            'orthanc-client.retry.times' => 1,
            'orthanc-client.queue.enabled' => false,
        ]);

        Http::fake([
            'https://orthanc.test/api/v1/notify' => Http::response([], 500),
        ]);

        $this->assertFalse(Orthanc::notify('general', 'info', 'message', []));
        Http::assertSentCount(1);
    }
}
