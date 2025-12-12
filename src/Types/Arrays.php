<?php

declare(strict_types=1);

namespace Phuture\Coherence\Types;

use Phuture\Coherence\Class\FluentClass;
use Phuture\Coherence\Interface\Arrayable;
use Phuture\Coherence\Arrays as Transformer;

/**
 * A fluent, array-like wrapper that provides object-oriented array manipulation.
 *
 * This class combines the power of PHP's native array operations with the convenience
 * of object-oriented syntax and method chaining. It implements key PHP interfaces to
 * provide seamless array-like behavior while maintaining the fluent interface pattern.
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
class Arrays extends FluentClass implements Arrayable, \ArrayAccess, \Countable, \IteratorAggregate
{
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
     * Returns an iterator for the array data.
     *
     * This method allows the Arrays object to be used in foreach loops and other
     * iterator contexts. It creates an ArrayIterator from the internal array data.
     *
     * @return \Traversable An iterator that can be used to traverse the array elements
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->toArray());
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
     * Transforms an array into an associative array according to a specified key.
     *
     * You can specify which field to use as the key, and optionally which field to use as the value.
     * If no value field is specified, the entire item is used.
     *
     * @param string|int $key The field to use as the associative array key
     * @param string|int|null $value Optional field to use as the value. If null, uses the entire item
     * @return self An instance of the Arrays class with the transformed array
     */
    public function associate(string|int $key, string|int|null $value = null): self
    {
        $this->data = Transformer::associate($this->data, $key, $value);

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
     */
    public function combine(array $values): self
    {
        $this->data = Transformer::combine($this->data, $values);

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
     */
    public function column(int|string|null $column, int|string|null $index = null): self
    {
        $this->data = Transformer::column($this->data, $column, $index);

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
     */
    public function changeKeyCase(int $case = CASE_LOWER): self
    {
        $this->data = Transformer::changeKeyCase($this->data, $case);

        return $this;
    }

    /**
     * Computes the difference of arrays with additional index check.
     *
     * This method compares arrays and returns the values from the current array that are not
     * present in any of the other arrays, preserving keys and checking both value and key.
     *
     * @param callable|array|null $callback Optional comparison function or array for simple comparison (default: null)
     * @param array ...$arrays Additional arrays to compare against
     * @return self An instance of the Arrays class with the transformed array
     */
    public function difference(callable|array|null $callback = null, array ...$arrays): self
    {
        $this->data = Transformer::difference($this->data, $callback, ...$arrays);

        return $this;
    }

    /**
     * Computes the difference of arrays using keys for comparison.
     *
     * This method compares arrays based on their keys and returns the key/value pairs from the
     * current array whose keys are not present in any of the other arrays.
     *
     * @param callable|array|null $callback Optional comparison function or array for simple comparison (default: null)
     * @param array ...$arrays Additional arrays to compare against
     * @return self An instance of the Arrays class with the transformed array
     */
    public function differenceKeys(callable|array|null $callback = null, array ...$arrays): self
    {
        $this->data = Transformer::differenceKeys($this->data, $callback, ...$arrays);

        return $this;
    }

    /**
     * Fills an array with values using the specified keys.
     *
     * This method creates a new array where each key from the provided array of keys is
     * mapped to the same value, useful for creating lookup tables or initializing arrays.
     *
     * @param array $keys Array of values to be used as keys
     * @param mixed $value The value to fill the array with
     * @return self An instance of the Arrays class with the transformed array
     */
    public function fillKeys(array $keys, mixed $value): self
    {
        $this->data = Transformer::fillKeys($keys, $value);

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
     */
    public function filter(?callable $callback = null): self
    {
        $this->data = Transformer::filter($this->data, $callback);

        return $this;
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
     */
    public function grep(string $pattern, bool $invert = false): self
    {
        $this->data = Transformer::grep($this->data, $pattern, $invert);

        return $this;
    }

    /**
     * Computes the intersection of arrays with additional index check.
     *
     * This method compares arrays and returns the values from the current array that are
     * present in all of the other arrays, preserving keys and checking both value and key.
     *
     * @param callable|array|null $callback Optional comparison function or array for simple comparison (default: null)
     * @param array ...$arrays Additional arrays to intersect with
     * @return self An instance of the Arrays class with the transformed array
     */
    public function intersect(callable|array|null $callback = null, array ...$arrays): self
    {
        $this->data = Transformer::intersect($this->data, $callback, ...$arrays);

        return $this;
    }

    /**
     * Computes the intersection of arrays using keys for comparison.
     *
     * This method compares arrays based on their keys and returns the key/value pairs from the
     * current array whose keys are present in all of the other arrays.
     *
     * @param callable|array|null $callback Optional comparison function or array for simple comparison (default: null)
     * @param array ...$arrays Additional arrays to intersect with
     * @return self An instance of the Arrays class with the transformed array
     */
    public function intersectKeys(callable|array|null $callback = null, array ...$arrays): self
    {
        $this->data = Transformer::intersectKeys($this->data, $callback, ...$arrays);

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
     */
    public function mapWithKeys(callable $callback): self
    {
        $this->data = Transformer::mapWithKeys($this->data, $callback);

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
     */
    public function notation(string $prefix = ''): self
    {
        $this->data = Transformer::notation($this->data, $prefix);

        return $this;
    }

    /**
     * Filters the array to include only specified keys.
     *
     * This method returns only the key/value pairs from the current array that have
     * keys present in the provided array of allowed keys.
     *
     * @param array $keys Array of keys to include in the result
     * @return self An instance of the Arrays class with the transformed array
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
     */
    public function pad(int $length, mixed $value): self
    {
        $this->data = Transformer::pad($this->data, $length, $value);

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
     */
    public function slice(int $offset, ?int $length = null, bool $preserve_keys = false): self
    {
        $this->data = Transformer::slice($this->data, $offset, $length, $preserve_keys);

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
     */
    public function split(int $length, bool $preserveKeys = false): self
    {
        $this->data = Transformer::split($this->data, $length, $preserveKeys);

        return $this;
    }

    /**
     * Removes duplicate values from an array.
     *
     * This method removes duplicate values from the array and returns a new array with only unique values,
     * with options for different comparison methods.
     *
     * @param int $flags The comparison flags to use for uniqueness (default: SORT_STRING)
     * @return self An instance of the Arrays class with the transformed array
     */
    public function unique(int $flags = SORT_STRING): self
    {
        $this->data = Transformer::unique($this->data, $flags);

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
     */
    public function wrap(string $prefix = '', string $suffix = ''): self
    {
        $this->data = Transformer::wrap($this->data, $prefix, $suffix);

        return $this;
    }

    /**
     * Swaps keys and values in an array.
     *
     * This method exchanges keys and values so that values become keys and keys become values.
     * If multiple values are the same, only the last key will be preserved in the result.
     *
     * @return self An instance of the Arrays class with the transformed array
     */
    public function flip(): self
    {
        $this->data = Transformer::flip($this->data);

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
     */
    public function keys(): self
    {
        $this->data = Transformer::keys($this->data);

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
     */
    public function values(): self
    {
        $this->data = Transformer::values($this->data);

        return $this;
    }

    /**
     * Collapses an array of arrays into a single array.
     *
     * This method takes an array containing other arrays and merges them into one
     * single array by flattening only one level of nesting, preserving deeper nested structures.
     *
     * @return self An instance of the Arrays class with the transformed array
     */
    public function collapse(): self
    {
        $this->data = Transformer::collapse($this->data);

        return $this;
    }

    /**
     * Flattens a multidimensional array into a single level.
     *
     * This method recursively flattens all nested arrays into a single-dimensional array,
     * traversing through ALL levels of nesting and collecting only the scalar values.
     *
     * @return self An instance of the Arrays class with the transformed array
     */
    public function flatten(): self
    {
        $this->data = Transformer::flatten($this->data);

        return $this;
    }

    /**
     * Normalizes a multi-dimensional array by converting all objects to arrays.
     *
     * This method recursively processes an array and converts any objects into plain arrays,
     * useful when working with data that might contain mixed object and array structures.
     *
     * @return self An instance of the Arrays class with the transformed array
     */
    public function normalize(): self
    {
        $this->data = Transformer::normalize($this->data);

        return $this;
    }

    /**
     * Expands a flattened array with dot notation keys back into a multi-dimensional array.
     *
     * This method takes a flat array where keys use dot notation to represent nested paths
     * and converts it back into a multi-dimensional array structure.
     *
     * @return self An instance of the Arrays class with the transformed array
     */
    public function denote(): self
    {
        $this->data = Transformer::denote($this->data);

        return $this;
    }
}
