<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use stdClass;
use ArrayIterator;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Type\Arrays;
use Phuture\Coherence\Enum\{KeyCase, SortComparison};
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/../bootstrap.php';

class ArraysTest extends TestCase
{
    public function testAppend(): void
    {
        // Happy path: adds new keys
        $result = (new Arrays(['name' => 'Desk']))
            ->append(['price' => 100, 'color' => 'brown'])
            ->toArray();
        Assert::same(['name' => 'Desk', 'price' => 100, 'color' => 'brown'], $result);

        // Existing key is not overwritten
        $result = (new Arrays(['name' => 'Desk', 'price' => null]))
            ->append(['price' => 999, 'color' => 'brown'])
            ->toArray();
        Assert::same(['name' => 'Desk', 'price' => null, 'color' => 'brown'], $result);

        // Empty items: no change
        $result = (new Arrays(['a' => 1]))->append([])->toArray();
        Assert::same(['a' => 1], $result);

        // Returns self for chaining
        $arrays = new Arrays([]);
        Assert::type(Arrays::class, $arrays->append(['x' => 1]));
    }
    public function testAssociate(): void
    {
        $array = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => 'secret',
                'created_at' => '2023-01-01'
            ],
            [
                'id' => 2,
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
                'password' => 'secret',
                'created_at' => '2023-01-01'
            ]
        ];
        $arrays = new Arrays($array);
        $result = $arrays->associate('email', 'name')->toArray();
        Assert::same(
            ['john@example.com' => 'John Doe', 'jane@example.com' => 'Jane Doe'],
            $result
        );

        // Test with id as key only
        $users = new Arrays([
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com'],
        ]);
        $result = $users->associate('id')->toArray();
        Assert::same([
            1 => ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
            2 => ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com'],
        ], $result);
    }

    public function testChangeKeyCase(): void
    {
        $data = new Arrays(['NAME' => 'John', 'AGE' => 30, 'Email' => 'john@example.com']);

        // Test to lowercase
        $result = $data->changeKeyCase(KeyCase::Lower)->toArray();
        Assert::same(['name' => 'John', 'age' => 30, 'email' => 'john@example.com'], $result);

        // Test to uppercase
        $data = new Arrays(['name' => 'John', 'age' => 30]);
        $result = $data->changeKeyCase(KeyCase::Upper)->toArray();
        Assert::same(['NAME' => 'John', 'AGE' => 30], $result);
    }

    public function testCollapse(): void
    {
        $data = new Arrays([[1, 2, 3], [4, 5], [6, 7, 8, 9]]);
        $result = $data->collapse()->toArray();
        Assert::same([1, 2, 3, 4, 5, 6, 7, 8, 9], $result);

        // Test with empty arrays
        $empty = new Arrays([[], ['single'], []]);
        $result = $empty->collapse()->toArray();
        Assert::same(['single'], $result);
    }

    public function testColumn(): void
    {
        // Test extract name column
        $users = new Arrays([
            ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com'],
        ]);
        $result = $users->column('name')->toArray();
        Assert::same(['John', 'Jane'], $result);

        // Test with index
        $usersWithIndex = new Arrays([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);
        $result = $usersWithIndex->column('name', 'id')->toArray();
        Assert::same([1 => 'John', 2 => 'Jane'], $result);
    }

    public function testCombine(): void
    {
        $keys = new Arrays(['name', 'age']);

        // Test with matching array length
        $values = ['John', 30];
        $result = $keys->combine($values)->toArray();
        Assert::same(['name' => 'John', 'age' => 30], $result);

        // Test with mismatched length (should throw exception)
        Assert::exception(function () use ($keys) {
            $keys->combine(['John'])->toArray();
        }, InvalidArgumentException::class, 'Invalid Argument: Both arrays must have the same number of elements');
    }

    public function testComplexFluentChain(): void
    {
        $users = new Arrays([
            ['id' => 1, 'name' => 'John', 'age' => 30, 'active' => true],
            ['id' => 2, 'name' => 'Jane', 'age' => 25, 'active' => false],
            ['id' => 3, 'name' => 'Bob', 'age' => 35, 'active' => true],
            ['id' => 4, 'name' => 'Alice', 'age' => 28, 'active' => true],
        ]);

        // Complex transformation chain
        $result = $users->filter(fn ($user) => $user['active'])
            ->sortAssoc(false, fn ($a, $b) => $a['age'] - $b['age'])
            ->associate('id', 'name')
            ->values()
            ->wrap('User: ', '!')
            ->toArray();

        Assert::same(['User: Alice!', 'User: John!', 'User: Bob!'], $result);
    }

    public function testConstructor(): void
    {
        $arrays = new Arrays(['apple', 'banana', 'cherry']);
        Assert::same(['apple', 'banana', 'cherry'], $arrays->toArray());

        // Test with empty array
        $empty = new Arrays([]);
        Assert::same([], $empty->toArray());

        // Test with associative array
        $assoc = new Arrays(['name' => 'John', 'age' => 30]);
        Assert::same(['name' => 'John', 'age' => 30], $assoc->toArray());
    }

    public function testCount(): void
    {
        $data = new Arrays(['a', 'b', 'c']);
        Assert::same(3, $data->count());

        $empty = new Arrays([]);
        Assert::same(0, $empty->count());

        // Test with count() function
        Assert::same(3, count($data));
    }

    public function testCountable(): void
    {
        $arrays = new Arrays([1, 2, 3, 4, 5]);

        // Test count method
        Assert::same(5, $arrays->count());

        // Test count function
        Assert::same(5, count($arrays));

        // Test empty array
        $empty = new Arrays([]);
        Assert::same(0, $empty->count());
        Assert::same(0, count($empty));
    }

    public function testCrossJoin(): void
    {
        $colors = new Arrays(['red', 'blue']);
        $sizes = ['small', 'large'];

        $result = $colors->crossJoin($sizes)->toArray();
        Assert::same([
            ['red', 'small'],
            ['red', 'large'],
            ['blue', 'small'],
            ['blue', 'large'],
        ], $result);
    }

    public function testDenote(): void
    {
        $flat = new Arrays([
            'user.name' => 'John',
            'user.profile.age' => 30,
            'settings.theme' => 'dark',
        ]);

        $result = $flat->denote()->toArray();
        Assert::same([
            'settings' => [
                'theme' => 'dark'
            ],
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30
                ]
            ]
        ], $result);
    }

    public function testDifference(): void
    {
        $base = new Arrays([1, 2, 3, 4, 5]);
        $result = $base->difference([2, 4])->toArray();
        Assert::same([0 => 1, 2 => 3, 4 => 5], $result);

        // Test with associative arrays
        $assoc = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);
        $result = $assoc->difference(['b' => 'banana'])->toArray();
        Assert::same(['a' => 'apple', 'c' => 'cherry'], $result);
    }

    public function testDifferenceAssoc(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);
        $compare = ['a' => 'apple', 'b' => 'blueberry'];

        $result = $base->differenceAssoc($compare)->toArray();
        Assert::same(['b' => 'banana', 'c' => 'cherry'], $result);
    }

    public function testDifferenceKeys(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);
        $compare = ['a' => 'avocado', 'x' => 'xigua'];

        $result = $base->differenceKeys($compare)->toArray();
        Assert::same(['b' => 'banana', 'c' => 'cherry'], $result);
    }

    public function testFillKeys(): void
    {
        // Happy path: string keys all set to null
        $result = (new Arrays(['name', 'email', 'phone']))
            ->fillKeys(null)
            ->toArray();
        Assert::same(['name' => null, 'email' => null, 'phone' => null], $result);

        // All keys set to a non-null value
        $result = (new Arrays(['read', 'write', 'delete']))
            ->fillKeys(false)
            ->toArray();
        Assert::same(['read' => false, 'write' => false, 'delete' => false], $result);

        // Numeric keys
        $result = (new Arrays([10, 20, 30]))
            ->fillKeys('value')
            ->toArray();
        Assert::same([10 => 'value', 20 => 'value', 30 => 'value'], $result);

        // Empty array throws exception
        Assert::exception(function () {
            (new Arrays([]))->fillKeys(null);
        }, \Phuture\Coherence\Exception\InvalidArgumentException::class);

        // Returns self for chaining
        $arrays = new Arrays(['a', 'b']);
        Assert::type(Arrays::class, $arrays->fillKeys(0));
    }

    public function testFilter(): void
    {
        $numbers = new Arrays([1, 2, 3, 4, 5, 6]);
        $result = $numbers->filter(fn ($n) => $n % 2 === 0)->toArray();
        Assert::same([1 => 2, 3 => 4, 5 => 6], $result);

        // Test without callback (removes falsy values)
        $mixed = new Arrays([0, '', 'hello', null, false, 42]);
        $result = $mixed->filter()->toArray();
        Assert::same([2 => 'hello', 5 => 42], $result);
    }

    public function testFlatten(): void
    {
        $nested = new Arrays([1, [2, [3, 4], 5], 6]);
        $result = $nested->flatten()->toArray();
        Assert::same([1, 2, 3, 4, 5, 6], $result);

        // Test with associative arrays
        $assoc = new Arrays(['user' => ['name' => 'John', 'age' => 30]]);
        $result = $assoc->flatten()->toArray();
        Assert::same(['John', 30], $result);
    }

    public function testFlip(): void
    {
        $data = new Arrays(['a' => 'apple', 'b' => 'banana']);
        $result = $data->flip()->toArray();
        Assert::same(['apple' => 'a', 'banana' => 'b'], $result);

        // Test with numeric keys
        $numeric = new Arrays([0 => 'zero', 1 => 'one']);
        $result = $numeric->flip()->toArray();
        Assert::same(['zero' => 0, 'one' => 1], $result);
    }

    public function testFluentInterface(): void
    {
        $data = new Arrays([3, 1, 4, 1, 5, 9]);

        // Test method chaining
        $result = $data->sort()
            ->filter(fn ($n) => $n > 2)
            ->map(fn ($n) => $n * 2)
            ->values()
            ->toArray();

        Assert::same([6, 8, 10, 18], $result);
    }



    public function testGrep(): void
    {
        // Test basic regex match
        $words = new Arrays(['apple', 'banana', 'cherry', 'date']);
        $result = $words->grep('/^b/')->toArray();
        Assert::same([1 => 'banana'], $result);

        // Test case-insensitive pattern
        $words = new Arrays(['apple', 'banana', 'cherry']);
        $result = $words->grep('/APPLE/i')->toArray();
        Assert::same([0 => 'apple'], $result);
    }

    public function testGroupByCallback(): void
    {
        $numbers = new Arrays([1, 2, 3, 4, 5, 6]);
        $result = $numbers->groupBy(fn ($n) => $n % 2)->toArray();

        Assert::count(2, $result);
        Assert::same([1, 3, 5], $result[1]);
        Assert::same([2, 4, 6], $result[0]);
    }

    public function testGroupByChaining(): void
    {
        $users = new Arrays([
            ['name' => 'John', 'department' => 'Sales', 'active' => true],
            ['name' => 'Jane', 'department' => 'IT', 'active' => true],
            ['name' => 'Bob', 'department' => 'Sales', 'active' => false]
        ]);

        // Group by department, then filter to active users only in Sales
        $grouped = $users->groupBy('department')->toArray();
        $salesUsers = new Arrays($grouped['Sales']);
        $activeSales = $salesUsers->where(fn ($user) => $user['active'])->toArray();

        Assert::count(1, $activeSales);
        Assert::same('John', $activeSales[0]['name']);
    }

    public function testGroupByEmptyArray(): void
    {
        $items = new Arrays([]);
        $result = $items->groupBy('key')->toArray();
        Assert::same([], $result);
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

        $items = new Arrays([$obj1, $obj2, $obj3]);
        $result = $items->groupBy('type')->toArray();

        Assert::count(2, $result);
        Assert::count(2, $result['fruit']);
        Assert::count(1, $result['vegetable']);
        Assert::same('apple', $result['fruit'][0]->name);
        Assert::same('banana', $result['fruit'][1]->name);
        Assert::same('carrot', $result['vegetable'][0]->name);
    }

    public function testGroupByStringKey(): void
    {
        $users = new Arrays([
            ['name' => 'John', 'department' => 'Sales'],
            ['name' => 'Jane', 'department' => 'IT'],
            ['name' => 'Bob', 'department' => 'Sales']
        ]);
        $result = $users->groupBy('department')->toArray();

        Assert::count(2, $result);
        Assert::count(2, $result['Sales']);
        Assert::count(1, $result['IT']);
        Assert::same('John', $result['Sales'][0]['name']);
        Assert::same('Bob', $result['Sales'][1]['name']);
        Assert::same('Jane', $result['IT'][0]['name']);
    }

    public function testInsertAfter(): void
    {
        // Happy path
        $result = (new Arrays(['first' => 10, 'second' => 20]))
            ->insertAfter('first', ['hello' => 'world'])
            ->toArray();
        Assert::same(['first' => 10, 'hello' => 'world', 'second' => 20], $result);

        // Key does not exist: items appended
        $result = (new Arrays(['first' => 10]))
            ->insertAfter('missing', ['new' => 20])
            ->toArray();
        Assert::same(['first' => 10, 'new' => 20], $result);

        // Empty items: no change
        $result = (new Arrays(['a' => 1]))->insertAfter('a', [])->toArray();
        Assert::same(['a' => 1], $result);

        // Returns self for chaining
        $arrays = new Arrays(['a' => 1]);
        Assert::type(Arrays::class, $arrays->insertAfter('a', ['b' => 2]));
    }

    public function testInsertBefore(): void
    {
        // Happy path
        $result = (new Arrays(['first' => 10, 'second' => 20]))
            ->insertBefore('second', ['hello' => 'world'])
            ->toArray();
        Assert::same(['first' => 10, 'hello' => 'world', 'second' => 20], $result);

        // Key does not exist: items prepended
        $result = (new Arrays(['first' => 10]))
            ->insertBefore('missing', ['new' => 5])
            ->toArray();
        Assert::same(['new' => 5, 'first' => 10], $result);

        // Empty items: no change
        $result = (new Arrays(['a' => 1]))->insertBefore('a', [])->toArray();
        Assert::same(['a' => 1], $result);

        // Returns self for chaining
        $arrays = new Arrays(['b' => 2]);
        Assert::type(Arrays::class, $arrays->insertBefore('b', ['a' => 1]));
    }

    public function testIntersect(): void
    {
        $base = new Arrays([1, 2, 3, 4, 5]);
        $result = $base->intersect([2, 4, 6, 8], [3, 4])->toArray();
        Assert::same([3 => 4], $result);
    }

    public function testIntersectAssoc(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);
        $compare1 = ['a' => 'apple', 'b' => 'blueberry'];

        $result = $base->intersectAssoc($compare1)->toArray();
        Assert::same(['a' => 'apple'], $result);
    }

    public function testIntersectKeys(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);
        $compare = ['a' => 'avocado', 'b' => 'blueberry'];

        $result = $base->intersectKeys($compare)->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana'], $result);
    }

    public function testIterate(): void
    {
        // Happy path: multiply each value by 2
        $result = (new Arrays([10, 20, 30]))
            ->iterate(function (&$value) {
                $value = $value * 2;
            })
            ->toArray();
        Assert::same([20, 40, 60], $result);

        // Recursive: uppercase all strings in nested array
        $result = (new Arrays(['user' => ['name' => 'john', 'city' => 'nyc']]))
            ->iterate(function (&$value) {
                if (is_string($value)) {
                    $value = strtoupper($value);
                }
            }, true)
            ->toArray();
        Assert::same(['user' => ['name' => 'JOHN', 'city' => 'NYC']], $result);

        // With extra args passed to callback
        $result = (new Arrays([1, 2, 3]))
            ->iterate(function (&$value, $key, $extra) {
                $value += $extra;
            }, false, 10)
            ->toArray();
        Assert::same([11, 12, 13], $result);

        // Returns self for chaining
        $arrays = new Arrays([1, 2]);
        Assert::type(Arrays::class, $arrays->iterate(function (&$v) {
            $v++;
        }));
    }

    public function testIteratorAggregate(): void
    {
        $arrays = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);

        // Test getIterator method
        $iterator = $arrays->getIterator();
        Assert::type(ArrayIterator::class, $iterator);

        // Test foreach compatibility
        $results = [];
        foreach ($arrays as $key => $value) {
            $results[$key] = $value;
        }
        Assert::same(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'], $results);

        // Test iterator functionality
        $iterator->rewind();
        Assert::true($iterator->valid());
        Assert::same('a', $iterator->key());
        Assert::same('apple', $iterator->current());

        $iterator->next();
        Assert::true($iterator->valid());
        Assert::same('b', $iterator->key());
        Assert::same('banana', $iterator->current());
    }

    public function testKeys(): void
    {
        $data = new Arrays(['name' => 'John', 'age' => 30]);
        $result = $data->keys()->toArray();
        Assert::same(['name', 'age'], $result);

        // Test with numeric keys
        $numeric = new Arrays(['a', 'b', 'c']);
        $result = $numeric->keys()->toArray();
        Assert::same([0, 1, 2], $result);
    }

    public function testMap(): void
    {
        $numbers = new Arrays([1, 2, 3, 4, 5]);
        $result = $numbers->map(fn ($n) => $n * 2)->toArray();
        Assert::same([2, 4, 6, 8, 10], $result);

        // Test with associative array
        $assoc = new Arrays(['a' => 1, 'b' => 2]);
        $result = $assoc->map(fn ($n) => $n * $n)->toArray();
        Assert::same(['a' => 1, 'b' => 4], $result);
    }

    public function testMapKeys(): void
    {
        // Test with strtoupper
        $data = new Arrays(['first_name' => 'John', 'last_name' => 'Doe']);
        $result = $data->mapKeys('strtoupper')->toArray();
        Assert::same(['FIRST_NAME' => 'John', 'LAST_NAME' => 'Doe'], $result);

        // Test with custom callback
        $data = new Arrays(['first_name' => 'John', 'last_name' => 'Doe']);
        $result = $data->mapKeys(fn ($key) => "user_{$key}")->toArray();
        Assert::same([
            'user_first_name' => 'John',
            'user_last_name' => 'Doe'
        ], $result);
    }

    public function testMapWithKeys(): void
    {
        $users = new Arrays([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);

        $result = $users->mapWithKeys(fn ($user) => [$user['id'] => $user['name']])->toArray();
        Assert::same([1 => 'John', 2 => 'Jane'], $result);

        // Test with complex transformation
        $users = new Arrays([
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane'],
        ]);
        $result = $users->mapWithKeys(fn ($user) => [
            "user_{$user['id']}" => $user['name']
        ])->toArray();
        Assert::same([
            'user_1' => 'John',
            'user_2' => 'Jane'
        ], $result);
    }

    public function testMergeDeep(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana']);
        $result = $base->merge(['c' => 'cherry'])->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'], $result);

        // Shallow (default): overlapping string key is overwritten
        $overlap = new Arrays(['a' => 'apple']);
        $result = $overlap->merge(['a' => 'avocado'])->toArray();
        Assert::same(['a' => 'avocado'], $result);

        // Recursive: overlapping string key is combined into an array
        $overlap2 = new Arrays(['a' => 'apple']);
        $result = $overlap2->merge(['a' => 'avocado'], true)->toArray();
        Assert::same(['a' => ['apple', 'avocado']], $result);

        // Numeric keys are always reindexed
        $numeric = new Arrays([1, 2]);
        $result = $numeric->merge([3, 4])->toArray();
        Assert::same([1, 2, 3, 4], $result);
    }

    public function testMergeShallow(): void
    {
        $base = new Arrays([1, 2, 3]);
        $result = $base->merge([4, 5, 6])->toArray();
        Assert::same([1, 2, 3, 4, 5, 6], $result);

        $assoc1 = new Arrays(['a' => 'apple']);
        $result = $assoc1->merge(['b' => 'banana'])->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana'], $result);
    }

    public function testNormalize(): void
    {
        $obj1 = new stdClass();
        $obj1->name = 'John';
        $obj1->age = 30;

        $obj2 = new stdClass();
        $obj2->city = 'New York';

        $data = new Arrays([$obj1, $obj2, ['mixed' => 'array']]);
        $result = $data->normalize()->toArray();
        Assert::same([
            ['name' => 'John', 'age' => 30],
            ['city' => 'New York'],
            ['mixed' => 'array']
        ], $result);
    }

    public function testNotation(): void
    {
        $nested = new Arrays([
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30
                ]
            ],
            'settings' => [
                'theme' => 'dark'
            ]
        ]);

        $result = $nested->notation()->toArray();
        Assert::same([
            'user.name' => 'John',
            'user.profile.age' => 30,
            'settings.theme' => 'dark'
        ], $result);

        // Test with prefix (use fresh object and correct prefix without trailing dot)
        $nested = new Arrays([
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30
                ]
            ],
            'settings' => [
                'theme' => 'dark'
            ]
        ]);
        $result = $nested->notation('data')->toArray();
        Assert::same([
            'data.user.name' => 'John',
            'data.user.profile.age' => 30,
            'data.settings.theme' => 'dark'
        ], $result);
    }

    public function testOffsetExists(): void
    {
        $fluentArray = new Arrays(['a' => 1, 'b' => 2]);
        Assert::true(isset($fluentArray['a']));
        Assert::false(isset($fluentArray['c']));

        // Numeric keys
        $numericArray = new Arrays([10, 20]);
        Assert::true(isset($numericArray[0]));
        Assert::false(isset($numericArray[5]));

        // Null value exists but is falsy — isset returns false for null
        $nullValueArray = new Arrays(['x' => null]);
        Assert::false(isset($nullValueArray['x']));
    }

    public function testOffsetGet(): void
    {
        $fluentArray = new Arrays(['x' => 42, 'y' => 'hello']);
        Assert::same(42, $fluentArray['x']);
        Assert::same('hello', $fluentArray['y']);

        // Non-existent offset returns null
        Assert::null($fluentArray['missing']);

        // Numeric keys
        $numericArray = new Arrays([10, 20, 30]);
        Assert::same(20, $numericArray[1]);
    }

    public function testOffsetSet(): void
    {
        $fluentArray = new Arrays(['a' => 1]);

        // Named key assignment
        $fluentArray['b'] = 2;
        Assert::same(['a' => 1, 'b' => 2], $fluentArray->toArray());

        // Null offset appends
        $fluentArray[] = 3;
        Assert::same(['a' => 1, 'b' => 2, 0 => 3], $fluentArray->toArray());

        // Overwrite existing key
        $fluentArray['a'] = 99;
        Assert::same(99, $fluentArray['a']);
    }

    public function testOffsetUnset(): void
    {
        $fluentArray = new Arrays(['a' => 1, 'b' => 2, 'c' => 3]);
        unset($fluentArray['b']);
        Assert::same(['a' => 1, 'c' => 3], $fluentArray->toArray());

        // Unsetting a non-existent key is a no-op
        unset($fluentArray['z']);
        Assert::same(['a' => 1, 'c' => 3], $fluentArray->toArray());
    }

    public function testOnly(): void
    {
        $data = new Arrays([
            'name' => 'John',
            'age' => 30,
            'email' => 'john@example.com',
            'city' => 'New York'
        ]);

        $result = $data->only(['name', 'email'])->toArray();
        Assert::same([
            'name' => 'John',
            'email' => 'john@example.com'
        ], $result);

        // Test with non-existent keys
        $result = $data->only(['name', 'nonexistent'])->toArray();
        Assert::same(['name' => 'John'], $result);
    }

    public function testPad(): void
    {
        $data = new Arrays(['a', 'b', 'c']);

        // Test padding to larger size
        $result = $data->pad(5, 'x')->toArray();
        Assert::same(['a', 'b', 'c', 'x', 'x'], $result);

        // Test padding to smaller size (no change)
        $data = new Arrays(['a', 'b', 'c']);
        $result = $data->pad(2, 'x')->toArray();
        Assert::same(['a', 'b', 'c'], $result);

        // Test with associative array
        $assoc = new Arrays(['first' => 'a', 'second' => 'b']);
        $result = $assoc->pad(4, 'x')->toArray();
        Assert::same(['first' => 'a', 'second' => 'b', 0 => 'x', 1 => 'x'], $result);
    }

    public function testPartition(): void
    {
        // Happy path: split into even and odd
        [$even, $odd] = (new Arrays([1, 2, 3, 4, 5, 6]))
            ->partition(fn ($n) => $n % 2 === 0)
            ->toArray();
        Assert::same([1 => 2, 3 => 4, 5 => 6], $even);
        Assert::same([0 => 1, 2 => 3, 4 => 5], $odd);

        // Associative array: keys are preserved in both groups
        [$active, $inactive] = (new Arrays([
            'user1' => ['active' => true],
            'user2' => ['active' => false],
            'user3' => ['active' => true],
        ]))
            ->partition(fn ($user) => $user['active'])
            ->toArray();
        Assert::same(['user1' => ['active' => true], 'user3' => ['active' => true]], $active);
        Assert::same(['user2' => ['active' => false]], $inactive);

        // Empty array: both groups are empty
        [$a, $b] = (new Arrays([]))->partition(fn ($v) => $v > 0)->toArray();
        Assert::same([], $a);
        Assert::same([], $b);

        // Returns self for chaining
        $arrays = new Arrays([1, 2, 3]);
        Assert::type(Arrays::class, $arrays->partition(fn ($v) => $v > 1));
    }

    public function testPrepend(): void
    {
        // Happy path: adds new keys at the beginning
        $result = (new Arrays(['name' => 'John']))
            ->prepend(['id' => 1, 'role' => 'admin'])
            ->toArray();
        Assert::same(['id' => 1, 'role' => 'admin', 'name' => 'John'], $result);

        // Existing key is not overwritten
        $result = (new Arrays(['name' => 'John', 'age' => null]))
            ->prepend(['age' => 30, 'role' => 'admin'])
            ->toArray();
        Assert::same(['role' => 'admin', 'name' => 'John', 'age' => null], $result);

        // Empty items: no change
        $result = (new Arrays(['a' => 1]))->prepend([])->toArray();
        Assert::same(['a' => 1], $result);

        // Returns self for chaining
        $arrays = new Arrays([]);
        Assert::type(Arrays::class, $arrays->prepend(['x' => 1]));
    }

    public function testPull(): void
    {
        // Happy path: last element removed
        $result = (new Arrays([1, 2, 3]))->pull()->toArray();
        Assert::same([1, 2], $result);

        // Associative array: last element removed
        $result = (new Arrays(['name' => 'John', 'age' => 30]))->pull()->toArray();
        Assert::same(['name' => 'John'], $result);

        // Empty array throws exception
        Assert::exception(function () {
            (new Arrays([]))->pull();
        }, \Phuture\Coherence\Exception\OutOfBoundsException::class);

        // Returns self for chaining
        $arrays = new Arrays([1, 2, 3]);
        Assert::type(Arrays::class, $arrays->pull());
    }

    public function testPush(): void
    {
        // Happy path: add single element
        $result = (new Arrays([1, 2]))->push(3)->toArray();
        Assert::same([1, 2, 3], $result);

        // Add multiple elements
        $result = (new Arrays(['apple']))->push('banana', 'cherry')->toArray();
        Assert::same(['apple', 'banana', 'cherry'], $result);

        // Push onto empty array
        $result = (new Arrays([]))->push(42)->toArray();
        Assert::same([42], $result);

        // Returns self for chaining
        $arrays = new Arrays([]);
        Assert::type(Arrays::class, $arrays->push(1));
    }

    public function testRemove(): void
    {
        // Happy path: simple key removal
        $result = (new Arrays(['name' => 'John', 'age' => 30]))
            ->remove('age')
            ->toArray();
        Assert::same(['name' => 'John'], $result);

        // Nested key removal via array path
        $result = (new Arrays(['user' => ['name' => 'John', 'age' => 30], 'debug' => true]))
            ->remove(['user', 'age'])
            ->toArray();
        Assert::same(['user' => ['name' => 'John'], 'debug' => true], $result);

        // Non-existent key: no change
        $result = (new Arrays(['a' => 1]))->remove('missing')->toArray();
        Assert::same(['a' => 1], $result);

        // Returns self for chaining
        $arrays = new Arrays(['a' => 1, 'b' => 2]);
        Assert::type(Arrays::class, $arrays->remove('a'));
    }

    public function testRename(): void
    {
        $data = new Arrays([
            'old_name' => 'John',
            'old_age' => 30,
            'email' => 'john@example.com'
        ]);

        // Test single key rename
        $result = $data->rename('old_name', 'name')->toArray();
        Assert::same([
            'name' => 'John',
            'old_age' => 30,
            'email' => 'john@example.com'
        ], $result);

        // Test nested key rename
        $data = new Arrays([
            'user' => [
                'old_name' => 'John',
                'old_age' => 30
            ]
        ]);
        $result = $data->rename(['user', 'old_name'], 'name')->toArray();
        Assert::same([
            'user' => [
                'name' => 'John',
                'old_age' => 30
            ]
        ], $result);
    }

    public function testReplace(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);

        // Test non-recursive replace
        $result = $base->replace(false, ['b' => 'blueberry', 'd' => 'date'])->toArray();
        Assert::same(['a' => 'apple', 'b' => 'blueberry', 'c' => 'cherry', 'd' => 'date'], $result);

        // Test recursive replace
        $nestedBase = new Arrays([
            'user' => ['name' => 'John', 'details' => ['age' => 30]],
            'settings' => ['theme' => 'light']
        ]);

        $nestedReplace = [
            'user' => ['name' => 'Jane'],
            'settings' => ['theme' => 'dark', 'language' => 'en']
        ];

        $result = $nestedBase->replace(true, $nestedReplace)->toArray();
        Assert::same([
            'user' => ['name' => 'Jane', 'details' => ['age' => 30]],
            'settings' => ['theme' => 'dark', 'language' => 'en']
        ], $result);
    }

    public function testReverse(): void
    {
        $data = new Arrays(['a', 'b', 'c', 'd']);

        $result = $data->reverse()->toArray();
        Assert::same(['d', 'c', 'b', 'a'], $result);

        $data = new Arrays(['a', 'b', 'c', 'd']);
        $result = $data->reverse(true)->toArray();
        Assert::same([3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a'], $result);

        $assoc = new Arrays(['first' => 'a', 'second' => 'b']);
        $result = $assoc->reverse(true)->toArray();
        Assert::same(['second' => 'b', 'first' => 'a'], $result);
    }

    public function testShift(): void
    {
        // Happy path: first element removed, numeric keys re-indexed
        $result = (new Arrays([1, 2, 3]))->shift()->toArray();
        Assert::same([2, 3], $result);

        // Associative array: first element removed, string keys preserved
        $result = (new Arrays(['name' => 'John', 'age' => 30]))->shift()->toArray();
        Assert::same(['age' => 30], $result);

        // Empty array throws exception
        Assert::exception(function () {
            (new Arrays([]))->shift();
        }, \Phuture\Coherence\Exception\OutOfBoundsException::class);

        // Returns self for chaining
        $arrays = new Arrays([1, 2, 3]);
        Assert::type(Arrays::class, $arrays->shift());
    }

    public function testShuffle(): void
    {
        // Happy path: same elements present in a (potentially) different order
        $original = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
        $result = (new Arrays($original))->shuffle()->toArray();
        Assert::count(count($original), $result);
        sort($result);
        Assert::same($original, $result);

        // Empty array: no error
        $result = (new Arrays([]))->shuffle()->toArray();
        Assert::same([], $result);

        // Returns self for chaining
        $arrays = new Arrays([1, 2, 3]);
        Assert::type(Arrays::class, $arrays->shuffle());
    }

    public function testSlice(): void
    {
        $data = new Arrays(['a', 'b', 'c', 'd', 'e', 'f']);

        // Test basic slice
        $result = $data->slice(2, 3)->toArray();
        Assert::same([0 => 'c', 1 => 'd', 2 => 'e'], $result);

        // Test without length
        $data = new Arrays(['a', 'b', 'c', 'd', 'e', 'f']);
        $result = $data->slice(3)->toArray();
        Assert::same([0 => 'd', 1 => 'e', 2 => 'f'], $result);

        // Test with preserve_keys
        $data = new Arrays(['a', 'b', 'c', 'd', 'e', 'f']);
        $result = $data->slice(1, 3, true)->toArray();
        Assert::same([1 => 'b', 2 => 'c', 3 => 'd'], $result);
    }

    public function testSort(): void
    {
        $data = new Arrays([3, 1, 4, 1, 5, 9, 2, 6]);

        // Test default sort
        $result = $data->sort()->toArray();
        Assert::same([1, 1, 2, 3, 4, 5, 6, 9], $result);

        // Test reverse sort
        $data = new Arrays([3, 1, 4, 1, 5, 9, 2, 6]);
        $result = $data->sort(true)->toArray();
        Assert::same([9, 6, 5, 4, 3, 2, 1, 1], $result);

        // Test custom callback
        $words = new Arrays(['banana', 'apple', 'cherry']);
        $result = $words->sort(false, fn ($a, $b) => strlen($b) - strlen($a))->toArray();
        Assert::same(['banana', 'cherry', 'apple'], $result);
    }

    public function testSortAssoc(): void
    {
        $data = new Arrays(['b' => 'banana', 'a' => 'apple', 'c' => 'cherry']);

        // Test default sort
        $result = $data->sortAssoc()->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'], $result);

        // Test reverse sort
        $result = $data->sortAssoc(true)->toArray();
        Assert::same(['c' => 'cherry', 'b' => 'banana', 'a' => 'apple'], $result);

        // Test custom callback
        $data = new Arrays(['short' => 'a', 'medium' => 'bb', 'long' => 'ccc']);
        $result = $data->sortAssoc(false, fn ($a, $b) => strlen($a) - strlen($b))->toArray();
        Assert::same(['short' => 'a', 'medium' => 'bb', 'long' => 'ccc'], $result);
    }

    public function testSortByCallableCriteria(): void
    {
        $data = new Arrays([
            ['name' => 'Charlie', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 35],
        ]);

        $result = $data->sortBy(fn ($item) => $item['age'])->toArray();

        Assert::same(25, $result[0]['age']);
        Assert::same(30, $result[1]['age']);
        Assert::same(35, $result[2]['age']);
    }

    public function testSortByChaining(): void
    {
        $data = new Arrays([
            ['name' => 'Charlie', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 35],
        ]);

        $result = $data->sortBy('name')->values()->toArray();

        Assert::count(3, $result);
        Assert::same('Alice', $result[0]['name']);
    }

    public function testSortByReverse(): void
    {
        $data = new Arrays([
            ['name' => 'Alice', 'score' => 80],
            ['name' => 'Bob', 'score' => 95],
            ['name' => 'Charlie', 'score' => 70],
        ]);

        $result = $data->sortBy('score', true)->toArray();

        Assert::same(95, $result[0]['score']);
        Assert::same(80, $result[1]['score']);
        Assert::same(70, $result[2]['score']);
    }

    public function testSortByStringCriteria(): void
    {
        $data = new Arrays([
            ['name' => 'Charlie', 'age' => 30],
            ['name' => 'Alice', 'age' => 25],
            ['name' => 'Bob', 'age' => 35],
        ]);

        $result = $data->sortBy('name')->toArray();

        Assert::same('Alice', $result[0]['name']);
        Assert::same('Bob', $result[1]['name']);
        Assert::same('Charlie', $result[2]['name']);
    }

    public function testSortKeys(): void
    {
        $data = new Arrays(['c' => 'cherry', 'a' => 'apple', 'b' => 'banana']);

        // Test default sort
        $result = $data->sortKeys()->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'], $result);

        // Test reverse sort
        $result = $data->sortKeys(true)->toArray();
        Assert::same(['c' => 'cherry', 'b' => 'banana', 'a' => 'apple'], $result);

        // Test custom callback (sort by key length)
        $data = new Arrays(['aaa' => 1, 'b' => 2, 'cc' => 3]);
        $result = $data->sortKeys(false, fn ($a, $b) => strlen($a) - strlen($b))->toArray();
        Assert::same(['b' => 2, 'cc' => 3, 'aaa' => 1], $result);
    }

    public function testSortNatural(): void
    {
        $data = new Arrays(['img12.png', 'img10.png', 'img2.png', 'img1.png']);

        // Test natural sort
        $result = $data->sortNatural()->toArray();
        Assert::same([3 => 'img1.png', 2 => 'img2.png', 1 => 'img10.png', 0 => 'img12.png'], $result);

        // Test case insensitive
        $mixedCase = new Arrays(['File2.txt', 'file10.txt', 'file1.txt']);
        $result = $mixedCase->sortNatural(true)->toArray();
        Assert::same([2 => 'file1.txt', 0 => 'File2.txt', 1 => 'file10.txt'], $result);
    }

    public function testSplice(): void
    {
        // Happy path: remove elements
        $result = (new Arrays(['a', 'b', 'c', 'd', 'e']))->splice(2, 2)->toArray();
        Assert::same(['a', 'b', 'e'], $result);

        // Remove and replace
        $result = (new Arrays(['a', 'b', 'c', 'd', 'e']))->splice(2, 2, ['X', 'Y'])->toArray();
        Assert::same(['a', 'b', 'X', 'Y', 'e'], $result);

        // Insert without removing (length = 0)
        $result = (new Arrays(['a', 'b', 'e']))->splice(2, 0, ['c', 'd'])->toArray();
        Assert::same(['a', 'b', 'c', 'd', 'e'], $result);

        // Remove from offset to end
        $result = (new Arrays(['a', 'b', 'c', 'd']))->splice(2)->toArray();
        Assert::same(['a', 'b'], $result);

        // Returns self for chaining
        $arrays = new Arrays([1, 2, 3, 4]);
        Assert::type(Arrays::class, $arrays->splice(0, 1));
    }

    public function testSplit(): void
    {
        $data = new Arrays(['a', 'b', 'c', 'd', 'e', 'f', 'g']);

        // Test basic split
        $result = $data->split(3)->toArray();
        Assert::same([
            ['a', 'b', 'c'],
            ['d', 'e', 'f'],
            ['g']
        ], $result);

        // Test with preserveKeys
        $assoc = new Arrays(['first' => 'a', 'second' => 'b', 'third' => 'c', 'fourth' => 'd']);
        $result = $assoc->split(2, true)->toArray();
        Assert::same([
            ['first' => 'a', 'second' => 'b'],
            ['third' => 'c', 'fourth' => 'd']
        ], $result);
    }

    public function testToArray(): void
    {
        $arrays = new Arrays(['key' => 'value']);
        $result = $arrays->toArray();
        Assert::type('array', $result);
        Assert::same(['key' => 'value'], $result);
    }

    public function testToJson(): void
    {
        // Simple associative array
        $arrays = new Arrays(['name' => 'John', 'age' => 30]);
        Assert::same('{"name":"John","age":30}', $arrays->toJson());

        // Indexed array
        $arrays = new Arrays([1, 2, 3]);
        Assert::same('[1,2,3]', $arrays->toJson());

        // Empty array
        $arrays = new Arrays([]);
        Assert::same('[]', $arrays->toJson());

        // Nested array
        $arrays = new Arrays(['user' => ['name' => 'Jane', 'scores' => [10, 20]]]);
        Assert::same('{"user":{"name":"Jane","scores":[10,20]}}', $arrays->toJson());

        // Array with boolean and null values
        $arrays = new Arrays(['active' => true, 'deleted' => false, 'ref' => null]);
        Assert::same('{"active":true,"deleted":false,"ref":null}', $arrays->toJson());
    }

    public function testToObject(): void
    {
        // Simple associative array becomes stdClass
        $arrays = new Arrays(['name' => 'John', 'age' => 30]);
        $result = $arrays->toObject();
        Assert::type(stdClass::class, $result);
        Assert::same('John', $result->name);
        Assert::same(30, $result->age);

        // Empty array becomes empty stdClass
        $arrays = new Arrays([]);
        $result = $arrays->toObject();
        Assert::type(stdClass::class, $result);
        Assert::same([], (array) $result);

        // Nested associative array — nested arrays are recursively converted to stdClass
        $arrays = new Arrays(['user' => ['name' => 'Jane', 'age' => 25]]);
        $result = $arrays->toObject();
        Assert::type(stdClass::class, $result);
        Assert::type(stdClass::class, $result->user);
        Assert::same('Jane', $result->user->name);
        Assert::same(25, $result->user->age);
    }

    public function testUnique(): void
    {
        $data = new Arrays([1, 2, 2, 3, 4, 4, 4, 5]);

        // Test default (SortComparison::String)
        $result = $data->unique()->toArray();
        Assert::same([0 => 1, 1 => 2, 3 => 3, 4 => 4, 7 => 5], $result);

        // Test with numeric comparison
        $data = new Arrays([1, 1, 2, 2, 3, 3]);
        $result = $data->unique(SortComparison::Numeric)->toArray();
        Assert::same([0 => 1, 2 => 2, 4 => 3], $result);
    }

    public function testUnlessCallbackReceivesArraysInstance(): void
    {
        $received = new Arrays([]);
        (new Arrays([1, 2, 3]))
            ->unless(false, function ($array) use (&$received) {
                $received = $array;
            });
        Assert::type(Arrays::class, $received);
        Assert::same([1, 2, 3], $received->toArray());
    }

    public function testUnlessChainingContinuesAfterFalse(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->unless(false, fn ($array) => $array->filter(fn ($n) => $n > 1))
            ->map(fn ($n) => $n * 10)
            ->toArray();
        Assert::same([1 => 20, 2 => 30], $result);
    }

    public function testUnlessChainingContinuesAfterTrue(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->unless(true, fn ($array) => $array->filter(fn ($n) => $n > 1))
            ->map(fn ($n) => $n * 10)
            ->toArray();
        Assert::same([10, 20, 30], $result);
    }

    public function testUnlessMultipleConditionsInChain(): void
    {
        $skipFilter = true;
        $skipSort = false;

        $result = (new Arrays([3, 1, 2]))
            ->unless($skipFilter, fn ($array) => $array->filter(fn ($n) => $n > 1))
            ->unless($skipSort, fn ($array) => $array->sort())
            ->toArray();
        Assert::same([1, 2, 3], $result);
    }

    public function testUnlessReturnsSelf(): void
    {
        $arrays = new Arrays([1, 2, 3]);
        $result = $arrays->unless(false, fn ($array) => $array->filter(fn ($n) => $n > 0));
        Assert::type(Arrays::class, $result);
    }

    public function testUnlessWithBooleanFalse(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->unless(false, fn ($array) => $array->filter(fn ($n) => $n > 2))
            ->toArray();
        Assert::same([2 => 3], $result);
    }

    public function testUnlessWithBooleanTrue(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->unless(true, fn ($array) => $array->filter(fn ($n) => $n > 2))
            ->toArray();
        Assert::same([1, 2, 3], $result);
    }

    public function testUnlessWithCallableConditionFalse(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->unless(fn ($data) => count($data) >= 5, fn ($array) => $array->pad(5, 0))
            ->toArray();
        Assert::same([1, 2, 3, 0, 0], $result);
    }

    public function testUnlessWithCallableConditionTrue(): void
    {
        $result = (new Arrays([1, 2, 3, 4, 5]))
            ->unless(fn ($data) => count($data) >= 5, fn ($array) => $array->pad(5, 0))
            ->toArray();
        Assert::same([1, 2, 3, 4, 5], $result);
    }

    public function testValues(): void
    {
        $data = new Arrays(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']);
        $result = $data->values()->toArray();
        Assert::same([0 => 'apple', 1 => 'banana', 2 => 'cherry'], $result);

        // Test with mixed keys
        $mixed = new Arrays([0 => 'zero', 'key' => 'value', 5 => 'five']);
        $result = $mixed->values()->toArray();
        Assert::same([0 => 'zero', 1 => 'value', 2 => 'five'], $result);
    }

    public function testWhenAndUnlessCombinedInChain(): void
    {
        // when(true) applies filter: keep only even numbers -> [2, 4]
        // unless(true) skips its callback (condition is true, so "unless" does nothing)
        // Final result: [2, 4]
        $result = (new Arrays([1, 2, 3, 4, 5]))
            ->when(true, fn ($array) => $array->filter(fn ($n) => $n % 2 === 0))
            ->unless(true, fn ($array) => $array->filter(fn ($n) => $n > 3))
            ->values()
            ->toArray();

        Assert::same([2, 4], $result);

        // when(true) applies filter: keep only even numbers -> [2, 4]
        // unless(false) fires its callback (condition is false, so "unless" acts): keep > 3 -> [4]
        // Final result: [4]
        $result = (new Arrays([1, 2, 3, 4, 5]))
            ->when(true, fn ($array) => $array->filter(fn ($n) => $n % 2 === 0))
            ->unless(false, fn ($array) => $array->filter(fn ($n) => $n > 3))
            ->values()
            ->toArray();

        Assert::same([4], $result);
    }

    public function testWhenCallbackReceivesArraysInstance(): void
    {
        $received = new Arrays([]);
        (new Arrays([1, 2, 3]))
            ->when(true, function ($array) use (&$received) {
                $received = $array;
            });
        Assert::type(Arrays::class, $received);
        Assert::same([1, 2, 3], $received->toArray());
    }

    public function testWhenChainingContinuesAfterFalse(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->when(false, fn ($array) => $array->filter(fn ($n) => $n > 1))
            ->map(fn ($n) => $n * 10)
            ->toArray();
        Assert::same([10, 20, 30], $result);
    }

    public function testWhenChainingContinuesAfterTrue(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->when(true, fn ($array) => $array->filter(fn ($n) => $n > 1))
            ->map(fn ($n) => $n * 10)
            ->toArray();
        Assert::same([1 => 20, 2 => 30], $result);
    }

    public function testWhenMultipleConditionsInChain(): void
    {
        $isProduction = false;
        $applyTax = true;

        $result = (new Arrays([100, 200, 300]))
            ->when($isProduction, fn ($array) => $array->map(fn ($n) => (int) ($n * 1.2)))
            ->when($applyTax, fn ($array) => $array->map(fn ($n) => (int) ($n * 1.1)))
            ->toArray();
        Assert::same([110, 220, 330], $result);
    }

    public function testWhenReturnsSelf(): void
    {
        $arrays = new Arrays([1, 2, 3]);
        $result = $arrays->when(true, fn ($array) => $array->filter(fn ($n) => $n > 0));
        Assert::type(Arrays::class, $result);
    }

    public function testWhenWithBooleanFalse(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->when(false, fn ($array) => $array->filter(fn ($n) => $n > 2))
            ->toArray();
        Assert::same([1, 2, 3], $result);
    }

    public function testWhenWithBooleanTrue(): void
    {
        $result = (new Arrays([1, 2, 3, 4, 5]))
            ->when(true, fn ($array) => $array->filter(fn ($n) => $n > 2))
            ->toArray();
        Assert::same([2 => 3, 3 => 4, 4 => 5], $result);
    }

    public function testWhenWithCallableConditionFalse(): void
    {
        $result = (new Arrays([1, 2, 3, 4, 5]))
            ->when(fn ($data) => count($data) < 5, fn ($array) => $array->pad(5, 0))
            ->toArray();
        Assert::same([1, 2, 3, 4, 5], $result);
    }

    public function testWhenWithCallableConditionTrue(): void
    {
        $result = (new Arrays([1, 2, 3]))
            ->when(fn ($data) => count($data) < 5, fn ($array) => $array->pad(5, 0))
            ->toArray();
        Assert::same([1, 2, 3, 0, 0], $result);
    }

    public function testWhere(): void
    {
        $users = new Arrays([
            ['name' => 'John', 'age' => 25, 'active' => true],
            ['name' => 'Jane', 'age' => 17, 'active' => true],
            ['name' => 'Bob', 'age' => 30, 'active' => false]
        ]);
        $adults = $users->where(fn ($user) => $user['age'] >= 18)->toArray();

        Assert::count(2, $adults);
        Assert::same('John', $adults[0]['name']);
        Assert::same('Bob', $adults[2]['name']);
    }

    public function testWhereChaining(): void
    {
        $users = new Arrays([
            ['name' => 'John', 'age' => 25, 'active' => true],
            ['name' => 'Jane', 'age' => 17, 'active' => true],
            ['name' => 'Bob', 'age' => 30, 'active' => false],
            ['name' => 'Alice', 'age' => 28, 'active' => true]
        ]);

        // Chain: filter active, then filter adults, then get names
        $result = $users->where(fn ($user) => $user['active'])
            ->where(fn ($user) => $user['age'] >= 18)
            ->map(fn ($user) => $user['name'])
            ->values()
            ->toArray();

        Assert::same(['John', 'Alice'], $result);
    }

    public function testWhereIn(): void
    {
        $users = new Arrays([
            ['id' => 1, 'name' => 'John', 'role' => 'admin'],
            ['id' => 2, 'name' => 'Jane', 'role' => 'user'],
            ['id' => 3, 'name' => 'Bob', 'role' => 'admin'],
            ['id' => 4, 'name' => 'Alice', 'role' => 'moderator']
        ]);
        $result = $users->whereIn('role', ['admin', 'moderator'])->toArray();

        Assert::count(3, $result);
        Assert::same('John', $result[0]['name']);
        Assert::same('Bob', $result[2]['name']);
        Assert::same('Alice', $result[3]['name']);
    }

    public function testWhereInChaining(): void
    {
        $users = new Arrays([
            ['id' => 1, 'name' => 'John', 'role' => 'admin', 'active' => true],
            ['id' => 2, 'name' => 'Jane', 'role' => 'user', 'active' => true],
            ['id' => 3, 'name' => 'Bob', 'role' => 'admin', 'active' => false],
            ['id' => 4, 'name' => 'Alice', 'role' => 'moderator', 'active' => true]
        ]);

        // Chain: filter admins/moderators, then filter active, then get names
        $result = $users->whereIn('role', ['admin', 'moderator'])
            ->where(fn ($user) => $user['active'])
            ->map(fn ($user) => $user['name'])
            ->values()
            ->toArray();

        Assert::same(['John', 'Alice'], $result);
    }

    public function testWhereInEmptyValues(): void
    {
        $items = new Arrays([
            ['type' => 'A'],
            ['type' => 'B']
        ]);
        $result = $items->whereIn('type', [])->toArray();
        Assert::same([], $result);
    }

    public function testWrap(): void
    {
        $data = new Arrays(['apple', 'banana', 'cherry']);

        // Test with prefix and suffix
        $result = $data->wrap('<', '>')->toArray();
        Assert::same(['<apple>', '<banana>', '<cherry>'], $result);

        // Test with only prefix
        $data = new Arrays(['apple', 'banana', 'cherry']);
        $result = $data->wrap('prefix_')->toArray();
        Assert::same(['prefix_apple', 'prefix_banana', 'prefix_cherry'], $result);

        // Test with only suffix
        $data = new Arrays(['apple', 'banana', 'cherry']);
        $result = $data->wrap('', '_suffix')->toArray();
        Assert::same(['apple_suffix', 'banana_suffix', 'cherry_suffix'], $result);

        // Test with no prefix or suffix (no change)
        $data = new Arrays(['apple', 'banana', 'cherry']);
        $result = $data->wrap()->toArray();
        Assert::same(['apple', 'banana', 'cherry'], $result);
    }

    public function testZip(): void
    {
        // Happy path: pair two equal-length arrays
        $result = (new Arrays([1, 2, 3]))
            ->zip(['a', 'b', 'c'])
            ->toArray();
        Assert::same([[1, 'a'], [2, 'b'], [3, 'c']], $result);

        // Three arrays
        $result = (new Arrays([1, 2]))
            ->zip(['a', 'b'], ['x', 'y'])
            ->toArray();
        Assert::same([[1, 'a', 'x'], [2, 'b', 'y']], $result);

        // Different lengths: shortest determines output count
        $result = (new Arrays([1, 2, 3, 4]))
            ->zip(['a', 'b'])
            ->toArray();
        Assert::same([[1, 'a'], [2, 'b']], $result);

        // No additional arrays throws exception
        Assert::exception(function () {
            (new Arrays([1, 2]))->zip();
        }, \Phuture\Coherence\Exception\InvalidArgumentException::class);

        // Returns self for chaining
        $arrays = new Arrays([1, 2]);
        Assert::type(Arrays::class, $arrays->zip(['a', 'b']));
    }
}

(new ArraysTest())->run();
