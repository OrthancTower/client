<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Unit;

use OrthancTower\Client\OrthancClient;
use OrthancTower\Client\Tests\TestCase;

class OrthancClientTest extends TestCase
{
    public function test_notify_returns_false_when_disabled()
    {
        config([
            'orthanc-client.enabled' => false,
            'orthanc-client.api_url' => 'https://example.test',
            'orthanc-client.api_token' => 'token',
        ]);

        $client = new OrthancClient();
        $this->assertFalse($client->notify('general', 'info', 'msg', []));
    }
}
