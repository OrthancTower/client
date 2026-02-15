<?php

declare(strict_types=1);

namespace OrthancTower\Client\Contracts;

use OrthancTower\Contracts\DTO\NotificationPayload;
use OrthancTower\Contracts\Enums\Channel;
use OrthancTower\Contracts\Enums\Level;

interface OrthancClientContract
{
    public function notify(Channel|string $channel, Level|string $level, string $message, array $context = []): bool;

    public function sendNow(NotificationPayload|array $payload): bool;

    public function critical(Channel|string $channel, string $message, array $context = []): bool;

    public function error(Channel|string $channel, string $message, array $context = []): bool;

    public function warning(Channel|string $channel, string $message, array $context = []): bool;

    public function info(Channel|string $channel, string $message, array $context = []): bool;

    public function success(Channel|string $channel, string $message, array $context = []): bool;

    public function debug(Channel|string $channel, string $message, array $context = []): bool;

    public function testConnection(): bool;

    public function getChannels(): array;
}
