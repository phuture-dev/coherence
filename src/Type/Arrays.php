<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Phuture\Coherence\Interface\Arrayable;
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Enum\ArrayComparator;
use Phuture\Coherence\Arrays as Transformer;

/**
 * A fluent, array-like wrapper that provides object-oriented array manipulation.
 *
 * Each method delegates to the corresponding static method on `\Phuture\Coherence\Arrays`, stores the
 * result internally, and returns `$this` to enable method chaining. Retrieve the final
 * value by calling `get()` or invoking the object directly.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Types\Arrays;
 *
 * // Create from array
 * $fruits = new Arrays(['apple', 'banana', 'cherry']);
 *
 * // Use like an array
 * echo $fruits[0]; // 'apple'
 * $fruits[] = 'orange'; // Append
 *
 * foreach ($fruits as $fruit) {
 *     echo $fruit;
 * }
 *
 * // Use fluent methods
 * $result = $fruits->reverse()
 *     ->notation()
 *     ->toArray();
 * ```
 */
class Arrays extends FluentClass implements Arrayable, ArrayAccess, Countable, IteratorAggregate
{
    /**
     * Transforms an array into an associative array according to a specified key.
     *
     * You can specify which field to use as the key, and optionally which field to use as the value.
     * If no value field is specified, the entire item is used.
     *
     * @param string|int $key The field to use as the associative array key
     * @param string|int|null $value Optional field to use as the value. If null, uses the entire item
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::associate()
     */
    public function associate(string|int $key, string|int|null $value = null): self
    {
        $this->data = Transformer::associate($this->data, $key, $value);

        return $this;
    }

    /**
     * Changes the case of all keys in an array.
     *
     * This method converts all string keys in the array to either uppercase or lowercase,
     * which is useful for standardizing key formats when working with data from different sources.
     *
     * @param int $case The case to convert keys to (CASE_LOWER or CASE_UPPER, default: CASE_LOWER)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::changeKeyCase()
     */
    public function changeKeyCase(int $case = CASE_LOWER): self
    {
        $this->data = Transformer::changeKeyCase($this->data, $case);

        return $this;
    }

    /**
     * Collapses an array of arrays into a single array.
     *
     * This method takes an array containing other arrays and merges them into one
     * single array by flattening only one level of nesting, preserving deeper nested structures.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::collapse()
     */
    public function collapse(): self
    {
        $this->data = Transformer::collapse($this->data);

        return $this;
    }

    /**
     * Extracts a single column from a multi-dimensional array.
     *
     * This method pulls out values from a specific field across all rows in an array,
     * similar to selecting a column from a spreadsheet. Useful when working with
     * database results or arrays of objects.
     *
     * @param int|string|null $column The column name or index to extract
     * @param int|string|null $index Optional column to use as keys in the result (default: null)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::column()
     */
    public function column(int|string|null $column, int|string|null $index = null): self
    {
        $this->data = Transformer::column($this->data, $column, $index);

        return $this;
    }

    /**
     * Creates an array by pairing keys with values from two separate arrays.
     *
     * This method takes an array of values and combines them with the current array
     * into a single associative array where the current array provides the keys and the provided
     * array the values. Both arrays must have the same number of elements.
     *
     * @param array $values Array of values to use
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::combine()
     */
    public function combine(array $values): self
    {
        $this->data = Transformer::combine($this->data, $values);

        return $this;
    }

    /**
     * Counts the number of elements in the array.
     *
     * This method returns the total number of items stored in the array.
     * It's part of the Countable interface, allowing the object to be used with
     * PHP's built-in count() function.
     *
     * @return int The number of elements in the array
     */
    public function count(): int
    {
        return count($this->toArray());
    }

    /**
     * Compute the Cartesian product of the array with the given arrays.
     *
     * @param array ...$arrays Arrays to cross join with
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::crossJoin()
     */
    public function crossJoin(array ...$arrays): self
    {
        $this->data = Transformer::crossJoin($this->data, ...$arrays);

        return $this;
    }

    /**
     * Expands a flattened array with dot notation keys back into a multi-dimensional array.
     *
     * This method takes a flat array where keys use dot notation to represent nested paths
     * and converts it back into a multi-dimensional array structure.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::denote()
     */
    public function denote(): self
    {
        $this->data = Transformer::denote($this->data);

        return $this;
    }

