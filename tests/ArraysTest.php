<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use stdClass;
use ArrayObject;
use JsonSerializable;
use Phuture\Coherence\Arrays;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Enum\ArrayComparator;
use Phuture\Coherence\Exception\{InvalidArgumentException, LogicException, OutOfBoundsException};

require __DIR__ . '/bootstrap.php';

class ArraysTest extends TestCase
{
    public function testAccessibleWithArray(): void
    {
        Assert::true(Arrays::accessible([]));
        Assert::true(Arrays::accessible([1, 2, 3]));
    }

    public function testAccessibleWithArrayObject(): void
    {
        $object = new ArrayObject([1, 2, 3]);
        Assert::true(Arrays::accessible($object));
    }

    public function testAccessibleWithNonArray(): void
    {
        Assert::false(Arrays::accessible('string'));
        Assert::false(Arrays::accessible(123));
        Assert::false(Arrays::accessible(null));
        Assert::false(Arrays::accessible(new stdClass()));
    }

    public function testAppend(): void
    {
        $array = ['name' => 'John'];
        Arrays::append($array, ['age' => 30, 'city' => 'NYC']);
        Assert::same(['name' => 'John', 'age' => 30, 'city' => 'NYC'], $array);
    }

    public function testAppendDoesNotOverwrite(): void
    {
        $array = ['name' => 'John', 'age' => 25];
        Arrays::append($array, ['age' => 30, 'city' => 'NYC']);
        Assert::same(['name' => 'John', 'age' => 25, 'city' => 'NYC'], $array);
    }

    public function testAppendWithNullValues(): void
    {
        $array = ['name' => null];
        Arrays::append($array, ['name' => 'John']);
        Assert::same(['name' => null], $array);
    }

