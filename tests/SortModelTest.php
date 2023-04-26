<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Thoss\GapSort\Tests\Dummy;

const SORT_GAP = 100;
const SORT_COLUMN = 'custom_order';

beforeAll(function () {
    config()->set('gap-sort.order_gap', SORT_GAP);
    config()->set('gap-sort.order_column', SORT_COLUMN);

    Schema::create('dummies', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name')->nullable();
        $table->unsignedInteger(SORT_COLUMN)->nullable();
        $table->timestamps();
    });
});

it('can create dummies with correct initial order', function () {
    $dummy1 = Dummy::create(['name' => 'dummy1']);
    $dummy2 = Dummy::create(['name' => 'dummy2']);
    $dummy3 = Dummy::create(['name' => 'dummy3']);

    expect($dummy1->{SORT_COLUMN})->toBe(100);
    expect($dummy2->{SORT_COLUMN})->toBe(200);
    expect($dummy3->{SORT_COLUMN})->toBe(300);
});