    /**
     * Computes the difference of arrays with additional index check.
     *
     * This method compares arrays and returns the values from the current array that are not
     * present in any of the other arrays, preserving keys and checking both value and key.
     *
     * @param array ...$arrays Additional arrays to compare against
     * @param callable $callback Optional comparison function that returns <0, 0, or >0 (optional)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::difference()
     */
    public function difference(...$arrays): self
    {
        $this->data = Transformer::difference($this->data, ...$arrays);

        return $this;
    }

    /**
     * Computes the difference of arrays with additional index check.
     *
     * This method compares the array against other arrays and returns the values
     * in the first array that are not present in any of the other arrays,
     * checking both keys and values for equality.
     *
     * @param array ...$arrays Arrays to compare against
     * @param ArrayComparator $comparator The comparator to use with the provided callback(s) (required with callbacks)
     * @param callable $firstCallback Optional comparison function that returns <0, 0, or >0 (optional)
     * @param callable $secondCallback Optional comparison function that returns <0, 0, or >0 (optional)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::differenceAssoc()
     */
    public function differenceAssoc(...$arrays): self
    {
        $this->data = Transformer::differenceAssoc($this->data, ...$arrays);

        return $this;
    }

    /**
     * Computes the difference of arrays using keys for comparison.
     *
     * This method compares arrays based on their keys and returns the key/value pairs from the
     * current array whose keys are not present in any of the other arrays.
     *
     * @param array ...$arrays Additional arrays to compare against
     * @param callable $callback Optional comparison function for keys that returns <0, 0, or >0 (optional)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::differenceKeys()
     */
    public function differenceKeys(...$arrays): self
    {
        $this->data = Transformer::differenceKeys($this->data, ...$arrays);

        return $this;
    }

    /**
     * Filters elements of an array using a callback function.
     *
     * This method iterates over each value in the array passing them to the callback function.
     * If the callback returns true, the current value is returned to the result array.
     *
     * @param callable|null $callback The callback function to use for filtering (default: null)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::filter()
     */
    public function filter(?callable $callback = null): self
    {
        $this->data = Transformer::filter($this->data, $callback);

        return $this;
    }

    /**
     * Flattens a multidimensional array into a single level.
     *
     * This method recursively flattens all nested arrays into a single-dimensional array,
     * traversing through ALL levels of nesting and collecting only the scalar values.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::flatten()
     */
    public function flatten(): self
    {
        $this->data = Transformer::flatten($this->data);

        return $this;
    }

    /**
     * Swaps keys and values in an array.
     *
     * This method exchanges keys and values so that values become keys and keys become values.
     * If multiple values are the same, only the last key will be preserved in the result.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::flip()
     */
    public function flip(): self
    {
        $this->data = Transformer::flip($this->data);

        return $this;
    }

    /**
     * Returns an iterator for the array data.
     *
     * This method allows the Arrays object to be used in foreach loops and other
     * iterator contexts. It creates an ArrayIterator from the internal array data.
     *
     * @return Traversable An iterator that can be used to traverse the array elements
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Filters array elements using regular expression matching.
     *
     * This method searches each element in the array for matches to the regular expression
     * given and returns only the elements that match (or don't match if inverted).
     *
     * @param string $pattern The regular expression pattern to match
     * @param bool $invert If true, returns elements that do not match the pattern (default: false)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::grep()
     */
    public function grep(string $pattern, bool $invert = false): self
    {
        $this->data = Transformer::grep($this->data, $pattern, $invert);

        return $this;
    }

    /**
     * Groups array elements by a specified key or callback function.
     *
     * This method organizes items in an array into groups based on a common value.
     * You can either specify a key name (for arrays of arrays/objects) or provide
     * a custom function that determines how items should be grouped.
     *
     * @param callable|string $groupBy The key name to group by, or a callback function
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::groupBy()
     */
    public function groupBy(callable|string $groupBy): self
    {
        $this->data = Transformer::groupBy($this->data, $groupBy);

        return $this;
    }

    /**
     * Computes the intersection of arrays with additional index check.
     *
     * This method compares arrays and returns the values from the current array that are
     * present in all of the other arrays, preserving keys and checking both value and key.
     *
     * @param array ...$arrays Additional arrays to intersect with
     * @param callable $callback Optional comparison function that returns <0, 0, or >0 (optional)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::intersect()
     */
    public function intersect(...$arrays): self
    {
        $this->data = Transformer::intersect($this->data, ...$arrays);

        return $this;
    }