    public function testAssociate(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];
        $result = Arrays::associate($array, 'id');
        Assert::same([
            1 => ['id' => 1, 'name' => 'John'],
            2 => ['id' => 2, 'name' => 'Jane']
        ], $result);
    }

    public function testAssociateWithValue(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];
        $result = Arrays::associate($array, 'id', 'name');
        Assert::same([1 => 'John', 2 => 'Jane'], $result);
    }

    public function testAverage(): void
    {
        $numbers = [1, 2, 3, 4, 5];
        $avg = Arrays::average($numbers);
        Assert::same(3.0, $avg);

        // With decimal values
        $prices = [10.5, 20.0, 30.5];
        $avg = Arrays::average($prices);
        Assert::true(abs($avg - 20.333333333333332) < 0.0001);

        // Single element
        $single = [42];
        $avg = Arrays::average($single);
        Assert::same(42.0, $avg);
    }

    public function testAverageEmpty(): void
    {
        $avg = Arrays::average([]);
        Assert::null($avg);
    }

    public function testAverageWithFloats(): void
    {
        $numbers = [1.5, 2.5, 3.5];
        $avg = Arrays::average($numbers);
        Assert::same(2.5, $avg);
    }

    public function testAverageWithNegativeNumbers(): void
    {
        $numbers = [-5, 0, 5];
        $avg = Arrays::average($numbers);
        Assert::same(0.0, $avg);
    }

    public function testChangeKeyCase(): void
    {
        $array = ['Name' => 'John', 'AGE' => 30];
        $result = Arrays::changeKeyCase($array, CASE_LOWER);
        Assert::same(['name' => 'John', 'age' => 30], $result);
    }

    public function testChangeKeyCaseNumericKeys(): void
    {
        $array = [0 => 'a', 'Name' => 'John'];
        $result = Arrays::changeKeyCase($array, CASE_LOWER);
        Assert::same([0 => 'a', 'name' => 'John'], $result);
    }

    public function testChangeKeyCaseUpper(): void
    {
        $array = ['name' => 'John', 'age' => 30];
        $result = Arrays::changeKeyCase($array, CASE_UPPER);
        Assert::same(['NAME' => 'John', 'AGE' => 30], $result);
    }

    public function testCollapse(): void
    {
        $arrays = [[1, 2], [3, 4], [5, 6]];
        $result = Arrays::collapse($arrays);
        Assert::same([1, 2, 3, 4, 5, 6], $result);
    }

    public function testCollapseEmptyArray(): void
    {
        $arrays = [];
        $result = Arrays::collapse($arrays);
        Assert::same([], $result);
    }

    public function testCollapseWithAssociativeArrays(): void
    {
        $arrays = [['a' => 1], ['b' => 2], ['c' => 3]];
        $result = Arrays::collapse($arrays);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    public function testCollapseWithEmptyArrays(): void
    {
        $arrays = [[1, 2], [], [3, 4]];
        $result = Arrays::collapse($arrays);
        Assert::same([1, 2, 3, 4], $result);
    }

    public function testCollapseWithMixedKeys(): void
    {
        $arrays = [['a', 'b'], ['c'], ['d' => 4, 'e']];
        $result = Arrays::collapse($arrays);
        Assert::same(['a', 'b', 'c', 'd' => 4, 'e'], $result);
    }

    public function testCollapseWithSingleArray(): void
    {
        $arrays = [[1, 2, 3]];
        $result = Arrays::collapse($arrays);
        Assert::same([1, 2, 3], $result);
    }

    public function testColumn(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
            ['id' => 3, 'name' => 'Bob']
        ];
        $result = Arrays::column($array, 'name');
        Assert::same(['John', 'Jane', 'Bob'], $result);
    }

    public function testColumnNullReturnsAllRows(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];
        $result = Arrays::column($array, null, 'id');
        Assert::same([
            1 => ['id' => 1, 'name' => 'John'],
            2 => ['id' => 2, 'name' => 'Jane']
        ], $result);
    }

    public function testColumnThrowsOnAssociativePath(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John']
        ];
        Assert::exception(
            fn () => Arrays::column($array, ['key' => 'value']),
            InvalidArgumentException::class,
            '~Column path must be a list~'
        );
    }

    public function testColumnThrowsOnNonStringPathElements(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John']
        ];
        Assert::exception(
            fn () => Arrays::column($array, ['profile', 123]),
            InvalidArgumentException::class,
            '~Column path must be a list of strings~'
        );
    }

    public function testColumnWithDeepNestedPath(): void
    {
        $array = [
            ['id' => 1, 'user' => ['profile' => ['settings' => ['theme' => 'dark']]]],
            ['id' => 2, 'user' => ['profile' => ['settings' => ['theme' => 'light']]]]
        ];
        $result = Arrays::column($array, ['user', 'profile', 'settings', 'theme']);
        Assert::same(['dark', 'light'], $result);
    }

    public function testColumnWithEmptyPath(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];
        $result = Arrays::column($array, []);
        // Empty path returns the whole item
        Assert::same([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ], $result);
    }

    public function testColumnWithIndex(): void
    {
        $array = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
            ['id' => 3, 'name' => 'Bob']
        ];
        $result = Arrays::column($array, 'name', 'id');
        Assert::same([1 => 'John', 2 => 'Jane', 3 => 'Bob'], $result);
    }

    public function testColumnWithNestedPath(): void
    {
        $array = [
            ['id' => 1, 'profile' => ['email' => 'john@example.com']],
            ['id' => 2, 'profile' => ['email' => 'jane@example.com']]
        ];
        $result = Arrays::column($array, ['profile', 'email']);
        Assert::same(['john@example.com', 'jane@example.com'], $result);
    }

    public function testColumnWithNestedPathAndIndex(): void
    {
        $array = [
            ['id' => 1, 'profile' => ['email' => 'john@example.com']],
            ['id' => 2, 'profile' => ['email' => 'jane@example.com']]
        ];
        $result = Arrays::column($array, ['profile', 'email'], 'id');
        Assert::same([1 => 'john@example.com', 2 => 'jane@example.com'], $result);
    }

    public function testColumnWithNestedPathAndNestedIndex(): void
    {
        $array = [
            ['id' => 1, 'profile' => ['email' => 'john@example.com']],
            ['id' => 2, 'profile' => ['email' => 'jane@example.com']]
        ];
        $result = Arrays::column($array, ['profile', 'email'], ['profile', 'email']);
        Assert::same(['john@example.com' => 'john@example.com', 'jane@example.com' => 'jane@example.com'], $result);
    }

    public function testColumnWithNestedPathAndObjects(): void
    {
        $array = [
            (object) ['id' => 1, 'profile' => (object) ['email' => 'john@example.com']],
            (object) ['id' => 2, 'profile' => (object) ['email' => 'jane@example.com']]
        ];
        $result = Arrays::column($array, ['profile', 'email']);
        Assert::same(['john@example.com', 'jane@example.com'], $result);
    }

    public function testColumnWithNestedPathReturnsNullForMissing(): void
    {
        $array = [
            ['id' => 1, 'profile' => ['email' => 'john@example.com']],
            ['id' => 2] // Missing profile
        ];
        $result = Arrays::column($array, ['profile', 'email']);
        Assert::same(['john@example.com', null], $result);
    }

    public function testCombine(): void
    {
        $keys = ['a', 'b', 'c'];
        $values = [1, 2, 3];
        $result = Arrays::combine($keys, $values);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    public function testCombineThrowsOnMismatch(): void
    {
        Assert::exception(
            fn () => Arrays::combine(['a', 'b'], [1]),
            InvalidArgumentException::class
        );
    }

    public function testContains(): void
    {
        $array = [1, 2, 3, '4'];
        Assert::true(Arrays::contains($array, 1));
        Assert::true(Arrays::contains($array, 3));
        Assert::false(Arrays::contains($array, 4));
        Assert::false(Arrays::contains($array, 5));
    }

    public function testContainsWithObjects(): void
    {
        $obj1 = new stdClass();
        $obj1->id = 1;
        $obj2 = new stdClass();
        $obj2->id = 2;
        $array = [$obj1, $obj2];

        Assert::true(Arrays::contains($array, $obj1, true));
        Assert::false(Arrays::contains($array, new stdClass()));
    }

    public function testCount(): void
    {
        $array = [1, 2, 2, 3, 3, 3];
        $result = Arrays::count($array);
        Assert::same([1 => 1, 2 => 2, 3 => 3], $result);
    }

    public function testCountStrings(): void
    {
        $array = ['a', 'b', 'a', 'c', 'a'];
        $result = Arrays::count($array);
        Assert::same(['a' => 3, 'b' => 1, 'c' => 1], $result);
    }

    public function testCrossJoin(): void
    {
        $result = Arrays::crossJoin([1, 2], ['a', 'b']);
        Assert::same([
            [1, 'a'],
            [1, 'b'],
            [2, 'a'],
            [2, 'b']
        ], $result);
    }

    public function testCrossJoinThreeArrays(): void
    {
        $sizes = ['S', 'M'];
        $colors = ['red', 'blue'];
        $types = ['shirt', 'pants'];
        $result = Arrays::crossJoin($sizes, $colors, $types);

        // Should return 8 combinations (2 * 2 * 2)
        Assert::count(8, $result);

        // Check a few specific combinations
        Assert::contains(['S', 'red', 'shirt'], $result);
        Assert::contains(['M', 'blue', 'pants'], $result);
        Assert::contains(['S', 'blue', 'shirt'], $result);
        Assert::contains(['M', 'red', 'pants'], $result);
    }

    public function testCrossJoinThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::crossJoin([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testCrossJoinWithAssociativeArrays(): void
    {
        $result = Arrays::crossJoin(['first' => 1, 'second' => 2], ['x' => 'a', 'y' => 'b']);
        Assert::same([
            [1, 'a'],
            [1, 'b'],
            [2, 'a'],
            [2, 'b']
        ], $result);
    }

    public function testCrossJoinWithEmptyArrays(): void
    {
        $result = Arrays::crossJoin([], []);
        Assert::same([], $result);

        $result = Arrays::crossJoin([1], ['a']);
        Assert::same([[1, 'a']], $result);
    }

    public function testDenote(): void
    {
        $array = [
            'name' => 'John',
            'address.city' => 'NYC',
            'address.zip' => '10001'
        ];
        $result = Arrays::denote($array);
        Assert::same([
            'name' => 'John',
            'address' => [
                'city' => 'NYC',
                'zip' => '10001'
            ]
        ], $result);
    }

    public function testDenoteEmptyArrayPath(): void
    {
        // Edge case: empty intermediate arrays
        $array = [
            'a.b.c.d.e' => 'deep value'
        ];

        $result = Arrays::denote($array, true);
        Assert::same([
            'a' => [
                'b' => [
                    'c' => [
                        'd' => [
                            'e' => 'deep value'
                        ]
                    ]
                ]
            ]
        ], $result);
    }

    public function testDenoteIntermediatePathValueLossNonStrict(): void
    {
        // Path serves as both final value and intermediate path
        $array = [
            'config.db' => 'mysql',
            'config.db.host' => 'localhost'
        ];
        $result = Arrays::denote($array);

        // 'mysql' is lost when 'db' is converted to array
        Assert::same([
            'config' => [
                'db' => [
                    'host' => 'localhost'
                ]
            ]
        ], $result);
    }

    public function testDenoteIntermediatePathValueLossStrict(): void
    {
        $array = [
            'config.db' => 'mysql',
            'config.db.host' => 'localhost'
        ];

        Assert::exception(
            fn () => Arrays::denote($array, true),
            LogicException::class
        );
    }

    public function testDenoteMultipleConflicts(): void
    {
        // Multiple conflicts - strict mode should catch the first one
        $array = [
            'a' => 'scalar1',
            'a.b' => 'value1',
            'c.d' => 'value2',
            'c.d.e' => 'value3'
        ];

        Assert::exception(
            fn () => Arrays::denote($array, true),
            LogicException::class
        );
    }

    public function testDenoteNestedStructureOverwrittenByScalarNonStrict(): void
    {
        // In non-strict mode, nested structure is silently overwritten
        $array = [
            'user.name.first' => 'Jane',
            'user.name.last' => 'Doe',
            'user.name' => 'John' // processed last, overwrites nested structure
        ];
        $result = Arrays::denote($array);

        // Nested 'first' and 'last' keys are lost
        Assert::same([
            'user' => [
                'name' => 'John'
            ]
        ], $result);
    }

    public function testDenoteNestedStructureOverwrittenByScalarStrict(): void
    {
        $array = [
            'user.name.first' => 'Jane',
            'user.name.last' => 'Doe',
            'user.name' => 'John' // processed last, overwrites nested structure
        ];

        Assert::exception(
            fn () => Arrays::denote($array, true),
            LogicException::class
        );
    }

    public function testDenoteNoConflictInStrictMode(): void
    {
        // Valid data without conflicts should work in strict mode
        $array = [
            'name' => 'John',
            'address.city' => 'NYC',
            'address.zip' => '10001',
            'contact.email' => 'john@example.com',
            'contact.phone' => '555-1234'
        ];

        $result = Arrays::denote($array, true);
        Assert::same([
            'name' => 'John',
            'address' => [
                'city' => 'NYC',
                'zip' => '10001'
            ],
            'contact' => [
                'email' => 'john@example.com',
                'phone' => '555-1234'
            ]
        ], $result);
    }

    public function testDenoteOrderDependentConflictA(): void
    {
        // Order A: nested path first, then parent
        $array = [
            'a.b.c' => 1,
            'a.b' => 2
        ];
        $result = Arrays::denote($array);

        // Nested value is lost
        Assert::same([
            'a' => [
                'b' => 2
            ]
        ], $result);
    }

    public function testDenoteOrderDependentConflictB(): void
    {
        // Order B: parent first, then nested path
        $array = [
            'a.b' => 2,
            'a.b.c' => 1
        ];
        $result = Arrays::denote($array);

        // Parent value is lost
        Assert::same([
            'a' => [
                'b' => [
                    'c' => 1
                ]
            ]
        ], $result);
    }

    public function testDenoteScalarOverwrittenByArrayNonStrict(): void
    {
        // In non-strict mode, scalar value is silently replaced by array
        $array = [
            'user' => 'John', // scalar value
            'user.name' => 'Jane' // needs 'user' to be an array
        ];
        $result = Arrays::denote($array);

        // Scalar 'John' is lost, replaced by array
        Assert::same([
            'user' => [
                'name' => 'Jane'
            ]
        ], $result);
    }

    public function testDenoteScalarOverwrittenByArrayStrict(): void
    {
        $array = [
            'user' => 'John', // scalar value
            'user.name' => 'Jane' // needs 'user' to be an array
        ];

        Assert::exception(
            fn () => Arrays::denote($array, true),
            LogicException::class
        );
    }

    public function testDenoteStrictPreventsOrderDependentConflict(): void
    {
        $array1 = [
            'a.b.c' => 1,
            'a.b' => 2
        ];

        Assert::exception(
            fn () => Arrays::denote($array1, true),
            LogicException::class
        );

        $array2 = [
            'a.b' => 2,
            'a.b.c' => 1
        ];

        Assert::exception(
            fn () => Arrays::denote($array2, true),
            LogicException::class
        );
    }

    public function testDifference(): void
    {
        $array1 = [1, 2, 3, 4];
        $array2 = [2, 4];
        $result = Arrays::difference($array1, $array2);
        Assert::same([0 => 1, 2 => 3], $result);
    }

    public function testDifferenceAssoc(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 1, 'd' => 4];
        $result = Arrays::differenceAssoc($array1, $array2);
        Assert::same(['b' => 2, 'c' => 3], $result);
    }

    public function testDifferenceAssocThrowsWithCallbacksButNoComparator(): void
    {
        Assert::exception(
            fn () => Arrays::differenceAssoc(['a' => 1], ['b' => 2], fn ($a, $b) => $a <=> $b),
            InvalidArgumentException::class
        );
    }

    public function testDifferenceAssocThrowsWithMissingComparator(): void
    {
        Assert::exception(
            fn () => Arrays::differenceAssoc(
                ['a' => 1],
                ['b' => 2],
                fn ($a, $b) => $a <=> $b
            ),
            InvalidArgumentException::class
        );
    }

    public function testDifferenceAssocThrowsWithNoComparisonArray(): void
    {
        Assert::exception(
            fn () => Arrays::differenceAssoc(['a' => 1]),
            InvalidArgumentException::class
        );
    }

    public function testDifferenceAssocWithBothComparator(): void
    {
        $array1 = ['Name' => 'John', 'Age' => 30];
        $array2 = ['name' => 'JOHN', 'age' => 25];
        $result = Arrays::differenceAssoc(
            $array1,
            $array2,
            ArrayComparator::Both,
            fn ($a, $b) => strcasecmp((string) $a, (string) $b), // value comparison
            fn ($a, $b) => strcasecmp((string) $a, (string) $b)  // key comparison
        );
        Assert::same(['Age' => 30], $result);
    }

    public function testDifferenceAssocWithCallback(): void
    {
        $array1 = ['name' => 'John', 'AGE' => 30];
        $array2 = ['name' => 'JOHN'];
        $result = Arrays::differenceAssoc(
            $array1,
            $array2,
            ArrayComparator::Value,
            fn ($a, $b) => strcasecmp((string) $a, (string) $b)
        );
        Assert::same(['AGE' => 30], $result);
    }

    public function testDifferenceAssocWithKeyComparator(): void
    {
        $array1 = ['Apple' => 100, 'Banana' => 200];
        $array2 = ['apple' => 100];
        $result = Arrays::differenceAssoc(
            $array1,
            $array2,
            ArrayComparator::Key,
            fn ($a, $b) => strcasecmp((string) $a, (string) $b)
        );
        Assert::same(['Banana' => 200], $result);
    }

    public function testDifferenceAssocWithMultipleArrays(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 1, 'd' => 4];
        $array3 = ['c' => 3, 'e' => 5];
        $result = Arrays::differenceAssoc($array1, $array2, $array3);
        Assert::same(['b' => 2], $result);
    }

    public function testDifferenceKeys(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 10, 'c' => 30];
        $result = Arrays::differenceKeys($array1, $array2);
        Assert::same(['b' => 2], $result);
    }

    public function testDifferenceKeysThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::differenceKeys([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testDifferenceKeysWithCallback(): void
    {
        $array1 = ['A' => 1, 'b' => 2, 'C' => 3];
        $array2 = ['a' => 10];
        $result = Arrays::differenceKeys($array1, $array2, fn ($a, $b) => strcasecmp($a, $b));
        Assert::same(['b' => 2, 'C' => 3], $result);
    }

    public function testDifferenceMultipleArrays(): void
    {
        $array1 = [1, 2, 3, 4, 5];
        $result = Arrays::difference($array1, [2], [4, 5]);
        Assert::same([0 => 1, 2 => 3], $result);
    }

    public function testDifferenceThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::difference([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testDifferenceWithCallback(): void
    {
        $array1 = ['a', 'B', 'c'];
        $array2 = ['A', 'b'];
        $result = Arrays::difference($array1, $array2, fn ($a, $b) => strcasecmp($a, $b));
        Assert::same([2 => 'c'], $result);
    }

    public function testEvery(): void
    {
        $array = [2, 4, 6];
        Assert::true(Arrays::every($array, fn ($v) => $v % 2 === 0));
        Assert::false(Arrays::every($array, fn ($v) => $v > 3));
    }

    public function testEveryEmpty(): void
    {
        Assert::true(Arrays::every([], fn () => false));
    }

    public function testExists(): void
    {
        $array = ['name' => 'John', 'age' => null, 0 => 'zero'];
        Assert::true(Arrays::exists($array, 'name'));
        Assert::true(Arrays::exists($array, 'age'));
        Assert::true(Arrays::exists($array, 0));
        Assert::false(Arrays::exists($array, 'email'));
    }

    public function testFill(): void
    {
        $result = Arrays::fill(0, 3, 'x');
        Assert::same(['x', 'x', 'x'], $result);
    }

    public function testFillKeys(): void
    {
        $result = Arrays::fillKeys(['a', 'b', 'c'], 0);
        Assert::same(['a' => 0, 'b' => 0, 'c' => 0], $result);
    }

    public function testFillWithStartIndex(): void
    {
        $result = Arrays::fill(5, 3, 'x');
        Assert::same([5 => 'x', 6 => 'x', 7 => 'x'], $result);
    }

    public function testFilterDefault(): void
    {
        $array = [0, 1, false, 2, '', 3, null];
        $result = Arrays::filter($array);
        Assert::same([1 => 1, 3 => 2, 5 => 3], $result);
    }

    public function testFilterWithCallback(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = Arrays::filter($array, fn ($v) => $v > 2);
        Assert::same([2 => 3, 3 => 4, 4 => 5], $result);
    }

    public function testFilterWithKeyCallback(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::filter($array, fn ($v, $k) => $k !== 'b', ARRAY_FILTER_USE_BOTH);
        Assert::same(['a' => 1, 'c' => 3], $result);
    }

    public function testFind(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = Arrays::find($array, fn ($v) => $v > 3);
        Assert::same(4, $result);
    }

    public function testFindKey(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::findKey($array, fn ($v) => $v === 2);
        Assert::same('b', $result);
    }

    public function testFindKeyNotFound(): void
    {
        $array = ['a' => 1, 'b' => 2];
        $result = Arrays::findKey($array, fn ($v) => $v === 10);
        Assert::null($result);
    }

    public function testFindNotFound(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::find($array, fn ($v) => $v > 10);
        Assert::null($result);
    }

    public function testFirst(): void
    {
        Assert::same(1, Arrays::first([1, 2, 3]));
        Assert::same('a', Arrays::first(['a', 'b', 'c']));
        Assert::exception(
            fn () => Arrays::first([]),
            OutOfBoundsException::class
        );
    }

    public function testFirstKey(): void
    {
        Assert::same(0, Arrays::firstKey([1, 2, 3]));
        Assert::same('name', Arrays::firstKey(['name' => 'John', 'age' => 30]));
        Assert::exception(
            fn () => Arrays::firstKey([]),
            OutOfBoundsException::class
        );
    }

    public function testFirstWithAssociativeArray(): void
    {
        Assert::same('John', Arrays::first(['name' => 'John', 'age' => 30]));
    }

    public function testFlatten(): void
    {
        $array = [1, [2, 3], [4, [5, 6]], 7];
        $result = Arrays::flatten($array);
        Assert::same([1, 2, 3, 4, 5, 6, 7], $result);
    }

    public function testFlattenDeeplyNested(): void
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
        $result = Arrays::flatten($array);
        Assert::same(['deep value'], $result);
    }

    public function testFlattenEmptyArrays(): void
    {
        $array = [[], [[], []], ['value']];
        $result = Arrays::flatten($array);
        Assert::same(['value'], $result);
    }

    public function testFlattenMixedDataTypes(): void
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
        $result = Arrays::flatten($array);
        Assert::same(['hello', 42, true, null, 1, 2, 3, 'deep'], $result);
    }

    public function testFlattenMixedStructure(): void
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
        $result = Arrays::flatten($array);
        Assert::same(['John', 30, 'Jane', 25, 'dark', true,  false, true], $result);
    }

    public function testFlattenOnlyEmptyArrays(): void
    {
        $array = [[], [[]], [[[]]]];
        $result = Arrays::flatten($array);
        Assert::same([], $result);
    }

    public function testFlattenWithAssociativeArrays(): void
    {
        $array = ['a' => 1, 'b' => ['c' => 2, 'd' => ['e' => 3]]];
        $result = Arrays::flatten($array);
        Assert::same([1, 2, 3], $result);
    }

    public function testFlip(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::flip($array);
        Assert::same([1 => 'a', 2 => 'b', 3 => 'c'], $result);
    }

    public function testFlipDuplicateValues(): void
    {
        $array = ['a' => 1, 'b' => 1, 'c' => 2];
        $result = Arrays::flip($array);
        Assert::same([1 => 'b', 2 => 'c'], $result);
    }

    public function testFromString(): void
    {
        $result = Arrays::fromString('John|Diego|Steve', '|');
        Assert::same(['John', 'Diego', 'Steve'], $result);
    }

    public function testFromStringWithLimit(): void
    {
        $result = Arrays::fromString('John|Diego|Steve', '|', 2);
        Assert::same(['John', 'Diego|Steve'], $result);
    }

    public function testGet(): void
    {
        $array = ['name' => 'John', 'age' => 30];
        Assert::same('John', Arrays::get($array, 'name'));
        Assert::same(30, Arrays::get($array, 'age'));
        Assert::null(Arrays::get($array, 'email', null));
        Assert::same('default', Arrays::get($array, 'email', 'default'));
    }

    public function testGetReference(): void
    {
        $array = ['a' => ['b' => 'value']];
        $ref = &Arrays::getReference($array, ['a', 'b']);
        $ref = 'new value';
        Assert::same('new value', $array['a']['b']);
    }

    public function testGetReferenceCreatesPath(): void
    {
        $array = [];
        $ref = &Arrays::getReference($array, ['a', 'b', 'c']);
        $ref = 'created';
        Assert::same('created', $array['a']['b']['c']);
    }

    public function testGetThrowsWhenMissing(): void
    {
        $array = ['name' => 'John'];
        Assert::exception(
            fn () => Arrays::get($array, 'email'),
            OutOfBoundsException::class
        );
    }

    public function testGetWithNestedPath(): void
    {
        $array = ['user' => ['name' => 'John', 'address' => ['city' => 'NYC']]];
        Assert::same('John', Arrays::get($array, ['user', 'name']));
        Assert::same('NYC', Arrays::get($array, ['user', 'address', 'city']));
        Assert::same('default', Arrays::get($array, ['user', 'email'], 'default'));
    }

    public function testGrep(): void
    {
        $array = ['apple', 'banana', 'cherry', 'apricot'];
        $result = Arrays::grep($array, '/^ap/');
        Assert::same([0 => 'apple', 3 => 'apricot'], $result);
    }

    public function testGrepInvert(): void
    {
        $array = ['apple', 'banana', 'cherry', 'apricot'];
        $result = Arrays::grep($array, '/^ap/', true);
        Assert::same([1 => 'banana', 2 => 'cherry'], $result);
    }

    public function testGrepWithInvalidRegex(): void
    {
        $array = ['apple', 'banana', 'cherry', 'apricot'];
        Assert::exception(
            fn () => Arrays::grep($array, 'invalid regex'),
            LogicException::class
        );
    }

    public function testGroupByCallback(): void
    {
        $numbers = [1, 2, 3, 4, 5, 6];
        $result = Arrays::groupBy($numbers, fn ($n) => $n % 2);

        Assert::count(2, $result);
        Assert::same([1, 3, 5], $result[1]);
        Assert::same([2, 4, 6], $result[0]);
    }

    public function testGroupByEmptyArray(): void
    {
        $result = Arrays::groupBy([], 'key');
        Assert::same([], $result);
    }

    public function testGroupByMissingKey(): void
    {
        $items = [
            ['name' => 'John', 'type' => 'A'],
            ['name' => 'Jane'] // missing 'type' key
        ];
        $result = Arrays::groupBy($items, 'type');

        Assert::count(2, $result);
        Assert::count(1, $result['A']);
        Assert::count(1, $result['']);
        Assert::same('Jane', $result[''][0]['name']);
    }

    public function testGroupByObjects(): void
    {
        $obj1 = new stdClass();
        $obj1->type = 'fruit';
        $obj1->name = 'apple';

        $obj2 = new stdClass();
        $obj2->type = 'vegetable';
        $obj2->name = 'carrot';

        $obj3 = new stdClass();
        $obj3->type = 'fruit';
        $obj3->name = 'banana';

        $items = [$obj1, $obj2, $obj3];
        $result = Arrays::groupBy($items, 'type');

        Assert::count(2, $result);
        Assert::count(2, $result['fruit']);
        Assert::count(1, $result['vegetable']);
        Assert::same('apple', $result['fruit'][0]->name);
        Assert::same('banana', $result['fruit'][1]->name);
        Assert::same('carrot', $result['vegetable'][0]->name);
    }

    public function testGroupByPreservesAllItems(): void
    {
        $items = ['a', 'b', 'c', 'a', 'b', 'a'];
        $result = Arrays::groupBy($items, fn ($item) => $item);

        Assert::count(3, $result);
        Assert::count(3, $result['a']);
        Assert::count(2, $result['b']);
        Assert::count(1, $result['c']);
    }

    public function testGroupByStringKey(): void
    {
        $users = [
            ['name' => 'John', 'department' => 'Sales'],
            ['name' => 'Jane', 'department' => 'IT'],
            ['name' => 'Bob', 'department' => 'Sales']
        ];
        $result = Arrays::groupBy($users, 'department');

        Assert::count(2, $result);
        Assert::count(2, $result['Sales']);
        Assert::count(1, $result['IT']);
        Assert::same('John', $result['Sales'][0]['name']);
        Assert::same('Bob', $result['Sales'][1]['name']);
        Assert::same('Jane', $result['IT'][0]['name']);
    }

    public function testHasWithNestedPath(): void
    {
        $array = ['user' => ['name' => 'John', 'address' => ['city' => 'NYC']]];
        Assert::true(Arrays::has($array, ['user', 'name']));
        Assert::true(Arrays::has($array, ['user', 'address', 'city']));
        Assert::false(Arrays::has($array, ['user', 'email']));
        Assert::false(Arrays::has($array, ['user', 'address', 'zip']));
    }

    public function testHasWithNullValues(): void
    {
        $array = ['key' => null];
        Assert::true(Arrays::has($array, 'key'));
    }

    public function testHasWithNumericKeys(): void
    {
        $array = [0 => 'zero', 5 => 'five'];
        Assert::true(Arrays::has($array, 0));
        Assert::true(Arrays::has($array, 5));
        Assert::false(Arrays::has($array, 1));
    }

    public function testHasWithSimpleKey(): void
    {
        $array = ['name' => 'John', 'age' => 30];
        Assert::true(Arrays::has($array, 'name'));
        Assert::true(Arrays::has($array, 'age'));
        Assert::false(Arrays::has($array, 'email'));
    }

    public function testInsertAfter(): void
    {
        $array = ['a' => 1, 'c' => 3];
        Arrays::insertAfter($array, 'a', ['b' => 2]);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testInsertAfterAtEnd(): void
    {
        $array = ['a' => 1, 'b' => 2];
        Arrays::insertAfter($array, 'b', ['c' => 3]);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testInsertAfterNonExistentKey(): void
    {
        $array = ['a' => 1];
        Arrays::insertAfter($array, 'z', ['b' => 2]);
        Assert::same(['a' => 1, 'b' => 2], $array);
    }

    public function testInsertBefore(): void
    {
        $array = ['a' => 1, 'c' => 3];
        Arrays::insertBefore($array, 'c', ['b' => 2]);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testInsertBeforeAtBeginning(): void
    {
        $array = ['b' => 2, 'c' => 3];
        Arrays::insertBefore($array, 'b', ['a' => 1]);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testInsertBeforeNonExistentKey(): void
    {
        $array = ['a' => 1];
        Arrays::insertBefore($array, 'z', ['b' => 2]);
        Assert::same(['b' => 2, 'a' => 1], $array);
    }

    public function testIntersect(): void
    {
        $array1 = [1, 2, 3, 4];
        $array2 = [2, 3, 5];
        $result = Arrays::intersect($array1, $array2);
        Assert::same([1 => 2, 2 => 3], $result);
    }

    public function testIntersectAssoc(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 1, 'b' => 3, 'c' => 3];
        $result = Arrays::intersectAssoc($array1, $array2);
        Assert::same(['a' => 1, 'c' => 3], $result);
    }

    public function testIntersectAssocThrowsWithCallbackNoComparator(): void
    {
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['a' => 1, 'c' => 3];

        Assert::exception(
            fn () => Arrays::intersectAssoc($array1, $array2, fn ($a, $b) => $a <=> $b),
            InvalidArgumentException::class
        );
    }

    public function testIntersectAssocThrowsWithInvalidComparator(): void
    {
        $array1 = ['a' => 1, 'b' => 2];
        $array2 = ['a' => 1, 'c' => 3];

        Assert::exception(
            fn () => Arrays::intersectAssoc(
                $array1,
                $array2,
                ArrayComparator::Key,
                fn ($a, $b) => $a <=> $b,
                fn ($key1, $key2) => $key1 <=> $key2
            ),
            LogicException::class
        );
    }

    public function testIntersectAssocThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::intersectAssoc([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testIntersectAssocWithOneCallback(): void
    {
        $array1 = ['A' => 'hello', 'B' => 'world'];
        $array2 = ['A' => 'HELLO', 'C' => 'test'];

        $result = Arrays::intersectAssoc(
            $array1,
            $array2,
            ArrayComparator::Value,
            fn ($a, $b) => strcasecmp($a, $b)
        );
        Assert::same(['A' => 'hello'], $result);

        $array1 = ['A' => 1, 'B' => 2, 'C' => 3];
        $array2 = ['a' => 1, 'b' => 5, 'd' => 4];

        $result = Arrays::intersectAssoc(
            $array1,
            $array2,
            ArrayComparator::Key,
            fn ($a, $b) => strcasecmp($a, $b)
        );
        Assert::same(['A' => 1], $result);
    }

    public function testIntersectAssocWithTwoCallbacks(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 1, 'b' => 2, 'd' => 4];
        $array3 = ['a' => 1, 'c' => 3, 'd' => 4];

        $result = Arrays::intersectAssoc(
            $array1,
            $array2,
            $array3,
            ArrayComparator::Both,
            fn ($a, $b) => $a <=> $b,
            fn ($key1, $key2) => strcasecmp($key1, $key2)
        );
        Assert::same(['a' => 1], $result);
    }

    public function testIntersectKeys(): void
    {
        $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
        $array2 = ['a' => 10, 'c' => 30, 'd' => 40];
        $result = Arrays::intersectKeys($array1, $array2);
        Assert::same(['a' => 1, 'c' => 3], $result);
    }

    public function testIntersectKeysThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::intersectKeys([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testIntersectKeysWithCallback(): void
    {
        $array1 = ['A' => 1, 'b' => 2, 'C' => 3];
        $array2 = ['a' => 10, 'c' => 30];
        $result = Arrays::intersectKeys($array1, $array2, fn ($a, $b) => strcasecmp($a, $b));
        Assert::same(['A' => 1, 'C' => 3], $result);
    }

    public function testIntersectMultipleArrays(): void
    {
        $array1 = [1, 2, 3, 4];
        $result = Arrays::intersect($array1, [2, 3, 5], [2, 3, 6]);
        Assert::same([1 => 2, 2 => 3], $result);
    }

    public function testIntersectThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::intersect([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testIntersectWithCallback(): void
    {
        $array1 = ['a', 'B', 'c'];
        $array2 = ['A', 'C'];
        $result = Arrays::intersect($array1, $array2, fn ($a, $b) => strcasecmp($a, $b));
        Assert::same([0 => 'a', 2 => 'c'], $result);
    }

    public function testIsAssoc(): void
    {
        Assert::true(Arrays::isAssoc(['a' => 1, 'b' => 2]));
        Assert::true(Arrays::isAssoc([1 => 'a', 0 => 'b']));
        Assert::true(Arrays::isAssoc([0 => 'a', 2 => 'b']));
        Assert::true(Arrays::isAssoc([5 => 'first', 10 => 'second']));
        Assert::true(Arrays::isAssoc(['name' => 'John', 'age' => 30]));
        Assert::true(Arrays::isAssoc([0 => 'zero', 'key' => 'value']));
    }

    public function testIsAssocReturnsFalse(): void
    {
        Assert::false(Arrays::isAssoc([]));
        Assert::false(Arrays::isAssoc([1, 2, 3]));
        Assert::false(Arrays::isAssoc([0 => 'a', 1 => 'b', 2 => 'c']));
        Assert::false(Arrays::isAssoc(['a', 'b', 'c']));
        Assert::false(Arrays::isAssoc([0 => 'first', 1 => 'second']));
    }

    public function testIsBlank(): void
    {
        Assert::true(Arrays::isBlank([]));
    }

    public function testIsBlankReturnsFalse(): void
    {
        Assert::false(Arrays::isBlank([1, 2, 3]));
        Assert::false(Arrays::isBlank(['a', 'b', 'c']));
        Assert::false(Arrays::isBlank([0 => 'a']));
        Assert::false(Arrays::isBlank([0 => '', 1 => 0]));
        Assert::false(Arrays::isBlank(['key' => 'value']));
        Assert::false(Arrays::isBlank(['null' => null]));
    }

    public function testIsFilled(): void
    {
        Assert::true(Arrays::isFilled([1, 2, 3]));
        Assert::true(Arrays::isFilled(['a', 'b', 'c']));
        Assert::true(Arrays::isFilled([0 => 'a']));
        Assert::true(Arrays::isFilled([0 => '', 1 => 0]));
        Assert::true(Arrays::isFilled(['key' => 'value']));
        Assert::true(Arrays::isFilled(['null' => null]));
        Assert::true(Arrays::isFilled([false, null, '']));
    }

    public function testIsFilledReturnsFalse(): void
    {
        Assert::false(Arrays::isFilled([]));
    }

    public function testIsList(): void
    {
        Assert::true(Arrays::isList([]));
        Assert::true(Arrays::isList([1, 2, 3]));
        Assert::true(Arrays::isList([0 => 'a', 1 => 'b', 2 => 'c']));
    }

    public function testIsListReturnsFalse(): void
    {
        Assert::false(Arrays::isList(['a' => 1, 'b' => 2]));
        Assert::false(Arrays::isList([1 => 'a', 0 => 'b']));
        Assert::false(Arrays::isList([0 => 'a', 2 => 'b']));
    }

    public function testIterate(): void
    {
        $array = [1, 2, 3];
        $sum = 0;
        Arrays::iterate($array, function ($value) use (&$sum) {
            $sum += $value;
        });
        Assert::same(6, $sum);
    }

    public function testIterateModifyByReference(): void
    {
        $array = [1, 2, 3];
        Arrays::iterate($array, function (&$value) {
            $value *= 2;
        });
        Assert::same([2, 4, 6], $array);
    }

    public function testIterateRecursive(): void
    {
        $array = [1, [2, 3], [4, [5]]];
        $values = [];
        Arrays::iterate($array, function ($value) use (&$values) {
            if (is_scalar($value)) {
                $values[] = $value;
            }
        }, true);
        Assert::same([1, 2, 3, 4, 5], $values);
    }

    public function testIterateWithKeyAndValue(): void
    {
        $array = ['a' => 1, 'b' => 2];
        $result = [];
        Arrays::iterate($array, function ($value, $key) use (&$result) {
            $result[$key] = $value * 2;
        });
        Assert::same(['a' => 2, 'b' => 4], $result);
    }

    public function testJoin(): void
    {
        // Test joining arrays
        $result = Arrays::join(['a' => 1, 'b' => 2], ['c' => 3, 'd' => 4]);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], $result);
    }

    public function testJoinOverlappingKeys(): void
    {
        // Last occurrence keeps its value
        $result = Arrays::join(['a' => 1, 'b' => 2], ['b' => 99, 'c' => 3]);
        Assert::same(['a' => 1, 'b' => 99, 'c' => 3], $result);
    }

    public function testJoinThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::join([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testKeys(): void
    {
        Assert::same([0, 1, 2], Arrays::keys([10, 20, 30]));
        Assert::same(['name', 'age'], Arrays::keys(['name' => 'John', 'age' => 30]));
        Assert::same([], Arrays::keys([]));
    }

    public function testLast(): void
    {
        Assert::same(3, Arrays::last([1, 2, 3]));
        Assert::same('c', Arrays::last(['a', 'b', 'c']));
        Assert::exception(
            fn () => Arrays::last([]),
            OutOfBoundsException::class
        );
    }

    public function testLastKey(): void
    {
        Assert::same(2, Arrays::lastKey([1, 2, 3]));
        Assert::same('age', Arrays::lastKey(['name' => 'John', 'age' => 30]));
        Assert::exception(
            fn () => Arrays::lastKey([]),
            OutOfBoundsException::class
        );
    }

    public function testLastWithAssociativeArray(): void
    {
        Assert::same(30, Arrays::last(['name' => 'John', 'age' => 30]));
    }

    public function testLength(): void
    {
        Assert::same(3, Arrays::length([1, 2, 3]));
        Assert::same(0, Arrays::length([]));
    }

    public function testLengthRecursive(): void
    {
        $array = [1, [2, 3], [4, [5, 6]]];
        // COUNT_RECURSIVE counts the array elements plus the nested arrays themselves
        Assert::same(9, Arrays::length($array, COUNT_RECURSIVE));
    }

    public function testMap(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::map($array, fn ($v) => $v * 2);
        Assert::same([2, 4, 6], $result);
    }

    public function testMapKeys(): void
    {
        $array = ['a' => 1, 'b' => 2];
        $result = Arrays::mapKeys($array, fn ($k) => strtoupper($k));
        Assert::same(['A' => 1, 'B' => 2], $result);
    }

    public function testMapPreservesKeys(): void
    {
        $array = ['a' => 1, 'b' => 2];
        $result = Arrays::map($array, fn ($v) => $v * 2);
        Assert::same(['a' => 2, 'b' => 4], $result);
    }

    public function testMapWithKeys(): void
    {
        $input = ['first' => 'John', 'second' => 'Jane'];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return [strtoupper($key) => strtoupper($value)];
        });
        $expected = ['FIRST' => 'JOHN', 'SECOND' => 'JANE'];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysFiltersElementsWhenCallbackReturnsNull(): void
    {
        $input = ['keep', 'filter', 'keep_too'];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return $value === 'filter' ? null : [$key => $value];
        });
        $expected = [0 => 'keep', 2 => 'keep_too'];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysHandlesDuplicateKeysFromCallback(): void
    {
        $input = ['first', 'second'];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return ['duplicate_key' => $value];
        });
        $expected = ['duplicate_key' => 'second'];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysHandlesNullCallbackReturnsProperly(): void
    {
        $input = ['a' => 'value1', 'b' => 'value2', 'c' => 'value3'];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return $key === 'b' ? null : [$key => $value];
        });
        $expected = ['a' => 'value1', 'c' => 'value3'];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysPreservesNoElementsWhenAllCallbacksReturnNull(): void
    {
        $input = ['a', 'b', 'c'];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return null;
        });
        $expected = [];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysThrowsExceptionWhenCallbackReturnsEmptyArray(): void
    {
        $input = ['key' => 'value'];
        Assert::exception(
            function () use ($input) {
                Arrays::mapWithKeys($input, function ($value, $key) {
                    return [];
                });
            },
            InvalidArgumentException::class
        );
    }

    public function testMapWithKeysThrowsExceptionWhenCallbackReturnsMultiplePairs(): void
    {
        $input = ['key' => 'value'];
        Assert::exception(
            function () use ($input) {
                Arrays::mapWithKeys($input, function ($value, $key) {
                    return ['key1' => 'value1', 'key2' => 'value2'];
                });
            },
            InvalidArgumentException::class
        );
    }

    public function testMapWithKeysThrowsExceptionWhenCallbackReturnsNonArray(): void
    {
        $input = ['key' => 'value'];
        Assert::exception(
            function () use ($input) {
                Arrays::mapWithKeys($input, function ($value, $key) {
                    return 'invalid_string';
                });
            },
            InvalidArgumentException::class
        );
    }

    public function testMapWithKeysWithComplexTransformations(): void
    {
        $input = [
            ['name' => 'John', 'age' => 25],
            ['name' => 'Jane', 'age' => 30]
        ];
        $result = Arrays::mapWithKeys($input, function ($item, $index) {
            return [$item['name'] => $item['age']];
        });
        $expected = ['John' => 25, 'Jane' => 30];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysWithMixedKeysAndCompleteKeyTransformation(): void
    {
        $input = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return ['item_' . $value => $key];
        });
        $expected = ['item_1' => 'a', 'item_2' => 'b', 'item_3' => 'c'];
        Assert::same($expected, $result);
    }

    public function testMapWithKeysWithNumericArrayKeys(): void
    {
        $input = ['apple', 'banana', 'cherry'];
        $result = Arrays::mapWithKeys($input, function ($value, $key) {
            return ['fruit_' . $key => $value];
        });
        $expected = ['fruit_0' => 'apple', 'fruit_1' => 'banana', 'fruit_2' => 'cherry'];
        Assert::same($expected, $result);
    }

    public function testMedianEmpty(): void
    {
        $med = Arrays::median([]);
        Assert::null($med);
    }

    public function testMedianEvenCount(): void
    {
        $numbers = [1, 2, 3, 4];
        $med = Arrays::median($numbers);
        Assert::same(2.5, $med);

        // Unsorted input
        $numbers = [4, 1, 3, 2];
        $med = Arrays::median($numbers);
        Assert::same(2.5, $med);
    }

    public function testMedianOddCount(): void
    {
        $numbers = [1, 3, 5];
        $med = Arrays::median($numbers);
        Assert::same(3.0, $med);

        // Unsorted input
        $numbers = [5, 1, 3];
        $med = Arrays::median($numbers);
        Assert::same(3.0, $med);
    }

    public function testMedianSingleElement(): void
    {
        $numbers = [42];
        $med = Arrays::median($numbers);
        Assert::same(42.0, $med);
    }

    public function testMedianTwoElements(): void
    {
        $numbers = [1, 5];
        $med = Arrays::median($numbers);
        Assert::same(3.0, $med);
    }

    public function testMedianWithDuplicates(): void
    {
        $numbers = [1, 2, 2, 3, 3];
        $med = Arrays::median($numbers);
        Assert::same(2.0, $med);
    }

    public function testMedianWithFloats(): void
    {
        $numbers = [1.5, 2.5, 3.5];
        $med = Arrays::median($numbers);
        Assert::same(2.5, $med);
    }

    public function testMerge(): void
    {
        $result = Arrays::merge([1, 2], [3, 4]);
        Assert::same([1, 2, 3, 4], $result);
    }

    public function testMergeAssociative(): void
    {
        $result = Arrays::merge(['a' => 1, 'b' => 2], ['b' => 3, 'c' => 4]);

        // Note: Arrays::merge converts duplicate scalar values to arrays
        Assert::same(['a' => 1, 'b' => [2, 3], 'c' => 4], $result);
    }

    public function testMergeRecursive(): void
    {
        $array1 = ['a' => ['x' => 1, 'y' => 2]];
        $array2 = ['a' => ['y' => 3, 'z' => 4]];
        $result = Arrays::merge($array1, $array2);

        // Note: Arrays::merge converts duplicate scalar values to arrays
        Assert::same(['a' => ['x' => 1, 'y' => [2, 3], 'z' => 4]], $result);
    }

    public function testMergeThrowsWithSingleArray(): void
    {
        Assert::exception(
            fn () => Arrays::merge([1, 2, 3]),
            InvalidArgumentException::class
        );
    }

    public function testNormalize(): void
    {
        $obj = new stdClass();
        $obj->name = 'John';
        $obj->age = 30;
        $array = ['user' => $obj];
        $result = Arrays::normalize($array);
        Assert::same(['user' => ['name' => 'John', 'age' => 30]], $result);
    }

    public function testNormalizeRecursive(): void
    {
        $inner = new stdClass();
        $inner->city = 'NYC';
        $outer = new stdClass();
        $outer->address = $inner;
        $array = ['user' => $outer];
        $result = Arrays::normalize($array);
        Assert::same(['user' => ['address' => ['city' => 'NYC']]], $result);
    }

    public function testNotation(): void
    {
        $array = [
            'name' => 'John',
            'address' => [
                'city' => 'NYC',
                'zip' => '10001'
            ]
        ];
        $result = Arrays::notation($array);
        Assert::same([
            'name' => 'John',
            'address.city' => 'NYC',
            'address.zip' => '10001'
        ], $result);
    }

    public function testNotationDenoteRoundTrip(): void
    {
        // Test simple nested array
        $simple = [
            'name' => 'John',
            'address' => [
                'city' => 'NYC',
                'zip' => '10001'
            ]
        ];
        $flattened = Arrays::notation($simple);
        $result = Arrays::denote($flattened);
        Assert::same($simple, $result);

        // Test deeply nested structure
        $deep = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => 'deep value'
                    ]
                ]
            ]
        ];
        $flattened = Arrays::notation($deep);
        $result = Arrays::denote($flattened);
        Assert::same($deep, $result);

        // Test mixed data types
        $mixed = [
            'string' => 'hello',
            'number' => 42,
            'boolean' => true,
            'null' => null,
            'nested' => [
                'array' => [1, 2, 3],
                'object' => [
                    'prop' => 'value'
                ]
            ]
        ];
        $flattened = Arrays::notation($mixed);
        $result = Arrays::denote($flattened);
        Assert::same($mixed, $result);

        // Test with empty arrays as values
        $withEmpty = [
            'config' => [
                'settings' => [],
                'active' => true
            ]
        ];
        $flattened = Arrays::notation($withEmpty);
        $result = Arrays::denote($flattened);
        Assert::same($withEmpty, $result);

        // Test complex multi-level structure
        $complex = [
            'user' => [
                'name' => 'Jane',
                'profile' => [
                    'email' => 'jane@example.com',
                    'settings' => [
                        'theme' => 'dark',
                        'notifications' => true
                    ]
                ]
            ],
            'active' => true,
            'metadata' => [
                'created' => '2024-01-01',
                'updated' => '2024-01-02'
            ]
        ];
        $flattened = Arrays::notation($complex);
        $result = Arrays::denote($flattened);
        Assert::same($complex, $result);

        // Test numeric and string keys
        $mixedKeys = [
            0 => 'zero',
            'key' => 'value',
            'nested' => [
                1 => 'one',
                'inner' => 'data'
            ]
        ];
        $flattened = Arrays::notation($mixedKeys);
        $result = Arrays::denote($flattened);
        Assert::same($mixedKeys, $result);
    }

    public function testNotationWithPrefix(): void
    {
        $array = ['a' => 1, 'b' => ['c' => 2]];
        $result = Arrays::notation($array, 'prefix');
        Assert::same([
            'prefix.a' => 1,
            'prefix.b.c' => 2
        ], $result);
    }

    public function testOf(): void
    {
        $result = Arrays::of([1, 2, 3]);
        Assert::type('Phuture\Coherence\Type\Arrays', $result);
        Assert::same([1, 2, 3], $result->get());
        Assert::same([1, 2, 3], $result->toArray());
        Assert::same([1, 2, 3], $result());
        Assert::same(1, $result[0]);
    }

    public function testOfReverseFluent(): void
    {
        $result = Arrays::of([1, 2, 3]);
        Assert::type('Phuture\Coherence\Type\Arrays', $result);
        Assert::same([3, 2, 1], $result->reverse(false)->get());
    }

    public function testOnly(): void
    {
        $array = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'created_at' => '2023-01-01'
        ];

        // Extract specific fields
        $result = Arrays::only($array, ['id', 'name', 'email']);
        Assert::same(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'], $result);

        // Single field extraction
        $result = Arrays::only($array, ['name']);
        Assert::same(['name' => 'John Doe'], $result);

        // Keys that don't exist are ignored
        $result = Arrays::only($array, ['id', 'name', 'nonexistent']);
        Assert::same(['id' => 1, 'name' => 'John Doe'], $result);

        // Empty keys array returns empty array
        $result = Arrays::only($array, []);
        Assert::same([], $result);

        // With numeric keys
        $numeric = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
        $result = Arrays::only($numeric, [10, 30]);
        Assert::same([10 => 'ten', 30 => 'thirty'], $result);

        // All keys exist
        $result = Arrays::only($array, ['id', 'name', 'email', 'password', 'created_at']);
        Assert::same($array, $result);
    }

    public function testPad(): void
    {
        $array = [1, 2];
        $result = Arrays::pad($array, 5, 0);
        Assert::same([1, 2, 0, 0, 0], $result);
    }

    public function testPadNegative(): void
    {
        $array = [1, 2];
        $result = Arrays::pad($array, -5, 0);
        Assert::same([0, 0, 0, 1, 2], $result);
    }

    public function testPadNoChange(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::pad($array, 2, 0);
        Assert::same([1, 2, 3], $result);
    }

    public function testPartition(): void
    {
        $numbers = [1, 2, 3, 4, 5, 6];
        [$even, $odd] = Arrays::partition($numbers, fn ($n) => $n % 2 === 0);

        Assert::same([1 => 2, 3 => 4, 5 => 6], $even);
        Assert::same([0 => 1, 2 => 3, 4 => 5], $odd);
    }

    public function testPartitionAllFail(): void
    {
        $items = [1, 2, 3, 4];
        [$passed, $failed] = Arrays::partition($items, fn ($x) => $x > 10);

        Assert::same([], $passed);
        Assert::same([1, 2, 3, 4], $failed);
    }

    public function testPartitionAllPass(): void
    {
        $items = [1, 2, 3, 4];
        [$passed, $failed] = Arrays::partition($items, fn ($x) => $x > 0);

        Assert::same([1, 2, 3, 4], $passed);
        Assert::same([], $failed);
    }

    public function testPartitionEmptyArray(): void
    {
        [$passed, $failed] = Arrays::partition([], fn ($x) => true);

        Assert::same([], $passed);
        Assert::same([], $failed);
    }

    public function testPartitionPreservesKeys(): void
    {
        $items = [
            'a' => ['active' => true],
            'b' => ['active' => false],
            'c' => ['active' => true]
        ];
        [$active, $inactive] = Arrays::partition($items, fn ($item) => $item['active']);

        Assert::same(['a', 'c'], array_keys($active));
        Assert::same(['b'], array_keys($inactive));
    }

    public function testPartitionWithCallbackReceivingKey(): void
    {
        $items = ['a' => 1, 'b' => 2, 'c' => 3];
        [$withKeyB, $withoutKeyB] = Arrays::partition($items, fn ($item, $key) => $key === 'b');

        Assert::same(['b' => 2], $withKeyB);
        Assert::same(['a' => 1, 'c' => 3], $withoutKeyB);
    }

    public function testPrepend(): void
    {
        $array = ['name' => 'John'];
        Arrays::prepend($array, ['age' => 30, 'city' => 'NYC']);
        Assert::same(['age' => 30, 'city' => 'NYC', 'name' => 'John'], $array);
    }

    public function testPrependDoesNotOverwrite(): void
    {
        $array = ['name' => 'John', 'age' => null];
        Arrays::prepend($array, ['age' => 30, 'city' => 'NYC']);
        Assert::same(['age' => 30, 'city' => 'NYC', 'name' => 'John'], $array);
    }

    public function testPrependWithNullValues(): void
    {
        $array = ['name' => null];
        Arrays::prepend($array, ['name' => 'John']);
        Assert::same(['name' => 'John'], $array);
    }

    public function testProduct(): void
    {
        Assert::same(24, Arrays::product([2, 3, 4]));
        Assert::same(7.5, Arrays::product([2.5, 3]));
    }

    public function testProductEmpty(): void
    {
        Assert::same(1, Arrays::product([]));
    }

    public function testPull(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::pull($array);
        Assert::same(3, $result);
        Assert::same([1, 2], $array);
    }

    public function testPullEmpty(): void
    {
        $array = [];
        Assert::exception(
            function () use ($array) {
                Arrays::pull($array);
            },
            OutOfBoundsException::class
        );
        Assert::same([], $array);
    }

    public function testPush(): void
    {
        $array = [1, 2];
        $result = Arrays::push($array, 3, 4, 5);
        Assert::same([1, 2, 3, 4, 5], $array);
        Assert::same(5, $result);
    }

    public function testPushEmpty(): void
    {
        $array = [];
        $result = Arrays::push($array, 1);
        Assert::same([1], $array);
        Assert::same(1, $result);
    }

    public function testRandom(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = Arrays::random($array);
        Assert::contains($result, $array);
    }

    public function testRandomKeys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::randomKeys($array, 1);
        Assert::contains($result, ['a', 'b', 'c']);
    }

    public function testRandomKeysMultiple(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $result = Arrays::randomKeys($array, 2);
        Assert::count(2, $result);
        foreach ($result as $key) {
            Assert::contains($key, ['a', 'b', 'c', 'd']);
        }
    }

    public function testRandomThrowsOnEmpty(): void
    {
        Assert::exception(
            fn () => Arrays::random([]),
            OutOfBoundsException::class
        );
    }

    public function testRecursionLimitConstant(): void
    {
        Assert::same(1000, Arrays::RECURSION_LIMIT);
    }

    public function testReduce(): void
    {
        $array = [1, 2, 3, 4];
        $result = Arrays::reduce($array, fn ($carry, $item) => $carry + $item, 0);
        Assert::same(10, $result);
    }

    public function testReduceWithInitial(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::reduce($array, fn ($carry, $item) => $carry + $item, 10);
        Assert::same(16, $result);
    }

    public function testRemove(): void
    {
        $array = ['name' => 'John', 'age' => 30, 'city' => 'NYC', 'more' => ['zip' => 10001]];
        Arrays::remove($array, 'age');
        Assert::same(['name' => 'John', 'city' => 'NYC', 'more' => ['zip' => 10001]], $array);
        Arrays::remove($array, ['more', 'zip']);
        Assert::same(['name' => 'John', 'city' => 'NYC', 'more' => []], $array);
    }

    public function testRemoveNestedPath(): void
    {
        $array = ['user' => ['name' => 'John', 'age' => 30]];
        Arrays::remove($array, ['user', 'age']);
        Assert::same(['user' => ['name' => 'John']], $array);
    }

    public function testRemoveNonExistentKey(): void
    {
        $array = ['name' => 'John'];
        Arrays::remove($array, 'age');
        Assert::same(['name' => 'John'], $array);
    }

    public function testRename(): void
    {
        $array = ['old_name' => 'value', 'age' => 30];
        Arrays::rename($array, 'old_name', 'new_name');
        Assert::same(['new_name' => 'value', 'age' => 30], $array);
    }

    public function testRenameNestedPath(): void
    {
        $array = ['user' => ['old_key' => 'value']];
        Arrays::rename($array, ['user', 'old_key'], 'new_key');
        Assert::same(['user' => ['new_key' => 'value']], $array);
    }

    public function testRenamePreservesOrder(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        Arrays::rename($array, 'b', 'x');
        Assert::same(['a', 'x', 'c'], array_keys($array));
    }

    public function testReplace(): void
    {
        $base = ['a' => 1, 'b' => 2];
        $result = Arrays::replace($base, false, ['b' => 3, 'c' => 4]);
        Assert::same(['a' => 1, 'b' => 3, 'c' => 4], $result);
    }

    public function testReplaceMultipleArrays(): void
    {
        $base = ['a' => 1];
        $result = Arrays::replace($base, false, ['b' => 2], ['c' => 3]);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $result);
    }

    public function testReplaceRecursive(): void
    {
        $base = ['a' => ['x' => 1, 'y' => 2], 'b' => 2];
        $result = Arrays::replace($base, true, ['a' => ['y' => 3, 'z' => 4]]);
        Assert::same(['a' => ['x' => 1, 'y' => 3, 'z' => 4], 'b' => 2], $result);
    }

    public function testReverse(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::reverse($array, false);
        Assert::same([3, 2, 1], $result);
    }

    public function testReverseNoPreserveKeys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::reverse($array, false);
        Assert::same([3, 2, 1], $result);
    }

    public function testReversePreserveKeys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::reverse($array, true);
        Assert::same(['c' => 3, 'b' => 2, 'a' => 1], $result);
    }

    public function testSearch(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        Assert::same('b', Arrays::search($array, 2));
        Assert::same('c', Arrays::search($array, 3));
        Assert::false(Arrays::search($array, 4));
    }

    public function testSearchStrict(): void
    {
        $array = [0, 1, '2'];
        Assert::same(1, Arrays::search($array, 1, true));
        Assert::false(Arrays::search($array, '1', true));
        Assert::same(2, Arrays::search($array, 2, false));
    }

    public function testShift(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::shift($array);
        Assert::same(1, $result);
        Assert::same([2, 3], $array);
    }

    public function testShiftEmpty(): void
    {
        $array = [];
        Assert::exception(
            fn () => Arrays::shift($array),
            OutOfBoundsException::class
        );
    }

    public function testShuffle(): void
    {
        $array = [1, 2, 3, 4, 5];
        $original = $array;
        Arrays::shuffle($array);
        Assert::same(count($original), count($array));
        foreach ($original as $value) {
            Assert::contains($value, $array);
        }
    }

    public function testSlice(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = Arrays::slice($array, 1, 3);
        Assert::same([2, 3, 4], $result);
    }

    public function testSliceNegativeOffset(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = Arrays::slice($array, -2);
        Assert::same([4, 5], $result);
    }

    public function testSlicePreserveKeys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::slice($array, 1, 1, true);
        Assert::same(['b' => 2], $result);
    }

    public function testSome(): void
    {
        $array = [1, 2, 3];
        Assert::true(Arrays::some($array, fn ($v) => $v > 2));
        Assert::false(Arrays::some($array, fn ($v) => $v > 10));
    }

    public function testSomeEmpty(): void
    {
        Assert::false(Arrays::some([], fn () => true));
    }

    public function testSort(): void
    {
        $array = [3, 1, 2];
        Arrays::sort($array);
        Assert::same([1, 2, 3], $array);
    }

    public function testSortAssoc(): void
    {
        $array = ['b' => 2, 'a' => 1, 'c' => 3];
        Arrays::sortAssoc($array);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testSortAssocReverse(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        Arrays::sortAssoc($array, true);
        Assert::same(['c' => 3, 'b' => 2, 'a' => 1], $array);
    }

    public function testSortKeys(): void
    {
        $array = ['c' => 3, 'a' => 1, 'b' => 2];
        Arrays::sortKeys($array);
        Assert::same(['a' => 1, 'b' => 2, 'c' => 3], $array);
    }

    public function testSortKeysReverse(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        Arrays::sortKeys($array, true);
        Assert::same(['c' => 3, 'b' => 2, 'a' => 1], $array);
    }

    public function testSortKeysWithCallback(): void
    {
        $array = ['B' => 2, 'a' => 1, 'C' => 3];
        Arrays::sortKeys($array, false, fn ($a, $b) => strcasecmp($a, $b));
        Assert::same(['a' => 1, 'B' => 2, 'C' => 3], $array);
    }

    public function testSortNatural(): void
    {
        $array = ['img12.jpg', 'img10.jpg', 'img2.jpg', 'img1.jpg'];
        Arrays::sortNatural($array);
        // Natural sort preserves keys, so the order is reversed from the original
        Assert::same([3 => 'img1.jpg', 2 => 'img2.jpg', 1 => 'img10.jpg', 0 => 'img12.jpg'], $array);
    }

    public function testSortNaturalCaseInsensitive(): void
    {
        $array = ['IMG12.jpg', 'img10.jpg', 'IMG2.jpg', 'img1.jpg'];
        Arrays::sortNatural($array, true);
        // Natural sort preserves keys
        Assert::same([3 => 'img1.jpg', 2 => 'IMG2.jpg', 1 => 'img10.jpg', 0 => 'IMG12.jpg'], $array);
    }

    public function testSortReverse(): void
    {
        $array = [1, 3, 2];
        Arrays::sort($array, true);
        Assert::same([3, 2, 1], $array);
    }

    public function testSortWithCallback(): void
    {
        $array = ['a', 'B', 'c'];
        Arrays::sort($array, false, fn ($a, $b) => strcasecmp($a, $b));
        Assert::same(['a', 'B', 'c'], $array);
    }

    public function testSplice(): void
    {
        $array = [1, 2, 3, 4, 5];
        $removed = Arrays::splice($array, 1, 2);
        Assert::same([2, 3], $removed);
        Assert::same([1, 4, 5], $array);
    }

    public function testSpliceWithReplacement(): void
    {
        $array = [1, 2, 3, 4, 5];
        $removed = Arrays::splice($array, 1, 2, ['a', 'b']);
        Assert::same([2, 3], $removed);
        Assert::same([1, 'a', 'b', 4, 5], $array);
    }

    public function testSplit(): void
    {
        $array = [1, 2, 3, 4, 5];
        $result = Arrays::split($array, 2);
        Assert::same([[1, 2], [3, 4], [5]], $result);
    }

    public function testSplitPreserveKeys(): void
    {
        $array = ['a' => 1, 'b' => 2, 'c' => 3];
        $result = Arrays::split($array, 2, true);
        Assert::same([['a' => 1, 'b' => 2], ['c' => 3]], $result);
    }

    public function testSum(): void
    {
        Assert::same(10, Arrays::sum([1, 2, 3, 4]));
        Assert::same(7.5, Arrays::sum([2.5, 5]));
    }

    public function testSumEmpty(): void
    {
        Assert::same(0, Arrays::sum([]));
    }

    public function testToArrayPrecedence(): void
    {
        // Test that toArray() takes precedence over other methods
        $object = new class () {
            public function toArray(): array
            {
                return ['method' => 'toArray'];
            }

            public function toJson(): string
            {
                return '{"method": "toJson"}';
            }
        };

        $result = Arrays::toArray($object);
        Assert::same(['method' => 'toArray'], $result);
    }

    public function testToArrayWithEmptyValues(): void
    {
        Assert::same([], Arrays::toArray(null));
        Assert::same([''], Arrays::toArray(''));
        Assert::same([0], Arrays::toArray(0));
        Assert::same([0.0], Arrays::toArray(0.0));
    }

    public function testToArrayWithExistingArray(): void
    {
        $array = [1, 2, 3];
        $result = Arrays::toArray($array);
        Assert::same([1, 2, 3], $result);

        $assocArray = ['name' => 'John', 'age' => 30];
        $result = Arrays::toArray($assocArray);
        Assert::same(['name' => 'John', 'age' => 30], $result);
    }

    public function testToArrayWithInvalidJsonString(): void
    {
        // Invalid JSON should be treated as regular string
        $result = Arrays::toArray('{"invalid": json}');
        Assert::same(['{"invalid": json}'], $result);

        // Non-object JSON array should be wrapped
        $result = Arrays::toArray('"just a string"');
        Assert::same(['"just a string"'], $result);

        // JSON null should be wrapped
        $result = Arrays::toArray('null');
        Assert::same(['null'], $result);
    }

    public function testToArrayWithJsonSerializable(): void
    {
        $object = new class () implements JsonSerializable {
            public function jsonSerialize(): array
            {
                return ['name' => 'Jane', 'age' => 25];
            }
        };

        $result = Arrays::toArray($object);
        Assert::same(['name' => 'Jane', 'age' => 25], $result);
    }

    public function testToArrayWithJsonString(): void
    {
        // Valid JSON object
        $result = Arrays::toArray('{"name": "Alice", "age": 28}');
        Assert::same(['name' => 'Alice', 'age' => 28], $result);

        // Valid JSON array
        $result = Arrays::toArray('["apple", "banana", "cherry"]');
        Assert::same(['apple', 'banana', 'cherry'], $result);

        // Nested JSON
        $result = Arrays::toArray('{"user": {"name": "Bob", "email": "bob@example.com"}, "active": true}');
        Assert::same(['user' => ['name' => 'Bob', 'email' => 'bob@example.com'], 'active' => true], $result);
    }

    public function testToArrayWithNull(): void
    {
        $result = Arrays::toArray(null);
        Assert::same([], $result);
    }

    public function testToArrayWithRegularString(): void
    {
        $result = Arrays::toArray('regular text');
        Assert::same(['regular text'], $result);

        $result = Arrays::toArray('');
        Assert::same([''], $result);

        $result = Arrays::toArray('123');
        Assert::same(['123'], $result);
    }

    public function testToArrayWithScalarValues(): void
    {
        // String
        $result = Arrays::toArray('hello');
        Assert::same(['hello'], $result);

        // Integer
        $result = Arrays::toArray(42);
        Assert::same([42], $result);

        // Float
        $result = Arrays::toArray(3.14);
        Assert::same([3.14], $result);

        // Boolean
        $result = Arrays::toArray(true);
        Assert::same([true], $result);

        $result = Arrays::toArray(false);
        Assert::same([false], $result);
    }

    public function testToArrayWithStdClass(): void
    {
        $object = new stdClass();
        $object->name = 'John';
        $object->age = 30;
        $object->active = true;

        $result = Arrays::toArray($object);
        Assert::same(['name' => 'John', 'age' => 30, 'active' => true], $result);
    }

    public function testToArrayWithToJsonMethod(): void
    {
        $object = new class () {
            public function toJson(): string
            {
                return '{"product": "Laptop", "price": 999.99}';
            }
        };

        $result = Arrays::toArray($object);
        Assert::same(['product' => 'Laptop', 'price' => 999.99], $result);
    }

    public function testToArrayWithToObjectMethod(): void
    {
        $object = new class () {
            public function toArray(): array
            {
                return ['id' => 1, 'title' => 'Test Article'];
            }
        };

        $result = Arrays::toArray($object);
        Assert::same(['id' => 1, 'title' => 'Test Article'], $result);
    }

    public function testToObject(): void
    {
        $array = ['name' => 'John', 'age' => 30];
        $result = Arrays::toObject($array);

        Assert::type(stdClass::class, $result);
        Assert::same('John', $result->name);
        Assert::same(30, $result->age);
    }

    public function testToObjectDeeplyNested(): void
    {
        $array = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => [
                            'deep' => 'value'
                        ]
                    ]
                ]
            ]
        ];
        $result = Arrays::toObject($array);

        Assert::type(stdClass::class, $result);
        Assert::type(stdClass::class, $result->level1);
        Assert::type(stdClass::class, $result->level1->level2);
        Assert::type(stdClass::class, $result->level1->level2->level3);
        Assert::type(stdClass::class, $result->level1->level2->level3->level4);
        Assert::same('value', $result->level1->level2->level3->level4->deep);
    }

    public function testToObjectEmptyArray(): void
    {
        $array = [];
        $result = Arrays::toObject($array);

        Assert::type(stdClass::class, $result);
        Assert::same([], (array) $result);
    }

    public function testToObjectIsOppositeOfNormalize(): void
    {
        $original = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'email' => 'john@example.com',
                    'settings' => [
                        'theme' => 'dark',
                        'notifications' => true
                    ]
                ]
            ],
            'active' => true
        ];

        // Convert to object, then back to array
        $object = Arrays::toObject($original);
        $normalized = Arrays::normalize((array) $object);

        Assert::same($original, $normalized);
    }

    public function testToObjectMultidimensional(): void
    {
        $array = [
            'user' => [
                'name' => 'Jane',
                'address' => [
                    'street' => '123 Main St',
                    'city' => 'NYC'
                ]
            ],
            'settings' => ['theme' => 'dark']
        ];
        $result = Arrays::toObject($array);

        Assert::type(stdClass::class, $result);
        Assert::type(stdClass::class, $result->user);
        Assert::same('Jane', $result->user->name);
        Assert::type(stdClass::class, $result->user->address);
        Assert::same('123 Main St', $result->user->address->street);
        Assert::same('NYC', $result->user->address->city);
        Assert::type(stdClass::class, $result->settings);
        Assert::same('dark', $result->settings->theme);
    }

    public function testToObjectNumericKeys(): void
    {
        // PHP stdClass will convert numeric keys to inaccessible properties
        // So we test with mixed keys
        $array = [
            0 => 'zero',
            1 => 'one',
            'string' => 'value'
        ];
        $result = Arrays::toObject($array);

        Assert::type(stdClass::class, $result);
        Assert::same('value', $result->string);
        // Numeric keys become properties that need to be accessed differently
        $resultArray = (array) $result;
        Assert::same('zero', $resultArray[0]);
        Assert::same('one', $resultArray[1]);
    }

    public function testToObjectPreservesScalarValues(): void
    {
        $array = [
            'int' => 123,
            'float' => 45.67,
            'bool_true' => true,
            'bool_false' => false,
            'null' => null,
            'string' => 'test'
        ];
        $result = Arrays::toObject($array);

        Assert::same(123, $result->int);
        Assert::same(45.67, $result->float);
        Assert::same(true, $result->bool_true);
        Assert::same(false, $result->bool_false);
        Assert::same(null, $result->null);
        Assert::same('test', $result->string);
    }

    public function testToObjectWithMixedTypes(): void
    {
        $array = [
            'string' => 'hello',
            'number' => 42,
            'boolean' => true,
            'null' => null,
            'nested' => [
                'array' => [1, 2, 3],
                'object' => (object)['prop' => ['value' => 23]]
            ]
        ];
        $result = Arrays::toObject($array);

        Assert::type(stdClass::class, $result);
        Assert::same('hello', $result->string);
        Assert::same(42, $result->number);
        Assert::same(true, $result->boolean);
        Assert::same(null, $result->null);
        Assert::type(stdClass::class, $result->nested);
        Assert::same([1, 2, 3], $result->nested->array);
        Assert::type(stdClass::class, $result->nested->object);
        Assert::same(23, $result->nested->object->prop->value);
    }

    public function testUnique(): void
    {
        $array = [1, 2, 2, 3, 1, 4];
        $result = Arrays::unique($array);
        Assert::same([0 => 1, 1 => 2, 3 => 3, 5 => 4], $result);
    }

    public function testUniqueNumeric(): void
    {
        $array = ['1', 1, '2', 2];
        $result = Arrays::unique($array, SORT_NUMERIC);
        Assert::same([0 => '1', 2 => '2'], $result);
    }

    public function testUniqueStrings(): void
    {
        $array = ['a', 'b', 'a', 'c'];
        $result = Arrays::unique($array);
        Assert::same([0 => 'a', 1 => 'b', 3 => 'c'], $result);
    }

    public function testUnshift(): void
    {
        $array = [3, 4];
        $result = Arrays::unshift($array, 1, 2);
        Assert::same([1, 2, 3, 4], $array);
        Assert::same(4, $result);
    }

    public function testUnshiftEmpty(): void
    {
        $array = [];
        $result = Arrays::unshift($array, 1);
        Assert::same([1], $array);
        Assert::same(1, $result);
    }

    public function testValues(): void
    {
        Assert::same([10, 20, 30], Arrays::values([10, 20, 30]));
        Assert::same(['John', 30], Arrays::values(['name' => 'John', 'age' => 30]));
        Assert::same([], Arrays::values([]));
    }

    public function testWhere(): void
    {
        $users = [
            ['name' => 'John', 'age' => 25, 'active' => true],
            ['name' => 'Jane', 'age' => 17, 'active' => true],
            ['name' => 'Bob', 'age' => 30, 'active' => false]
        ];
        $adults = Arrays::where($users, fn ($user) => $user['age'] >= 18);

        Assert::count(2, $adults);
        Assert::same('John', $adults[0]['name']);
        Assert::same('Bob', $adults[2]['name']);
    }

    public function testWhereEmpty(): void
    {
        $numbers = [1, 2, 3, 4];
        $result = Arrays::where($numbers, fn ($n) => $n > 10);

        Assert::same([], $result);
    }

    public function testWhereIn(): void
    {
        $users = [
            ['id' => 1, 'name' => 'John', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Jane', 'role' => 'user'],
            ['id' => 3, 'name' => 'Bob', 'role' => 'admin'],
            ['id' => 4, 'name' => 'Alice', 'role' => 'moderator']
        ];
        $result = Arrays::whereIn($users, 'role', ['admin', 'moderator']);

        Assert::count(3, $result);
        Assert::same('John', $result[0]['name']);
        Assert::same('Bob', $result[2]['name']);
        Assert::same('Alice', $result[3]['name']);
    }

    public function testWhereInEmptyValues(): void
    {
        $items = [
            ['type' => 'A'],
            ['type' => 'B']
        ];
        $result = Arrays::whereIn($items, 'type', []);

        Assert::same([], $result);
    }

    public function testWhereInMissingKey(): void
    {
        $items = [
            ['type' => 'A'],
            ['name' => 'no type'],
            ['type' => 'B']
        ];
        $result = Arrays::whereIn($items, 'type', ['A', 'B']);

        Assert::count(2, $result);
        Assert::same('A', $result[0]['type']);
        Assert::same('B', $result[2]['type']);
    }

    public function testWhereInMultipleValues(): void
    {
        $items = [
            ['type' => 'A', 'value' => 1],
            ['type' => 'B', 'value' => 2],
            ['type' => 'C', 'value' => 3],
            ['type' => 'D', 'value' => 4]
        ];
        $result = Arrays::whereIn($items, 'type', ['A', 'C', 'D']);

        Assert::count(3, $result);
        Assert::same('A', $result[0]['type']);
        Assert::same('C', $result[2]['type']);
        Assert::same('D', $result[3]['type']);
    }

    public function testWhereInStrictComparison(): void
    {
        $items = [
            ['id' => 1],
            ['id' => '1'],
            ['id' => 2],
            ['id' => true]
        ];
        $result = Arrays::whereIn($items, 'id', [1]);

        Assert::count(1, $result);
        Assert::same(['id' => 1], $result[0]);
    }

    public function testWhereInWithObjects(): void
    {
        $obj1 = new stdClass();
        $obj1->type = 'fruit';

        $obj2 = new stdClass();
        $obj2->type = 'vegetable';

        $obj3 = new stdClass();
        $obj3->type = 'fruit';

        $items = [$obj1, $obj2, $obj3];
        $result = Arrays::whereIn($items, 'type', ['fruit']);

        Assert::count(2, $result);
        Assert::same('fruit', $result[0]->type);
        Assert::same('fruit', $result[2]->type);
    }

    public function testWherePreservesKeys(): void
    {
        $items = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
        $result = Arrays::where($items, fn ($n) => $n % 2 === 0);

        Assert::same(['b', 'd'], array_keys($result));
        Assert::same([2, 4], array_values($result));
    }

    public function testWrap(): void
    {
        $array = ['a', 'b', 'c'];
        $result = Arrays::wrap($array, '<', '>');
        Assert::same(['<a>', '<b>', '<c>'], $result);
    }

    public function testWrapSkipsNonScalars(): void
    {
        $array = ['a', ['nested'], 'c'];
        $result = Arrays::wrap($array, '<', '>');
        Assert::same(['<a>', ['nested'], '<c>'], $result);
    }

    public function testZip(): void
    {
        $numbers = [1, 2, 3];
        $letters = ['a', 'b', 'c'];
        $result = Arrays::zip($numbers, $letters);
        Assert::same([[1, 'a'], [2, 'b'], [3, 'c']], $result);
    }

    public function testZipDifferentLengths(): void
    {
        $a = [1, 2, 3, 4];
        $b = ['a', 'b'];
        $result = Arrays::zip($a, $b);
        Assert::same([[1, 'a'], [2, 'b']], $result);
    }

    public function testZipEmptyArrays(): void
    {
        $result = Arrays::zip([], []);
        Assert::same([], $result);
    }

    public function testZipPreservesNestedArrays(): void
    {
        $a = [[1, 2], [3, 4]];
        $b = [['a'], ['b']];
        $result = Arrays::zip($a, $b);
        Assert::same([[[1, 2], ['a']], [[3, 4], ['b']]], $result);
    }

    public function testZipSingleArrayThrows(): void
    {
        Assert::exception(
            fn () => Arrays::zip([1, 2, 3]),
            InvalidArgumentException::class,
            '~At least two arrays are required~'
        );
    }

    public function testZipThreeArrays(): void
    {
        $ids = [1, 2];
        $names = ['John', 'Jane'];
        $ages = [30, 25];
        $result = Arrays::zip($ids, $names, $ages);
        Assert::same([[1, 'John', 30], [2, 'Jane', 25]], $result);
    }

    public function testZipWithAssociativeArrays(): void
    {
        $a = ['x' => 1, 'y' => 2];
        $b = ['x' => 'a', 'y' => 'b'];
        $result = Arrays::zip($a, $b);
        // Zip uses numeric indices, ignoring keys
        Assert::same([[1, 'a'], [2, 'b']], $result);
    }
}

(new ArraysTest())->run();
