<?php

declare(strict_types=1);

namespace OrthancTower\Client\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool notify(string $channel, string $level, string $message, array $context = [])
 * @method static bool critical(string $channel, string $message, array $context = [])
 * @method static bool error(string $channel, string $message, array $context = [])
 * @method static bool warning(string $channel, string $message, array $context = [])
 * @method static bool info(string $channel, string $message, array $context = [])
 * @method static bool success(string $channel, string $message, array $context = [])
 * @method static bool debug(string $channel, string $message, array $context = [])
 * @method static bool testConnection()
 * @method static array getChannels()
 *
 * @see \OrthancTower\Client\OrthancClient
 */
class Orthanc extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'orthanc-client';
    }
}
