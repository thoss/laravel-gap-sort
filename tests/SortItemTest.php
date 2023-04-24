<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Database\Eloquent\Model as Eloquent;
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

        $this->sortItem = new SortItem(User::class);

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
        Schema::drop('users');
    }

    protected function createSchema()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->unsignedInteger(self::SORT_COLUM)->nullable();
            $table->timestamps();
        });
    }

    protected function createUsers($count = 1)
    {
        return collect(range(1, $count))->map(function (int $i) {
            return User::create([
                'name' => 'Test-'.$i,
            ]);
        });
    }

    public function testCreatingModelsWithCorrectOrder()
    {
        $users = $this->createUsers(10);

        $users->each(function ($user, $index) {
            $this->assertEquals((self::SORT_GAP * $index) + self::SORT_GAP, $user->order);
        });
    }
}

class User extends Eloquent
{
    use Sortable;

    protected $fillable = [
        'name',
    ];
}
