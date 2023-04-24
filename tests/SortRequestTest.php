<?php

namespace Thoss\GapSort\Tests;

use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Validator;
use PHPUnit\Framework\TestCase;
use Thoss\GapSort\Requests\SortRequest;

final class SortRequestTest extends TestCase
{
    protected $rules = null;
    protected $transLang = 'en';
    protected $request;

    public function testValidationTrue()
    {
        $expectedTrue = $this->validate([
            'main' => 25,
            'previous' => 30,
            'next' => 35,
        ]);

        $this->assertTrue($expectedTrue->passes());
    }

    public function testValidationFieldsShouldBeDeferent()
    {
        $expectedFalse = $this->validate([
            'main' => 30,
            'previous' => 30,
            'next' => 35,
        ]);

        $this->assertFalse($expectedFalse->passes());
    }

    public function testValidationMainisRequired()
    {
        $expectedFalse = $this->validate([
            'main' => null,
            'previous' => 30,
            'next' => 35,
        ]);

        $this->assertFalse($expectedFalse->passes());
    }

    public function testValidationOtherFieldsAreNullabe()
    {
        $expectedTrue = $this->validate([
            'main' => 25,
            'previous' => null,
            'next' => null,
        ]);

        $this->assertTrue($expectedTrue->passes());
    }

    protected function validate($data)
    {
        $trans = new Translator(
            new ArrayLoader(),
            $this->transLang
        );

        return new Validator($trans, $data, $this->request->rules());
    }

    /**is a template method and is run once for each test method*/
    public function setUp(): void
    {
        $this->request = new SortRequest();
        $this->request->setMethod('POST');
    }
}
