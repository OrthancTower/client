<?php

declare(strict_types=1);

namespace OrthancTower\Client\Contracts;

interface OrthancClientContract
{
    public function notify(string $channel, string $level, string $message, array $context = []): bool;
    public function sendNow(array $payload): bool;
    public function critical(string $channel, string $message, array $context = []): bool;
    public function error(string $channel, string $message, array $context = []): bool;
    public function warning(string $channel, string $message, array $context = []): bool;
    public function info(string $channel, string $message, array $context = []): bool;
    public function success(string $channel, string $message, array $context = []): bool;
    public function debug(string $channel, string $message, array $context = []): bool;
    public function testConnection(): bool;
    public function getChannels(): array;
}
