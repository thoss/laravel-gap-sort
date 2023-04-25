<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase;
use Thoss\GapSort\SortModel;
use Thoss\GapSort\Traits\Sortable;

final class SortItemTest extends TestCase
{
    public const SORT_COLUM = 'custom_order';

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
        $app['config']->set('laravel-gap-sort.order_gap', self::SORT_GAP);
        $app['config']->set('laravel-gap-sort.order_column', self::SORT_COLUM);

        Carbon::setTestNow(Carbon::now());

        $app['config']->set('database.default', 'test');
        $app['config']->set('database.connections.test', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->createSchema();
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
                self::SORT_COLUM => $i * self::SORT_GAP, // TODO: Das müsste der Sortable Trait übernehmen
            ]);
        });
    }

    public function testCreatingModelsWithCorrectOrder()
    {
        $dummies = $this->createDummies(10);

        $dummies->each(function ($dummy, $index) {
            $this->assertEquals((self::SORT_GAP * $index) + self::SORT_GAP, $dummy->{self::SORT_COLUM});
        });
    }

    public function testInitializeTable()
    {
        $dummies = $this->createDummies(10);

        // Reset to 0
        Dummy::whereIn('id', $dummies->pluck('id')->toArray())
        ->update([
            self::SORT_COLUM => 0,
        ]);

        $dummies = Dummy::all();

        // Assert 0
        $dummies->each(function ($dummy) {
            $this->assertEquals(0, $dummy->{self::SORT_COLUM});
        });

        // Init the table
        dispatch(new SortModel(modelString: Dummy::class, initTable:true));
        
        $dummies = Dummy::all();

        $dummies->each(function ($dummy, $index) {
            $this->assertEquals((self::SORT_GAP * $index) + self::SORT_GAP, $dummy->{self::SORT_COLUM});
        });
    }

    public function testMultipleOperationsWithReInitTable () {
        $this->createDummies(3);

        dispatch(new SortModel(modelString: Dummy::class, main:3, previous:1, next:2));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(150, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(200, $sortedDummies[2]->{self::SORT_COLUM});

        dispatch(new SortModel(modelString: Dummy::class, main:2, previous:1, next:3));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(125, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(150, $sortedDummies[2]->{self::SORT_COLUM});
        
        dispatch(new SortModel(modelString: Dummy::class, main:3, previous:2, next:1));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();
        
        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(112, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(125, $sortedDummies[2]->{self::SORT_COLUM});

        dispatch(new SortModel(modelString: Dummy::class, main:2, previous:1, next:3));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(106, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(112, $sortedDummies[2]->{self::SORT_COLUM});
        
        dispatch(new SortModel(modelString: Dummy::class, main:3, previous:1, next:2));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(103, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(106, $sortedDummies[2]->{self::SORT_COLUM});

        dispatch(new SortModel(modelString: Dummy::class, main:2, previous:1, next:3));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();
        
        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(101, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(103, $sortedDummies[2]->{self::SORT_COLUM});
        
        dispatch(new SortModel(modelString: Dummy::class, main:3, previous:1, next:2));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        // new order after reinitialize table
        $this->assertEquals(100, $sortedDummies[0]->{self::SORT_COLUM});
        $this->assertEquals(150, $sortedDummies[1]->{self::SORT_COLUM});
        $this->assertEquals(200, $sortedDummies[2]->{self::SORT_COLUM});
    }

    public function testOrderVeryFirst () {
        $this->createDummies(3);

        dispatch(new SortModel(modelString: Dummy::class, main:3, next:1));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        $this->assertEquals($sortedDummies->where('id', 3)->first()->id, $sortedDummies[0]->id);
        $this->assertEquals($sortedDummies->where('id', 1)->first()->id, $sortedDummies[1]->id);
        $this->assertEquals($sortedDummies->where('id', 2)->first()->id, $sortedDummies[2]->id);
    }

    public function testOrderVeryLast () {
        $this->createDummies(3);

        dispatch(new SortModel(modelString: Dummy::class, main:1, previous:3));

        $sortedDummies = Dummy::orderBy(self::SORT_COLUM)->get();

        $this->assertEquals($sortedDummies->where('id', 2)->first()->id, $sortedDummies[0]->id);
        $this->assertEquals($sortedDummies->where('id', 3)->first()->id, $sortedDummies[1]->id);
        $this->assertEquals($sortedDummies->where('id', 1)->first()->id, $sortedDummies[2]->id);
    }
}

class Dummy extends Model
{
    use Sortable;

    protected $guarded = [];
}
