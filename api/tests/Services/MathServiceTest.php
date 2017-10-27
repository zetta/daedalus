<?php

use Daedalus\Services\MathService;
use PHPUnit\Framework\TestCase;

class MathServiceTest extends TestCase
{
    protected $service;

    public function setup()
    {
        $this->service = new MathService();
    }

    /**
     * @dataProvider permutationsProvider
     */
    public function testPermutations(array $input, int $expected)
    {
        $iterable = $this->service->getPermutations($input);
        $this->assertCount($expected, $iterable);
    }

    public function permutationsProvider() : array
    {
        return [
            [
                ['a', 'b', 'c'],
                6,
            ],
            [
                ['a', 'b', 'c', 'd'],
                24,
            ],
            [
                ['a', 'b', 'c', 'd', 'e'],
                120,
            ],
            [
                ['a', 'b', 'c', 'd', 'e', 'f'],
                720,
            ],
            [
                ['a', 'b'],
                2,
            ],
            [
                ['a'],
                1,
            ]
        ];
    }
}
