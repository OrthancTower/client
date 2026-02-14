<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Feature;

use Illuminate\Support\Facades\Http;
use OrthancTower\Client\Facades\Orthanc;
use OrthancTower\Client\Tests\TestCase;

class HttpErrorsTest extends TestCase
{
    public function test_422_does_not_retry_and_returns_false()
    {
        config([
            'orthanc-client.enabled' => true,
            'orthanc-client.api_url' => 'https://orthanc.test',
            'orthanc-client.api_token' => 'token',
            'orthanc-client.retry.enabled' => true,
            'orthanc-client.retry.times' => 3,
            'orthanc-client.queue.enabled' => false,
        ]);

        Http::fake([
            'https://orthanc.test/api/v1/notify' => Http::response([], 422),
        ]);

        $this->assertFalse(Orthanc::notify('general', 'info', 'message', []));
        Http::assertSentCount(1);
    }

    public function test_timeout_retries_then_fails()
    {
        config([
            'orthanc-client.enabled' => true,
            'orthanc-client.api_url' => 'https://orthanc.test',
            'orthanc-client.api_token' => 'token',
            'orthanc-client.retry.enabled' => true,
            'orthanc-client.retry.times' => 3,
            'orthanc-client.retry.base_ms' => 1,
            'orthanc-client.retry.cap_ms' => 2,
            'orthanc-client.retry.jitter' => 'none',
            'orthanc-client.queue.enabled' => false,
        ]);

        Http::fake(function () {
            throw new \RuntimeException('timeout');
        });

        $delays = [];
        app('orthanc-client')->setSleeper(function (int $ms) use (&$delays) {
            $delays[] = $ms;
        });

        $this->assertFalse(Orthanc::notify('general', 'info', 'message', []));
        $this->assertCount(2, $delays);
    }
}
