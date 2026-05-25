<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Support;

use stdClass;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Enum\ArrayComparator;
use Phuture\Coherence\Support\ArgumentExtractor;
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/../bootstrap.php';

class ArgumentExtractorTest extends TestCase
{
    /**
     * Dummy method for testing static callable references.
     */
    public static function dummyMethod($x): int
    {
        return $x;
    }

    public function testGetCallbacksFromArgumentsExtractsMultipleCallbacks(): void
    {
        $callback1 = fn ($x) => $x * 2;
        $callback2 = fn ($x) => $x + 1;
        $arguments = ['a', $callback1, $callback2];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(2, $result);
        Assert::same($callback1, $result[0]);
        Assert::same($callback2, $result[1]);
        Assert::same(['a'], $arguments);
    }
    public function testGetCallbacksFromArgumentsExtractsSingleCallback(): void
    {
        $callback = fn ($x) => $x * 2;
        $arguments = ['a', 'b', $callback];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(1, $result);
        Assert::same($callback, $result[0]);
        Assert::same(['a', 'b'], $arguments);
    }

    public function testGetCallbacksFromArgumentsPreservesOrder(): void
    {
        $callback1 = fn ($x) => $x * 2;
        $callback2 = fn ($x) => $x + 1;
        $arguments = [$callback1, $callback2];

        $result = TestClass::getCallbacks($arguments);

        Assert::same($callback1, $result[0]);
        Assert::same($callback2, $result[1]);
    }

    public function testGetCallbacksFromArgumentsStopsAtNonCallable(): void
    {
        $callback1 = fn ($x) => $x * 2;
        $callback2 = fn ($x) => $x + 1;
        $arguments = ['a', $callback1, 'stop', $callback2];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(1, $result);
        Assert::same($callback2, $result[0]);
        Assert::same(['a', $callback1, 'stop'], $arguments);
    }

    public function testGetCallbacksFromArgumentsWithEmptyArray(): void
    {
        $arguments = [];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(0, $result);
        Assert::count(0, $arguments);
    }

    public function testGetCallbacksFromArgumentsWithLimit(): void
    {
        $callback1 = fn ($x) => $x * 2;
        $callback2 = fn ($x) => $x + 1;
        $callback3 = fn ($x) => $x - 1;
        $arguments = ['a', $callback1, $callback2, $callback3];

        $result = TestClass::getCallbacks($arguments, 2);

        Assert::count(2, $result);
        Assert::same($callback2, $result[0]);
        Assert::same($callback3, $result[1]);
        Assert::same(['a'], $arguments);
    }

    public function testGetCallbacksFromArgumentsWithMixedTypes(): void
    {
        $callback = fn ($x) => $x * 2;
        $arguments = ['a', 123, $callback, 'string', []];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(0, $result);
        Assert::same(['a', 123, $callback, 'string', []], $arguments);
    }

    public function testGetCallbacksFromArgumentsWithNoCallbacks(): void
    {
        $arguments = ['a', 'b', 'c', 123, []];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(0, $result);
        Assert::same(['a', 'b', 'c', 123, []], $arguments);
    }

    public function testGetCallbacksFromArgumentsWithObjectCallable(): void
    {
        $object = new class () {
            public function __invoke($x)
            {
                return $x * 2;
            }
        };
        $arguments = ['a', $object];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(1, $result);
        Assert::same($object, $result[0]);
        Assert::same(['a'], $arguments);
    }

    public function testGetCallbacksFromArgumentsWithStaticCallable(): void
    {
        $arguments = ['a', [self::class, 'dummyMethod']];

        $result = TestClass::getCallbacks($arguments);

        Assert::count(1, $result);
        Assert::same([self::class, 'dummyMethod'], $result[0]);
        Assert::same(['a'], $arguments);
    }

    public function testGetCallbacksFromArgumentsWithZeroLimit(): void
    {
        $callback = fn ($x) => $x * 2;
        $arguments = ['a', $callback];

        $result = TestClass::getCallbacks($arguments, 0);

        Assert::count(0, $result);
        Assert::same(['a'], $arguments);
    }

    public function testGetEnumsFromArgumentsExtractsMultipleEnums(): void
    {
        $arguments = ['a', ArrayComparator::Key, ArrayComparator::Value];

        $result = TestClass::getEnums($arguments, ArrayComparator::class, 2);

        Assert::count(2, $result);
        Assert::same(ArrayComparator::Key, $result[0]);
        Assert::same(ArrayComparator::Value, $result[1]);
        Assert::same(['a'], $arguments);
    }

