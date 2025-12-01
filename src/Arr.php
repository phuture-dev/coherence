<?php

namespace Advandz\Kernel;

use Advandz\Kernel\Class\StaticClass;

/**
 * Array utility class providing consistent wrappers around native PHP array functions.
 *
 * This class offers static methods for comprehensive array operations including searching,
 * sorting, manipulation, and transformation. All methods follow camelCase naming conventions
 * and provide a clean, object-oriented interface to PHP's native array functions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class Arr extends StaticClass
{
    /**
     * Checks if a value exists in an array.
     *
     * Provides a consistent wrapper around the native function in_array.
     *
     * @param array $array The array to search in
     * @param mixed $value The value to search for
     * @param bool $strict Whether to use strict comparison (default: false)
     * @return bool Returns true if the value is found, false otherwise
     * @see https://www.php.net/manual/en/function.in-array.php
     */
    public static function contains(array $array, mixed $value, bool $strict = false): bool
    {
        return in_array($value, $array, $strict);
    }

    /**
     * Checks if a key exists in an array.
     *
     * Provides a consistent wrapper around the native function array_key_exists.
     *
     * @param array $array The array to search in
     * @param string|int $key The key to check for
     * @return bool Returns true if the key exists, false otherwise
     * @see https://www.php.net/manual/en/function.array-key-exists.php
     */
    public static function containsKey(array $array, string|int $key): bool
    {
        return array_key_exists($key, $array);
    }

    /**
     * Computes the intersection of arrays using keys for comparison.
     *
     * Provides a consistent wrapper around the native function array_intersect_key.
     *
     * @param array $array The array with keys to check
     * @param array ...$arrays Arrays to compare keys against
     * @return array Returns an array containing all entries whose keys are present in all arguments
     * @see https://www.php.net/manual/en/function.array-intersect-key.php
     */
    public static function intersectKeys(array $array, array ...$arrays): array
    {
        return array_intersect_key($array, ...$arrays);
    }

    /**
     * Sorts an array in ascending order.
     *
     * Provides a consistent wrapper around the native function sort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param int $flags Sorting type flags (default: SORT_REGULAR)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.sort.php
     */
    public static function sort(array &$array, int $flags = SORT_REGULAR): bool
    {
        return sort($array, $flags);
    }

    /**
     * Sorts an array in descending order.
     *
     * Provides a consistent wrapper around the native function rsort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param int $flags Sorting type flags (default: SORT_REGULAR)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.rsort.php
     */
    public static function sortReverse(array &$array, int $flags = SORT_REGULAR): bool
    {
        return rsort($array, $flags);
    }

    /**
     * Sorts an array by keys in ascending order.
     *
     * Provides a consistent wrapper around the native function ksort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param int $flags Sorting type flags (default: SORT_REGULAR)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.ksort.php
     */
    public static function sortKeys(array &$array, int $flags = SORT_REGULAR): bool
    {
        return ksort($array, $flags);
    }

    /**
     * Sorts an array by keys in descending order.
     *
     * Provides a consistent wrapper around the native function krsort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param int $flags Sorting type flags (default: SORT_REGULAR)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.krsort.php
     */
    public static function sortKeysReverse(array &$array, int $flags = SORT_REGULAR): bool
    {
        return krsort($array, $flags);
    }

    /**
     * Sorts an array in ascending order and maintains index association.
     *
     * Provides a consistent wrapper around the native function asort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param int $flags Sorting type flags (default: SORT_REGULAR)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.asort.php
     */
    public static function sortAssoc(array &$array, int $flags = SORT_REGULAR): bool
    {
        return asort($array, $flags);
    }

    /**
     * Sorts an array in descending order and maintains index association.
     *
     * Provides a consistent wrapper around the native function arsort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param int $flags Sorting type flags (default: SORT_REGULAR)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.arsort.php
     */
    public static function sortAssocReverse(array &$array, int $flags = SORT_REGULAR): bool
    {
        return arsort($array, $flags);
    }

    /**
     * Sorts an array using natural order algorithm.
     *
     * Provides a consistent wrapper around the native functions natsort and natcasesort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param bool $case_insensitive Whether to use case-insensitive sorting (default: false)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.natsort.php
     * @see https://www.php.net/manual/en/function.natcasesort.php
     */
    public static function sortNatural(array &$array, bool $case_insensitive = false): bool
    {
        return $case_insensitive ? natcasesort($array) : natsort($array);
    }

    /**
     * Sorts an array using a user-defined comparison function.
     *
     * Provides a consistent wrapper around the native function usort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param callable $callback The comparison function
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.usort.php
     */
    public static function sortUser(array &$array, callable $callback): bool
    {
        return usort($array, $callback);
    }

    /**
     * Sorts an array by keys using a user-defined comparison function.
     *
     * Provides a consistent wrapper around the native function uksort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param callable $callback The comparison function
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.uksort.php
     */
    public static function sortKeysUser(array &$array, callable $callback): bool
    {
        return uksort($array, $callback);
    }

    /**
     * Sorts an array using a user-defined comparison function and maintains index association.
     *
     * Provides a consistent wrapper around the native function uasort.
     *
     * @param array $array The array to sort (passed by reference)
     * @param callable $callback The comparison function
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.uasort.php
     */
    public static function sortAssocUser(array &$array, callable $callback): bool
    {
        return uasort($array, $callback);
    }

    /**
     * Returns a random value from an array.
     *
     * Provides a wrapper around array_rand that returns the value instead of the key.
     *
     * @param array $array The array to pick from
     * @return mixed Returns a random value from the array
     * @see https://www.php.net/manual/en/function.array-rand.php
     */
    public static function random(array $array): mixed
    {
        $key = array_rand($array);

        return $array[$key];
    }

    /**
     * Picks one or more random keys from an array.
     *
     * Provides a consistent wrapper around the native function array_rand.
     *
     * @param array $array The array to pick from
     * @param int $num Number of keys to pick (default: 1)
     * @return string|int|array Returns a single key if num is 1, otherwise an array of keys
     * @see https://www.php.net/manual/en/function.array-rand.php
     */
    public static function randomKeys(array $array, int $num = 1): string|int|array
    {
        return array_rand($array, $num);
    }

    /**
     * Shuffles an array randomly.
     *
     * Provides a consistent wrapper around the native function shuffle.
     *
     * @param array $array The array to shuffle (passed by reference)
     * @return bool Returns true on success, false on failure
     * @see https://www.php.net/manual/en/function.shuffle.php
     */
    public static function shuffle(array &$array): bool
    {
        return shuffle($array);
    }

    /**
     * Counts all elements in an array.
     *
     * Provides a consistent wrapper around the native function count.
     *
     * @param array $array The array to count
     * @param int $mode Counting mode (default: COUNT_NORMAL)
     * @return int Returns the number of elements in the array
     * @see https://www.php.net/manual/en/function.count.php
     */
    public static function len(array $array, int $mode = COUNT_NORMAL): int
    {
        return count($array, $mode);
    }

    /**
     * Collapses an array of objects into a key-value array.
     *
     * Transforms an array of objects into an associative array using specified object properties.
     *
     * @param array $array The array of objects to collapse
     * @param string $key The object property to use as array key (default: 'key')
     * @param string|array $value The object property or properties to use as array value (default: 'value')
     * @return array Returns the collapsed associative array
     */
    public static function collapse(array $array, string $key = 'key', string|array $value = 'value'): array
    {
        if (empty($array)) {
            return [];
        }

        $list = [];
        foreach ($array as $element) {
            if (!isset($element->{$key})) {
                continue;
            }

            if (is_string($value)) {
                $list[$element->{$key}] = $element->{$value};
            } elseif (is_array($value)) {
                $composed_value = '';
                foreach ($value as $value_name) {
                    $composed_value .= $element->{$value_name} . ' ';
                }

                $list[$element->{$key}] = trim($composed_value);
            }
        }

        return $list;
    }

    /**
     * Searches for a value in an array and returns its key.
     *
     * Provides a consistent wrapper around the native function array_search.
     *
     * @param array $array The array to search in
     * @param mixed $value The value to search for
     * @param bool $strict Whether to use strict comparison (default: false)
     * @return int|string|false Returns the key if found, false otherwise
     * @see https://www.php.net/manual/en/function.array-search.php
     */
    public static function find(array $array, mixed $value, bool $strict = false): int|string|false
    {
        return array_search($value, $array, $strict);
    }

    /**
     * Applies a callback function to the elements of arrays.
     *
     * Provides a consistent wrapper around the native function array_map.
     *
     * @param array $array The array to run through the callback
     * @param callable|null $callback The callback function to apply
     * @param array ...$arrays Additional arrays to process
     * @return array Returns an array containing the results
     * @see https://www.php.net/manual/en/function.array-map.php
     */
    public static function apply(array $array, ?callable $callback, array ...$arrays): array
    {
        return array_map($callback, $array, ...$arrays);
    }

    /**
     * Joins array elements with a separator string.
     *
     * Provides a consistent wrapper around the native function implode.
     *
     * @param array $array The array to join
     * @param string $separator The separator string (default: '')
     * @return string Returns a string containing a string representation of all array elements
     * @see https://www.php.net/manual/en/function.implode.php
     */
    public static function join(array $array, string $separator = ''): string
    {
        return implode($separator, $array);
    }
}
