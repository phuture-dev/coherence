<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Arrays;
use Tester\{Assert, TestCase};

require __DIR__ . '/bootstrap.php';

class FlattenDepthTest extends TestCase
{
    public function testFlattenBasicBackwardCompat(): void
    {
        $array = [1, [2, 3], [4, [5, 6]], 7];
        Assert::same([1, 2, 3, 4, 5, 6, 7], Arrays::flatten($array));
    }

    public function testFlattenAssociativeBackwardCompat(): void
    {
        $array = ['a' => 1, 'b' => ['c' => 2, 'd' => ['e' => 3]]];
        Assert::same([1, 2, 3], Arrays::flatten($array));
    }

    public function testFlattenEmptyArraysBackwardCompat(): void
    {
        Assert::same(['value'], Arrays::flatten([[], [[]], ['value']]));
    }

    public function testFlattenOnlyEmptyArraysBackwardCompat(): void
    {
        Assert::same([], Arrays::flatten([[], [[]], [[[]]]]));
    }

    public function testFlattenDeeplyNestedBackwardCompat(): void
    {
        $array = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => [
                            'level5' => 'deep value'
                        ]
                    ]
                ]
            ]
        ];
        Assert::same(['deep value'], Arrays::flatten($array));
    }

    public function testFlattenMixedDataTypesBackwardCompat(): void
    {
        $array = [
            'string' => 'hello',
            'number' => 42,
            'boolean' => true,
            'null' => null,
            'nested' => [
                'array' => [1, 2, 3],
                'deep' => [
                    'value' => 'deep',
                    'empty' => []
                ]
            ]
        ];
        Assert::same(['hello', 42, true, null, 1, 2, 3, 'deep'], Arrays::flatten($array));
    }

    public function testFlattenMixedStructureBackwardCompat(): void
    {
        $array = [
            'users' => [
                ['name' => 'John', 'age' => 30],
                ['name' => 'Jane', 'age' => 25]
            ],
            'settings' => [
                'theme' => 'dark',
                'notifications' => ['email' => true, 'sms' => false]
            ],
            'active' => true
        ];
        Assert::same(['John', 30, 'Jane', 25, 'dark', true, false, true], Arrays::flatten($array));
    }

    public function testFlattenWithDepthOne(): void
    {
        $array = [1, [2, [3, 4], 5], 6];
        Assert::same([1, 2, [3, 4], 5, 6], Arrays::flatten($array, 1));
    }

    public function testFlattenWithDepthTwo(): void
    {
        $array = [1, [2, [3, [4]]], 5];
        Assert::same([1, 2, 3, [4], 5], Arrays::flatten($array, 2));
    }

    public function testFlattenWithDepthZero(): void
    {
        $array = [1, [2, [3]], 4];
        Assert::same([1, [2, [3]], 4], Arrays::flatten($array, 0));
    }

    public function testFlattenWithDepthOnEmptyArray(): void
    {
        Assert::same([], Arrays::flatten([], 2));
    }

    public function testFlattenWithDepthPreservesRemainingNesting(): void
    {
        $array = ['a' => ['b' => ['c' => ['d' => 'value']]]];
        Assert::same([['c' => ['d' => 'value']]], Arrays::flatten($array, 1));
    }

    public function testFlattenWithDepthEqualToNestingLevel(): void
    {
        Assert::same([1, 2, 3], Arrays::flatten([1, [2, [3]]], 2));
    }

    public function testFlattenWithDepthGreaterThanNestingLevel(): void
    {
        Assert::same([1, 2, 3], Arrays::flatten([1, [2, [3]]], 10));
    }

    public function testFlattenWithDepthOnMixedTypes(): void
    {
        $array = [
            'string' => 'hello',
            'nested' => [
                'number' => 42,
                'deeper' => ['value' => 'deep'],
            ],
            'simple' => true,
        ];
        Assert::same(['hello', 42, ['value' => 'deep'], true], Arrays::flatten($array, 1));
    }
}

(new FlattenDepthTest())->run();
