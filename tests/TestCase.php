<?php

declare(strict_types=1);

namespace OrthancTower\Client\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use OrthancTower\Client\OrthancClientServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            OrthancClientServiceProvider::class,
        ];
    }
}
