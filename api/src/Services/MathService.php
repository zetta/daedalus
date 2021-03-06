<?php

namespace Daedalus\Services;

use \Generator;

/**
 * Service for math operations
 */
class MathService
{
    /**
     * Returns all the possible permutations of the given elements
     * @param array $elements
     * @return Generator
     */
    public function getPermutations(array $elements) : Generator
    {
        if (count($elements) <= 1) {
            yield $elements;
        } else {
            foreach ($this->getPermutations(array_slice($elements, 1)) as $permutation) {
                foreach (range(0, count($elements) - 1) as $i) {
                    yield array_merge(
                        array_slice($permutation, 0, $i),
                        [$elements[0]],
                        array_slice($permutation, $i)
                    );
                }
            }
        }
    }
}
