<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Thoss\GapSort\SortItem;
use Thoss\GapSort\Traits\Sortable;

final class SortItemTest extends TestCase
{
    protected $request = null;
    protected $sortItem = null;

    public const SORT_COLUM = 'order';

    public const SORT_GAP = 100;

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-gap-sort.sorting.gap', self::SORT_GAP);
        $app['config']->set('laravel-gap-sort.sorting.column', self::SORT_COLUM);

        $this->sortItem = new SortItem(Dummy::class);

        Carbon::setTestNow(Carbon::now());

        $app['config']->set('database.default', 'test');
        $app['config']->set('database.connections.test', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->createSchema();
        $this->request = Request::create('', 'POST');
    }

    /**
     * Tear down the database schema.
     */
    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
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

    protected function createDummies($count = 1)
    {
        return collect(range(1, $count))->map(function (int $i) {
            return Dummy::create([
                'name' => 'Test-'.$i,
            ]);
        });
    }

    public function testCreatingModelsWithCorrectOrder()
    {
        $dummies = $this->createDummies(10);

        $dummies->each(function ($user, $index) {
            $this->assertEquals((self::SORT_GAP * $index) + self::SORT_GAP, $user->order);
        });
    }
}

class Dummy extends Model
{
    use Sortable;

    protected $fillable = [
        'name',
    ];
}
