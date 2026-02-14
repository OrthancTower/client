<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Unit;

use OrthancTower\Client\Support\ContextBuilder;
use OrthancTower\Client\Tests\TestCase;

class ContextBuilderTest extends TestCase
{
    public function test_sanitization_and_flags()
    {
        config([
            'orthanc-client.context.app_name' => 'My App',
            'orthanc-client.context.include_user' => false,
            'orthanc-client.context.include_ip' => false,
            'orthanc-client.context.include_route' => false,
            'orthanc-client.context.include_user_agent' => false,
            'orthanc-client.context.sanitize_fields' => ['app.name', 'timestamp'],
        ]);

        $builder = new ContextBuilder();
        $context = $builder->build([]);

        $this->assertSame('[REDACTED]', $context['app']['name']);
        $this->assertSame('[REDACTED]', $context['timestamp']);
        $this->assertArrayNotHasKey('user', $context);
        $this->assertArrayNotHasKey('ip', $context);
        $this->assertArrayNotHasKey('route', $context);
        $this->assertArrayNotHasKey('user_agent', $context);
    }
}