    /**
     * Computes the intersection of arrays using keys for comparison.
     *
     * This method compares arrays based on their keys and returns the key/value pairs from the
     * current array whose keys are present in all of the other arrays.
     *
     * @param array ...$arrays Additional arrays to intersect with
     * @param ArrayComparator $comparator The comparator to use with the provided callback(s) (required with callbacks)
     * @param callable $firstCallback Optional comparison function that returns <0, 0, or >0 (optional)
     * @param callable $secondCallback Optional comparison function that returns <0, 0, or >0 (optional)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::intersectAssoc()
     */
    public function intersectAssoc(...$arrays): self
    {
        $this->data = Transformer::intersectAssoc($this->data, ...$arrays);

        return $this;
    }

    /**
     * Computes the intersection of arrays using keys for comparison.
     *
     * This method compares arrays based on their keys and returns the key/value pairs from the
     * current array whose keys are present in all of the other arrays.
     *
     * @param array ...$arrays Additional arrays to intersect with
     * @param callable $callback Optional comparison function that returns <0, 0, or >0 (optional)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::intersectKeys()
     */
    public function intersectKeys(...$arrays): self
    {
        $this->data = Transformer::intersectKeys($this->data, ...$arrays);

        return $this;
    }

    /**
     * Join the array with the given arrays by concatenating elements.
     *
     * @param array ...$arrays Arrays to join with
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::join()
     */
    public function join(array ...$arrays): self
    {
        $this->data = Transformer::join($this->data, ...$arrays);

        return $this;
    }

    /**
     * Returns all the keys from an array.
     *
     * This method extracts all keys from an array and returns them as a new indexed array.
     * The keys can be strings, integers, or a mix of both. The resulting array will have
     * numeric keys starting from 0.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::keys()
     */
    public function keys(): self
    {
        $this->data = Transformer::keys($this->data);

        return $this;
    }

    /**
     * Applies a callback function to all elements of the array.
     *
     * This method returns an array containing all elements of the current array after
     * applying the callback function to each one, useful for transforming data.
     *
     * @param callable $callback The callback function to apply to each element
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::map()
     */
    public function map(callable $callback): self
    {
        $this->data = Transformer::map($this->data, $callback);

        return $this;
    }

    /**
     * Applies a callback function to the keys of an array.
     *
     * This method transforms the keys of an array using a callback function while preserving
     * the associated values, useful for renaming or reformatting keys.
     *
     * @param callable $callback The callback function to apply to each key
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::mapKeys()
     */
    public function mapKeys(callable $callback): self
    {
        $this->data = Transformer::mapKeys($this->data, $callback);

        return $this;
    }

    /**
     * Applies a callback function that returns key/value pairs.
     *
     * This method iterates over the array and passes each element to the callback,
     * which should return an associative array with a single key/value pair to be added to the result.
     *
     * @param callable $callback The callback function that returns key/value pairs
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::mapWithKeys()
     */
    public function mapWithKeys(callable $callback): self
    {
        $this->data = Transformer::mapWithKeys($this->data, $callback);

        return $this;
    }

