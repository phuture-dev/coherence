<?php

declare(strict_types=1);

namespace Advandz\Kernel\Tests;

use Advandz\Kernel\Arr;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Test case for the Arr utility class.
 */
class ArrTest extends TestCase
{
    public function testContains(): void
    {
        $array = [1, 2, 3];
        Assert::equal(in_array(2, $array), Arr::contains($array, 2));
    }

    public function testContainsKey(): void
    {
        $array = ['name' => 'John'];
        Assert::equal(array_key_exists('name', $array), Arr::containsKey($array, 'name'));
    }

    public function testIntersectKeys(): void
    {
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['b' => 20, 'c' => 30];
        Assert::equal(array_intersect_key($array1, $array2), Arr::intersectKeys($array1, $array2));
    }

    public function testSort(): void
    {
        $array1 = [3, 1, 2];
        $array2 = [3, 1, 2];
        sort($array1);
        Arr::sort($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortReverse(): void
    {
        $array1 = [1, 2, 3];
        $array2 = [1, 2, 3];
        rsort($array1);
        Arr::sortReverse($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortKeys(): void
    {
        $array1 = ['c' => 3, 'a' => 1, 'b' => 2];
        $array2 = ['c' => 3, 'a' => 1, 'b' => 2];
        ksort($array1);
        Arr::sortKeys($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortKeysReverse(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 1, 'b' => 2, 'c' => 3];
        krsort($array1);
        Arr::sortKeysReverse($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortAssoc(): void
    {
        $array1 = ['c' => 3, 'a' => 1, 'b' => 2];
        $array2 = ['c' => 3, 'a' => 1, 'b' => 2];
        asort($array1);
        Arr::sortAssoc($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortAssocReverse(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 1, 'b' => 2, 'c' => 3];
        arsort($array1);
        Arr::sortAssocReverse($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortNatural(): void
    {
        $array1 = ['img12.png', 'img2.png', 'img1.png'];
        $array2 = ['img12.png', 'img2.png', 'img1.png'];
        natsort($array1);
        Arr::sortNatural($array2);
        Assert::equal($array1, $array2);
    }

    public function testSortUser(): void
    {
        $array1 = [3, 1, 2];
        $array2 = [3, 1, 2];
        $callback = fn($a, $b) => $a <=> $b;
        usort($array1, $callback);
        Arr::sortUser($array2, $callback);
        Assert::equal($array1, $array2);
    }

    public function testSortKeysUser(): void
    {
        $array1 = ['c' => 3, 'a' => 1, 'b' => 2];
        $array2 = ['c' => 3, 'a' => 1, 'b' => 2];
        $callback = fn($a, $b) => $a <=> $b;
        uksort($array1, $callback);
        Arr::sortKeysUser($array2, $callback);
        Assert::equal($array1, $array2);
    }

    public function testSortAssocUser(): void
    {
        $array1 = ['c' => 3, 'a' => 1, 'b' => 2];
        $array2 = ['c' => 3, 'a' => 1, 'b' => 2];
        $callback = fn($a, $b) => $a <=> $b;
        uasort($array1, $callback);
        Arr::sortAssocUser($array2, $callback);
        Assert::equal($array1, $array2);
    }

    public function testRandom(): void
    {
        $array = ['a', 'b', 'c'];
        $key = array_rand($array);
        // Compare that both return a value from the array
        Assert::true(in_array(Arr::random($array), $array));
    }

    public function testRandomKeys(): void
    {
        $array = ['a' => 1, 'b' => 2];
        // Random methods may return different keys, so verify the key exists in array
        $key = Arr::randomKeys($array);
        Assert::true(array_key_exists($key, $array));
    }

    public function testShuffle(): void
    {
        $array1 = [1, 2, 3];
        $array2 = [1, 2, 3];
        shuffle($array1);
        Arr::shuffle($array2);
        // Compare counts as shuffle is random
        Assert::equal(count($array1), count($array2));
    }

    public function testLen(): void
    {
        $array = [1, 2, 3];
        Assert::equal(count($array), Arr::len($array));
    }

    public function testCollapse(): void
    {
        // Collapse is a custom method, not a native wrapper
        $array = [
            (object)['key' => 1, 'value' => 'John'],
            (object)['key' => 2, 'value' => 'Jane'],
        ];
        $result = Arr::collapse($array);
        Assert::equal([1 => 'John', 2 => 'Jane'], $result);
    }

    public function testFind(): void
    {
        $array = ['a' => 1, 'b' => 2];
        Assert::equal(array_search(2, $array), Arr::find($array, 2));
    }

    public function testApply(): void
    {
        $array = [1, 2, 3];
        $callback = fn($x) => $x * 2;
        Assert::equal(array_map($callback, $array), Arr::apply($array, $callback));
    }

    public function testJoin(): void
    {
        $array = ['Hello', 'World'];
        Assert::equal(implode(' ', $array), Arr::join($array, ' '));
    }
}

// Run the test
(new ArrTest())->run();
