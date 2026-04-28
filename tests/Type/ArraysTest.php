<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use stdClass;
use ArrayIterator;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Type\Arrays;
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/../bootstrap.php';

class ArraysTest extends TestCase
{
    public function testArrayAccess(): void
    {
        $arrays = new Arrays(['name' => 'John', 'age' => 30]);

        // Test offsetExists
        Assert::true($arrays->offsetExists('name'));
        Assert::true($arrays->offsetExists('age'));
        Assert::false($arrays->offsetExists('nonexistent'));

        // Test offsetGet
        Assert::same('John', $arrays->offsetGet('name'));
        Assert::same(30, $arrays->offsetGet('age'));
        Assert::null($arrays->offsetGet('nonexistent'));

        // Test offsetSet
        $arrays->offsetSet('city', 'New York');
        Assert::same('New York', $arrays->offsetGet('city'));

        // Test offsetSet with null (append)
        $arrays->offsetSet(null, 'appended');
        Assert::same('appended', $arrays->offsetGet(0));

        // Test offsetUnset
        $arrays->offsetUnset('age');
        Assert::false($arrays->offsetExists('age'));
    }

    public function testArrayAccessSyntax(): void
    {
        $arrays = new Arrays(['a', 'b', 'c']);

        // Test array-like access
        Assert::same('a', $arrays[0]);
        Assert::same('b', $arrays[1]);
        Assert::true(isset($arrays[2]));
        Assert::false(isset($arrays[10]));

        // Test array-like modification
        $arrays[] = 'd';
        Assert::same('d', $arrays[3]);

        $arrays[10] = 'z';
        Assert::same('z', $arrays[10]);

        unset($arrays[1]);
        Assert::false(isset($arrays[1]));
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

    public function testAssociateThrowsOnNullKey(): void
    {
        $arrays = new Arrays([['id' => null, 'name' => 'John']]);
        Assert::exception(
            fn () => $arrays->associate('id'),
            InvalidArgumentException::class
        );
    }

    public function testChangeKeyCase(): void
    {
        $data = new Arrays(['NAME' => 'John', 'AGE' => 30, 'Email' => 'john@example.com']);

        // Test to lowercase
        $result = $data->changeKeyCase(CASE_LOWER)->toArray();
        Assert::same(['name' => 'John', 'age' => 30, 'email' => 'john@example.com'], $result);

        // Test to uppercase
        $data = new Arrays(['name' => 'John', 'age' => 30]);
        $result = $data->changeKeyCase(CASE_UPPER)->toArray();
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
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30
                ]
            ],
            'settings' => [
                'theme' => 'dark'
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

    public function testJoin(): void
    {
        $base = new Arrays([1, 2, 3]);
        $result = $base->join([4, 5, 6])->toArray();
        Assert::same([1, 2, 3, 4, 5, 6], $result);

        // Test with associative arrays
        $assoc1 = new Arrays(['a' => 'apple']);
        $assoc2 = ['b' => 'banana'];
        $result = $assoc1->join($assoc2)->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana'], $result);
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

    public function testMerge(): void
    {
        $base = new Arrays(['a' => 'apple', 'b' => 'banana']);
        $result = $base->merge(['c' => 'cherry'])->toArray();
        Assert::same(['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'], $result);

        // Test with overlapping keys (array_merge_recursive behavior)
        $overlap = new Arrays(['a' => 'apple']);
        $result = $overlap->merge(['a' => 'avocado'])->toArray();
        Assert::same(['a' => ['apple', 'avocado']], $result);

        // Test with numeric keys (reindexing)
        $numeric = new Arrays([1, 2]);
        $result = $numeric->merge([3, 4])->toArray();
        Assert::same([1, 2, 3, 4], $result);
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

        // Test with preserveKeys (default)
        $result = $data->reverse()->toArray();
        Assert::same([3 => 'd', 2 => 'c', 1 => 'b', 0 => 'a'], $result);

        // Test without preserveKeys
        $data = new Arrays(['a', 'b', 'c', 'd']);
        $result = $data->reverse(false)->toArray();
        Assert::same(['d', 'c', 'b', 'a'], $result);

        // Test with associative array (keys always preserved)
        $assoc = new Arrays(['first' => 'a', 'second' => 'b']);
        $result = $assoc->reverse()->toArray();
        Assert::same(['second' => 'b', 'first' => 'a'], $result);
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

    public function testUnique(): void
    {
        $data = new Arrays([1, 2, 2, 3, 4, 4, 4, 5]);

        // Test default (SORT_STRING)
        $result = $data->unique()->toArray();
        Assert::same([0 => 1, 1 => 2, 3 => 3, 4 => 4, 7 => 5], $result);

        // Test with numeric sort
        $data = new Arrays([1, 1, 2, 2, 3, 3]);
        $result = $data->unique(SORT_NUMERIC)->toArray();
        Assert::same([0 => 1, 2 => 2, 4 => 3], $result);
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
}

(new ArraysTest())->run();
