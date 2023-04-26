<?php

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use Thoss\GapSort\Requests\SortRequest;

function validate($data)
{
    $trans = new Translator(
            new ArrayLoader(),
            'en'
        );

    $request = app()->make(SortRequest::class);
    $request->setMethod('POST');

    return new Validator($trans, $data, $request->rules());
}

it('can pass the validation rules', function () {
    $expectedTrue = validate([
        'main' => 25,
        'previous' => 30,
        'next' => 35,
    ]);

    expect($expectedTrue->passes())->toBeTrue();
});

it('cannot pass the validation rules when items not differs', function () {
    $expectedFalse = validate([
        'main' => 30,
        'previous' => 30,
        'next' => 35,
    ]);

    expect($expectedFalse->passes())->toBeFalse();
});

it('can pass the validation rules when previous and next are null', function () {
    $expectedTrue = validate([
        'main' => 25,
        'previous' => null,
        'next' => null,
    ]);

    expect($expectedTrue->passes())->toBeTrue();
});

it('cannot pass the validation rules when main is null', function () {
    $expectedFalse = validate([
        'main' => null,
        'previous' => 30,
        'next' => 35,
    ]);

    expect($expectedFalse->passes())->toBeFalse();
});

it('cannot pass the validation rules when main is equal to previous', function () {
    $expectedFalse = validate([
        'main' => 30,
        'previous' => 30,
        'next' => 35,
    ]);

    expect($expectedFalse->passes())->toBeFalse();
});

it('cannot pass the validation rules when main is equal to next', function () {
    $expectedFalse = validate([
        'main' => 35,
        'previous' => 30,
        'next' => 35,
    ]);

    expect($expectedFalse->passes())->toBeFalse();
});

it('cannot pass the validation rules when previous is equal to next', function () {
    $expectedFalse = validate([
        'main' => 25,
        'previous' => 35,
        'next' => 35,
    ]);

    expect($expectedFalse->passes())->toBeFalse();
});
