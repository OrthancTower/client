<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests\Unit;

use OrthancTower\Client\Support\ContextBuilder;
use OrthancTower\Client\Tests\TestCase;

class SanitizationNestedTest extends TestCase
{
    public function test_nested_sanitization_paths()
    {
        config([
            'orthanc-client.context.include_user' => false,
            'orthanc-client.context.sanitize_fields' => [
                'user.email',
                'app.url',
            ],
        ]);

        $builder = new ContextBuilder();
        $context = $builder->build([
            'user' => [
                'id' => 1,
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ],
        ]);

        $this->assertSame('[REDACTED]', $context['user']['email']);
        $this->assertSame('[REDACTED]', $context['app']['url']);
        $this->assertSame(1, $context['user']['id']);
        $this->assertSame('John Doe', $context['user']['name']);
    }
}
