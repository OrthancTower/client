<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Feature;

use Illuminate\Support\Facades\Http;
use OrthancTower\Client\Facades\Orthanc;
use OrthancTower\Client\Tests\TestCase;

class RetryAfterTest extends TestCase
{
    public function test_honors_retry_after_header_on_429()
    {
        config([
            'orthanc-client.enabled' => true,
            'orthanc-client.api_url' => 'https://orthanc.test',
            'orthanc-client.api_token' => 'token',
            'orthanc-client.retry.enabled' => true,
            'orthanc-client.retry.times' => 2,
            'orthanc-client.retry.base_ms' => 1,
            'orthanc-client.retry.cap_ms' => 5,
            'orthanc-client.retry.jitter' => 'none',
            'orthanc-client.queue.enabled' => false,
        ]);

        Http::fake([
            'https://orthanc.test/api/notify' => Http::sequence()
                ->push([], 429, ['Retry-After' => '1'])
                ->push(['success' => true], 200),
        ]);

        $delays = [];
        app('orthanc-client')->setSleeper(function (int $ms) use (&$delays) {
            $delays[] = $ms;
        });

        $this->assertTrue(Orthanc::notify('general', 'info', 'message', []));
        $this->assertNotEmpty($delays);
        $this->assertTrue(in_array(1000, $delays, true));
    }
}
