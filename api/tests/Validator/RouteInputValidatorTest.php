<?php

use Daedalus\Validator\RouteInputValidator;
use PHPUnit\Framework\TestCase;

class RouteInputValidatorTest extends TestCase
{

    protected $validator;

    public function setup()
    {
        $this->validator = new RouteInputValidator();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($input, $expected)
    {
        $this->assertEquals($expected, $this->validator->isValid($input));
    }

    /**
     * DataProvider for testIsValid
     */
    public function isValidProvider()
    {
        return [
            [
                '[
                    ["22.372081", "114.107877"],
                    ["22.284419", "114.159510"],
                    ["22.326442", "114.167811"]
                ]',
                true
            ],
            [
                '[
                    ["22.372081", "114.107877"],
                    ["22.284419", "114.159510"]
                ]',
                true
            ],
            [
                '[
                    ["22.372081", "114.107877", "114.107877"],
                    ["22.284419", "114.159510"],
                    ["22.326442", "114.167811"]
                ]',
                false
            ],
            [
                '[["22.372081", "114.107877"]]',
                false
            ],
            [
                '[]',
                false
            ],
            [
                '[
                    ["22.372081"],
                    ["22.284419", "114.159510"],
                    ["22.326442", "114.167811"]
                ]',
                false
            ],
            [
                '[
                    ["22.372081",,],
                    ["22.284419", "114.159510"],
                    ["22.326442", "114.167811"]
                ]',
                false
            ],
            [
                '[
                    ["22.372081", "114.107877"],
                    ["22.284419", "a"],
                    ["22.326442", "114.167811"]
                ]',
                false
            ],
        ];
    }
}
