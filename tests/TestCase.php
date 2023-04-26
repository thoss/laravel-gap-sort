<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Thoss\GapSort\GapSortServiceProvider;

class TestCase extends Orchestra
{
    public const SORT_COLUM = 'custom_order';

    public const SORT_GAP = 100;

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

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('gap-sort.order_gap', self::SORT_GAP);
        $app['config']->set('gap-sort.order_column', self::SORT_COLUM);

        $this->createSchema();
    }

    /**
     * Tear down the database schema.
     */
    protected function tearDown(): void
    {
        Schema::drop('dummies');
    }

    protected function createSchema()
    {
        Schema::create('dummies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger(self::SORT_COLUM)->nullable();
            $table->timestamps();
        });
    }
}
