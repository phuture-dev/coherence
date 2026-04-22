<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use WeakMap;
use stdClass;
use TypeError;
use ArrayAccess;
use Traversable;
use JsonSerializable;
use Phuture\Coherence\Enum\ArrayComparator;
use Phuture\Coherence\Exception\LogicException;
use Phuture\Coherence\Interface\{Arrayable, Jsonable};
use Phuture\Coherence\Support\{ArgumentExtractor, StaticClass};
use Phuture\Coherence\Exception\{InvalidArgumentException, InvalidDataTypeException, OutOfBoundsException};

/**
 * Comprehensive array manipulation utility class with advanced data processing capabilities.
 *
 * This utility class offers a complete toolkit for array manipulation, including a fluent interface,
 * recursive operations with built-in safety limits, and complex data transformations using dot notation,
 * associative arrays, and sophisticated sorting with custom comparators.
 *
 * Key features:
 *
 * - **Fluent Interface**: Chainable methods for elegant array manipulation and transformation
 * - **Recursive Operations**: Deep array processing with built-in recursion limit protection
 * - **Advanced Sorting**: Multi-dimensional sorting with custom comparators and sort flags
 * - **Set Operations**: Array difference, intersection, and comparison with key/value modes
 * - **Data Transformation**: Normalization, flattening, association, and dot notation utilities
 * - **Safe Navigation**: Null-safe array access with configurable default values
 * - **Filtering & Querying**: Extensive filtering capabilities including regex-based grep
 * - **Memory Optimization**: Reference-based operations for efficient memory usage
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Arrays extends StaticClass
{
    use ArgumentExtractor;

    /**
     * Maximum number of elements allowed in cross join results to prevent memory exhaustion.
     */
    public const CROSS_JOIN_LIMIT = 1000000;

    /**
     * Maximum recursion depth for nested array operations to prevent infinite recursion.
     */
    public const RECURSION_LIMIT = 1000;

    /**
     * Retrieves a reference to an array element by key.
     *
     * This method returns a reference to an array element, allowing you to modify it directly.
     * If the element doesn't exist, it will be created with a null value. This is useful for
     * dynamically building or modifying array structures.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $config = [
     *     'database' => [
     *         'host' => 'localhost'
     *     ]
     * ];
     *
     * // Get reference to existing value
     * $hostRef = &Arrays::getReference($config, ['database', 'host']);
     * $hostRef = '127.0.0.1';
     * // $config['database']['host'] is now '127.0.0.1'
     *
     * // Get reference to non-existent value (creates it)
     * $portRef = &Arrays::getReference($config, ['database', 'port']);
     * $portRef = 3306;
     * // $config['database']['port'] is now 3306
     *
     * // Simple key reference
     * $debugRef = &Arrays::getReference($config, 'debug');
     * $debugRef = true;
     * // $config['debug'] is now true
     * ```
     *
     * @param array $array The array to retrieve the reference from (passed by reference)
     * @param string|int|array $key The key to access (string/int for direct access, array for a nested path).
     * @return mixed Returns a reference to the array element
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException If the traversed item is not an array
     * @see \Phuture\Coherence\Arrays::get()
     */
    public static function &getReference(array &$array, string|int|array $key): mixed
    {
        $keys = is_array($key) ? $key : [$key];
        $current = &$array;

        foreach ($keys as $i => $k) {
            // Validate key type - PHP arrays only accept string or int keys
            if (!is_string($k) && !is_int($k)) {
                throw new InvalidArgumentException(
                    'Invalid Argument: Array keys must be strings or integers, ' . get_debug_type($k) . ' given'
                );
            }

            if (!array_key_exists($k, $current)) {
                // For intermediate keys, create an array; for final key, create null
                $current[$k] = ($i < count($keys) - 1) ? [] : null;
            }

            if ($i < count($keys) - 1) {
                if (!is_array($current[$k])) {
                    throw new InvalidArgumentException(
                        "Invalid Argument: The traversed item is not an array"
                    );
                }
                $current = &$current[$k];
            } else {
                $current = &$current[$k];
            }
        }

        return $current;
    }

    /**
     * Checks if a value can be accessed like an array.
     *
     * This method determines if a given value supports array-style access using square brackets.
     * Returns true for arrays and objects implementing ArrayAccess or Arrayable.
     *
     * This is useful when you need to verify that a value can be safely accessed with bracket
     * notation before attempting to read or write values using keys.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * Arrays::accessible(['a' => 1, 'b' => 2]);
     * // Returns: true
     *
     * Arrays::accessible('text string');
     * // Returns: false
     *
     * Arrays::accessible(new stdClass());
     * // Returns: false
     *
     * Arrays::accessible(new ArrayObject());
     * // Returns: true
     * ```
     *
     * @param mixed $value The value to check for array accessibility.
     * @return bool Returns true if the value can be accessed as an array, false otherwise
     */
    public static function accessible(mixed $value): bool
    {
        return is_array($value)
            || $value instanceof ArrayAccess
            || $value instanceof Arrayable;
    }

    /**
     * Appends key-value pairs to an array if the keys don't exist.
     *
     * This method appends new key-value pairs to the end of an array. If a key already exists,
     * it remains unchanged. The array is modified by reference.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'Desk'];
     * Arrays::append($array, ['price' => 100]);
     * // Result: ['name' => 'Desk', 'price' => 100]
     *
     * $array = ['name' => 'Desk', 'price' => null];
     * Arrays::append($array, ['price' => 100, 'color' => 'brown']);
     * // Result: ['name' => 'Desk', 'price' => null, 'color' => 'brown']
     *
     * $array = [];
     * Arrays::append($array, ['user' => 'demo', 'email' => 'example@example.com']);
     * // Result: ['user' => 'demo', 'email' => 'example@example.com']
     * ```
     *
     * @param array $array The array to add key-value pairs to (passed by reference)
     * @param array $items Associative array of key-value pairs to append.
     * @see \Phuture\Coherence\Arrays::prepend()
     */
    public static function append(array &$array, array $items): void
    {
        foreach ($items as $k => $v) {
            if (!array_key_exists($k, $array)) {
                $array[$k] = $v;
            }
        }
    }

    /**
     * Transforms an array into an associative array according to a specified key.
     *
     * This method reorganizes a flat array (typically from a database result) into an
     * associative structure. You can specify which field to use as the key, and optionally
     * which field to use as the value. If no value field is specified, the entire item is used.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     ['id' => 1, 'name' => 'John', 'role' => 'admin'],
     *     ['id' => 2, 'name' => 'Mary', 'role' => 'user'],
     * ];
     *
     * // Simple key indexing (returns full items)
     * $result = Arrays::associate($users, 'name');
     * // Returns: ['John' => ['id' => 1, 'name' => 'John', 'role' => 'admin'], 'Mary' => [...]]
     *
     * // Key-value mapping
     * $result = Arrays::associate($users, 'name', 'role');
     * // Returns: ['John' => 'admin', 'Mary' => 'user']
     *
     * // Use id as key
     * $result = Arrays::associate($users, 'id');
     * // Returns: [1 => ['id' => 1, ...], 2 => ['id' => 2, ...]]
     * ```
     *
     * @param array $array The array to transform.
     * @param string|int $key The field to use as the associative array key.
     * @param string|int|null $value Optional field to use as the value. If null, uses the entire item.
     * @return array Returns an associative array indexed by the specified key
     */
    public static function associate(array $array, string|int $key, string|int|null $value = null): array
    {
        if (empty($array)) {
            return [];
        }

        $result = [];
        foreach ($array as $item) {
            // Support both array and object access
            $keyValue = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);

            if ($keyValue === null) {
                continue;
            }

            // If no value field specified, use the entire item
            if ($value === null) {
                $result[$keyValue] = $item;
            } else {
                $result[$keyValue] = is_array($item) ? ($item[$value] ?? null) : ($item->{$value} ?? null);
            }
        }

        return $result;
    }

    /**
     * Calculates the average (arithmetic mean) of values in an array.
     *
     * This method adds up all the numbers in an array and divides by the count
     * of elements to find the middle value. Think of it like finding the "typical"
     * value in a set of numbers.
     *
     * If the array is empty, this method returns null. Non-numeric values are
     * treated as zero in the calculation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [1, 2, 3, 4, 5];
     * $avg = Arrays::average($numbers);
     *
     * // Returns: 3.0
     *
     * // With decimal values
     * $prices = [10.5, 20.0, 30.5];
     * $avg = Arrays::average($prices);
     *
     * // Returns: 20.333333333333332
     *
     * // Empty array returns null
     * $avg = Arrays::average([]);
     *
     * // Returns: null
     * ```
     *
     * @param array $array The array containing numeric values
     * @return float|null The average value, or null if the array is empty
     * @see \Phuture\Coherence\Arrays::sum()
     * @see \Phuture\Coherence\Arrays::median()
     */
    public static function average(array $array): ?float
    {
        $count = count($array);

        if ($count === 0) {
            return null;
        }

        return self::sum($array) / $count;
    }

    /**
     * Changes the case of all keys in an array.
     *
     * This method converts all string keys in an array to either lowercase or uppercase.
     * Numeric keys remain unchanged. This is useful when you need to normalize array
     * keys for case-insensitive comparisons or standardize data from external sources.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['Name' => 'John', 'EMAIL' => 'john@example.com', 'Age' => 30];
     *
     * // Convert to lowercase (default)
     * $lower = Arrays::changeKeyCase($array);
     * // Returns: ['name' => 'John', 'email' => 'john@example.com', 'age' => 30]
     *
     * // Convert to uppercase
     * $upper = Arrays::changeKeyCase($array, CASE_UPPER);
     * // Returns: ['NAME' => 'John', 'EMAIL' => 'john@example.com', 'AGE' => 30]
     * ```
     *
     * @param array $array The array whose keys to change case.
     * @param int $case Either CASE_LOWER (default) or CASE_UPPER.
     * @return array Returns a new array with case-changed keys
     */
    public static function changeKeyCase(array $array, int $case = CASE_LOWER): array
    {
        return array_change_key_case($array, $case);
    }

    /**
     * Collapses one level of a multidimensional array.
     *
     * This method takes an array containing other arrays and merges them into one
     * single array by flattening only one level of nesting. It's useful when you have
     * multiple arrays that you want to combine into a single list while preserving
     * any deeper nested array structures.
     *
     * Unlike flatten() which recursively flattens all nested levels, collapse() only merges
     * one level of nesting, preserving any deeper nested structures.
     *
     * Later values overwrite earlier ones for duplicate keys.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $arrays = [[1, 2], [3, 4], [5, 6]];
     * $result = Arrays::collapse($arrays);
     * // Returns: [1, 2, 3, 4, 5, 6]
     *
     * // With associative arrays
     * $arrays = [['a' => 1], ['b' => 2], ['c' => 3]];
     * $result = Arrays::collapse($arrays);
     * // Returns: ['a' => 1, 'b' => 2, 'c' => 3]
     * ```
     *
     * @param array $array An array containing other arrays to merge.
     * @return array Returns a single flattened array with all values from the nested arrays
     * @see \Phuture\Coherence\Arrays::flatten()
     */
    public static function collapse(array $array): array
    {
        return array_merge([], ...$array);
    }

    /**
     * Extracts values from a single column in a multidimensional array.
     *
     * This method pulls out values from a specific field across all rows in an array,
     * similar to selecting a column from a spreadsheet. Useful when working with
     * database results or arrays of objects.
     *
     * You can extract values from nested paths using an array of keys. For example,
     * to get email addresses from a nested user profile structure.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     ['id' => 1, 'name' => 'John'],
     *     ['id' => 2, 'name' => 'Jane'],
     *     ['id' => 3, 'name' => 'Bob']
     * ];
     *
     * $names = Arrays::column($users, 'name');
     * // Returns: ['John', 'Jane', 'Bob']
     *
     * // Index by another column
     * $indexed = Arrays::column($users, 'name', 'id');
     * // Returns: [1 => 'John', 2 => 'Jane', 3 => 'Bob']
     *
     * // Extract from nested path using array notation
     * $users = [
     *     ['id' => 1, 'profile' => ['email' => 'john@example.com']],
     *     ['id' => 2, 'profile' => ['email' => 'jane@example.com']]
     * ];
     * $emails = Arrays::column($users, ['profile', 'email']);
     * // Returns: ['john@example.com', 'jane@example.com']
     *
     * // Nested path with index
     * $indexed = Arrays::column($users, ['profile', 'email'], 'id');
     * // Returns: [1 => 'john@example.com', 2 => 'jane@example.com']
     * ```
     *
     * @param array $array The multidimensional array to extract from.
     * @param int|string|array|null $column The column name, index, or nested path array to extract.
     * @param int|string|array|null $index Optional column, index, or nested path array to use as keys (default: null).
     * @return array Returns an array of values from the specified column
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException If the column path is not a valid list of strings
     * @throws \Phuture\Coherence\Exception\InvalidDataTypeException If the array contains invalid data types
     */
    public static function column(
        array $array,
        int|string|array|null $column,
        int|string|array|null $index = null
    ): array {
        // Handle array-based column path (nested path notation)
        if (is_array($column)) {
            // Validate that column path is a list of strings
            if (!self::isList($column) || array_filter($column, 'is_string') !== $column) {
                throw new InvalidArgumentException(
                    'Invalid Argument: Column path must be a list of strings'
                );
            }

            $result = [];
            foreach ($array as $item) {
                // Convert objects to arrays (handles nested properties)
                if (is_object($item)) {
                    $item = json_decode(json_encode($item), true, self::RECURSION_LIMIT);
                }

                if (!is_array($item)) {
                    continue;
                }

                $value = self::get($item, $column, null);

                if ($index === null) {
                    $result[] = $value;
                } else {
                    $key = self::get($item, (array) $index, null);
                    if ($key !== null) {
                        $result[$key] = $value;
                    }
                }
            }

            return $result;
        }

        // Use native array_column for simple string/int column (backward compatible)
        try {
            return array_column($array, $column, $index);
        } catch (TypeError $e) {
            throw new InvalidDataTypeException(
                "Invalid Data Type: {$e->getMessage()}"
            );
        }
    }

    /**
     * Creates an array by pairing keys with values from two separate arrays.
     *
     * This method takes one array of keys and another array of values, and combines them
     * into a single associative array where the first array provides the keys and the second
     * provides the values. Both arrays must have the same number of elements.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $keys = ['name', 'email', 'age'];
     * $values = ['John', 'john@example.com', 30];
     *
     * $result = Arrays::combine($keys, $values);
     * // Returns: ['name' => 'John', 'email' => 'john@example.com', 'age' => 30]
     *
     * // Creating a lookup table
     * $ids = [1, 2, 3];
     * $names = ['Alice', 'Bob', 'Charlie'];
     * $lookup = Arrays::combine($ids, $names);
     * // Returns: [1 => 'Alice', 2 => 'Bob', 3 => 'Charlie']
     * ```
     *
     * @param array $keys Array of keys to use
     * @param array $values Array of values to use
     * @return array Returns an associative array combining the keys and values
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When arrays have different lengths
     * @throws \Phuture\Coherence\Exception\InvalidDataTypeException When keys contain non-string or non-integer values
     */
    public static function combine(array $keys, array $values): array
    {
        if (count($keys) !== count($values)) {
            throw new InvalidArgumentException(
                "Invalid Argument: Both arrays must have the same number of elements"
            );
        }

        foreach ($keys as $value) {
            if (!is_int($value) && !is_string($value)) {
                throw new InvalidDataTypeException(
                    "Invalid Argument: Only arrays with string and integer values can be used as keys"
                );
            }
        }

        return array_combine($keys, $values);
    }

    /**
     * Checks if a value exists in an array.
     *
     * This method searches through an array to see if a specific value exists anywhere
     * in it. By default, it uses strict comparison (===) which checks both value and
     * type. You can disable strict mode to use loose comparison (==) which only checks
     * the value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $colors = ['red', 'blue', 'green'];
     * $hasBlue = Arrays::contains($colors, 'blue');
     * // Returns: true
     * ```
     *
     * @param array $array The array to search in.
     * @param mixed $value The value to search for.
     * @return bool Returns true if the value exists in the array, false otherwise
     * @see \Phuture\Coherence\Arrays::exists()
     * @see \Phuture\Coherence\Arrays::search()
     */
    public static function contains(array $array, mixed $value): bool
    {
        return in_array($value, $array, true);
    }

    /**
     * Counts how many times each unique value appears in an array.
     *
     * This method goes through an array and creates a report showing how many times
     * each unique value occurs. The result is an associative array where keys are
     * the values from the original array, and values are the counts.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $votes = ['apple', 'banana', 'apple', 'orange', 'banana', 'apple'];
     * $tally = Arrays::count($votes);
     * // Returns: ['apple' => 3, 'banana' => 2, 'orange' => 1]
     *
     * $numbers = [1, 2, 2, 3, 3, 3];
     * $frequency = Arrays::count($numbers);
     * // Returns: [1 => 1, 2 => 2, 3 => 3]
     * ```
     *
     * @param array $array The array whose values to count.
     * @return array Returns an associative array with values as keys and occurrence counts as values
     * @see \Phuture\Coherence\Arrays::length()
     */
    public static function count(array $array): array
    {
        return array_count_values($array);
    }

    /**
     * Creates a Cartesian product of multiple arrays.
     *
     * This method generates all possible combinations by taking one element from each
     * provided array. For example, if you provide arrays [1, 2] and ['a', 'b'], it will
     * generate all combinations: [1, 'a'], [1, 'b'], [2, 'a'], [2, 'b'].
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic cross join with equal length arrays
     * $result = Arrays::crossJoin([1, 2], ['a', 'b']);
     * // Returns: [
     * //     [1, 'a'],
     * //     [1, 'b'],
     * //     [2, 'a'],
     * //     [2, 'b']
     * // ]
     *
     * // Cross join with three arrays
     * $sizes = ['S', 'M'];
     * $colors = ['red', 'blue'];
     * $types = ['shirt', 'pants'];
     * $result = Arrays::crossJoin($sizes, $colors, $types);
     * // Returns 8 combinations: ['S', 'red', 'shirt'], ['S', 'red', 'pants'], etc.
     * ```
     *
     * @param array ...$arrays Two or more arrays to cross join
     * @return array Returns a multidimensional array containing all possible combinations
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When less than 2 arrays are provided
     */
    public static function crossJoin(array ...$arrays): array
    {
        if (count($arrays) < 2) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least two non-empty arrays are required"
            );
        }

        // Calculate potential result size to prevent memory exhaustion
        $expectedSize = 1;
        foreach ($arrays as $arr) {
            $expectedSize *= count($arr);
            if ($expectedSize > self::CROSS_JOIN_LIMIT) {
                throw new LogicException(
                    'Invalid Argument: Cross join would produce too many elements '
                    . '(over ' . number_format(self::CROSS_JOIN_LIMIT) . ' limit)'
                );
            }
        }

        // Start with the first array
        $combinations = array_map(fn ($item) => [$item], $arrays[0]);

        // Process remaining arrays
        for ($i = 1; $i < count($arrays); $i++) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($arrays[$i] as $item) {
                    $newCombinations[] = array_merge($combination, [$item]);
                }
            }
            $combinations = $newCombinations;
        }

        return $combinations;
    }

    /**
     * Expands a flattened array with dot notation keys back into a multidimensional array.
     *
     * This method takes a flat array where keys use dot notation to represent nested paths
     * and converts it back into a multidimensional array structure. For example, a key like
     * 'user.address.city' becomes ['user']['address']['city'].
     *
     * This is the reverse operation of the flatten method and is useful when you need to
     * reconstruct complex nested structures from simple key-value pairs.
     *
     * When $strict is false (default), conflicting keys will result in later values overwriting
     * earlier ones. When $strict is true, a LogicException will be thrown if conflicts are detected.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $flat = [
     *     'name' => 'John',
     *     'address.city' => 'NYC',
     *     'address.zip' => '10001'
     * ];
     * $nested = Arrays::denote($flat);
     *
     * // Returns: ['name' => 'John', 'address' => ['city' => 'NYC', 'zip' => '10001']]
     * ```
     *
     * @param array $array The flattened array with dot notation keys.
     * @param bool $strict If true, throws exception on data conflicts; if false, later values overwrite earlier ones
     * @return array Returns a multidimensional array with nested structure
     * @throws \Phuture\Coherence\Exception\LogicException When $strict is true and a data conflict is detected
     * @see \Phuture\Coherence\Arrays::notation()
     */
    public static function denote(array $array, bool $strict = false): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $keys = explode('.', (string) $key);

            // Add recursion limit protection
            if (count($keys) >= self::RECURSION_LIMIT) {
                throw new LogicException(
                    "Limit Exceeded: Key depth exceeds limit of " . self::RECURSION_LIMIT
                );
            }

            $lastIndex = count($keys) - 1;
            $temp = &$result;

            foreach ($keys as $index => $k) {
                if ($index === $lastIndex) {
                    // Final key: set the value
                    if ($strict && isset($temp[$k]) && is_array($temp[$k]) && !empty($temp[$k])) {
                        $path = implode('.', array_slice($keys, 0, $index + 1));
                        throw new LogicException(
                            "Data conflict at path '{$path}': Cannot overwrite nested structure with scalar value"
                        );
                    }
                    $temp[$k] = $value;
                } else {
                    // Intermediate key: ensure array exists
                    if (!isset($temp[$k])) {
                        $temp[$k] = [];
                    } elseif (!is_array($temp[$k])) {
                        if ($strict) {
                            $path = implode('.', array_slice($keys, 0, $index + 1));
                            throw new LogicException(
                                "Data conflict at path '{$path}': Cannot convert scalar value to array"
                            );
                        }
                        $temp[$k] = [];
                    }
                    $temp = &$temp[$k];
                }
            }
        }

        return $result;
    }

    /**
     * Returns elements from the first array that are not present in other arrays.
     *
     * This method compares values across multiple arrays and returns only those values from
     * the first array that don't appear in any of the other arrays. Keys are preserved.
     *
     * You can optionally provide a custom comparison function as the last parameter.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic usage
     * $array1 = ['a', 'b', 'c', 'd'];
     * $array2 = ['b', 'd'];
     * $array3 = ['e', 'f'];
     * $result = Arrays::difference($array1, $array2, $array3);
     * // Returns: [0 => 'a', 2 => 'c']
     *
     * // With custom comparison function (case-insensitive)
     * $array1 = ['Apple', 'Banana', 'Cherry'];
     * $array2 = ['banana', 'APPLE'];
     * $result = Arrays::difference(
     *     $array1,
     *     $array2,
     *     fn($a, $b) => strcasecmp($a, $b)
     * );
     * // Returns: [2 => 'Cherry']
     *
     * // Comparing objects by property
     * $products1 = [
     *     (object)['id' => 1, 'name' => 'Laptop'],
     *     (object)['id' => 2, 'name' => 'Mouse'],
     *     (object)['id' => 3, 'name' => 'Keyboard']
     * ];
     * $products2 = [(object)['id' => 2, 'name' => 'Mouse']];
     * $result = Arrays::difference(
     *     $products1,
     *     $products2,
     *     fn($a, $b) => $a->id <=> $b->id
     * );
     * // Returns: [0 => Laptop object, 2 => Keyboard object]
     * ```
     *
     * @param array $array The array to compare from.
     * @param array ...$arrays Arrays to compare against
     * @param callable|null $callback Optional comparison function that returns <0, 0, or >0
     * @return array Returns values from the first array not found in other arrays
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When a comparison array is not provided
     * @see \Phuture\Coherence\Arrays::differenceAssoc()
     * @see \Phuture\Coherence\Arrays::differenceKeys()
     */
    public static function difference(array $array, ...$arrays): array
    {
        $callbacks = self::getCallbacksFromArguments($arrays, 1);

        if (count($arrays) < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least one comparison array is required"
            );
        }

        if ($callbacks !== []) {
            return array_udiff($array, ...$arrays, ...$callbacks);
        }

        return array_diff($array, ...$arrays);
    }

    /**
     * Returns elements from the first array that are not present in other arrays,
     * comparing both keys and values with optional custom comparison.
     *
     * This method computes the difference of arrays with additional index check, meaning
     * both the keys and values must match for an element to be considered present in other
     * arrays. You can optionally provide custom comparison functions and specify what to compare
     * (keys, values, or both) using the ArrayComparator enum.
     *
     * The method supports flexible parameter order where callbacks and the comparator can
     * be provided at the end of the argument list.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     * use Phuture\Coherence\Enum\ArrayComparator;
     *
     * // Basic usage
     * $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
     * $array2 = ['a' => 1, 'd' => 4];
     * $result = Arrays::differenceAssoc($array1, $array2);
     * // Returns: ['b' => 2, 'c' => 3]
     *
     * // With custom value comparison and comparator
     * $array1 = ['name' => 'John', 'AGE' => 30];
     * $array2 = ['name' => 'JOHN'];
     * $result = Arrays::differenceAssoc(
     *     $array1,
     *     $array2,
     *     ArrayComparator::Value, // compare values using callback
     *     fn($a, $b) => strcasecmp($a, $b) // callback for case-insensitive comparison
     * );
     * // Returns: ['AGE' => 30]
     *
     * // With custom key comparison
     * $array1 = ['Apple' => 100, 'Banana' => 200];
     * $array2 = ['apple' => 100];
     * $result = Arrays::differenceAssoc(
     *     $array1,
     *     $array2,
     *     ArrayComparator::Key, // compare keys using callback
     *     fn($a, $b) => strcasecmp($a, $b) // callback for case-insensitive key comparison
     * );
     * // Returns: ['Banana' => 200]
     *
     * // With custom comparison for both keys and values
     * $array1 = ['Name' => 'John', 'Age' => 30];
     * $array2 = ['name' => 'JOHN', 'age' => 25];
     * $result = Arrays::differenceAssoc(
     *     $array1,
     *     $array2,
     *     ArrayComparator::Both, // compare both keys and values using callbacks
     *     fn($a, $b) => strcasecmp($a, $b), // callback for value comparison
     *     fn($a, $b) => strcasecmp($a, $b)  // callback for key comparison
     * );
     * // Returns: ['Age' => 30]
     * ```
     *
     * @param array $array The array to compare from.
     * @param array ...$arrays Arrays to compare against
     * @param ArrayComparator $comparator The comparator to use with the provided callback(s) (required with callbacks)
     * @param callable|null $firstCallback Optional comparison function that returns <0, 0, or >0
     * @param callable|null $secondCallback Optional comparison function that returns <0, 0, or >0
     * @return array Returns key-value pairs from the first array not found in other arrays
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When no comparison arrays are provided
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When callbacks are provided without an ArrayComparator
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When more than two callbacks are provided
     * @throws \Phuture\Coherence\Exception\LogicException When no ArrayComparator enum is provided when needed
     * @throws \Phuture\Coherence\Exception\LogicException When ArrayComparator::Both is not used with exactly two callbacks
     * @see \Phuture\Coherence\Arrays::difference()
     * @see \Phuture\Coherence\Arrays::differenceKeys()
     * @see \Phuture\Coherence\Enum\ArrayComparator
     */
    public static function differenceAssoc(array $array, ...$arrays): array
    {
        $callbacks = self::getCallbacksFromArguments($arrays, 2);
        $enums = self::getEnumsFromArguments($arrays, ArrayComparator::class, 1);

        if (count($arrays) < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least one comparison array is required"
            );
        }

        if ($callbacks !== [] && $enums == []) {
            throw new InvalidArgumentException(
                "Invalid Argument: When providing custom callbacks, an ArrayComparator must be provided"
            );
        }

        if (count($callbacks) > 2) {
            throw new InvalidArgumentException(
                "Invalid Argument: Only two callbacks are allowed"
            );
        }

        if ($callbacks !== []) {
            $comparator = $enums[0] ?? throw new LogicException(
                "Invalid Comparator: No ArrayComparator enum provided"
            );

            if (count($callbacks) === 2 && $comparator !== ArrayComparator::Both) {
                throw new LogicException(
                    "Invalid Comparator: Only ArrayComparator::Both can be used with two callbacks"
                );
            }

            if (count($callbacks) < 2 && $comparator == ArrayComparator::Both) {
                throw new LogicException(
                    "Invalid Comparator: ArrayComparator::Both can be used only with two callbacks"
                );
            }

            return match ($comparator) {
                ArrayComparator::Key => array_diff_uassoc($array, ...$arrays, ...$callbacks),
                ArrayComparator::Value => array_udiff_assoc($array, ...$arrays, ...$callbacks),
                ArrayComparator::Both => array_udiff_uassoc($array, ...$arrays, ...$callbacks),
                default => array_diff_assoc($array, ...$arrays)
            };
        }

        return array_diff_assoc($array, ...$arrays);
    }

    /**
     * Returns keys from the first array that are not present in other arrays.
     *
     * This method compares only the keys (not values) across multiple arrays and returns
     * key-value pairs from the first array whose keys don't appear in any of the other arrays.
     *
     * You can optionally provide a custom comparison function as the last parameter.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic usage
     * $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
     * $array2 = ['a' => 99, 'd' => 4];
     *
     * $result = Arrays::differenceKeys($array1, $array2);
     * // Returns: ['b' => 2, 'c' => 3]
     * // (keys 'b' and 'c' don't exist in array2, values don't matter)
     *
     * // With callback for type-insensitive key comparison
     * $array1 = [1 => 'one', 2 => 'two', 3 => 'three'];
     * $array2 = ['1' => 'ONE', '2' => 'TWO'];
     *
     * $result = Arrays::differenceKeys(
     *     $array1,
     *     $array2,
     *     fn($a, $b) => (string)$a <=> (string)$b
     * );
     * // Returns: [3 => 'three']
     * // (numeric keys 1 and 2 match string keys '1' and '2' when compared as strings)
     * ```
     *
     * @param array $array The array to compare from.
     * @param array ...$arrays Arrays to compare against
     * @param callable|null $callback Optional comparison function for keys that returns <0, 0, or >0
     * @return array Returns key-value pairs whose keys are not found in other arrays
     * @see \Phuture\Coherence\Arrays::difference()
     * @see \Phuture\Coherence\Arrays::differenceAssoc()
     */
    public static function differenceKeys(array $array, ...$arrays): array
    {
        $callbacks = self::getCallbacksFromArguments($arrays, 1);

        if (count($arrays) < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least one comparison array is required"
            );
        }

        if ($callbacks !== []) {
            return array_diff_ukey($array, ...$arrays, ...$callbacks);
        }

        return array_diff_key($array, ...$arrays);
    }

    /**
     * Checks if all array elements satisfy a callback function.
     *
     * This method tests every element in an array against a condition you provide.
     * It only returns true if ALL elements pass the test. Think of it like checking
     * if everyone in a group has completed their homework.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [2, 4, 6, 8];
     * $allEven = Arrays::every($numbers, fn($n) => $n % 2 === 0);
     * // Returns: true (all numbers are even)
     *
     * $ages = [18, 21, 16, 25];
     * $allAdults = Arrays::every($ages, fn($age) => $age >= 18);
     * // Returns: false (16 < 18)
     * ```
     *
     * @param array $array The array whose elements to test.
     * @param callable $callback A function that receives each element and returns true if it passes the test
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return bool Returns true if ALL elements pass the callback test, false otherwise
     * @see \Phuture\Coherence\Arrays::some()
     */
    public static function every(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if (!$callback($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a key exists in an array.
     *
     * This method determines whether a specific key exists in an array, regardless
     * of what value is associated with that key. This is useful when you need to
     * check for the presence of a key even if its value is null or empty.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $user = ['name' => 'John', 'age' => 25, 'email' => null];
     * $hasEmail = Arrays::exists($user, 'email');
     * // Returns: true (key exists even though value is null)
     *
     * $hasPhone = Arrays::exists($user, 'phone');
     * // Returns: false (key doesn't exist)
     * ```
     *
     * @param array $array The array to search in
     * @param string|int $key The key to search for
     * @return bool Returns true if the key exists in the array, false otherwise
     * @see \Phuture\Coherence\Arrays::contains()
     */
    public static function exists(array $array, string|int $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * Creates an array filled with a specific value.
     *
     * This method generates a new array with a specified number of elements, all set to
     * the same value. The array starts at a given index position, which can be positive
     * or negative. Useful for initializing arrays with default values.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Create array starting at index 0
     * $array = Arrays::fill(0, 3, 'hello');
     * // Returns: [0 => 'hello', 1 => 'hello', 2 => 'hello']
     *
     * // Start at a different index
     * $array = Arrays::fill(5, 3, 'x');
     * // Returns: [5 => 'x', 6 => 'x', 7 => 'x']
     *
     * // Use negative index
     * $array = Arrays::fill(-2, 2, 0);
     * // Returns: [-2 => 0, -1 => 0]
     * ```
     *
     * @param int $startIndex The first index of the returned array
     * @param int $count Number of elements to insert (must be greater than zero)
     * @param mixed $value The value to fill the array with
     * @return array Returns a new array filled with the specified value
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When count is less than or equal to zero
     * @see \Phuture\Coherence\Arrays::fillKeys()
     */
    public static function fill(int $startIndex, int $count, mixed $value): array
    {
        if ($count <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: \$count must be greater than zero"
            );
        }

        return array_fill($startIndex, $count, $value);
    }

    /**
     * Creates an array using specified keys, all with the same value.
     *
     * This method generates a new array where you provide the exact keys to use,
     * and all those keys are set to the same value. This is useful when you need
     * to initialize an associative array with specific keys.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Create array with string keys
     * $array = Arrays::fillKeys(['name', 'email', 'phone'], null);
     * // Returns: ['name' => null, 'email' => null, 'phone' => null]
     *
     * // Initialize with default values
     * $permissions = Arrays::fillKeys(['read', 'write', 'delete'], false);
     * // Returns: ['read' => false, 'write' => false, 'delete' => false]
     *
     * // Use numeric keys
     * $array = Arrays::fillKeys([10, 20, 30], 'value');
     * // Returns: [10 => 'value', 20 => 'value', 30 => 'value']
     * ```
     *
     * @param array $keys Array of keys to use for the new array
     * @param mixed $value The value to assign to all keys
     * @return array Returns a new array with specified keys and the same value for all
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When keys array is empty
     * @see \Phuture\Coherence\Arrays::fill()
     */
    public static function fillKeys(array $keys, mixed $value): array
    {
        if (empty($keys)) {
            throw new InvalidArgumentException(
                "Invalid Argument: Keys array must be a non-empty array"
            );
        }

        return array_fill_keys($keys, $value);
    }

    /**
     * Filters elements of an array using a callback function.
     *
     * This method creates a new array containing only the elements that pass a test
     * you provide. The callback function receives each element and should return true
     * to keep it or false to remove it.
     *
     * If no callback is provided, it removes all
     * elements that evaluate to false (like null, 0, false, empty string).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [1, 2, 3, 4, 5, 6];
     *
     * // Keep only even numbers
     * $even = Arrays::filter($numbers, fn($n, $k) => $n % 2 === 0);
     * // Returns: [1 => 2, 3 => 4, 5 => 6]
     *
     * // Remove falsy values (no callback)
     * $mixed = [0, 1, false, 2, '', 3, null];
     * $filtered = Arrays::filter($mixed);
     * // Returns: [1 => 1, 3 => 2, 5 => 3]
     * ```
     *
     * @param array $array The array to filter.
     * @param callable|null $callback Optional function to test each element (default: removes falsy values)
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return array Returns a new array containing only the filtered elements
     * @see \Phuture\Coherence\Arrays::grep()
     * @see \Phuture\Coherence\Arrays::find()
     */
    public static function filter(array $array, ?callable $callback = null): array
    {
        if (is_null($callback)) {
            $callback = fn ($value) => (bool) $value;
        }

        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Returns the first element that passes a test function.
     *
     * This method searches through an array and returns the first element that makes
     * your test function return true. If no element passes the test, it returns null.
     * This is useful when you need to find a specific item in an array based on a condition.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     ['id' => 1, 'name' => 'John', 'active' => false],
     *     ['id' => 2, 'name' => 'Jane', 'active' => true],
     *     ['id' => 3, 'name' => 'Bob', 'active' => true]
     * ];
     *
     * // Find first active user
     * $activeUser = Arrays::find($users, fn($user) => $user['active']);
     * // Returns: ['id' => 2, 'name' => 'Jane', 'active' => true]
     *
     * // Find user by name
     * $john = Arrays::find($users, fn($user) => $user['name'] === 'John');
     * // Returns: ['id' => 1, 'name' => 'John', 'active' => false]
     *
     * // No match found
     * $admin = Arrays::find($users, fn($user) => $user['role'] === 'admin');
     * // Returns: null
     * ```
     *
     * @param array $array The array to search through.
     * @param callable $callback Function that tests each element, returns true to select it
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return mixed Returns the first matching element, or null if none found
     * @see \Phuture\Coherence\Arrays::findKey()
     */
    public static function find(array $array, callable $callback): mixed
    {
        return array_find($array, $callback);
    }

    /**
     * Returns the key of the first element that passes a test function.
     *
     * This method searches through an array and returns the key (not the value) of the
     * first element that makes your test function return true. If no element passes the
     * test, it returns null. Useful when you need to know the position or key of an item.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     'user1' => ['name' => 'John', 'active' => false],
     *     'user2' => ['name' => 'Jane', 'active' => true],
     *     'user3' => ['name' => 'Bob', 'active' => true]
     * ];
     *
     * // Find key of first active user
     * $key = Arrays::findKey($users, fn($user) => $user['active']);
     * // Returns: 'user2'
     *
     * // With numeric keys
     * $numbers = [10, 20, 30, 40];
     * $key = Arrays::findKey($numbers, fn($n) => $n > 25);
     * // Returns: 2 (the index of 30)
     *
     * // No match found
     * $key = Arrays::findKey($users, fn($user) => $user['role'] === 'admin');
     * // Returns: null
     * ```
     *
     * @param array $array The array to search through.
     * @param callable $callback Function that tests each element, returns true to select it
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return mixed Returns the key of the first matching element, or null if none found
     * @see \Phuture\Coherence\Arrays::find()
     */
    public static function findKey(array $array, callable $callback): mixed
    {
        return array_find_key($array, $callback);
    }

    /**
     * Returns the first value of an array.
     *
     * This method retrieves the first value from an array without modifying it.
     * The method throws an exception for empty arrays. This is useful for quickly
     * accessing the first element without worrying about array keys or positions.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $first = Arrays::first($array);
     * // Returns: 'John'
     *
     * // With numeric array
     * $numbers = [10, 20, 30];
     * $first = Arrays::first($numbers);
     * // Returns: 10
     *
     * // Empty array - throws exception
     * $empty = [];
     * $first = Arrays::first($empty);
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to get the first value from.
     * @return mixed Returns the first value
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When the array is empty
     * @see \Phuture\Coherence\Arrays::last()
     */
    public static function first(array $array): mixed
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        return array_first($array);
    }

    /**
     * Returns the first key of an array.
     *
     * This method retrieves the first key from an array without modifying it.
     * The method throws an exception for empty arrays. The key can be a string or integer.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $firstKey = Arrays::firstKey($array);
     * // Returns: 'name'
     *
     * // With numeric keys
     * $numbers = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
     * $firstKey = Arrays::firstKey($numbers);
     * // Returns: 10
     *
     * // Empty array - throws exception
     * $empty = [];
     * $firstKey = Arrays::firstKey($empty);
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to get the first key from.
     * @return string|int Returns the first key
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When the array is empty
     * @see \Phuture\Coherence\Arrays::lastKey()
     */
    public static function firstKey(array $array): string|int
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        return array_key_first($array);
    }

    /**
     * Flattens a multidimensional array into a single level.
     *
     * This method recursively flattens all nested arrays into a single-dimensional array.
     * Unlike collapse() which only merges one level of arrays, flatten() recursively
     * traverses through ALL levels of nesting and collects only the scalar values.
     *
     * Keys from associative arrays are not preserved in the flattened result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = [1, [2, 3], [4, [5, 6]], 7];
     * $result = Arrays::flatten($array);
     * // Returns: [1, 2, 3, 4, 5, 6, 7]
     *
     * // With associative arrays
     * $array = ['a' => 1, 'b' => ['c' => 2, 'd' => ['e' => 3]]];
     * $result = Arrays::flatten($array);
     * // Returns: [1, 2, 3]
     * ```
     *
     * @param array $array A potentially multidimensional array to flatten
     * @return array Returns a single-dimensional array containing all scalar values from the nested structure
     * @see \Phuture\Coherence\Arrays::collapse()
     */
    public static function flatten(array $array, int $depth = 0): array
    {
        if ($depth >= self::RECURSION_LIMIT) {
            throw new LogicException(
                "Limit Exceeded: Recursion depth exceeded limit of " . self::RECURSION_LIMIT
            );
        }

        $result = [];

        foreach ($array as $value) {
            if (is_array($value)) {
                if (!empty($value)) {
                    // Optimize: avoid array_merge() overhead by using loop
                    foreach (self::flatten($value, $depth + 1) as $item) {
                        $result[] = $item;
                    }
                }
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Exchanges all keys with their associated values in an array.
     *
     * This method swaps keys and values in an array, so that values become keys and
     * keys become values. If multiple values are the same, only the last key will be
     * preserved in the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'];
     * $flipped = Arrays::flip($array);
     * // Returns: ['apple' => 'a', 'banana' => 'b', 'cherry' => 'c']
     *
     * // Numeric keys become string values
     * $numbers = [1 => 'one', 2 => 'two', 3 => 'three'];
     * $flipped = Arrays::flip($numbers);
     * // Returns: ['one' => 1, 'two' => 2, 'three' => 3]
     *
     * // Duplicate values - last key wins
     * $duplicates = ['a' => 'same', 'b' => 'same', 'c' => 'different'];
     * $flipped = Arrays::flip($duplicates);
     * // Returns: ['same' => 'b', 'different' => 'c']
     * ```
     *
     * @param array $array The array to flip.
     * @return array Returns a new array with flipped keys and values
     * @throws \Phuture\Coherence\Exception\InvalidDataTypeException If one of the values is not a string nor integer
     * @see \Phuture\Coherence\Arrays::reverse()
     */
    public static function flip(array $array): array
    {
        foreach ($array as $value) {
            if (!is_int($value) && !is_string($value)) {
                throw new InvalidDataTypeException(
                    "Invalid Data Type: Only arrays with string and integer values can be flipped"
                );
            }
        }

        return array_flip($array);
    }

    /**
     * Converts a string into an array by splitting it with a separator.
     *
     * This method takes a string and splits it into an array using the specified separator.
     * It provides a convenient wrapper around PHP's explode() function with consistent
     * parameter ordering and sensible defaults.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic usage with space separator
     * $words = Arrays::fromString('Hello world from Arrays');
     * // Returns: ['Hello', 'world', 'from', 'Arrays']
     *
     * // Using custom separator
     * $fruits = Arrays::fromString('apple,banana,cherry', ',');
     * // Returns: ['apple', 'banana', 'cherry']
     *
     * // With limit
     * $parts = Arrays::fromString('one-two-three-four', '-', 3);
     * // Returns: ['one', 'two', 'three-four']
     * ```
     *
     * @param string $string The string to split into an array.
     * @param string $separator The character or string to split on (default: space).
     * @param int $limit The maximum number of array elements to return (default: PHP's default).
     * @return array An array of string parts.
     * @see \Phuture\Coherence\Arrays::toString()
     */
    public static function fromString(string $string, string $separator = ' ', int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $string, $limit);
    }

    /**
     * Retrieves a value from an array by key.
     *
     * This method provides a safe way to access array values without worrying about undefined key errors.
     * For nested arrays, you can pass an array of keys to navigate through the structure. When a key
     * doesn't exist and no default value was provided, an exception is thrown.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $data = [
     *     'user' => [
     *         'profile' => [
     *             'email' => 'user@example.com'
     *         ]
     *     ],
     *     'status' => 'active'
     * ];
     *
     * // Access simple key
     * $status = Arrays::get($data, 'status');
     * // Returns: 'active'
     *
     * // Access nested value using array path
     * $email = Arrays::get($data, ['user', 'profile', 'email']);
     * // Returns: 'user@example.com'
     *
     * // Provide default value for missing key
     * $role = Arrays::get($data, 'role', 'guest');
     * // Returns: 'guest'
     *
     * // Missing key without default throws exception
     * $role = Arrays::get($data, 'role');
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to retrieve the value from.
     * @param string|int|array $key The key to access (string/int for direct access, array for a nested path).
     * @param mixed $default Optional default value to return if the key is not found
     * @return mixed Returns the value at the specified key, or the default value if provided and the key doesn't exist
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When the key doesn't exist and no default value is provided
     * @see \Phuture\Coherence\Arrays::getReference()
     */
    public static function get(array $array, string|int|array $key, mixed $default = null): mixed
    {
        if (func_num_args() < 3 && !self::has($array, $key)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: Missing item in array and no default value provided"
            );
        }

        if (is_array($key)) {
            foreach ($key as $segment) {
                if (!is_array($array) || !array_key_exists($segment, $array)) {
                    return $default;
                }
                $array = $array[$segment];
            }

            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        return $default;
    }

    /**
     * Filters array elements by regular expression pattern.
     *
     * This method returns only those array elements whose values match the specified
     * regular expression pattern. When the invert parameter is true, it returns elements
     * that do NOT match the pattern instead.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $data = ['apple', 'banana', '123', '456', 'cherry'];
     *
     * // Get only numeric strings
     * $numbers = Arrays::grep($data, '~^\d+$~');
     * // Returns: ['123', '456']
     *
     * // Get only non-numeric strings (inverted)
     * $words = Arrays::grep($data, '~^\d+$~', true);
     * // Returns: ['apple', 'banana', 'cherry']
     *
     * // Match strings starting with 'a'
     * $startsWithA = Arrays::grep($data, '~^a~i');
     * // Returns: ['apple']
     * ```
     *
     * @param array $array The array to filter.
     * @param string $pattern Regular expression pattern to match against
     * @param bool $invert When true, returns elements that do NOT match the pattern (default: false)
     * @return array Returns filtered array with matching elements
     * @throws \Phuture\Coherence\Exception\LogicException When the regular expression pattern is invalid
     */
    public static function grep(array $array, string $pattern, bool $invert = false): array
    {
        // Validate pattern once before the loop
        set_error_handler(function ($errno, $errstr) use ($pattern) {
            restore_error_handler();
            throw new LogicException(
                "Invalid Pattern: The regular expression pattern \"{$pattern}\" is invalid"
            );
        });

        $testResult = @preg_match($pattern, '');
        restore_error_handler();

        if ($testResult === false) {
            throw new LogicException(
                "Invalid Pattern: The regular expression pattern \"{$pattern}\" is invalid"
            );
        }

        $result = [];
        foreach ($array as $key => $value) {
            $match = preg_match($pattern, (string)$value) === 1;
            if ($invert !== $match) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Groups array elements by a specified key or callback function.
     *
     * This method organizes items in an array into groups based on a common value.
     * You can either specify a key name (for arrays of arrays/objects) or provide
     * a custom function that determines how items should be grouped.
     *
     * Think of it like sorting a deck of cards into piles by suit - all hearts go
     * in one pile, all spades in another, and so on. Each pile keeps all the original
     * cards together.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Group by key name
     * $users = [
     *     ['name' => 'John', 'department' => 'Sales'],
     *     ['name' => 'Jane', 'department' => 'IT'],
     *     ['name' => 'Bob', 'department' => 'Sales']
     * ];
     * $grouped = Arrays::groupBy($users, 'department');
     * // Returns: [
     * //     'Sales' => [
     * //         ['name' => 'John', 'department' => 'Sales'],
     * //         ['name' => 'Bob', 'department' => 'Sales']
     * //     ],
     * //     'IT' => [
     * //         ['name' => 'Jane', 'department' => 'IT']
     * //     ]
     * // ]
     *
     * // Group by callback function
     * $numbers = [1, 2, 3, 4, 5, 6];
     * $grouped = Arrays::groupBy($numbers, fn($n) => $n % 2);
     * // Returns: [
     * //     1 => [1, 3, 5],  // odd numbers
     * //     0 => [2, 4, 6]   // even numbers
     * // ]
     * ```
     *
     * @param array $array The array to group.
     * @param callable|string $groupBy The key name to group by, or a callback function
     *  that returns the group key. The callback has the signature `function (mixed $item, mixed $key): mixed`
     * @return array Returns an associative array where keys are group identifiers
     *  and values are arrays of items belonging to each group
     * @see \Phuture\Coherence\Arrays::associate()
     * @see \Phuture\Coherence\Arrays::partition()
     */
    public static function groupBy(array $array, callable|string $groupBy): array
    {
        $result = [];

        foreach ($array as $key => $item) {
            // Determine the group key based on parameter type
            if (is_string($groupBy)) {
                // Access by key name (supports both arrays and objects)
                $groupKey = is_array($item) ? ($item[$groupBy] ?? null) : ($item->{$groupBy} ?? null);
            } else {
                // Use callback to determine group key
                $groupKey = $groupBy($item, $key);
            }

            // Convert null to empty string to avoid deprecated array offset warning
            $arrayKey = $groupKey ?? '';

            // Initialize group array if it doesn't exist
            if (!isset($result[$arrayKey])) {
                $result[$arrayKey] = [];
            }

            // Add item to its group
            $result[$arrayKey][] = $item;
        }

        return $result;
    }

    /**
     * Checks if a key exists in an array.
     *
     * This method determines whether a specific key exists in an array. For nested arrays,
     * you can pass an array of keys representing the path to check for existence.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $data = [
     *     'settings' => [
     *         'theme' => [
     *             'color' => 'blue'
     *         ]
     *     ],
     *     'active' => true
     * ];
     *
     * // Check simple key
     * Arrays::has($data, 'active');
     * // Returns: true
     *
     * // Check nested key using array path
     * Arrays::has($data, ['settings', 'theme', 'color']);
     * // Returns: true
     *
     * // Check non-existent key
     * Arrays::has($data, 'missing');
     * // Returns: false
     *
     * // Check non-existent nested key
     * Arrays::has($data, ['settings', 'theme', 'font']);
     * // Returns: false
     * ```
     *
     * @param array $array The array to check. for key existence
     * @param string|int|array $key The key to check (string/int for simple key, array for nested path).
     * @return bool Returns true if the key exists, false otherwise
     */
    public static function has(array $array, string|int|array $key): bool
    {
        // Handle array key
        if (is_array($key)) {
            foreach ($key as $segment) {
                if (is_array($array) && array_key_exists($segment, $array)) {
                    $array = $array[$segment];
                } else {
                    return false;
                }
            }

            return true;
        }

        // Handle simple key
        return array_key_exists($key, $array);
    }

    /**
     * Inserts elements after a specified key in an array.
     *
     * This method inserts new key-value pairs into an array at a position immediately after
     * the specified key.
     *
     * If a key already exists, it remains unchanged. The array is modified by reference.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['first' => 10, 'second' => 20];
     * Arrays::insertAfter($array, 'first', ['hello' => 'world']);
     * // Result: ['first' => 10, 'hello' => 'world', 'second' => 20]
     *
     * // Insert after non-existent key (appends)
     * $array = ['first' => 10];
     * Arrays::insertAfter($array, 'missing', ['new' => 20]);
     * // Result: ['first' => 10, 'new' => 20]
     * ```
     *
     * @param array $array The array to insert into (passed by reference).
     * @param string|int $key The reference key to insert after, or null to append
     * @param array $items Associative array of key-value pairs to insert
     * @see \Phuture\Coherence\Arrays::insertBefore()
     */
    public static function insertAfter(array &$array, string|int $key, array $items): void
    {
        if (empty($items)) {
            return;
        }

        // If key doesn't exist, append the items
        if (!array_key_exists($key, $array)) {
            foreach ($items as $k => $v) {
                $array[$k] = $v;
            }

            return;
        }

        // Single-pass rebuild with insertion after the matching key
        $result = [];
        foreach ($array as $k => $v) {
            $result[$k] = $v;
            if ($k === $key) {
                foreach ($items as $itemKey => $itemValue) {
                    $result[$itemKey] ??= $itemValue;
                }
            }
        }

        // Clear and rebuild the original array by reference
        $array = [];
        foreach ($result as $k => $v) {
            $array[$k] = $v;
        }
    }

    /**
     * Inserts elements before a specified key in an array.
     *
     * This method inserts new key-value pairs into an array at a position immediately before
     * the specified key.
     *
     * If a key already exists, it remains unchanged. The array is modified by reference.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['first' => 10, 'second' => 20];
     * Arrays::insertBefore($array, 'second', ['hello' => 'world']);
     * // Result: ['first' => 10, 'hello' => 'world', 'second' => 20]
     *
     * // Insert before non-existent key (prepends)
     * $array = ['first' => 10];
     * Arrays::insertBefore($array, 'missing', ['new' => 5]);
     * // Result: ['new' => 5, 'first' => 10]
     * ```
     *
     * @param array $array The array to insert into (passed by reference).
     * @param string|int $key The reference key to insert before, or null to prepend
     * @param array $items Associative array of key-value pairs to insert
     * @see \Phuture\Coherence\Arrays::insertAfter()
     */
    public static function insertBefore(array &$array, string|int $key, array $items): void
    {
        if (empty($items)) {
            return;
        }

        // If key doesn't exist, prepend the items
        if (!array_key_exists($key, $array)) {
            $array = $items + $array;

            return;
        }

        // Single-pass rebuild with insertion before the matching key
        $result = [];
        foreach ($array as $k => $v) {
            if ($k === $key) {
                foreach ($items as $itemKey => $itemValue) {
                    $result[$itemKey] ??= $itemValue;
                }
            }
            $result[$k] = $v;
        }

        // Clear and rebuild the original array by reference
        $array = [];
        foreach ($result as $k => $v) {
            $array[$k] = $v;
        }
    }

    /**
     * Returns elements that are present in all provided arrays.
     *
     * This method compares values across multiple arrays and returns only those values
     * that appear in every array. Keys are preserved from the first array.
     *
     * You can optionally provide a custom comparison function as the last parameter.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic usage
     * $array1 = ['a', 'b', 'c', 'd'];
     * $array2 = ['b', 'c', 'e'];
     * $array3 = ['c', 'b', 'f'];
     *
     * $common = Arrays::intersect($array1, $array2, $array3);
     * // Returns: [1 => 'b', 2 => 'c'] (values present in all arrays)
     *
     * // With custom comparison function (case-insensitive)
     * $array1 = ['Apple', 'Banana', 'Cherry'];
     * $array2 = ['BANANA', 'cherry'];
     *
     * $result = Arrays::intersect(
     *     $array1,
     *     $array2,
     *     fn($a, $b) => strcasecmp($a, $b)
     * );
     * // Returns: [1 => 'Banana', 2 => 'Cherry']
     *
     * // Comparing objects by property
     * $users1 = [
     *     (object)['id' => 1, 'name' => 'John'],
     *     (object)['id' => 2, 'name' => 'Jane'],
     *     (object)['id' => 3, 'name' => 'Bob']
     * ];
     * $users2 = [
     *     (object)['id' => 2, 'name' => 'Jane'],
     *     (object)['id' => 4, 'name' => 'Alice']
     * ];
     *
     * $result = Arrays::intersect(
     *     $users1,
     *     $users2,
     *     fn($a, $b) => $a->id <=> $b->id
     * );
     * // Returns: [1 => Jane object] (only user with id=2 exists in both)
     * ```
     *
     * @param array $array The array to compare from.
     * @param array ...$arrays Arrays to compare against
     * @param callable|null $callback Optional comparison function that returns <0, 0, or >0
     * @return array Returns values present in all arrays with keys preserved from the first array
     * @see \Phuture\Coherence\Arrays::intersectAssoc()
     * @see \Phuture\Coherence\Arrays::intersectKeys()
     */
    public static function intersect(array $array, ...$arrays): array
    {
        $callbacks = self::getCallbacksFromArguments($arrays, 1);

        if (count($arrays) < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least one comparison array is required"
            );
        }

        if ($callbacks !== []) {
            return array_uintersect($array, ...$arrays, ...$callbacks);
        }

        return array_intersect($array, ...$arrays);
    }

    /**
     * Returns elements that are present in all provided arrays, comparing both keys and values,
     * with optional custom comparison.
     *
     * This method is like intersect() but also checks that the keys match. An element is only
     * included if both its key and value from the first array exist as a pair in all other arrays.
     * You can provide custom comparison functions for values, keys, or both.
     *
     * The method supports flexible parameter order where callbacks and the comparator can
     * be provided at the end of the argument list.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     * use Phuture\Coherence\Enum\ArrayComparator;
     *
     * // Basic usage
     * $array1 = ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'];
     * $array2 = ['a' => 'apple', 'b' => 'banana', 'd' => 'date'];
     * $array3 = ['a' => 'apple', 'c' => 'coconut'];
     * $result = Arrays::intersectAssoc($array1, $array2, $array3);
     * // Returns: ['a' => 'apple']
     * // (only key 'a' with value 'apple' exists in all arrays)
     *
     * // With custom value comparison and comparator
     * $array1 = ['a' => 'Apple', 'b' => 'Banana'];
     * $array2 = ['a' => 'APPLE', 'b' => 'orange'];
     * $result = Arrays::intersectAssoc(
     *     $array1,
     *     $array2,
     *     ArrayComparator::Value, // compare values using callback
     *     fn($a, $b) => strcasecmp($a, $b) // callback for case-insensitive comparison
     * );
     * // Returns: ['a' => 'Apple']
     * // (key 'a' with case-insensitive value 'apple' exists in both)
     *
     * // With custom key comparison
     * $array1 = [1 => 'one', 2 => 'two', 3 => 'three'];
     * $array2 = ['1' => 'one', '2' => 'two'];
     * $result = Arrays::intersectAssoc(
     *     $array1,
     *     $array2,
     *     ArrayComparator::Key, // compare keys using callback
     *     fn($a, $b) => (string)$a <=> (string)$b // callback for key comparison
     * );
     * // Returns: [1 => 'one', 2 => 'two']
     * // (keys 1 and 2 match when compared as strings)
     *
     * // With custom comparison for both keys and values
     * $array1 = [1 => 'Apple', 2 => 'Banana'];
     * $array2 = ['1' => 'APPLE', '2' => 'BANANA'];
     * $result = Arrays::intersectAssoc(
     *     $array1,
     *     $array2,
     *     ArrayComparator::Both, // compare both keys and values using callbacks
     *     fn($a, $b) => strcasecmp($a, $b), // callback for value comparison
     *     fn($a, $b) => (string)$a <=> (string)$b // callback for key comparison
     * );
     * // Returns: [1 => 'Apple', 2 => 'Banana']
     * // (both key-value pairs match with custom comparisons)
     * ```
     *
     * @param array $array The array to compare from.
     * @param array ...$arrays Arrays to compare against
     * @param ArrayComparator $comparator The comparator to use with the provided callback(s) (required with callbacks)
     * @param callable|null $firstCallback Optional comparison function that returns <0, 0, or >0
     * @param callable|null $secondCallback Optional comparison function that returns <0, 0, or >0
     * @return array Returns key-value pairs present in all arrays
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When no comparison arrays are provided
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When callbacks are provided without an ArrayComparator
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When more than two callbacks are provided
     * @throws \Phuture\Coherence\Exception\LogicException When no ArrayComparator enum is provided when needed
     * @throws \Phuture\Coherence\Exception\LogicException When ArrayComparator::Both is not used with exactly two callbacks
     * @see \Phuture\Coherence\Arrays::intersect()
     * @see \Phuture\Coherence\Arrays::intersectKeys()
     * @see \Phuture\Coherence\Enum\ArrayComparator
     */
    public static function intersectAssoc(array $array, ...$arrays): array
    {
        $callbacks = self::getCallbacksFromArguments($arrays, 2);
        $enums = self::getEnumsFromArguments($arrays, ArrayComparator::class, 1);

        if (count($arrays) < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least one comparison array is required"
            );
        }

        if ($callbacks !== [] && $enums == []) {
            throw new InvalidArgumentException(
                "Invalid Argument: When providing custom callbacks, an ArrayComparator must be provided"
            );
        }

        if (count($callbacks) > 2) {
            throw new InvalidArgumentException(
                "Invalid Argument: Only two callbacks are allowed"
            );
        }

        if ($callbacks !== []) {
            $comparator = $enums[0] ?? throw new LogicException(
                "Invalid Comparator: No ArrayComparator enum provided"
            );

            if (count($callbacks) === 2 && $comparator !== ArrayComparator::Both) {
                throw new LogicException(
                    "Invalid Comparator: Only ArrayComparator::Both can be used with two callbacks"
                );
            }

            if (count($callbacks) < 2 && $comparator == ArrayComparator::Both) {
                throw new LogicException(
                    "Invalid Comparator: ArrayComparator::Both can be used only with two callbacks"
                );
            }

            return match ($comparator) {
                ArrayComparator::Key => array_intersect_uassoc($array, ...$arrays, ...$callbacks),
                ArrayComparator::Value => array_uintersect_assoc($array, ...$arrays, ...$callbacks),
                ArrayComparator::Both => array_uintersect_uassoc($array, ...$arrays, ...$callbacks),
                default => array_intersect_assoc($array, ...$arrays)
            };
        }

        return array_intersect_assoc($array, ...$arrays);
    }

    /**
     * Returns elements whose keys are present in all provided arrays.
     *
     * This method compares only the keys (not values) across multiple arrays and returns
     * key-value pairs from the first array whose keys appear in all other arrays. The values
     * don't need to match, only the keys. You can optionally provide a custom comparison function for keys.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array1 = ['a' => 1, 'b' => 2, 'c' => 3];
     * $array2 = ['a' => 99, 'c' => 88, 'd' => 4];
     * $array3 = ['a' => 77, 'c' => 66];
     *
     * $result = Arrays::intersectKeys($array1, $array2, $array3);
     * // Returns: ['a' => 1, 'c' => 3]
     * // (keys 'a' and 'c' exist in all arrays, values from first array are kept)
     *
     * // With callback for type-insensitive key comparison
     * $array1 = [1 => 'one', 2 => 'two', 3 => 'three'];
     * $array2 = ['1' => 'ONE', '2' => 'TWO'];
     *
     * $result = Arrays::intersectKeys(
     *     $array1,
     *     $array2,
     *     fn($a, $b) => (string)$a <=> (string)$b
     * );
     * // Returns: [1 => 'one', 2 => 'two']
     * // (numeric keys 1 and 2 match string keys '1' and '2' when compared as strings)
     * ```
     *
     * @param array $array The array to compare from.
     * @param array ...$arrays Arrays to compare against
     * @param callable|null $callback Optional comparison function that returns <0, 0, or >0
     * @return array Returns key-value pairs whose keys are found in all arrays
     * @see \Phuture\Coherence\Arrays::intersect()
     */
    public static function intersectKeys(array $array, ...$arrays): array
    {
        $callbacks = self::getCallbacksFromArguments($arrays, 1);

        if (count($arrays) < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least one comparison array is required"
            );
        }

        if ($callbacks !== []) {
            return array_intersect_ukey($array, ...$arrays, ...$callbacks);
        }

        return array_intersect_key($array, ...$arrays);
    }

    /**
     * Checks if the given array is an associative array.
     *
     * An array is considered "associative" if it does not have sequential
     * integer keys starting from 0. This method determines if an array
     * is not a list, but rather has string keys or non-sequential numeric keys.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Returns true - has string keys
     * Arrays::isAssoc(['name' => 'John', 'age' => 30]);
     *
     * // Returns true - non-sequential numeric keys
     * Arrays::isAssoc([1 => 'first', 3 => 'third']);
     *
     * // Returns false - sequential numeric keys starting from 0
     * Arrays::isAssoc([0 => 'first', 1 => 'second', 2 => 'third']);
     * ```
     *
     * @param array $array The array to check.
     * @return bool Returns true if the array is associative, false if it's a list
     * @see \Phuture\Coherence\Arrays::isList()
     */
    public static function isAssoc(array $array): bool
    {
        return !self::isList($array);
    }

    /**
     * Checks if the given array is empty (blank).
     *
     * An array is considered "blank" if it contains no elements.
     * This is a semantic wrapper around count($array) === 0 that
     * provides more expressive intent in array operations.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Returns true - completely empty array
     * Arrays::isBlank([]);
     *
     * // Returns false - contains elements, even if they're null or empty
     * Arrays::isBlank([null, '', 0]);
     * Arrays::isBlank(['name' => 'John']);
     * ```
     *
     * @param array $array The array to check.
     * @return bool Returns true if the array is empty, false otherwise
     * @see \Phuture\Coherence\Arrays::isFilled()
     */
    public static function isBlank(array $array): bool
    {
        return count($array) === 0;
    }

    /**
     * Checks if the given array is not empty (filled).
     *
     * An array is considered "filled" if it contains one or more elements.
     * This method is the logical opposite of isBlank() and provides
     * expressive intent when checking for non-empty arrays.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Returns true - contains elements, even if they're null or empty
     * Arrays::isFilled([1, 2, 3]);
     * Arrays::isFilled(['name' => 'John']);
     * Arrays::isFilled([null, '', 0]);
     *
     * // Returns false - completely empty array
     * Arrays::isFilled([]);
     * ```
     *
     * @param array $array The array to check.
     * @return bool Returns true if the array is not empty, false otherwise
     * @see \Phuture\Coherence\Arrays::isBlank()
     */
    public static function isFilled(array $array): bool
    {
        return !self::isBlank($array);
    }

    /**
     * Checks whether a given array is a list.
     *
     * This method determines if an array is a list, meaning it has sequential numeric
     * keys starting from 0 with no gaps. A list has keys like [0, 1, 2, 3], whereas
     * an associative array might have keys like ['name', 'age'] or [1, 3, 5].
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $list = ['apple', 'banana', 'cherry'];
     * $isList = Arrays::isList($list);
     * // Returns: true (keys are 0, 1, 2)
     *
     * $assoc = ['fruit' => 'apple', 'color' => 'red'];
     * $isList = Arrays::isList($assoc);
     * // Returns: false (has string keys)
     * ```
     *
     * @param array $array The array to check.
     * @return bool Returns true if the array is a list, false otherwise
     * @see \Phuture\Coherence\Arrays::isAssoc()
     */
    public static function isList(array $array): bool
    {
        return array_is_list($array);
    }

    /**
     * Applies a user-defined function to every element of an array.
     *
     * This method runs a custom function on each element in an array. You can
     * modify the values by passing them by reference in your callback function.
     * Optionally process nested arrays recursively to apply the function to all
     * levels of a multidimensional array.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic iteration
     * $prices = [10, 20, 30];
     * Arrays::iterate($prices, function(&$value, $key) {
     *     $value = $value * 1.1; // Add 10% tax
     * });
     * // $prices is now: [11, 22, 33]
     *
     * // Recursive iteration on nested arrays
     * $data = [
     *     'user' => ['name' => 'John', 'age' => 30],
     *     'settings' => ['theme' => 'dark', 'notifications' => true]
     * ];
     * Arrays::iterate($data, function(&$value, $key) {
     *     if (is_string($value)) {
     *         $value = strtoupper($value);
     *     }
     * }, true);
     * // $data is now with all string values in uppercase
     * ```
     *
     * @param array|object $array The array or object to iterate over (passed by reference)
     * @param callable $callback The function to apply to each element
     *  The callback has the signature `function (mixed $value, mixed $key): mixed`
     * @param bool $recursive Whether to recursively process nested arrays (default: false)
     * @param mixed $args Optional additional data to pass to the callback function
     * @return bool Returns true on success, false on failure
     */
    public static function iterate(
        array|object &$array,
        callable $callback,
        bool $recursive = false,
        mixed $args = null
    ): bool {
        if ($recursive) {
            return array_walk_recursive($array, $callback, $args);
        }

        return array_walk($array, $callback, $args);
    }

    /**
     * Joins multiple array chunks into a single array.
     *
     * This method combines multiple arrays into one, which is the reverse operation of split().
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic joining
     * $chunk1 = ['a', 'b', 'c'];
     * $chunk2 = ['d', 'e', 'f'];
     * $chunk3 = ['g'];
     * $result = Arrays::join($chunk1, $chunk2, $chunk3);
     * // Returns: ['a', 'b', 'c', 'd', 'e', 'f', 'g']
     *
     * $chunk1 = ['a' => 1, 'b' => 2];
     * $chunk2 = ['c' => 3, 'd' => 4];
     * $result = Arrays::join($chunk1, $chunk2);
     * // Returns: ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]
     *
     * // With associative arrays (may cause overwrites)
     * $chunk1 = ['name' => 'John', 'email' => 'john@example.com'];
     * $chunk2 = ['age' => 30, 'name' => 'Daniel'];
     * $result = Arrays::join($chunk1, $chunk2);
     * // Returns: ['name' => 'Daniel', 'email' => 'john@example.com', 'age' => 30]
     * ```
     *
     * @param array ...$arrays Two or more arrays to join together
     * @return array Returns a single merged array
     * @see \Phuture\Coherence\Arrays::split()
     */
    public static function join(array ...$arrays): array
    {
        if (count($arrays) < 2) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least two arrays are required"
            );
        }

        return array_merge(...$arrays);
    }

    /**
     * Returns all the keys from an array.
     *
     * This method extracts all keys from an array and returns them as a new indexed array.
     * The keys can be strings, integers, or a mix of both. The resulting array will have
     * numeric keys starting from 0.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $keys = Arrays::keys($array);
     * // Returns: ['name', 'email', 'age']
     *
     * // With numeric keys
     * $numbers = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
     * $keys = Arrays::keys($numbers);
     * // Returns: [10, 20, 30]
     *
     * // Mixed keys
     * $mixed = ['a' => 1, 0 => 2, 'b' => 3];
     * $keys = Arrays::keys($mixed);
     * // Returns: ['a', 0, 'b']
     * ```
     *
     * @param array $array The array from which to extract keys
     * @return array Returns an indexed array containing all keys from the input array
     * @see \Phuture\Coherence\Arrays::values()
     */
    public static function keys(array $array): array
    {
        return array_keys($array);
    }

    /**
     * Returns the last value of an array.
     *
     * This method retrieves the last value from an array without modifying it.
     * The method throws an exception for empty arrays. This is useful for quickly
     * accessing the last element without worrying about array keys or positions.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $last = Arrays::last($array);
     * // Returns: 30
     *
     * // With numeric array
     * $numbers = [10, 20, 30];
     * $last = Arrays::last($numbers);
     * // Returns: 30
     *
     * // Empty array - throws exception
     * $empty = [];
     * $last = Arrays::last($empty);
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to get the last value from
     * @return mixed Returns the last value
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When the array is empty
     * @see \Phuture\Coherence\Arrays::first()
     */
    public static function last(array $array): mixed
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        return array_last($array);
    }

    /**
     * Returns the last key of an array.
     *
     * This method retrieves the last key from an array without modifying it.
     * The method throws an exception for empty arrays. The key can be a string or integer.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $lastKey = Arrays::lastKey($array);
     * // Returns: 'age'
     *
     * // With numeric keys
     * $numbers = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
     * $lastKey = Arrays::lastKey($numbers);
     * // Returns: 30
     *
     * // Empty array - throws exception
     * $empty = [];
     * $lastKey = Arrays::lastKey($empty);
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to get the last key from
     * @return string|int Returns the last key
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When the array is empty
     * @see \Phuture\Coherence\Arrays::firstKey()
     */
    public static function lastKey(array $array): string|int
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        return array_key_last($array);
    }

    /**
     * Counts all elements in an array.
     *
     * This method returns the total number of elements in an array. By default, it
     * counts only the elements in the top level. You can optionally count all elements
     * recursively in a multidimensional array to get the total count of all nested
     * elements.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $fruits = ['apple', 'banana', 'cherry'];
     * $count = Arrays::length($fruits);
     *
     * // Returns: 3
     *
     * $nested = ['a', 'b', ['c', 'd', 'e']];
     * $total = Arrays::length($nested, COUNT_RECURSIVE);
     *
     * // Returns: 6 (counts all nested elements)
     * ```
     *
     * @param array $array The array to count elements in
     * @param int $mode Counting mode: COUNT_NORMAL or COUNT_RECURSIVE (default: COUNT_NORMAL)
     * @return int The number of elements in the array
     */
    public static function length(array $array, int $mode = COUNT_NORMAL): int
    {
        return count($array, $mode);
    }

    /**
     * Maps an array to a new structure using a callback that determines the values.
     *
     * This method transforms array values by applying a callback function to each value,
     * while preserving the keys. The callback receives the value as its argument and
     * should return the transformed value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['apple', 'banana', 'cherry'];
     *
     * // Convert values to uppercase
     * $result = Arrays::map($array, fn($value) => strtoupper($value));
     * // Returns: ['APPLE', 'BANANA', 'CHERRY']
     *
     * // Double numeric values
     * $numbers = [1, 2, 3, 4, 5];
     * $result = Arrays::map($numbers, fn($n) => $n * 2);
     * // Returns: [2, 4, 6, 8, 10]
     *
     * // Extract property from objects
     * $users = [
     *     (object)['name' => 'John', 'age' => 30],
     *     (object)['name' => 'Jane', 'age' => 25]
     * ];
     * $result = Arrays::map($users, fn($user) => $user->name);
     * // Returns: ['John', 'Jane']
     * ```
     *
     * @param array $array The array whose values to transform
     * @param callable $callback The callback function to apply to each value
     *  The callback has the signature `function (mixed $value): mixed`
     * @return array Returns a new array with transformed values and original keys
     * @see \Phuture\Coherence\Arrays::mapKeys()
     * @see \Phuture\Coherence\Arrays::mapWithKeys()
     * @see \Phuture\Coherence\Arrays::reduce()
     */
    public static function map(array $array, callable $callback): array
    {
        return array_map($callback, $array);
    }

    /**
     * Maps an array to a new structure using a callback that determines the keys.
     *
     * This method transforms array keys by applying a callback function to each key,
     * while preserving the values. The callback receives the key as its argument and
     * should return the new key.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['first_name' => 'John', 'last_name' => 'Doe'];
     *
     * // Convert keys to uppercase
     * $result = Arrays::mapKeys($array, fn($key) => strtoupper($key));
     * // Returns: ['FIRST_NAME' => 'John', 'LAST_NAME' => 'Doe']
     *
     * // Prefix all keys
     * $result = Arrays::mapKeys($array, fn($key) => 'user_' . $key);
     * // Returns: ['user_first_name' => 'John', 'user_last_name' => 'Doe']
     * ```
     *
     * @param array $array The array whose keys to transform
     * @param callable $callback The callback function to apply to each key
     *  The callback has the signature `function (mixed $key): mixed`
     * @return array Returns a new array with transformed keys and original values
     * @see \Phuture\Coherence\Arrays::map()
     * @see \Phuture\Coherence\Arrays::mapWithKeys()
     */
    public static function mapKeys(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $callback($key);
            $result[$newKey] = $value;
        }

        return $result;
    }

    /**
     * Maps an array to a new structure using a callback that determines both keys and values.
     *
     * This method iterates through an array and applies a callback function to each element.
     * Unlike map() which transforms only values, or mapKeys() which transforms only keys,
     * this method allows you to completely restructure the array by determining both the
     * new key and new value for each element. This is useful for restructuring data
     * or creating lookup tables from complex data structures.
     *
     * The callback receives both the value and its key, and should return an associative
     * array with exactly one key-value pair that becomes part of the new array.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     ['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
     *     ['id' => 2, 'name' => 'Jane', 'email' => 'jane@example.com']
     * ];
     *
     * // Create lookup table with ID as key and email as value
     * $lookup = Arrays::mapWithKeys($users, fn($user) => [$user['id'] => $user['email']]);
     * // Returns: [1 => 'john@example.com', 2 => 'jane@example.com']
     *
     * // Transform to associative array with custom key-value structure
     * $result = Arrays::mapWithKeys($users, fn($user) => [$user['name'] => $user['id']]);
     * // Returns: ['John' => 1, 'Jane' => 2]
     *
     * // Use both value and key in transformation
     * $data = ['a' => 10, 'b' => 20, 'c' => 30];
     * $result = Arrays::mapWithKeys($data, fn($value, $key) => [strtoupper($key) => $value * 2]);
     * // Returns: ['A' => 20, 'B' => 40, 'C' => 60]
     *
     * // Handle duplicate keys (later elements overwrite earlier ones)
     * $numbers = [1, 2, 3];
     * $result = Arrays::mapWithKeys($numbers, fn($num) => ['all' => $num]);
     * // Returns: ['all' => 3] (3 overwrites 1 and 2)
     * ```
     *
     * @param array $array The array to map to a new structure
     * @param callable $callback A function that receives ($value, $key) and returns an array with one key-value pair
     *  The callback has the signature `function (mixed $value, mixed $key): array`
     * @return array Returns a new array with the structure defined by the callback
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the callback doesn't return an array with exactly one element
     * @see \Phuture\Coherence\Arrays::map()
     * @see \Phuture\Coherence\Arrays::mapKeys()
     */
    public static function mapWithKeys(array $array, callable $callback): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $mapped = $callback($value, $key);

            // Allow null returns to filter out elements
            if ($mapped === null) {
                continue;
            }

            if (!is_array($mapped) || count($mapped) !== 1) {
                throw new InvalidArgumentException(
                    "Invalid Argument: Callback must return an array with exactly one key-value pair"
                );
            }

            $newKey = key($mapped);

            // Validate that the key is a valid PHP array key type
            if (!is_string($newKey) && !is_int($newKey)) {
                throw new InvalidArgumentException(
                    'Invalid Argument: Array keys must be strings or integers, '
                    . get_debug_type($newKey) . ' returned by callback'
                );
            }

            $result[$newKey] = current($mapped);
        }

        return $result;
    }

    /**
     * Calculates the median (middle value) of values in an array.
     *
     * This method finds the middle value in a sorted list of numbers. The median
     * is useful because it's not affected by extremely high or low values the way
     * an average is. Think of it like finding the value that splits your data in half.
     *
     * For an odd number of elements, the median is the middle value. For an even
     * number of elements, the median is the average of the two middle values.
     *
     * If the array is empty, this method returns null.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Odd number of elements - returns middle value
     * $numbers = [1, 3, 5];
     * $med = Arrays::median($numbers);
     *
     * // Returns: 3
     *
     * // Even number of elements - returns average of two middle values
     * $numbers = [1, 2, 3, 4];
     * $med = Arrays::median($numbers);
     *
     * // Returns: 2.5
     *
     * // Unsorted input gets sorted automatically
     * $numbers = [5, 1, 3, 2, 4];
     * $med = Arrays::median($numbers);
     *
     * // Returns: 3
     *
     * // Empty array returns null
     * $med = Arrays::median([]);
     *
     * // Returns: null
     * ```
     *
     * @param array $array The array containing numeric values
     * @return float|null The median value, or null if the array is empty
     * @see \Phuture\Coherence\Arrays::average()
     */
    public static function median(array $array): ?float
    {
        $count = count($array);

        if ($count === 0) {
            return null;
        }

        // Sort the array to find the middle value(s)
        $sorted = $array;
        sort($sorted);

        $middle = (int) floor(($count - 1) / 2);

        if ($count % 2 === 0) {
            // Even number of elements - average the two middle values
            return ($sorted[$middle] + $sorted[$middle + 1]) / 2;
        }

        // Odd number of elements - return the middle value
        return (float) $sorted[$middle];
    }

    /**
     * Combines multiple arrays into one.
     *
     * This method merges two or more arrays together into a single array. When merging,
     * numeric keys are renumbered starting from 0, while string keys are preserved. If
     * the same string key exists in multiple arrays, the later value overwrites the earlier one.
     * The recursive option allows deep merging of nested arrays.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array1 = ['a' => 'apple', 'b' => 'banana'];
     * $array2 = ['b' => 'blueberry', 'c' => 'cherry'];
     *
     * // Simple merge
     * $result = Arrays::merge($array1, $array2);
     * // Returns: ['a' => 'apple', 'b' => 'blueberry', 'c' => 'cherry']
     *
     * // Recursive merge
     * $config1 = ['db' => ['host' => 'localhost', 'port' => 3306]];
     * $config2 = ['db' => ['user' => 'admin']];
     * $result = Arrays::merge($config1, $config2);
     * // Returns: ['db' => ['host' => 'localhost', 'port' => 3306, 'user' => 'admin']]
     * ```
     *
     * @param array ...$arrays One or more arrays to merge together
     * @return array Returns a new merged array
     * @see \Phuture\Coherence\Arrays::join()
     * @see \Phuture\Coherence\Arrays::collapse()
     */
    public static function merge(...$arrays): array
    {
        if (count($arrays) < 2) {
            throw new InvalidArgumentException(
                "Invalid Argument: At least two non-empty arrays are required"
            );
        }

        return array_merge_recursive(...$arrays);
    }

    /**
     * Normalizes a multidimensional array by converting all objects to arrays.
     *
     * This method recursively processes an array and converts any objects (like stdClass)
     * into plain arrays. This is useful when you need to work with data that might contain
     * mixed object and array structures, such as JSON data that was decoded with object
     * conversion, or database results that return objects.
     *
     * The method produces the same result as using json_decode(json_encode($array), true)
     * but is more efficient and doesn't have the limitations of JSON encoding.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $obj = new stdClass();
     * $obj->name = 'John';
     * $obj->address = new stdClass();
     * $obj->address->city = 'NYC';
     *
     * $mixed = [
     *     'user' => $obj,
     *     'active' => true
     * ];
     *
     * $normalized = Arrays::normalize($mixed);
     *
     * // Returns: [
     * //     'user' => [
     * //         'name' => 'John',
     * //         'address' => ['city' => 'NYC']
     * //     ],
     * //     'active' => true
     * // ]
     * ```
     *
     * @param array $array The array to normalize, which may contain objects
     * @return array Returns a pure array with all objects converted to arrays
     * @see \Phuture\Coherence\Arrays::toObject()
     */
    public static function normalize(array $array): array
    {
        $result = [];
        self::normalizeRecursive($array, $result, 0);

        return $result;
    }

    /**
     * Flattens a multidimensional array into a single-level array using dot notation.
     *
     * This method takes a nested array (arrays within arrays) and converts it into a flat array
     * where the keys represent the path to each value using dots as separators. For example,
     * if you have a value at ['user']['name'], it becomes 'user.name' in the flattened array.
     *
     * Empty arrays are treated as leaf values and preserved in the output.
     *
     * This is useful for configuration arrays, deeply nested data structures, or when you need
     * to convert complex arrays into a simple list format.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $nested = [
     *     'name' => 'John',
     *     'address' => [
     *         'city' => 'NYC',
     *         'zip' => '10001'
     *     ]
     * ];
     * $flat = Arrays::notation($nested);
     *
     * // Returns: ['name' => 'John', 'address.city' => 'NYC', 'address.zip' => '10001']
     *
     * // Empty arrays are preserved
     * $data = ['key' => 'value', 'empty' => []];
     * $flat = Arrays::notation($data);
     * // Returns: ['key' => 'value', 'empty' => []]
     * ```
     *
     * @param array $array The multidimensional array to flatten
     * @param string $prefix Optional prefix to prepend to all keys (for internal recursion)
     * @return array Returns a flattened single-level array with dot notation keys
     * @see \Phuture\Coherence\Arrays::denote()
     */
    public static function notation(array $array, string $prefix = ''): array
    {
        $result = [];
        self::flattenToNotation($array, $prefix, $result);

        return $result;
    }

    /**
     * Creates a fluent wrapper for array manipulation with method chaining.
     *
     * This method wraps an array in a Types\Arrays instance, which enables fluent method chaining
     * for array operations. Instead of calling static methods one at a time, you can chain multiple
     * operations together and call get() or toArray() at the end to retrieve the final result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Using fluent chaining (chainable methods return arrays)
     * $result = Arrays::of([1, 2, 3, 4, 5])
     *     ->filter(fn($v) => $v > 2) // Returns array - chainable
     *     ->reverse() // Returns array - chainable
     *     ->values() // Returns array - chainable
     *     ->get();
     * // Returns: [5, 4, 3]
     *
     * // Equivalent to calling static methods individually:
     * $filtered = Arrays::filter([1, 2, 3, 4, 5], fn($v) => $v > 2);
     * $reversed = Arrays::reverse($filtered);
     * $result = Arrays::values($reversed);
     * // Alternative methods
     * $users = [
     *     ['name' => 'John', 'age' => 30],
     *     ['name' => 'Jane', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 35]
     * ];
     * // You can also use toArray to get the final result
     * $names = Arrays::of($users)
     *     ->column('name')
     *     ->toArray();
     * // Returns: ['John', 'Jane', 'Bob']
     *
     * // Or, call the object as a function to get the final result
     * $names = Arrays::of($users)
     *     ->column('name')();
     * // Returns: ['John', 'Jane', 'Bob']
     *
     * // Or, use the object as an array
     * $names = Arrays::of($users)
     *     ->column('name');
     * $name = $names[0];
     * // Returns 'John'
     * ```
     *
     * @param array $array The array to wrap for fluent operations
     * @return Type\Arrays A fluent wrapper instance that enables method chaining
     * @see \Phuture\Coherence\Type\Arrays For the fluent wrapper implementation
     */
    public static function of(array $array): Type\Arrays
    {
        return new Type\Arrays($array);
    }

    /**
     * Gets a subset of the items from the given array.
     *
     * This method returns a new array containing only the items whose keys
     * are specified in the $keys parameter. Keys that don't exist in the
     * original array will be ignored. This is useful when you need to extract
     * specific fields from a larger data structure.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $user = [
     *     'id' => 1,
     *     'name' => 'John Doe',
     *     'email' => 'john@example.com',
     *     'password' => 'secret',
     *     'created_at' => '2023-01-01'
     * ];
     * // Extract only specific fields
     * $safeUser = Arrays::only($user, ['id', 'name', 'email']);
     * // Result: ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']
     *
     * // Keys that don't exist are ignored
     * $partial = Arrays::only($user, ['id', 'name', 'nonexistent']);
     * // Result: ['id' => 1, 'name' => 'John Doe']
     *
     * // Empty keys array returns empty array
     * $empty = Arrays::only($user, []);
     * // Result: []
     * ```
     *
     * @param array $array The original array to extract items from
     * @param array $keys The list of keys to extract from the array
     * @return array A new array containing only the specified keys and their values
     */
    public static function only(array $array, array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        return self::intersectKeys($array, self::flip($keys));
    }

    /**
     * Pads an array to the specified length with a given value.
     *
     * This method fills an array to a specified length by adding elements with the given value.
     * If the length is positive, padding is added to the right (end) of the array. If negative,
     * padding is added to the left (beginning). The absolute value of the length determines the
     * final size of the array. If the length is smaller than or equal to the current array size,
     * no padding occurs.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Pad to the right
     * $array = [1, 2, 3];
     * $result = Arrays::pad($array, 5, 0);
     * // Returns: [1, 2, 3, 0, 0]
     *
     * // Pad to the left
     * $array = [1, 2, 3];
     * $result = Arrays::pad($array, -5, 0);
     * // Returns: [0, 0, 1, 2, 3]
     *
     * // No padding when length <= current size
     * $array = [1, 2, 3];
     * $result = Arrays::pad($array, 3, 0);
     * // Returns: [1, 2, 3]
     *
     * // Padding associative arrays
     * $array = ['name' => 'John', 'age' => 30];
     * $result = Arrays::pad($array, 4, null);
     * // Returns: ['name' => 'John', 'age' => 30, 0 => null, 1 => null]
     * ```
     *
     * @param array $array The input array to pad
     * @param int $length The desired length; positive pads right, negative pads left
     * @param mixed $value The value to use for padding
     * @return array Returns the padded array
     */
    public static function pad(array $array, int $length, mixed $value): array
    {
        return array_pad($array, $length, $value);
    }

    /**
     * Splits an array into two groups based on a callback function.
     *
     * This method separates items into those that pass a test and those that fail.
     * The first array in the returned pair contains all items where the callback
     * returns true, and the second array contains all items where it returns false.
     *
     * Think of it like sorting coins into two piles: one for heads and one for tails.
     * Both piles preserve which coins came from which position in the original pile.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [1, 2, 3, 4, 5, 6];
     *
     * // Partition into even and odd
     * [$even, $odd] = Arrays::partition($numbers, fn($n) => $n % 2 === 0);
     * // $even contains: [2, 4, 6]
     * // $odd contains: [1, 3, 5]
     *
     * // Partition associative array
     * $users = [
     *     'user1' => ['active' => true],
     *     'user2' => ['active' => false],
     *     'user3' => ['active' => true]
     * ];
     * [$active, $inactive] = Arrays::partition($users, fn($user) => $user['active']);
     * // $active contains: ['user1' => [...], 'user3' => [...]]
     * // $inactive contains: ['user2' => [...]]
     * ```
     *
     * @param array $array The array to partition.
     * @param callable $callback Function that returns true for the first array, false for the second.
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return array Returns an array with two elements: [passing_items, failing_items]
     * @see \Phuture\Coherence\Arrays::groupBy()
     * @see \Phuture\Coherence\Arrays::filter()
     */
    public static function partition(array $array, callable $callback): array
    {
        $passed = [];
        $failed = [];

        foreach ($array as $key => $item) {
            if ($callback($item, $key)) {
                $passed[$key] = $item;
            } else {
                $failed[$key] = $item;
            }
        }

        return [$passed, $failed];
    }

    /**
     * Prepends key-value pairs to an array.
     *
     * This method prepends new key-value pairs to the beginning of an array. If keys already exist,
     * they will be overwritten. The array is modified by reference.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John'];
     * Arrays::prepend($array, ['age' => 30, 'city' => 'NYC']);
     * // Result: ['age' => 30, 'city' => 'NYC', 'name' => 'John']
     *
     * $array = ['name' => 'John', 'age' => null];
     * Arrays::prepend($array, ['age' => 30, 'city' => 'NYC']);
     * // Result: ['age' => 30, 'city' => 'NYC', 'name' => 'John']
     *
     * $array = [];
     * Arrays::prepend($array, ['user' => 'demo', 'email' => 'example@example.com']);
     * // Result: ['user' => 'demo', 'email' => 'example@example.com']
     * ```
     *
     * @param array $array The array to add key-value pairs to (passed by reference)
     * @param array $items Associative array of key-value pairs to prepend
     * @see \Phuture\Coherence\Arrays::append()
     */
    public static function prepend(array &$array, array $items): void
    {
        if (empty($items)) {
            return;
        }

        // Check if we have string keys that need to be preserved
        $hasStringKeys = count(array_filter(array_keys($array), 'is_string')) > 0 ||
            count(array_filter(array_keys($items), 'is_string')) > 0;

        if (!$hasStringKeys) {
            // Fast path for numeric-only arrays: use array_unshift in reverse
            foreach (array_reverse($items, true) as $value) {
                array_unshift($array, $value);
            }

            return;
        }

        // For string keys: clear and rebuild the array in-place
        // This ensures the reference is properly updated
        $merged = $items + $array;
        $array = [];
        foreach ($merged as $key => $value) {
            $array[$key] = $value;
        }
    }

    /**
     * Calculates the product of the values in the array.
     *
     * This method multiplies all the numbers in an array together and returns the result.
     * If the array contains non-numeric values, they are treated as zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [2, 3, 4];
     * $result = Arrays::product($numbers);
     *
     * // Returns: 24 (2 * 3 * 4)
     * ```
     *
     * @param array $array The array containing values to multiply
     * @return int|float The product of all values in the array
     * @see \Phuture\Coherence\Arrays::sum()
     */
    public static function product(array $array): int|float
    {
        return array_product($array);
    }

    /**
     * Removes and returns the last element from the end of the array.
     *
     * This method removes the last element from an array and returns it. The array is modified
     * by reference, meaning the original array is shortened by one element. If the array is
     * empty, an exception is thrown. This is commonly used for implementing stack data structures
     * (LIFO - Last In, First Out) or removing the most recently added item.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Remove last element
     * $stack = ['first', 'second', 'third'];
     * $last = Arrays::pull($stack);
     * // $last contains: 'third'
     * // $stack is now: ['first', 'second']
     *
     * // With associative arrays
     * $data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $last = Arrays::pull($data);
     * // $last contains: 30
     * // $data is now: ['name' => 'John', 'email' => 'john@example.com']
     *
     * // Empty array throws exception
     * $empty = [];
     * $result = Arrays::pull($empty);
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to remove the last element from (passed by reference)
     * @return mixed Returns the last element
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException If the array is empty
     * @see \Phuture\Coherence\Arrays::push()
     * @see \Phuture\Coherence\Arrays::shift()
     */
    public static function pull(array &$array): mixed
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        return array_pop($array);
    }

    /**
     * Adds one or more elements to the end of an array.
     *
     * This method appends one or more values to the end of an array. The array is modified by
     * reference, meaning the original array grows in size. The method returns the new total
     * number of elements in the array. This is commonly used for implementing stack data
     * structures (LIFO - Last In, First Out) or building arrays dynamically.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Add single element
     * $stack = ['first', 'second'];
     * $count = Arrays::push($stack, 'third');
     * // $count is: 3
     * // $stack is now: ['first', 'second', 'third']
     *
     * // Add multiple elements
     * $items = ['apple'];
     * $count = Arrays::push($items, 'banana', 'cherry', 'date');
     * // $count is: 4
     * // $items is now: ['apple', 'banana', 'cherry', 'date']
     *
     * // With associative arrays (adds with numeric keys)
     * $data = ['name' => 'John'];
     * Arrays::push($data, 'extra value');
     * // $data is now: ['name' => 'John', 0 => 'extra value']
     * ```
     *
     * @param array $array The array to add elements to (passed by reference)
     * @param mixed ...$values One or more values to add to the end
     * @return int Returns the new number of elements in the array
     * @see \Phuture\Coherence\Arrays::pull()
     * @see \Phuture\Coherence\Arrays::unshift()
     */
    public static function push(array &$array, mixed ...$values): int
    {
        return array_push($array, ...$values);
    }

    /**
     * Returns a random value from an array.
     *
     * This method selects one element at random from an array and returns its value.
     * Each element has an equal probability of being selected. This is useful for
     * selecting random items from a list, implementing random features, or shuffling
     * data for testing purposes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Get random fruit
     * $fruits = ['apple', 'banana', 'cherry', 'date'];
     * $random = Arrays::random($fruits);
     * // Returns: one of the fruits (e.g., 'cherry')
     *
     * // With associative arrays
     * $colors = ['red' => '#FF0000', 'green' => '#00FF00', 'blue' => '#0000FF'];
     * $randomColor = Arrays::random($colors);
     * // Returns: one of the color codes (e.g., '#00FF00')
     *
     * // Single element array always returns that element
     * $single = ['only'];
     * $result = Arrays::random($single);
     * // Returns: 'only'
     * ```
     *
     * @param array $array The array to pick a random value from
     * @return mixed Returns a randomly selected value from the array
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When array is empty
     * @see \Phuture\Coherence\Arrays::randomKeys()
     * @see \Phuture\Coherence\Arrays::shuffle()
     */
    public static function random(array $array): mixed
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        $key = array_rand($array);

        return $array[$key];
    }

    /**
     * Picks one or more random keys from an array.
     *
     * This method selects one or more keys at random from an array and returns them. When
     * selecting a single key, it returns a string or integer. When selecting multiple keys,
     * it returns an array of keys. Each key has an equal probability of being selected, and
     * the same key will not be selected twice.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Get one random key (default)
     * $data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $randomKey = Arrays::randomKeys($data);
     * // Returns: one of the keys (e.g., 'email')
     *
     * // Get multiple random keys
     * $data = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5];
     * $randomKeys = Arrays::randomKeys($data, 3);
     * // Returns: an array of 3 keys (e.g., ['b', 'd', 'a'])
     *
     * // With numeric keys
     * $numbers = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
     * $keys = Arrays::randomKeys($numbers, 2);
     * // Returns: an array of 2 numeric keys (e.g., [20, 10])
     * ```
     *
     * @param array $array The array to pick random keys from
     * @param int $num The number of keys to select (default: 1)
     * @return string|int|array Returns a single key if $num is 1, or an array of keys if $num > 1
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When array is empty or num is less than 1 or greater than array size
     * @see \Phuture\Coherence\Arrays::random()
     */
    public static function randomKeys(array $array, int $num = 1): string|int|array
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        $arraySize = count($array);
        if ($num < 1 || $num > $arraySize) {
            throw new OutOfBoundsException(
                "Out Of Bounds: Number of keys must be between 1 and {$arraySize}"
            );
        }

        return array_rand($array, $num);
    }

    /**
     * Iteratively reduces an array to a single value using a callback function.
     *
     * This method processes each element in an array through a callback function to accumulate
     * a single result value. The callback receives two parameters: the accumulated result (carry)
     * and the current element. You can optionally provide an initial value to start the accumulation.
     * This is useful for summing values, building strings, flattening arrays, or any operation that
     * combines array elements into a single output.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Sum all numbers
     * $numbers = [1, 2, 3, 4, 5];
     * $sum = Arrays::reduce($numbers, fn($carry, $item) => $carry + $item, 0);
     * // Returns: 15
     *
     * // Concatenate strings
     * $words = ['Hello', 'beautiful', 'world'];
     * $sentence = Arrays::reduce($words, fn($carry, $word) => $carry . ' ' . $word, '');
     * // Returns: ' Hello beautiful world'
     *
     * // Build an associative array
     * $items = [['id' => 1, 'name' => 'Apple'], ['id' => 2, 'name' => 'Banana']];
     * $lookup = Arrays::reduce(
     *     $items,
     *     fn($carry, $item) => $carry + [$item['id'] => $item['name']],
     *     []
     * );
     * // Returns: [1 => 'Apple', 2 => 'Banana']
     *
     * // Calculate product
     * $numbers = [2, 3, 4];
     * $product = Arrays::reduce($numbers, fn($carry, $item) => $carry * $item, 1);
     * // Returns: 24
     * ```
     *
     * @param array $array The array to reduce
     * @param callable $callback Function that receives ($carry, $item) and returns the new carry value
     *  The callback has the signature `function (mixed $carry, mixed $item): mixed`
     * @param mixed $initial Optional initial value for the carry (default: null)
     * @return mixed Returns the final accumulated value
     * @see \Phuture\Coherence\Arrays::map()
     * @see \Phuture\Coherence\Arrays::filter()
     */
    public static function reduce(array $array, callable $callback, mixed $initial = null): mixed
    {
        return array_reduce($array, $callback, $initial);
    }

    /**
     * Removes a key-value pair from an array.
     *
     * This method removes a key from an array, including deeply nested keys by passing
     * an array path. The array is modified by reference, meaning the original array is
     * changed directly.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $data = [
     *     'config' => [
     *         'database' => [
     *             'host' => 'localhost',
     *             'port' => 3306
     *         ]
     *     ],
     *     'debug' => true
     * ];
     *
     * // Remove simple key
     * Arrays::remove($data, 'debug');
     * // Result: ['config' => [...]]
     *
     * // Remove nested key using array path
     * Arrays::remove($data, ['config', 'database', 'port']);
     * // Result: ['config' => ['database' => ['host' => 'localhost']]]
     * ```
     *
     * @param array $array The array to remove the key from (passed by reference)
     * @param string|int|array $key The key to remove (string/int for simple key, array for nested path)
     */
    public static function remove(array &$array, string|int|array $key): void
    {
        $path = (array) $key;
        $lastKey = array_pop($path);
        $current = &$array;

        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return;
            }
            $current = &$current[$segment];
        }

        unset($current[$lastKey]);
    }

    /**
     * Renames a key in an array.
     *
     * This method renames an existing key to a new name while preserving the value and key order.
     * The array is modified by reference. If the old key doesn't exist, an exception is thrown.
     * For nested arrays, you can pass an array path where the last element is the key to rename.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $data = [
     *     'first_name' => 'John',
     *     'last_name' => 'Doe',
     *     'age' => 30
     * ];
     *
     * // Rename simple key
     * Arrays::rename($data, 'first_name', 'firstName');
     * // Result: ['firstName' => 'John', 'last_name' => 'Doe', 'age' => 30]
     *
     * // Rename nested key using array path
     * $config = [
     *     'database' => [
     *         'db_host' => 'localhost',
     *         'db_port' => 3306
     *     ]
     * ];
     * Arrays::rename($config, ['database', 'db_host'], 'host');
     * // Result: ['database' => ['host' => 'localhost', 'db_port' => 3306]]
     *
     * // Trying to rename non-existent key throws exception
     * Arrays::rename($data, 'missing', 'new');
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array containing the key to rename (passed by reference)
     * @param string|int|array $oldKey The current key name (string/int for simple key, array for nested path)
     * @param string|int $newKey The new key name
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When the old key doesn't exist
     */
    public static function rename(array &$array, string|int|array $oldKey, string|int $newKey): void
    {
        $path = (array) $oldKey;
        $keyToRename = array_pop($path);
        $current = &$array;

        // Navigate to the parent array containing the key to rename
        foreach ($path as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                throw new OutOfBoundsException(
                    "Out Of Bounds: Missing item in array"
                );
            }
            $current = &$current[$segment];
        }

        // Check if the old key exists
        if (!array_key_exists($keyToRename, $current)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: Missing item in array"
            );
        }

        // Preserve key order by rebuilding the array
        $result = [];
        foreach ($current as $key => $value) {
            if ($key === $keyToRename) {
                $result[$newKey] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        $current = $result;
    }

    /**
     * Replaces elements from passed arrays into the first array.
     *
     * This method replaces values in the first array with values from subsequent arrays based on
     * matching keys. Elements with matching keys in later arrays overwrite those in earlier arrays.
     * When recursive mode is enabled, the method will also traverse nested arrays and perform
     * replacement operations recursively. This is useful for merging configuration arrays, updating
     * settings, or applying default values while preserving structure.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic replacement
     * $base = ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'];
     * $replacement = ['b' => 'blueberry', 'c' => 'coconut'];
     * $result = Arrays::replace($base, false, $replacement);
     * // Returns: ['a' => 'apple', 'b' => 'blueberry', 'c' => 'coconut']
     *
     * // Recursive replacement
     * $base = ['user' => ['name' => 'John', 'email' => 'john@example.com']];
     * $replacement = ['user' => ['email' => 'john.doe@example.com']];
     * $result = Arrays::replace($base, true, $replacement);
     * // Returns: ['user' => ['name' => 'John', 'email' => 'john.doe@example.com']]
     *
     * // Multiple replacement arrays
     * $base = ['a' => 1, 'b' => 2];
     * $result = Arrays::replace($base, false, ['b' => 3], ['c' => 4]);
     * // Returns: ['a' => 1, 'b' => 3, 'c' => 4]
     * ```
     *
     * @param array $array The base array whose elements will be replaced
     * @param bool $recursive Whether to recursively replace nested arrays (default: false)
     * @param array ...$replacements One or more arrays containing replacement values
     * @return array Returns the modified array with replaced values
     */
    public static function replace(array $array, bool $recursive = false, array ...$replacements): array
    {
        if ($recursive) {
            return array_replace_recursive($array, ...$replacements);
        } else {
            return array_replace($array, ...$replacements);
        }
    }

    /**
     * Returns an array with elements in reverse order.
     *
     * This method reverses the order of elements in an array. The first element becomes the last,
     * and the last element becomes the first. By default, numeric keys are renumbered starting from 0,
     * while string keys are always preserved. Set the preserveKeys parameter to true to maintain the
     * original numeric key associations.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Preserves numeric keys (default behavior)
     * $array = ['first', 'second', 'third'];
     * $result = Arrays::reverse($array);
     * // Returns: [2 => 'third', 1 => 'second', 0 => 'first']
     *
     * // Preserves string keys (default behavior)
     * $array = ['a' => 'apple', 'b' => 'banana', 'c' => 'cherry'];
     * $result = Arrays::reverse($array);
     * // Returns: ['c' => 'cherry', 'b' => 'banana', 'a' => 'apple']
     *
     * // Without key preservation (reindexes all keys)
     * $array = ['first', 'second', 'third'];
     * $result = Arrays::reverse($array, false);
     * // Returns: ['third', 'second', 'first']
     *
     * // Without key preservation (mixed keys)
     * $array = [0 => 'zero', 'name' => 'John', 1 => 'one'];
     * $result = Arrays::reverse($array, false);
     * // Returns: ['one', 'John', 'zero']
     * ```
     *
     * @param array $array The array to reverse
     * @param bool $preserveKeys Whether to preserve numeric keys (default: true)
     * @return array Returns a new array with elements in reverse order
     */
    public static function reverse(array $array, bool $preserveKeys = true): array
    {
        if (!$preserveKeys) {
            $array = array_values($array);
        }

        return array_reverse($array, $preserveKeys);
    }

    /**
     * Searches the array for a given value and returns the first corresponding key if successful.
     *
     * This method looks through an array for a specific value and returns the key of the first
     * match found. If the value is not found, it returns false. By default, it uses loose comparison,
     * but you can enable strict comparison to check both value and type. This is useful for finding
     * the position or key of a value in an array.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic search
     * $fruits = ['apple', 'banana', 'cherry', 'date'];
     * $key = Arrays::search($fruits, 'cherry');
     * // Returns: 2
     *
     * // With associative arrays
     * $data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $key = Arrays::search($data, 'john@example.com');
     * // Returns: 'email'
     *
     * // Value not found
     * $key = Arrays::search($fruits, 'mango');
     * // Returns: false
     *
     * // Loose vs strict comparison
     * $numbers = [1, 2, 3, '4', 5];
     * $key = Arrays::search($numbers, 4); // Loose comparison
     * // Returns: 3 (finds '4' as a string)
     * $key = Arrays::search($numbers, 4, true); // Strict comparison
     * // Returns: false (4 !== '4')
     * ```
     *
     * @param array $array The array to search
     * @param mixed $needle The value to search for
     * @param bool $strict Whether to use strict comparison (default: false)
     * @return int|string|false Returns the key of the first match, or false if not found
     * @see \Phuture\Coherence\Arrays::find()
     */
    public static function search(array $array, mixed $needle, bool $strict = false): int|string|false
    {
        return array_search($needle, $array, $strict);
    }

    /**
     * Removes and returns the first element from the beginning of the array.
     *
     * This method removes the first element from an array and returns it. The array is modified
     * by reference, meaning the original array is shortened by one element and all remaining
     * elements are shifted down. Numeric keys are re-indexed starting from 0, while string keys
     * remain unchanged. If the array is empty, it returns null. This is commonly used for
     * implementing queue data structures (FIFO - First In, First Out).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Remove first element
     * $queue = ['first', 'second', 'third'];
     * $first = Arrays::shift($queue);
     * // $first contains: 'first'
     * // $queue is now: ['second', 'third'] (reindexed to [0 => 'second', 1 => 'third'])
     *
     * // With associative arrays
     * $data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $first = Arrays::shift($data);
     * // $first contains: 'John'
     * // $data is now: ['email' => 'john@example.com', 'age' => 30]
     *
     * // Empty array throws exception
     * $empty = [];
     * $result = Arrays::shift($empty);
     * // Throws: OutOfBoundsException
     * ```
     *
     * @param array $array The array to remove the first element from (passed by reference)
     * @return mixed Returns the first element
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException When array is empty
     * @see \Phuture\Coherence\Arrays::unshift()
     * @see \Phuture\Coherence\Arrays::pull()
     */
    public static function shift(array &$array): mixed
    {
        if (empty($array)) {
            throw new OutOfBoundsException(
                "Out Of Bounds: \$array must be a non-empty array"
            );
        }

        return array_shift($array);
    }

    /**
     * Shuffles an array randomly.
     *
     * This method randomly rearranges the order of elements in an array. Each time
     * you run it, the elements will be in a different random order. The original
     * array keys are replaced with sequential numeric keys starting from 0.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic card shuffle
     * $cards = ['Ace', 'King', 'Queen', 'Jack'];
     * Arrays::shuffle($cards);
     * // $cards might now be: ['Queen', 'Ace', 'Jack', 'King']
     *
     * // Practical use case: random quiz questions
     * $questions = [
     *     'What is 2 + 2?',
     *     'What is the capital of France?',
     *     'Who wrote Romeo and Juliet?',
     *     'What is H2O?'
     * ];
     * Arrays::shuffle($questions);
     * // Now questions appear in random order for each quiz attempt
     *
     * // Creating a random password from character sets
     * $chars = ['a', 'b', 'c', '1', '2', '3', '!', '@', '#'];
     * Arrays::shuffle($chars);
     * $password = implode('', array_slice($chars, 0, 6));
     * // Generates random 6-character password
     * ```
     *
     * @param array $array The array to shuffle (passed by reference)
     * @return bool Returns true on success, false on failure
     */
    public static function shuffle(array &$array): bool
    {
        return shuffle($array);
    }

    /**
     * Extracts a slice of an array.
     *
     * This method returns a portion of an array starting at a specified offset and continuing for
     * a given length. The original array is not modified. You can use negative offsets to count
     * from the end of the array. By default, numeric keys are re-indexed starting from 0, but
     * you can preserve the original keys by setting preserveKeys to true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['a', 'b', 'c', 'd', 'e'];
     *
     * // Extract middle portion
     * $slice = Arrays::slice($array, 2, 2);
     * // Returns: ['c', 'd']
     *
     * // Start from position 1, take rest of array
     * $slice = Arrays::slice($array, 1);
     * // Returns: ['b', 'c', 'd', 'e']
     *
     * // Negative offset (count from end)
     * $slice = Arrays::slice($array, -2);
     * // Returns: ['d', 'e']
     *
     * // Preserve keys
     * $slice = Arrays::slice($array, 1, 3, true);
     * // Returns: [1 => 'b', 2 => 'c', 3 => 'd']
     *
     * // With associative arrays (keys always preserved)
     * $data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $slice = Arrays::slice($data, 1, 1);
     * // Returns: ['email' => 'john@example.com']
     * ```
     *
     * @param array $array The input array to extract from
     * @param int $offset The starting position (negative counts from end)
     * @param int|null $length Number of elements to extract (default: null for all remaining)
     * @param bool $preserve_keys Whether to preserve numeric keys (default: false)
     * @return array Returns the extracted portion of the array
     * @see \Phuture\Coherence\Arrays::splice()
     */
    public static function slice(array $array, int $offset, ?int $length = null, bool $preserve_keys = false): array
    {
        return array_slice($array, $offset, $length, $preserve_keys);
    }

    /**
     * Checks if at least one array element satisfies a callback function.
     *
     * This method tests elements in an array against a condition you provide.
     * It returns true if AT LEAST ONE element passes the test. Think of it like
     * checking if anyone in a group has completed their homework.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [1, 3, 5, 8];
     * $hasEven = Arrays::any($numbers, fn($n) => $n % 2 === 0);
     * // Returns: true (8 is even)
     *
     * $ages = [16, 15, 14];
     * $hasAdult = Arrays::any($ages, fn($age) => $age >= 18);
     * // Returns: false (none are 18 or older)
     * ```
     *
     * @param array $array The array whose elements to test.
     * @param callable $callback A function that receives each element and returns true if it passes the test
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return bool Returns true if ANY element passes the callback test, false if none pass
     * @see \Phuture\Coherence\Arrays::every()
     */
    public static function some(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sorts an array in ascending or descending order with optional index preservation.
     *
     * This method arranges array values from lowest to highest (ascending) or highest
     * to lowest (descending). You can provide a custom comparison function to define
     * your own sorting logic. Note that array keys are not preserved - elements are
     * re-indexed with sequential numeric keys starting from 0.
     *
     * You can optionally provide a custom comparison function as the last parameter.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [5, 2, 8, 1, 9];
     * Arrays::sort($numbers);
     * // $numbers is now: [1, 2, 5, 8, 9]
     *
     * // Sort in reverse (descending) order
     * Arrays::sort($numbers, true);
     * // $numbers is now: [9, 8, 5, 2, 1]
     *
     * // Sort with custom callback (case-insensitive string comparison)
     * $words = ['Apple', 'banana', 'Cherry', 'date'];
     * Arrays::sort($words, false, fn($a, $b) => strcasecmp($a, $b));
     * // $words is now: ['Apple', 'banana', 'Cherry', 'date']
     *
     * // Sort objects by property
     * $users = [
     *     (object)['name' => 'John', 'age' => 30],
     *     (object)['name' => 'Jane', 'age' => 25],
     *     (object)['name' => 'Bob', 'age' => 35]
     * ];
     * Arrays::sort($users, false, fn($a, $b) => $a->age <=> $b->age);
     * // Sorted by age: Jane (25), John (30), Bob (35)
     *
     * // Sort with callback in reverse
     * Arrays::sort($users, true, fn($a, $b) => strcmp($a->name, $b->name));
     * // Sorted by name descending: John, Jane, Bob
     * ```
     *
     * @param array $array The array to sort (passed by reference)
     * @param bool $reverse Whether to sort in descending order (default: false)
     * @param callable|null $callback Optional custom comparison function
     * @return bool Returns true on success, false on failure
     * @see \Phuture\Coherence\Arrays::sortKeys()
     * @see \Phuture\Coherence\Arrays::sortAssoc()
     * @see \Phuture\Coherence\Arrays::sortNatural()
     * @see \Phuture\Coherence\Arrays::sortMultidimensional()
     */
    public static function sort(
        array &$array,
        bool $reverse = false,
        ?callable $callback = null
    ): bool {
        if (!is_null($callback) && is_callable($callback)) {
            if ($reverse) {
                return usort($array, fn ($a, $b) => $callback($b, $a));
            }

            return usort($array, $callback);
        }

        return $reverse ? rsort($array) : sort($array);
    }

    /**
     * Sorts an array in ascending or descending order while maintaining index association.
     *
     * This method arranges array values from lowest to highest (ascending) or highest
     * to lowest (descending) while keeping the original keys paired with their values.
     * Unlike the regular sort method, this preserves the relationship between keys and
     * values. You can provide a custom comparison function to define your own sorting logic.
     *
     * You can optionally provide a custom comparison function as the last parameter.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $scores = ['John' => 85, 'Alice' => 92, 'Bob' => 78];
     * Arrays::sortAssoc($scores);
     * // $scores is now: ['Bob' => 78, 'John' => 85, 'Alice' => 92]
     * // Keys are preserved with their values
     *
     * // Sort with custom callback (case-insensitive string comparison)
     * $names = ['john' => 'John', 'ALICE' => 'Alice', 'bob' => 'Bob'];
     * Arrays::sortAssoc($names, false, fn($a, $b) => strcasecmp($a, $b));
     * // $names is now: ['ALICE' => 'Alice', 'bob' => 'Bob', 'john' => 'John']
     *
     * // Sort objects by property while preserving keys
     * $users = [
     *     'user1' => (object)['name' => 'John', 'age' => 30],
     *     'user2' => (object)['name' => 'Jane', 'age' => 25],
     *     'user3' => (object)['name' => 'Bob', 'age' => 35]
     * ];
     * Arrays::sortAssoc($users, false, fn($a, $b) => $a->age <=> $b->age);
     * // Sorted by age: user2 => Jane (25), user1 => John (30), user3 => Bob (35)
     *
     * // Sort with callback in reverse
     * $prices = ['item1' => 100, 'item2' => 50, 'item3' => 75];
     * Arrays::sortAssoc($prices, true, fn($a, $b) => $a <=> $b);
     * // $prices is now: ['item1' => 100, 'item3' => 75, 'item2' => 50]
     * ```
     *
     * @param array $array The array to sort (passed by reference)
     * @param bool $reverse Whether to sort in descending order (default: false)
     * @param callable|null $callback Optional custom comparison function for values
     * @return bool Returns true on success, false on failure
     * @see \Phuture\Coherence\Arrays::sort()
     */
    public static function sortAssoc(
        array &$array,
        bool $reverse = false,
        ?callable $callback = null
    ): bool {
        if (!is_null($callback) && is_callable($callback)) {
            if ($reverse) {
                return uasort($array, fn ($a, $b) => $callback($b, $a));
            }

            return uasort($array, $callback);
        }

        return $reverse ? arsort($array) : asort($array);
    }

    /**
     * Sorts an array by a given key or multiple keys.
     *
     * This method sorts a multidimensional array (array of arrays or objects) by one or more keys.
     * You can sort by a single key, multiple keys for multi-level sorting, or provide a custom callback
     * to determine the sort value. The original keys are not preserved - the array is re-indexed.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Sort by a single key
     * $users = [
     *     ['name' => 'John', 'age' => 30],
     *     ['name' => 'Alice', 'age' => 25],
     *     ['name' => 'Bob', 'age' => 35]
     * ];
     * Arrays::sortBy($users, 'age');
     * // Result: [
     * //     ['name' => 'Alice', 'age' => 25],
     * //     ['name' => 'John', 'age' => 30],
     * //     ['name' => 'Bob', 'age' => 35]
     * // ]
     *
     * // Sort by multiple keys (age ascending, then name descending)
     * Arrays::sortBy($users, ['age', 'name']);
     * // First sorts by age, then for equal ages, sorts by name
     *
     * // Sort in descending order
     * Arrays::sortBy($users, 'age', true);
     * // Result: [
     * //     ['name' => 'Bob', 'age' => 35],
     * //     ['name' => 'John', 'age' => 30],
     * //     ['name' => 'Alice', 'age' => 25]
     * // ]
     *
     * // Sort array of objects by property
     * $products = [
     *     (object)['name' => 'Apple', 'price' => 1.50],
     *     (object)['name' => 'Banana', 'price' => 0.75],
     *     (object)['name' => 'Cherry', 'price' => 2.00]
     * ];
     * Arrays::sortBy($products, 'price');
     * // Sorted by price ascending
     *
     * // Sort with custom callback for complex sorting
     * $words = ['apple', 'Banana', 'CHERRY', 'date'];
     * Arrays::sortBy($words, fn($item) => strtolower($item));
     * // Case-insensitive sort
     * ```
     *
     * @param array $array The array to sort (passed by reference)
     * @param string|array|callable $criteria The key(s) to sort by, or a callback that returns the sort value
     *  - string: Single key name (e.g., 'age', 'name')
     *  - array: Multiple keys for multi-level sorting (e.g., ['age', 'name'])
     *  - callable: Function that receives ($item) and returns the sort value
     * @param bool $reverse Whether to sort in descending order (default: false)
     * @param int $flags Sort flags for natural sorting (optional, e.g., SORT_NATURAL)
     * @return bool Returns true on success, false on failure
     * @see \Phuture\Coherence\Arrays::sort()
     * @see \Phuture\Coherence\Arrays::sortAssoc()
     * @see \Phuture\Coherence\Arrays::sortKeys()
     */
    public static function sortBy(
        array &$array,
        string|array|callable $criteria,
        bool $reverse = false,
        int $flags = 0
    ): bool {
        $comparator = function ($a, $b) use ($criteria, $flags) {
            $criteriaArray = is_array($criteria) ? $criteria : [$criteria];

            foreach ($criteriaArray as $criterion) {
                // Get the values to compare
                if (is_callable($criterion)) {
                    $aValue = $criterion($a);
                    $bValue = $criterion($b);
                } else {
                    $aValue = is_array($a) ? ($a[$criterion] ?? null) : ($a->{$criterion} ?? null);
                    $bValue = is_array($b) ? ($b[$criterion] ?? null) : ($b->{$criterion} ?? null);
                }

                // Apply flags for natural string comparison
                if ($flags & SORT_NATURAL) {
                    $comparison = strnatcmp((string) $aValue, (string) $bValue);
                } elseif ($flags & SORT_FLAG_CASE) {
                    $comparison = strcasecmp((string) $aValue, (string) $bValue);
                } else {
                    $comparison = $aValue <=> $bValue;
                }

                // If values are equal, continue to next criterion
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        };

        if ($reverse) {
            return usort($array, fn ($a, $b) => $comparator($b, $a));
        }

        return usort($array, $comparator);
    }

    /**
     * Sorts an array by keys in ascending or descending order.
     *
     * This method arranges an array based on its keys rather than its values. Keys
     * are sorted from lowest to highest (ascending) or highest to lowest (descending).
     * The association between keys and values is maintained. You can provide a custom
     * comparison function to define your own sorting logic for the keys.
     *
     * You can optionally provide a custom comparison function as the last parameter.
     *
     * The callback for the comparison function has the signature `function (mixed $a, mixed $b): int`
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $ages = ['John' => 25, 'Alice' => 30, 'Bob' => 20];
     * Arrays::sortKeys($ages);
     * // $ages is now: ['Alice' => 30, 'Bob' => 20, 'John' => 25]
     *
     * // Sort keys in reverse order
     * $ages = ['John' => 25, 'Alice' => 30, 'Bob' => 20];
     * Arrays::sortKeys($ages, true);
     * // $ages is now: ['John' => 25, 'Bob' => 20, 'Alice' => 30]
     *
     * // Sort with custom callback (case-insensitive key comparison)
     * $data = ['name' => 'John', 'AGE' => 30, 'Email' => 'john@example.com'];
     * Arrays::sortKeys($data, false, fn($a, $b) => strcasecmp($a, $b));
     * // $data is now: ['AGE' => 30, 'Email' => 'john@example.com', 'name' => 'John']
     *
     * // Sort keys by length
     * $items = ['aaa' => 1, 'b' => 2, 'cc' => 3];
     * Arrays::sortKeys($items, false, fn($a, $b) => strlen($a) - strlen($b));
     * // $items is now: ['b' => 2, 'cc' => 3, 'aaa' => 1]
     *
     * // Sort numeric string keys as integers
     * $numbers = ['10' => 'ten', '2' => 'two', '1' => 'one'];
     * Arrays::sortKeys($numbers, false, fn($a, $b) => (int)$a - (int)$b);
     * // $numbers is now: ['1' => 'one', '2' => 'two', '10' => 'ten']
     * ```
     *
     * @param array $array The array to sort by keys (passed by reference)
     * @param bool $reverse Whether to sort in descending order (default: false)
     * @param callable|null $callback Optional custom comparison function for keys
     * @return bool Returns true on success, false on failure
     * @see \Phuture\Coherence\Arrays::sort()
     */
    public static function sortKeys(
        array &$array,
        bool $reverse = false,
        ?callable $callback = null,
    ): bool {
        if (!is_null($callback) && is_callable($callback)) {
            if ($reverse) {
                return uksort($array, fn ($a, $b) => $callback($b, $a));
            }

            return uksort($array, $callback);
        }

        return $reverse ? krsort($array) : ksort($array);
    }

    /**
     * Sorts an array using natural order algorithm.
     *
     * This method sorts strings in the way a human would naturally order them, which
     * is especially useful for sorting filenames or version numbers. For example, it
     * will sort "file2.txt" before "file10.txt" (whereas a regular sort would put
     * "file10.txt" first). Optionally ignore letter case when sorting.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $files = ['file10.txt', 'file2.txt', 'file1.txt', 'file20.txt'];
     * Arrays::sortNatural($files);
     *
     * // $files is now: ['file1.txt', 'file2.txt', 'file10.txt', 'file20.txt']
     * // Notice that file2.txt comes before file10.txt
     * ```
     *
     * @param array $array The array to sort (passed by reference)
     * @param bool $caseInsensitive Whether to ignore case when sorting (default: false)
     * @return bool Returns true on success, false on failure
     * @see \Phuture\Coherence\Arrays::sort()
     */
    public static function sortNatural(array &$array, bool $caseInsensitive = false): bool
    {
        return $caseInsensitive ? natcasesort($array) : natsort($array);
    }

    /**
     * Removes a portion of an array and optionally replaces it with new elements.
     *
     * This method removes elements from an array starting at a specified offset and continuing for
     * a given length, then optionally inserts replacement elements at that position. The array is
     * modified by reference. The method returns an array containing the removed elements. This is
     * useful for inserting, removing, or replacing elements in the middle of an array.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Remove elements
     * $array = ['a', 'b', 'c', 'd', 'e'];
     * $removed = Arrays::splice($array, 2, 2);
     * // $removed contains: ['c', 'd']
     * // $array is now: ['a', 'b', 'e']
     *
     * // Remove and replace
     * $array = ['a', 'b', 'c', 'd', 'e'];
     * $removed = Arrays::splice($array, 2, 2, ['X', 'Y', 'Z']);
     * // $removed contains: ['c', 'd']
     * // $array is now: ['a', 'b', 'X', 'Y', 'Z', 'e']
     *
     * // Insert without removing (length = 0)
     * $array = ['a', 'b', 'e'];
     * Arrays::splice($array, 2, 0, ['c', 'd']);
     * // $array is now: ['a', 'b', 'c', 'd', 'e']
     *
     * // Remove from position to end
     * $array = ['a', 'b', 'c', 'd', 'e'];
     * Arrays::splice($array, 2);
     * // $array is now: ['a', 'b']
     * ```
     *
     * @param array $array The array to modify (passed by reference)
     * @param int $offset The starting position (negative counts from end)
     * @param int|null $length Number of elements to remove (default: null for all remaining)
     * @param mixed $replacement Elements to insert at the offset position (default: empty array)
     * @return array Returns an array containing the removed elements
     * @see \Phuture\Coherence\Arrays::slice()
     */
    public static function splice(array &$array, int $offset, ?int $length = null, mixed $replacement = []): array
    {
        return array_splice($array, $offset, $length, $replacement);
    }

    /**
     * Splits an array into chunks of a specified size.
     *
     * This method divides an array into smaller arrays (chunks) of a specified length. The last
     * chunk may contain fewer elements if the array doesn't divide evenly. By default, numeric
     * keys are re-indexed within each chunk starting from 0, but you can preserve the original
     * keys by setting preserveKeys to true.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Basic chunking
     * $array = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
     * $chunks = Arrays::split($array, 3);
     * // Returns: [['a', 'b', 'c'], ['d', 'e', 'f'], ['g']]
     *
     * // Preserve keys
     * $array = [1 => 'a', 2 => 'b', 3 => 'c', 4 => 'd'];
     * $chunks = Arrays::split($array, 2, true);
     * // Returns: [[1 => 'a', 2 => 'b'], [3 => 'c', 4 => 'd']]
     *
     * // With associative arrays (keys always preserved)
     * $data = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30, 'city' => 'NYC'];
     * $chunks = Arrays::split($data, 2);
     * // Returns: [['name' => 'John', 'email' => 'john@example.com'], ['age' => 30, 'city' => 'NYC']]
     *
     * // Processing in batches
     * $items = range(1, 100);
     * $batches = Arrays::split($items, 25);
     * // Returns: 4 arrays of 25 items each
     * ```
     *
     * @param array $array The array to split into chunks
     * @param int $length The size of each chunk (must be greater than 0)
     * @param bool $preserveKeys Whether to preserve array keys (default: false)
     * @return array Returns a multidimensional array of chunks
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException If length is less than 1
     * @see \Phuture\Coherence\Arrays::join()
     */
    public static function split(array $array, int $length, bool $preserveKeys = false): array
    {
        if ($length < 1) {
            throw new InvalidArgumentException(
                "Invalid Argument: Length must be at least 1"
            );
        }

        return array_chunk($array, $length, $preserveKeys);
    }

    /**
     * Calculates the sum of values in an array.
     *
     * This method adds up all the numbers in an array and returns the total.
     * If the array contains non-numeric values, they are treated as zero.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $numbers = [1, 2, 3, 4, 5];
     * $total = Arrays::sum($numbers);
     *
     * // Returns: 15
     * ```
     *
     * @param array $array The array containing values to sum
     * @return int|float The sum of all values in the array
     * @see \Phuture\Coherence\Arrays::product()
     * @see \Phuture\Coherence\Arrays::reduce()
     */
    public static function sum(array $array): int|float
    {
        return array_sum($array);
    }

    /**
     * This method converts various input types into an array.
     *
     * It supports various data types to arrays using smart conversion rules.
     * It handles objects with toArray() or toJson() methods, JsonSerializable objects,
     * existing arrays, scalar values, and JSON strings.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // From object with toArray method
     * $obj = new class { public function toArray() { return ['a' => 1]; } };
     * $result = Arrays::toArray($obj);
     * // Returns: ['a' => 1]
     *
     * // From object with toJson method
     * $obj = new class { public function toJson() { return '{"b": 2}'; } };
     * $result = Arrays::toArray($obj);
     * // Returns: ['b' => 2]
     *
     * // From JsonSerializable
     * $obj = new class implements \JsonSerializable {
     *     public function jsonSerialize() { return ['c' => 3]; }
     * };
     * $result = Arrays::toArray($obj);
     * // Returns: ['c' => 3]
     *
     * // From existing array
     * $result = Arrays::toArray([1, 2, 3]);
     * // Returns: [1, 2, 3]
     *
     * // From scalar values
     * $result = Arrays::toArray('hello');
     * // Returns: ['hello']
     *
     * $result = Arrays::toArray(42);
     * // Returns: [42]
     *
     * // From null
     * $result = Arrays::toArray(null);
     * // Returns: []
     *
     * // From stdClass
     * $obj = new \stdClass();
     * $obj->name = 'John';
     * $result = Arrays::toArray($obj);
     * // Returns: ['name' => 'John']
     *
     * // From JSON string
     * $result = Arrays::toArray('{"name": "Jane", "age": 25}');
     * // Returns: ['name' => 'Jane', 'age' => 25]
     * ```
     *
     * @param mixed $value The value to convert to array
     * @return array Returns the converted array
     * @see \Phuture\Coherence\Arrays::toObject()
     */
    public static function toArray(mixed $value): array
    {
        // Handle null
        if ($value === null) {
            return [];
        }

        // Handle existing arrays
        if (is_array($value)) {
            return $value;
        }

        // Handle Arrayable objects
        if ($value instanceof Arrayable || (is_object($value) && method_exists($value, 'toArray'))) {
            return $value->toArray();
        }

        // Handle Jsonable objects
        if ($value instanceof Jsonable || (is_object($value) && method_exists($value, 'toJson'))) {
            return json_decode($value->toJson(), true);
        }

        // Handle WeakMap objects
        if ($value instanceof WeakMap) {
            return iterator_to_array($value, false);
        }

        // Handle Traversable objects
        if ($value instanceof Traversable) {
            return iterator_to_array($value);
        }

        // Handle JsonSerializable objects
        if ($value instanceof JsonSerializable) {
            return json_decode(json_encode($value), true);
        }

        // Handle objects
        if ($value instanceof stdClass) {
            return (array) $value;
        }

        // Handle JSON strings
        if (is_string($value)) {
            $decoded = json_decode($value, true, self::RECURSION_LIMIT);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        // Handle scalar values
        if (is_scalar($value)) {
            return [$value];
        }

        return [];
    }

    /**
     * Converts associative arrays to objects recursively, leaving lists untouched.
     *
     * This method transforms an associative array and all its nested associative arrays
     * into stdClass objects. List arrays (indexed arrays without string keys) are preserved
     * as arrays and not converted to objects. This is the opposite of the normalize() method,
     * which converts objects to arrays.
     *
     * This is useful when you need to work with object notation for accessing
     * nested data structures, especially when dealing with JSON data or configuration
     * that you want to access with arrow syntax (->) instead of bracket notation ([]).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Simple array to object
     * $array = ['name' => 'John', 'age' => 30];
     * $obj = Arrays::toObject($array);
     * // Returns: { name: "John", age: 30 }
     *
     * // Multidimensional array to objects
     * $data = [
     *     'user' => [
     *         'name' => 'Jane',
     *         'address' => [
     *             'street' => '123 Main St',
     *             'city' => 'NYC'
     *         ]
     *     ],
     *     'settings' => ['theme' => 'dark']
     * ];
     * $obj = Arrays::toObject($data);
     * // Returns:
     * // {
     * //     user: {
     * //         name: "Jane",
     * //         address: { street: "123 Main St", city: "NYC" }
     * //     },
     * //     settings: { theme: "dark" }
     * // }
     *
     * // Accessing properties
     * echo $obj->user->name; // Outputs: Jane
     * echo $obj->user->address->city; // Outputs: NYC
     *
     * // Lists are preserved as arrays
     * $data = [
     *     'user' => 'John',
     *     'tags' => ['php', 'arrays', 'objects']  // list array
     * ];
     * $obj = Arrays::toObject($data);
     * // Returns: { user: "John", tags: ["php", "arrays", "objects"] }
     * // Note: tags remains an array, not converted to object
     * ```
     *
     * @param array $array The array to convert to objects, which may contain nested arrays
     * @return object Returns a stdClass object with associative arrays converted to objects and lists preserved
     * @see \Phuture\Coherence\Arrays::toArray()
     * @see \Phuture\Coherence\Arrays::normalize()
     */
    public static function toObject(array $array): object
    {
        $result = new stdClass();
        self::toObjectRecursive($array, $result, 0);

        return $result;
    }

    /**
     * Removes duplicate values from an array.
     *
     * This method filters an array to keep only unique values, removing any duplicates.
     * If the same value appears multiple times, only the first occurrence is kept.
     * The array keys are preserved from the original array.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $colors = ['red', 'blue', 'red', 'green', 'blue'];
     * $uniqueColors = Arrays::unique($colors);
     *
     * // Returns: ['red', 'blue', 'green']
     * ```
     *
     * @param array $array The array to remove duplicates from
     * @param int $flags Optional sorting behavior flags for comparison (default: SORT_STRING)
     * @return array Returns an array with unique values
     */
    public static function unique(array $array, int $flags = SORT_STRING): array
    {
        return array_unique($array, $flags);
    }

    /**
     * Prepends one or more elements to the beginning of an array.
     *
     * This method adds one or more values to the start of an array. The array is modified by
     * reference, meaning the original array grows in size and all existing elements are shifted
     * up. The method returns the new total number of elements in the array. Numeric keys are
     * re-indexed starting from 0, while string keys remain unchanged. This is commonly used for
     * implementing queue data structures (FIFO - First In, First Out).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Add single element to beginning
     * $queue = ['second', 'third'];
     * $count = Arrays::unshift($queue, 'first');
     * // $count is: 3
     * // $queue is now: ['first', 'second', 'third']
     *
     * // Add multiple elements
     * $items = ['cherry'];
     * $count = Arrays::unshift($items, 'apple', 'banana');
     * // $count is: 3
     * // $items is now: ['apple', 'banana', 'cherry']
     *
     * // With associative arrays (string keys preserved, numeric reindexed)
     * $data = ['email' => 'john@example.com', 'age' => 30];
     * Arrays::unshift($data, 'John');
     * // $data is now: [0 => 'John', 'email' => 'john@example.com', 'age' => 30]
     * ```
     *
     * @param array $array The array to add elements to (passed by reference)
     * @param mixed ...$values One or more values to add to the beginning
     * @return int Returns the new number of elements in the array
     * @see \Phuture\Coherence\Arrays::shift()
     * @see \Phuture\Coherence\Arrays::push()
     */
    public static function unshift(array &$array, mixed ...$values): int
    {
        return array_unshift($array, ...$values);
    }

    /**
     * Returns all values from an array.
     *
     * This method extracts all values from an array and returns them as a new indexed array
     * with numeric keys starting from 0. This effectively removes all the original keys and
     * re-indexes the array sequentially.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $array = ['name' => 'John', 'email' => 'john@example.com', 'age' => 30];
     * $values = Arrays::values($array);
     * // Returns: ['John', 'john@example.com', 30]
     *
     * // With numeric keys
     * $numbers = [10 => 'ten', 20 => 'twenty', 30 => 'thirty'];
     * $values = Arrays::values($numbers);
     * // Returns: ['ten', 'twenty', 'thirty']
     *
     * // Already indexed array (no change)
     * $indexed = ['apple', 'banana', 'cherry'];
     * $values = Arrays::values($indexed);
     * // Returns: ['apple', 'banana', 'cherry']
     * ```
     *
     * @param array $array The array from which to extract values
     * @return array Returns an indexed array containing all values from the input array
     * @see \Phuture\Coherence\Arrays::keys()
     */
    public static function values(array $array): array
    {
        return array_values($array);
    }

    /**
     * Filters an array using a callback function.
     *
     * This method creates a new array containing only the elements that pass a test
     * you provide. It is an alias for the filter method with a clearer name for
     * predicate-based filtering scenarios.
     *
     * Use this method when you want to find items that match specific conditions,
     * like finding all products above a certain price or all users with a certain status.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     ['name' => 'John', 'age' => 25, 'active' => true],
     *     ['name' => 'Jane', 'age' => 17, 'active' => true],
     *     ['name' => 'Bob', 'age' => 30, 'active' => false]
     * ];
     *
     * // Find adults (age 18+)
     * $adults = Arrays::where($users, fn($user) => $user['age'] >= 18);
     * // Returns: [
     * //     ['name' => 'John', 'age' => 25, 'active' => true],
     * //     ['name' => 'Bob', 'age' => 30, 'active' => false]
     * // ]
     *
     * // Find active users
     * $active = Arrays::where($users, fn($user) => $user['active']);
     * // Returns: [
     * //     ['name' => 'John', 'age' => 25, 'active' => true],
     * //     ['name' => 'Jane', 'age' => 17, 'active' => true]
     * // ]
     * ```
     *
     * @param array $array The array to filter.
     * @param callable $callback Function that tests each element, returns true to keep it
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return array Returns a new array containing only the elements that pass the test
     * @see \Phuture\Coherence\Arrays::filter()
     * @see \Phuture\Coherence\Arrays::whereIn()
     * @see \Phuture\Coherence\Arrays::grep()
     */
    public static function where(array $array, callable $callback): array
    {
        return self::filter($array, $callback);
    }

    /**
     * Filters an array where a key's value is in a given list of values.
     *
     * This method filters an array to only include items where a specific key
     * has a value that matches one of the values you provide. This is useful
     * when you want to find items that belong to a certain category or match
     * any of several possible values.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * $users = [
     *     ['id' => 1, 'name' => 'John', 'role' => 'admin'],
     *     ['id' => 2, 'name' => 'Jane', 'role' => 'user'],
     *     ['id' => 3, 'name' => 'Bob', 'role' => 'admin'],
     *     ['id' => 4, 'name' => 'Alice', 'role' => 'moderator']
     * ];
     *
     * // Find admins and moderators
     * $privileged = Arrays::whereIn($users, 'role', ['admin', 'moderator']);
     * // Returns: [
     * //     ['id' => 1, 'name' => 'John', 'role' => 'admin'],
     * //     ['id' => 3, 'name' => 'Bob', 'role' => 'admin'],
     * //     ['id' => 4, 'name' => 'Alice', 'role' => 'moderator']
     * // ]
     * ```
     *
     * @param array $array The array to filter.
     * @param string $key The key to check in each array item.
     * @param array $values The list of values to match against.
     * @return array Returns a new array containing only items where the key's value is in the values list
     * @see \Phuture\Coherence\Arrays::where()
     * @see \Phuture\Coherence\Arrays::filter()
     */
    public static function whereIn(array $array, string $key, array $values): array
    {
        return self::filter($array, function ($item) use ($key, $values) {
            $value = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);

            return in_array($value, $values, true);
        });
    }

    /**
     * Wraps scalar elements by surrounding them with prefix and suffix strings.
     *
     * This method processes each element in the array and wraps only scalar values (strings,
     * integers, floats, booleans, null) by converting them to strings and surrounding them
     * with the specified prefix and suffix. Non-scalar values like arrays, objects, and
     * resources are left unchanged. The original array keys are preserved.
     *
     * This is useful for formatting output, adding HTML tags to text values, or preparing
     * strings for display while preserving complex data structures within the array.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Add HTML tags to strings
     * $colors = ['red', 'green', 'blue'];
     * $result = Arrays::wrap($colors, '<b>', '</b>');
     * // Returns: ['<b>red</b>', '<b>green</b>', '<b>blue</b>']
     *
     * // Mixed types - only scalars are wrapped
     * $mixed = ['text', 123, ['nested'], new stdClass(), true];
     * $result = Arrays::wrap($mixed, '[', ']');
     * // Returns: ['[text]', '[123]', ['nested'], stdClass object, '[1]']
     *
     * // Preserve keys
     * $data = ['a' => 'red', 'b' => 'green'];
     * $result = Arrays::wrap($data, '<<', '>>');
     * // Returns: ['a' => '<<red>>', 'b' => '<<green>>']
     *
     * // Numbers are converted to strings
     * $numbers = [1, 2, 3];
     * $result = Arrays::wrap($numbers, '(', ')');
     * // Returns: ['(1)', '(2)', '(3)']
     * ```
     *
     * @param array $array The array whose scalar elements to wrap
     * @param string $prefix The string to prepend to each scalar element (default: empty string)
     * @param string $suffix The string to append to each scalar element (default: empty string)
     * @return array Returns a new array with wrapped scalar elements and preserved keys
     */
    public static function wrap(array $array, string $prefix = '', string $suffix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_scalar($value) || is_null($value)) {
                $result[$key] = $prefix . ((string) $value) . $suffix;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Combines multiple arrays by pairing elements at the same index.
     *
     * This method takes multiple arrays and creates a new array where each element
     * is an array containing the corresponding elements from each input array at
     * that position. Think of it like zipping together two jacket halves - the
     * teeth from each side pair up at the same position.
     *
     * If the arrays have different lengths, the shortest length determines the
     * number of pairs produced. Extra elements in longer arrays are ignored.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Arrays;
     *
     * // Pair two arrays
     * $numbers = [1, 2, 3];
     * $letters = ['a', 'b', 'c'];
     * $zipped = Arrays::zip($numbers, $letters);
     * // Returns: [[1, 'a'], [2, 'b'], [3, 'c']]
     *
     * // Zip three arrays
     * $ids = [1, 2];
     * $names = ['John', 'Jane'];
     * $ages = [30, 25];
     * $zipped = Arrays::zip($ids, $names, $ages);
     * // Returns: [[1, 'John', 30], [2, 'Jane', 25]]
     *
     * // Different lengths - uses shortest
     * $a = [1, 2, 3, 4];
     * $b = ['a', 'b'];
     * $zipped = Arrays::zip($a, $b);
     * // Returns: [[1, 'a'], [2, 'b']]
     *
     * // Create associative arrays
     * $keys = ['name', 'age'];
     * $values = ['John', 30];
     * $zipped = Arrays::zip($keys, $values);
     * // Returns: [['name', 'John'], ['age', 30]]
     * ```
     *
     * @param array ...$arrays Variable number of arrays to zip together
     * @return array Returns an array of paired elements from each input array
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException If fewer than two arrays are provided
     * @see \Phuture\Coherence\Arrays::unzip()
     */
    public static function zip(array ...$arrays): array
    {
        if (count($arrays) < 2) {
            throw new InvalidArgumentException(
                'Invalid Argument: At least two arrays are required for zip operation'
            );
        }

        // Convert all arrays to numeric-indexed arrays
        $arrays = array_map('array_values', $arrays);

        // Find the shortest array length
        $length = min(array_map('count', $arrays));

        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $pair = [];
            foreach ($arrays as $array) {
                $pair[] = $array[$i];
            }
            $result[] = $pair;
        }

        return $result;
    }

    /**
     * Recursively flattens a multidimensional array using dot notation keys.
     *
     * This private helper method traverses through nested arrays, building dot-separated
     * keys that represent the path to each value. For example, ['user' => ['name' => 'John']]
     * becomes ['user.name' => 'John']. The method protects against infinite recursion by
     * enforcing the RECURSION_LIMIT constant.
     *
     * @param array $array The multidimensional array to flatten.
     * @param string $prefix The current key prefix for nested elements (starts empty).
     * @param array $result The result array passed by reference where flattened key-value pairs are stored.
     * @param int $depth Current recursion depth to prevent stack overflow.
     * @throws \Phuture\Coherence\Exception\LogicException When recursion depth exceeds RECURSION_LIMIT to prevent stack overflow.
     */
    private static function flattenToNotation(array $array, string $prefix, array &$result, int $depth = 0): void
    {
        if ($depth >= self::RECURSION_LIMIT) {
            throw new LogicException(
                "Limit Exceeded: Recursion depth exceeded limit of " . self::RECURSION_LIMIT
            );
        }

        foreach ($array as $key => $value) {
            // Build the new key
            $newKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            // Check if value is a non-empty array that should be flattened
            if (is_array($value) && $value !== []) {
                self::flattenToNotation($value, $newKey, $result, $depth + 1);
            } else {
                // Keep scalar values as-is
                $result[$newKey] = $value;
            }
        }
    }

    /**
     * Recursively normalizes an array by converting all objects to arrays.
     *
     * This private helper method traverses through nested arrays and objects, converting
     * any objects to their array representation while preserving the overall structure.
     * This ensures that the entire data structure becomes a pure multidimensional array.
     * The method protects against infinite recursion by enforcing the RECURSION_LIMIT constant.
     *
     * @param array $array The array to normalize (may contain objects).
     * @param array $result The result array passed by reference containing only array values.
     * @param int $depth Current recursion depth to prevent stack overflow.
     * @throws \Phuture\Coherence\Exception\LogicException When recursion depth exceeds RECURSION_LIMIT to prevent stack overflow.
     */
    private static function normalizeRecursive(array $array, array &$result, int $depth = 0): void
    {
        if ($depth >= self::RECURSION_LIMIT) {
            throw new LogicException(
                "Limit Exceeded: Recursion depth exceeded limit of " . self::RECURSION_LIMIT
            );
        }

        foreach ($array as $key => $value) {
            if (is_object($value)) {
                // Convert object to array and recursively normalize
                $result[$key] = [];
                self::normalizeRecursive((array) $value, $result[$key], $depth + 1);
            } elseif (is_array($value)) {
                // Recursively normalize nested arrays
                $result[$key] = [];
                self::normalizeRecursive($value, $result[$key], $depth + 1);
            } else {
                // Keep scalar values as-is
                $result[$key] = $value;
            }
        }
    }

    /**
     * Recursively converts arrays to stdClass objects.
     *
     * This private helper method transforms a multidimensional array into a nested
     * object structure where each array becomes a stdClass instance. This is useful
     * for converting array-based data structures into object-based ones while preserving
     * the nested hierarchy. The method protects against infinite recursion by enforcing
     * the RECURSION_LIMIT constant.
     *
     * @param array $array The array to convert (may contain nested arrays).
     * @param stdClass $result The result object passed by reference containing the converted structure.
     * @param int $depth Current recursion depth to prevent stack overflow.
     * @throws \Phuture\Coherence\Exception\LogicException When recursion depth exceeds RECURSION_LIMIT to prevent stack overflow.
     */
    private static function toObjectRecursive(array $array, stdClass &$result, int $depth = 0): void
    {
        if ($depth >= self::RECURSION_LIMIT) {
            throw new LogicException(
                "Limit Exceeded: Recursion depth exceeded limit of " . self::RECURSION_LIMIT
            );
        }

        foreach ($array as $key => $value) {
            if ($value instanceof stdClass) {
                $value = (array) $value;
            }

            if (is_array($value) && ! self::isList($value)) {
                // Recursively convert nested arrays to objects
                $result->{$key} = new stdClass();
                self::toObjectRecursive($value, $result->{$key}, $depth + 1);
            } else {
                // Keep scalar values
                $result->{$key} = $value;
            }
        }
    }
}