    /**
     * Merge the array with the given arrays.
     *
     * @param array ...$arrays Arrays to merge with
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::merge()
     */
    public function merge(array ...$arrays): self
    {
        $this->data = Transformer::merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Normalizes a multi-dimensional array by converting all objects to arrays.
     *
     * This method recursively processes an array and converts any objects into plain arrays,
     * useful when working with data that might contain mixed object and array structures.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::normalize()
     */
    public function normalize(): self
    {
        $this->data = Transformer::normalize($this->data);

        return $this;
    }

    /**
     * Flattens a multi-dimensional array into dot notation.
     *
     * This method converts nested arrays into a flat structure using dot-separated keys,
     * making it easier to access nested values with simple string keys.
     *
     * @param string $prefix Optional prefix to prepend to each key (default: empty string)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::notation()
     */
    public function notation(string $prefix = ''): self
    {
        $this->data = Transformer::notation($this->data, $prefix);

        return $this;
    }

    /**
     * Checks if a specific key or index exists in the array.
     *
     * This method determines whether the given offset (key) exists in the array.
     * It's part of the ArrayAccess interface that allows array-like access using [] notation.
     *
     * @param mixed $offset The key or index to check for existence
     * @return bool Returns true if the offset exists, false otherwise
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->toArray());
    }

    /**
     * Retrieves a value from the array by its key or index.
     *
     * This method gets the value stored at the given offset. If the offset doesn't exist,
     * it returns null instead of throwing an error. This is part of the ArrayAccess interface.
     *
     * @param mixed $offset The key or index of the value to retrieve
     * @return mixed The value at the given offset, or null if the offset doesn't exist
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset] ?? null;
    }

    /**
     * Sets or modifies a value in the array at a specific key or index.
     *
     * This method assigns a value to the given offset. If the offset is null, the value
     * is appended to the end of the array. This is part of the ArrayAccess interface.
     *
     * @param mixed $offset The key or index where to store the value (null to append)
     * @param mixed $value The value to store in the array
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Removes a value from the array by its key or index.
     *
     * This method deletes the element at the given offset from the array.
     * If the offset doesn't exist, no action is taken. This is part of the ArrayAccess interface.
     *
     * @param mixed $offset The key or index of the element to remove
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Filters the array to include only specified keys.
     *
     * This method returns only the key/value pairs from the current array that have
     * keys present in the provided array of allowed keys.
     *
     * @param array $keys Array of keys to include in the result
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::only()
     */
    public function only(array $keys): self
    {
        $this->data = Transformer::only($this->data, $keys);

        return $this;
    }

    /**
     * Pads an array to the specified length with a value.
     *
     * This method adds elements to the end of an array until it reaches the specified size,
     * using the provided value for the new elements.
     *
     * @param int $length The size to pad the array to
     * @param mixed $value The value to pad the array with
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::pad()
     */
    public function pad(int $length, mixed $value): self
    {
        $this->data = Transformer::pad($this->data, $length, $value);

        return $this;
    }

    /**
     * Rename keys in the array.
     *
     * @param string|int|array $oldKey Old key name or array of key mappings
     * @param string|int $newKey New key name (when $oldKey is not an array)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::rename()
     */
    public function rename(string|int|array $oldKey, string|int $newKey): self
    {
        Transformer::rename($this->data, $oldKey, $newKey);

        return $this;
    }

    /**
     * Replaces elements from passed arrays into the current array.
     *
     * This method replaces values from the current array with values from the other arrays,
     * optionally performing recursive replacement for nested arrays.
     *
     * @param bool $recursive Whether to perform recursive replacement (default: false)
     * @param array ...$replacements Arrays containing elements to replace
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::replace()
     */
    public function replace(bool $recursive = false, array ...$replacements): self
    {
        $this->data = Transformer::replace($this->data, $recursive, ...$replacements);

        return $this;
    }

    /**
     * Returns an array with elements in reverse order.
     *
     * This method reverses the order of elements in an array. The first element becomes the last,
     * and the last element becomes the first. By default, numeric keys are renumbered starting from 0,
     * while string keys are always preserved. Set the preserveKeys parameter to true to maintain the
     * original numeric key associations.
     *
     * @param bool $preserveKeys Whether to preserve numeric keys (default: true)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::reverse()
     */
    public function reverse(bool $preserveKeys = true): self
    {
        $this->data = Transformer::reverse($this->data, $preserveKeys);

        return $this;
    }

    /**
     * Extracts a slice of the array.
     *
     * This method extracts a sequence of elements from the array and returns them as a new array,
     * with options to preserve or discard original keys.
     *
     * @param int $offset The starting offset of the slice
     * @param int|null $length The maximum length of the slice (default: null for all remaining elements)
     * @param bool $preserve_keys Whether to preserve original keys (default: false)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::slice()
     */
    public function slice(int $offset, ?int $length = null, bool $preserve_keys = false): self
    {
        $this->data = Transformer::slice($this->data, $offset, $length, $preserve_keys);

        return $this;
    }

    /**
     * Sort the array values using a callback function.
     *
     * @param bool $reverse Whether to sort in reverse order
     * @param callable|null $callback Custom comparison function
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::sort()
     */
    public function sort(bool $reverse = false, ?callable $callback = null): self
    {
        Transformer::sort($this->data, $reverse, $callback);

        return $this;
    }

    /**
     * Sort an associative array by values while maintaining key association.
     *
     * @param bool $reverse Whether to sort in reverse order
     * @param callable|null $callback Custom comparison function
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::sortAssoc()
     */
    public function sortAssoc(bool $reverse = false, ?callable $callback = null): self
    {
        Transformer::sortAssoc($this->data, $reverse, $callback);

        return $this;
    }

    /**
     * Sort the array by a given key or multiple keys.
     *
     * @param string|array|callable $criteria The key(s) to sort by, or a callback
     * @param bool $reverse Whether to sort in descending order
     * @param int $flags Sort flags for natural sorting
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::sortBy()
     */
    public function sortBy(string|array|callable $criteria, bool $reverse = false, int $flags = 0): self
    {
        Transformer::sortBy($this->data, $criteria, $reverse, $flags);

        return $this;
    }

    /**
     * Sort the array by keys.
     *
     * @param bool $reverse Whether to sort in reverse order
     * @param callable|null $callback Custom comparison function for keys
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::sortKeys()
     */
    public function sortKeys(bool $reverse = false, ?callable $callback = null): self
    {
        Transformer::sortKeys($this->data, $reverse, $callback);

        return $this;
    }

    /**
     * Sort the array using natural ordering algorithm.
     *
     * @param bool $case_insensitive Whether to perform case-insensitive comparison
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::sortNatural()
     */
    public function sortNatural(bool $case_insensitive = false): self
    {
        Transformer::sortNatural($this->data, $case_insensitive);

        return $this;
    }

    /**
     * Splits an array into chunks.
     *
     * This method divides an array into multiple smaller arrays of the specified size,
     * useful for processing large arrays in smaller batches.
     *
     * @param int $length The maximum size of each chunk
     * @param bool $preserveKeys Whether to preserve original keys in each chunk (default: false)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::split()
     */
    public function split(int $length, bool $preserveKeys = false): self
    {
        $this->data = Transformer::split($this->data, $length, $preserveKeys);

        return $this;
    }
    /**
     * Converts the object to a native PHP array.
     *
     * This method returns the internal data as a plain PHP array, which can be
     * used with any native PHP array function or passed to other APIs that expect arrays.
     *
     * @return array The internal data as a native PHP array
     */
    public function toArray(): array
    {
        return (array) $this->data;
    }

    /**
     * Removes duplicate values from an array.
     *
     * This method removes duplicate values from the array and returns a new array with only unique values,
     * with options for different comparison methods.
     *
     * @param int $flags The comparison flags to use for uniqueness (default: SORT_STRING)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::unique()
     */
    public function unique(int $flags = SORT_STRING): self
    {
        $this->data = Transformer::unique($this->data, $flags);

        return $this;
    }

    /**
     * Returns all values from an array.
     *
     * This method extracts all values from an array and returns them as a new indexed array
     * with numeric keys starting from 0. This effectively removes all the original keys and
     * re-indexes the array sequentially.
     *
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::values()
     */
    public function values(): self
    {
        $this->data = Transformer::values($this->data);

        return $this;
    }

    /**
     * Filters an array using a callback function.
     *
     * This method creates a new array containing only the elements that pass a test
     * you provide. It is an alias for the filter method with a clearer name for
     * predicate-based filtering scenarios.
     *
     * @param callable $callback Function that tests each element, returns true to keep it
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::where()
     */
    public function where(callable $callback): self
    {
        $this->data = Transformer::where($this->data, $callback);

        return $this;
    }

    /**
     * Filters an array where a key's value is in a given list of values.
     *
     * This method filters an array to only include items where a specific key
     * has a value that matches one of the values you provide.
     *
     * @param string $key The key to check in each array item
     * @param array $values The list of values to match against
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::whereIn()
     */
    public function whereIn(string $key, array $values): self
    {
        $this->data = Transformer::whereIn($this->data, $key, $values);

        return $this;
    }

    /**
     * Wraps each element of an array with a prefix and suffix.
     *
     * This method adds the specified prefix and suffix to each element in the array,
     * useful for formatting strings or adding markup.
     *
     * @param string $prefix The string to prepend to each element (default: empty string)
     * @param string $suffix The string to append to each element (default: empty string)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::wrap()
     */
    public function wrap(string $prefix = '', string $suffix = ''): self
    {
        $this->data = Transformer::wrap($this->data, $prefix, $suffix);

        return $this;
    }
}
