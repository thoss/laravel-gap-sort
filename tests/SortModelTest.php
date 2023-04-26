<?php

use Illuminate\Support\Facades\Config;
use Thoss\GapSort\SortModel;
use Thoss\GapSort\Tests\Dummy;

function get_order_gap()
{
    return Config::get('gap-sort.order_gap');
}

function get_order_column()
{
    return Config::get('gap-sort.order_column');
}

it('can create dummies with correct initial order', function () {
    $dummy1 = Dummy::create(['name' => 'dummy1']);
    $dummy2 = Dummy::create(['name' => 'dummy2']);
    $dummy3 = Dummy::create(['name' => 'dummy3']);

    expect($dummy1->{get_order_column()})->toBe(100);
    expect($dummy2->{get_order_column()})->toBe(200);
    expect($dummy3->{get_order_column()})->toBe(300);
});

it('can initialize table', function () {
    $dummy1 = Dummy::create(['name' => 'dummy1']);
    $dummy2 = Dummy::create(['name' => 'dummy2']);
    $dummy3 = Dummy::create(['name' => 'dummy3']);

    expect($dummy1->{get_order_column()})->toBe(100);
    expect($dummy2->{get_order_column()})->toBe(200);
    expect($dummy3->{get_order_column()})->toBe(300);

    // Reset to 0
    Dummy::whereIn('id', [$dummy1->id, $dummy2->id, $dummy3->id])
    ->update([
        get_order_column() => 0,
    ]);

    $dummies = Dummy::all();

    // Assert 0
    $dummies->each(function ($dummy) {
        expect($dummy->{get_order_column()})->toBe(0);
    });

    // Init the table
    dispatch(new SortModel(modelString: Dummy::class, initTable:true));
    
    $dummies = Dummy::all();

    $dummies->each(function ($dummy, $index) {
        expect($dummy->{get_order_column()})->toBe(($index * get_order_gap()) + get_order_gap());
    });
});

it('can do multiple operations with reinitialize table', function () {
    Dummy::create(['name' => 'dummy1']);
    Dummy::create(['name' => 'dummy2']);
    Dummy::create(['name' => 'dummy3']);

    dispatch(new SortModel(modelString: Dummy::class, main:3, previous:1, next:2));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(150);
    expect($sortedDummies[2]->{get_order_column()})->toBe(200);

    dispatch(new SortModel(modelString: Dummy::class, main:2, previous:1, next:3));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(125);
    expect($sortedDummies[2]->{get_order_column()})->toBe(150);

    dispatch(new SortModel(modelString: Dummy::class, main:3, previous:2, next:1));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();


    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(112);
    expect($sortedDummies[2]->{get_order_column()})->toBe(125);

    dispatch(new SortModel(modelString: Dummy::class, main:2, previous:1, next:3));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(106);
    expect($sortedDummies[2]->{get_order_column()})->toBe(112);

    dispatch(new SortModel(modelString: Dummy::class, main:3, previous:1, next:2));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(103);
    expect($sortedDummies[2]->{get_order_column()})->toBe(106);

    dispatch(new SortModel(modelString: Dummy::class, main:2, previous:1, next:3));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(101);
    expect($sortedDummies[2]->{get_order_column()})->toBe(103);

    dispatch(new SortModel(modelString: Dummy::class, main:3, previous:1, next:2));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    // new order after reinitialize table
    expect($sortedDummies[0]->{get_order_column()})->toBe(100);
    expect($sortedDummies[1]->{get_order_column()})->toBe(150);
    expect($sortedDummies[2]->{get_order_column()})->toBe(200);
});

it('can sort to very first', function () {
    Dummy::create(['name' => 'dummy1']);
    Dummy::create(['name' => 'dummy2']);
    Dummy::create(['name' => 'dummy3']);

    dispatch(new SortModel(modelString: Dummy::class, main:3, next:1));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies->where('id', 3)->first()->id)->toBe($sortedDummies[0]->id);
    expect($sortedDummies->where('id', 1)->first()->id)->toBe($sortedDummies[1]->id);
    expect($sortedDummies->where('id', 2)->first()->id)->toBe($sortedDummies[2]->id);
});

it('can sort to very last', function () {
    Dummy::create(['name' => 'dummy1']);
    Dummy::create(['name' => 'dummy2']);
    Dummy::create(['name' => 'dummy3']);

    dispatch(new SortModel(modelString: Dummy::class, main:1, previous:3));

    $sortedDummies = Dummy::orderBy(get_order_column())->get();

    expect($sortedDummies->where('id', 2)->first()->id)->toBe($sortedDummies[0]->id);
    expect($sortedDummies->where('id', 3)->first()->id)->toBe($sortedDummies[1]->id);
    expect($sortedDummies->where('id', 1)->first()->id)->toBe($sortedDummies[2]->id);
});