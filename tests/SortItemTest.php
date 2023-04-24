<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Thoss\GapSort\Support\SortItem;
use Thoss\GapSort\Traits\Model\Sortable;

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
        $app['config']->set('da-helper.sorting.gap', 100);
        $app['config']->set('da-helper.sorting.column', 'order');

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
            $table->integer('order')->nullable();
            $table->timestamps();
        });
    }

    protected function setAndGet($orders, $requestBody)
    {
        // create User
        foreach ($orders as $order) {
            User::create(['order' => $order]);
        }

        // set request body
        $this->request->merge($requestBody);

        $this->sortItem->handle($this->request);

        return (object) [
            'main' => isset($requestBody['main']) ? User::find($requestBody['main']) : null,
            'previous' => isset($requestBody['previous']) ? User::find($requestBody['previous']) : null,
            'next' => isset($requestBody['next']) ? User::find($requestBody['next']) : null,
        ];
    }

    public function testNoGap()
    {
        $users = $this->setAndGet([1, 2, 3], [
            'main' => 1,
            'previous' => 2,
            'next' => 3,
        ]);

        // ob Gap zwischen orders erstellt wurde
        $diffMainAndPrevious = $users->main->order - $users->previous->order;
        $diffNextAndMain = $users->next->order - $users->main->order;

        $this->assertTrue($diffMainAndPrevious > 1 && $diffNextAndMain > 1);

        // ob Items richtig sortiert wurden
        $this->assertTrue($users->main->order > $users->previous->order && $users->main->order < $users->next->order);
    }

    public function testWithoutNextItem()
    {
        $users = $this->setAndGet([1, 100], [
            'main' => 1,
            'previous' => 2,
        ]);

        $diffMainAndPrevious = $users->main->order - $users->previous->order;

        // wenn Gap > 1 wäre, ist MainOrder so oder so großer. D.h. richtig sortiert
        $this->assertTrue($diffMainAndPrevious > 1);
    }

    public function testWithoutPrevious()
    {
        $users = $this->setAndGet([1, 100], [
            'main' => 2,
            'next' => 1,
        ]);

        $diffNextAndMain = $users->next->order - $users->main->order;

        // wenn Gap > 1 wäre, ist MainOrder so oder so großer. D.h. richtig sortiert
        $this->assertTrue($diffNextAndMain > 1);
    }
}

class User extends Eloquent
{
    use Sortable;

    protected $fillable = ['order'];
}
