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

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-gap-sort.sorting.gap', 100);
        $app['config']->set('laravel-gap-sort.sorting.column', 'order');

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
            $table->unsignedInteger('order')->nullable();
            $table->timestamps();
        });
    }

    protected function createUsers($count = 1)
    {
        $users = collect([]);

        for ($i = 0; $i < $count; ++$i) {
            $users->push(User::create());
        }

        return $users;
    }

    public function testCreatingModelsWithCorrectOrder()
    {
        $users = $this->createUsers(5);

        $users->each(function ($u) {
            dd($u->order);
        });

        dd($users);
    }
}

class User extends Eloquent
{
    use Sortable;

    protected $fillable = ['order'];
}