    public function testGetEnumsFromArgumentsExtractsSingleEnum(): void
    {
        $arguments = ['a', 'b', ArrayComparator::Key];

        $result = TestClass::getEnums($arguments, ArrayComparator::class);

        Assert::count(1, $result);
        Assert::same(ArrayComparator::Key, $result[0]);
        Assert::same(['a', 'b'], $arguments);
    }

    public function testGetEnumsFromArgumentsPreservesOrder(): void
    {
        $arguments = [ArrayComparator::Key, ArrayComparator::Value];

        $result = TestClass::getEnums($arguments, ArrayComparator::class, 2);

        Assert::same(ArrayComparator::Key, $result[0]);
        Assert::same(ArrayComparator::Value, $result[1]);
    }

    public function testGetEnumsFromArgumentsStopsAtNonEnum(): void
    {
        $arguments = ['a', ArrayComparator::Key, 'stop', ArrayComparator::Value];

        $result = TestClass::getEnums($arguments, ArrayComparator::class);

        Assert::count(1, $result);
        Assert::same(ArrayComparator::Value, $result[0]);
        Assert::same(['a', ArrayComparator::Key, 'stop'], $arguments);
    }

    public function testGetEnumsFromArgumentsWithEmptyArray(): void
    {
        $arguments = [];

        $result = TestClass::getEnums($arguments, ArrayComparator::class);

        Assert::count(0, $result);
        Assert::count(0, $arguments);
    }

    public function testGetEnumsFromArgumentsWithInvalidEnumClass(): void
    {
        $arguments = ['a', 'b'];

        Assert::exception(
            fn () => TestClass::getEnums($arguments, 'NonExistentEnum'),
            InvalidArgumentException::class,
            'Invalid Argument: NonExistentEnum is not a valid enum class name'
        );
    }

    public function testGetEnumsFromArgumentsWithLimit(): void
    {
        $arguments = ['a', ArrayComparator::Key, ArrayComparator::Value, ArrayComparator::Both];

        $result = TestClass::getEnums($arguments, ArrayComparator::class, 2);

        Assert::count(2, $result);
        Assert::same(ArrayComparator::Value, $result[0]);
        Assert::same(ArrayComparator::Both, $result[1]);
        Assert::same(['a'], $arguments);
    }

    public function testGetEnumsFromArgumentsWithMixedEnumTypes(): void
    {
        $arguments = ['a', ArrayComparator::Key, 'string', ArrayComparator::Value];

        $result = TestClass::getEnums($arguments, ArrayComparator::class);

        Assert::count(1, $result);
        Assert::same(ArrayComparator::Value, $result[0]);
        Assert::same(['a', ArrayComparator::Key, 'string'], $arguments);
    }

    public function testGetEnumsFromArgumentsWithNoEnums(): void
    {
        $arguments = ['a', 'b', 'c', 123, []];

        $result = TestClass::getEnums($arguments, ArrayComparator::class);

        Assert::count(0, $result);
        Assert::same(['a', 'b', 'c', 123, []], $arguments);
    }

    public function testGetEnumsFromArgumentsWithNonEnumClass(): void
    {
        $arguments = ['a', 'b'];

        Assert::exception(
            fn () => TestClass::getEnums($arguments, stdClass::class),
            InvalidArgumentException::class,
            'Invalid Argument: stdClass is not a valid enum class name'
        );
    }

    public function testGetEnumsFromArgumentsWithZeroLimit(): void
    {
        $arguments = ['a', ArrayComparator::Key];

        $result = TestClass::getEnums($arguments, ArrayComparator::class, 0);

        Assert::count(0, $result);
        Assert::same(['a'], $arguments);
    }
}

/**
 * Test class that uses the ArgumentExtractor trait.
 */
class TestClass
{
    use ArgumentExtractor;

    /**
     * Expose private method for testing.
     */
    public static function getCallbacks(array &$arguments, int $limit = 2): array
    {
        return self::getCallbacksFromArguments($arguments, $limit);
    }

    /**
     * Expose private method for testing.
     */
    public static function getEnums(array &$arguments, string $enum, int $limit = 1): array
    {
        return self::getEnumsFromArguments($arguments, $enum, $limit);
    }
}

(new ArgumentExtractorTest())->run();
