<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Closure;
use Phuture\Coherence\Reflector;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/bootstrap.php';

class ReflectorTest extends TestCase
{
    public function testAliasFunctionReturnsClosure(): void
    {
        $alias = Reflector::aliasFunction('strlen');

        Assert::type(Closure::class, $alias);
    }

    public function testAliasFunctionClosureProducesIdenticalResultToOriginal(): void
    {
        $alias = Reflector::aliasFunction('strlen');

        Assert::same(strlen('hello'), $alias('hello'));
        Assert::same(strlen(''), $alias(''));
        Assert::same(strlen('longer string here'), $alias('longer string here'));
    }

    public function testAliasFunctionClosureForwardsMultipleArguments(): void
    {
        $alias = Reflector::aliasFunction('str_pad');

        Assert::same(str_pad('hi', 10), $alias('hi', 10));
        Assert::same(str_pad('hi', 10, '-', STR_PAD_LEFT), $alias('hi', 10, '-', STR_PAD_LEFT));
    }

    public function testAliasFunctionThrowsWhenFunctionDoesNotExist(): void
    {
        Assert::exception(function () {
            Reflector::aliasFunction('this_function_does_not_exist_anywhere');
        }, InvalidArgumentException::class, 'Invalid Argument: The given function does not exist');
    }

    public function testAliasFunctionClosureIsIndependentAcrossCalls(): void
    {
        $strlenAlias = Reflector::aliasFunction('strlen');
        $strtolowerAlias = Reflector::aliasFunction('strtolower');

        Assert::same(5, $strlenAlias('hello'));
        Assert::same('hello', $strtolowerAlias('HELLO'));
    }
}

// Run the tests
(new ReflectorTest())->run();
