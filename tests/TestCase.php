<?php

namespace Thoss\GapSort\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Thoss\GapSort\GapSortServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            GapSortServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        // config()->set('database.default', 'testing');
    }
}
