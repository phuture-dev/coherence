<?php

declare(strict_types=1);

namespace Phuture\Coherence\Types;

use Phuture\Coherence\Class\FluentClass;
use Phuture\Coherence\Interface\Arrayable;

/**
 * Fluent wrapper for array manipulation operations.
 *
 * This class provides a fluent interface for chaining array operations from the Arrays helper class.
 * It extends FluentClass and automatically forwards chained method calls to the static methods in
 * \Phuture\Coherence\Arrays, allowing for elegant method chaining on array data.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Types\Arrays;
 *
 * // Chain multiple array operations
 * $result = (new Arrays([1, 2, 3, 4, 5]))
 *     ->filter(fn($v) => $v > 2)
 *     ->reverse()
 *     ->values()
 *     ->get();
 * // Returns: [5, 4, 3]
 *
 * // Process associative arrays
 * $users = [
 *     ['name' => 'John', 'age' => 30],
 *     ['name' => 'Jane', 'age' => 25],
 *     ['name' => 'Bob', 'age' => 35]
 * ];
 * $names = (new Arrays($users))
 *     ->column('name')
 *     ->get();
 * // Returns: ['John', 'Jane', 'Bob']
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 * @see \Phuture\Coherence\Arrays For available array manipulation methods
 * @see \Phuture\Coherence\Class\FluentClass For the base fluent class implementation
 * @see \Phuture\Coherence\Interface\Arrayable For the arrayable interface implementation
 */
class Arrays extends FluentClass implements Arrayable
{
    protected ?string $class = \Phuture\Coherence\Arrays::class;

    /**
     * Initializes the fluent wrapper with optional data.
     *
     * @param mixed $data The initial data to wrap
     */
    public function __construct(mixed $data = null)
    {
        $this->data = \Phuture\Coherence\Arrays::from($data);

        parent::__construct($data);
    }

    /**
     * Convert the object data to an array.
     *
     * @return array The converted array data
     */
    public function toArray(): array
    {
        return \Phuture\Coherence\Arrays::from($this->data);
    }
}
