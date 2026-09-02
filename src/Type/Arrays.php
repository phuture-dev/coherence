<?php

declare(strict_types=1);

namespace Phuture\Coherence\Type;

use Override;
use Countable;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use IteratorAggregate;
use Phuture\Coherence\Interface\Arrayable;
use Phuture\Coherence\Support\FluentClass;
use Phuture\Coherence\Arrays as Transformer;
use Phuture\Coherence\Enum\{ArrayComparator, KeyCase, SortComparison};

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
     * Appends key-value pairs to the array if the keys do not already exist.
     *
     * @param array $items Associative array of key-value pairs to append if the keys are absent
     * @return self An instance of the Arrays class with the updated array
     * @see \Phuture\Coherence\Arrays::append()
     * @see \Phuture\Coherence\Type\Arrays::prepend()
     */
    public function append(array $items): self
    {
        Transformer::append($this->data, $items);

        return $this;
    }
    /**
     * Transforms an array into an associative array according to a specified key.
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
     * @param \Phuture\Coherence\Enum\KeyCase $case The case to convert keys to — Lower or Upper
     *  (default: KeyCase::Lower)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::changeKeyCase()
     * @see \Phuture\Coherence\Enum\KeyCase
     */
    public function changeKeyCase(KeyCase $case = KeyCase::Lower): self
    {
        $this->data = Transformer::changeKeyCase($this->data, $case);

        return $this;
    }

    /**
     * Collapses an array of arrays into a single array.
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
     * @return int The number of elements in the array
     */
    #[Override]
    public function count(): int
    {
        return count($this->toArray());
    }

    /**
     * Compute the Cartesian product of the array with the given arrays.
     *
     * @param array ...$arrays Arrays to cross join with
     * @return self An instance of the Arrays class with the transformed array
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the current array or any given array is empty
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
     * Creates an associative array using the current array as keys, all set to the same value.
     *
     * @param mixed $value The value to assign to every key
     * @return self An instance of the Arrays class with the transformed array
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException If the current array is empty
     * @see \Phuture\Coherence\Arrays::fillKeys()
     */
    public function fillKeys(mixed $value): self
    {
        $this->data = Transformer::fillKeys($this->data, $value);

        return $this;
    }

    /**
     * Filters elements of an array using a callback function.
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
     * @return Traversable An iterator that can be used to traverse the array elements
     */
    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Filters array elements using regular expression matching.
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
     * Inserts key-value pairs into the array immediately after a specified key.
     *
     * @param string|int $key The key after which the new items will be inserted
     * @param array $items Associative array of key-value pairs to insert
     * @return self An instance of the Arrays class with the updated array
     * @see \Phuture\Coherence\Arrays::insertAfter()
     * @see \Phuture\Coherence\Type\Arrays::insertBefore()
     */
    public function insertAfter(string|int $key, array $items): self
    {
        Transformer::insertAfter($this->data, $key, $items);

        return $this;
    }

    /**
     * Inserts key-value pairs into the array immediately before a specified key.
     *
     * @param string|int $key The key before which the new items will be inserted
     * @param array $items Associative array of key-value pairs to insert
     * @return self An instance of the Arrays class with the updated array
     * @see \Phuture\Coherence\Arrays::insertBefore()
     * @see \Phuture\Coherence\Type\Arrays::insertAfter()
     */
    public function insertBefore(string|int $key, array $items): self
    {
        Transformer::insertBefore($this->data, $key, $items);

        return $this;
    }

    /**
     * Computes the intersection of arrays with additional index check.
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
     * Applies a callback function to every element of the array.
     *
     * @param callable $callback The function to apply to each element.
     *  The callback has the signature `function (mixed &$value, mixed $key): void`
     * @param bool $recursive Whether to recursively process nested arrays (default: false)
     * @param mixed $args Optional extra data passed as a third argument to the callback (default: null)
     * @return self An instance of the Arrays class with the updated array
     * @see \Phuture\Coherence\Arrays::iterate()
     */
    public function iterate(callable $callback, bool $recursive = false, mixed $args = null): self
    {
        Transformer::iterate($this->data, $callback, $recursive, $args);

        return $this;
    }

    /**
     * Returns all the keys from an array.
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
     * Pass `true` as the last argument to enable recursive (deep) merging.
     *
     * @param mixed ...$arrays Arrays to merge with, with an optional trailing bool for recursive mode (default: false)
     * @return self An instance of the Arrays class with the transformed array
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the current array or any given array is empty
     * @see \Phuture\Coherence\Arrays::merge()
     */
    public function merge(mixed ...$arrays): self
    {
        $this->data = Transformer::merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Normalizes a multi-dimensional array by converting all objects to arrays.
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
     * Checks if an offset exists in the array.
     *
     * @param mixed $offset The offset to check
     * @return bool True if the offset exists, false otherwise
     */
    #[Override]
    public function offsetExists(mixed $offset): bool
    {
        if (!is_string($offset) && !is_int($offset)) {
            return false;
        }

        return isset($this->toArray()[$offset]);
    }

    /**
     * Returns the value at the given offset.
     *
     * @param mixed $offset The offset to retrieve
     * @return mixed The value at the given offset, or null if it does not exist
     */
    #[Override]
    public function offsetGet(mixed $offset): mixed
    {
        if (!is_string($offset) && !is_int($offset)) {
            return null;
        }

        return $this->toArray()[$offset] ?? null;
    }

    /**
     * Sets a value at the given offset.
     *
     * @param mixed $offset The offset to assign to, or null to append
     * @param mixed $value The value to set
     * @return void
     */
    #[Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $data = $this->toArray();
        if ($offset === null) {
            $data[] = $value;
        } elseif (is_string($offset) || is_int($offset)) {
            $data[$offset] = $value;
        }
        $this->data = $data;
    }

    /**
     * Removes the value at the given offset.
     *
     * @param mixed $offset The offset to unset
     * @return void
     */
    #[Override]
    public function offsetUnset(mixed $offset): void
    {
        if (!is_string($offset) && !is_int($offset)) {
            return;
        }
        $data = $this->toArray();
        unset($data[$offset]);
        $this->data = $data;
    }

    /**
     * Filters the array to include only specified keys.
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
     * Splits the array into two groups based on a callback function.
     *
     * @param callable $callback Function that returns true for the first group, false for the second.
     *  The callback has the signature `function (mixed $value, mixed $key): bool`
     * @return self An instance of the Arrays class whose data is `[$passed, $failed]`
     * @see \Phuture\Coherence\Arrays::partition()
     * @see \Phuture\Coherence\Type\Arrays::filter()
     */
    public function partition(callable $callback): self
    {
        $this->data = Transformer::partition($this->data, $callback);

        return $this;
    }

    /**
     * Prepends key-value pairs to the beginning of the array.
     *
     * @param array $items Associative array of key-value pairs to prepend if the keys are absent
     * @return self An instance of the Arrays class with the updated array
     * @see \Phuture\Coherence\Arrays::prepend()
     * @see \Phuture\Coherence\Type\Arrays::append()
     */
    public function prepend(array $items): self
    {
        Transformer::prepend($this->data, $items);

        return $this;
    }

    /**
     * Removes the last element from the array.
     *
     * @return self An instance of the Arrays class with the last element removed
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException If the array is empty
     * @see \Phuture\Coherence\Arrays::pull()
     * @see \Phuture\Coherence\Type\Arrays::push()
     * @see \Phuture\Coherence\Type\Arrays::shift()
     */
    public function pull(): self
    {
        Transformer::pull($this->data);

        return $this;
    }

    /**
     * Adds one or more elements to the end of the array.
     *
     * @param mixed ...$values One or more values to add to the end of the array
     * @return self An instance of the Arrays class with the new elements appended
     * @see \Phuture\Coherence\Arrays::push()
     * @see \Phuture\Coherence\Type\Arrays::pull()
     */
    public function push(mixed ...$values): self
    {
        Transformer::push($this->data, ...$values);

        return $this;
    }

    /**
     * Removes a key-value pair from the array.
     *
     * @param string|int|array $key The key to remove (string/int for a top-level key, array for a nested path)
     * @return self An instance of the Arrays class with the key removed
     * @see \Phuture\Coherence\Arrays::remove()
     */
    public function remove(string|int|array $key): self
    {
        Transformer::remove($this->data, $key);

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
     * @param bool $preserveKeys Whether to preserve numeric keys (default: false)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::reverse()
     */
    public function reverse(bool $preserveKeys = false): self
    {
        $this->data = Transformer::reverse($this->data, $preserveKeys);

        return $this;
    }

    /**
     * Removes the first element from the array.
     *
     * @return self An instance of the Arrays class with the first element removed
     * @throws \Phuture\Coherence\Exception\OutOfBoundsException If the array is empty
     * @see \Phuture\Coherence\Arrays::shift()
     * @see \Phuture\Coherence\Type\Arrays::pull()
     */
    public function shift(): self
    {
        Transformer::shift($this->data);

        return $this;
    }

    /**
     * Randomly rearranges the elements of the array.
     *
     * @return self An instance of the Arrays class with elements in random order
     * @see \Phuture\Coherence\Arrays::shuffle()
     */
    public function shuffle(): self
    {
        Transformer::shuffle($this->data);

        return $this;
    }

    /**
     * Extracts a slice of the array.
     *
     * @param int $offset The starting offset of the slice
     * @param int|null $length The maximum length of the slice (default: null for all remaining elements)
     * @param bool $preserveKeys Whether to preserve original keys (default: false)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::slice()
     */
    public function slice(int $offset, ?int $length = null, bool $preserveKeys = false): self
    {
        $this->data = Transformer::slice($this->data, $offset, $length, $preserveKeys);

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
     * Removes a portion of the array and optionally replaces it with new elements.
     *
     * @param int $offset The position to start removing elements (negative counts from the end)
     * @param int|null $length Number of elements to remove (default: null removes everything from offset onward)
     * @param mixed $replacement Elements to insert at the offset position (default: empty array)
     * @return self An instance of the Arrays class with the modified array
     * @see \Phuture\Coherence\Arrays::splice()
     * @see \Phuture\Coherence\Type\Arrays::slice()
     */
    public function splice(int $offset, ?int $length = null, mixed $replacement = []): self
    {
        Transformer::splice($this->data, $offset, $length, $replacement);

        return $this;
    }

    /**
     * Splits an array into chunks.
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
     * @return array The internal data as a native PHP array
     */
    #[Override]
    public function toArray(): array
    {
        return (array) $this->data;
    }

    /**
     * Converts the object to a JSON string.
     *
     * @return string The internal data as a JSON-encoded string
     */
    public function toJson(): string
    {
        return Transformer::toJson($this->data);
    }

    /**
     * Converts the object to a generic PHP object.
     *
     * @return object The internal data as a plain PHP object
     */
    public function toObject(): object
    {
        return Transformer::toObject($this->data);
    }

    /**
     * Removes duplicate values from an array.
     *
     * @param \Phuture\Coherence\Enum\SortComparison $comparison How values are compared to detect
     *  duplicates — Regular, Numeric, String or LocaleString (default: SortComparison::String)
     * @return self An instance of the Arrays class with the transformed array
     * @see \Phuture\Coherence\Arrays::unique()
     * @see \Phuture\Coherence\Enum\SortComparison
     */
    public function unique(SortComparison $comparison = SortComparison::String): self
    {
        $this->data = Transformer::unique($this->data, $comparison);

        return $this;
    }

    /**
     * Applies a callback when a condition is false, preserving the fluent chain regardless.
     *
     * @param bool|callable $condition A boolean value or a callback that receives the array data and returns a bool
     *  The callback has the signature `function (array $data): bool`
     * @param callable $callback The callback to execute when the condition is false
     *  The callback has the signature `function (self $array): void`
     * @return self The current instance for continued chaining
     * @see \Phuture\Coherence\Type\Arrays::when()
     */
    public function unless(bool|callable $condition, callable $callback): self
    {
        $resolved = is_callable($condition) ? $condition($this->data) : $condition;

        if (!$resolved) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Returns all values from an array.
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
     * Applies a callback when a condition is true, preserving the fluent chain regardless.
     *
     * @param bool|callable $condition A boolean value or a callback that receives the array data and returns a bool
     *  The callback has the signature `function (array $data): bool`
     * @param callable $callback The callback to execute when the condition is true
     *  The callback has the signature `function (self $array): void`
     * @return self The current instance for continued chaining
     * @see \Phuture\Coherence\Type\Arrays::unless()
     */
    public function when(bool|callable $condition, callable $callback): self
    {
        $resolved = is_callable($condition) ? $condition($this->data) : $condition;

        if ($resolved) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Filters an array using a callback function.
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

    /**
     * Combines the array with one or more other arrays by pairing elements at the same index.
     *
     * @param array ...$arrays One or more arrays to zip with the current array
     * @return self An instance of the Arrays class with the zipped pairs
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException If no additional arrays are provided
     * @see \Phuture\Coherence\Arrays::zip()
     */
    public function zip(array ...$arrays): self
    {
        $this->data = Transformer::zip($this->data, ...$arrays);

        return $this;
    }
}
