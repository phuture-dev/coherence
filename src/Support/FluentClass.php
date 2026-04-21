<?php

declare(strict_types=1);

namespace Phuture\Coherence\Support;

use Phuture\Coherence\Exception\MemberAccessException;

/**
 * Base class for creating fluent interfaces that enable method chaining for data transformations.
 *
 * This class provides the foundation for implementing the fluent interface pattern, allowing for more readable,
 * expressive code by eliminating intermediate variables and creating a natural language-like syntax for
 * sequential operations on data.
 *
 * The intended way to use FluentClass is by extending it and implementing your own transformation
 * methods. Each method should transform the internal `$data` property and return `$this` to enable chaining.
 *
 * Example:
 * ```php
 * namespace Phuture\Coherence;
 *
 * use Phuture\Coherence\Class\FluentClass;
 *
 * class FluentString extends FluentClass
 * {
 *     public function upper(): self
 *     {
 *         $this->data = strtoupper($this->data);
 *         return $this;
 *     }
 *
 *     public function reverse(): self
 *     {
 *         $this->data = strrev($this->data);
 *         return $this;
 *     }
 *
 *     public function trim(): self
 *     {
 *         $this->data = trim($this->data);
 *         return $this;
 *     }
 * }
 *
 * $result = FluentString::from('  hello  ')
 *     ->trim()
 *     ->upper()
 *     ->reverse()
 *     ->get();
 * // Returns "OLLEH"
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 * @phpstan-consistent-constructor
 */
abstract class FluentClass
{
    /**
     * The data being transformed through the fluent chain.
     *
     * @var mixed
     */
    protected mixed $data;

    /**
     * Initializes the fluent class with optional data.
     *
     * @param mixed $data The initial data to wrap
     */
    public function __construct(mixed $data = null)
    {
        $this->data = $data;
    }

    /**
     * Invokes the object and returns the transformed data.
     *
     * This magic method allows an object to be called as a function,
     * returning the transformed data. This provides a convenient shortcut
     * to access the data without explicitly calling get().
     *
     * Example:
     * ```php
     * $fluent = (new FluentString('  hello world  '))
     *     ->trim()
     *     ->upper();
     *
     * echo $fluent(); // Outputs: 'HELLO WORLD'
     *
     * // Equivalent to:
     * echo $fluent->get();
     * ```
     *
     * @return mixed The transformed data
     */
    public function __invoke(): mixed
    {
        return $this->data;
    }

    /**
     * Creates a new fluent instance from the given data.
     *
     * This static factory method provides a convenient way to create a new
     * instance of the fluent class with initial data. It's the preferred
     * way to instantiate fluent classes.
     *
     * @param mixed $data The initial data to wrap in the fluent instance
     * @return static A new fluent instance containing the provided data
     */
    public static function from(mixed $data): static
    {
        return new static($data);
    }

    /**
     * Returns the final transformed data from the fluent chain.
     *
     * @return mixed The wrapped data after all transformations
     */
    public function get(): mixed
    {
        return $this->data;
    }

    /**
     * Handle calls to undefined instance methods.
     *
     * @param string $name The name of the method being called
     * @param array $arguments Enumerated array containing the parameters passed to the method
     * @return mixed
     * @throws MemberAccessException
     */
    public function __call(string $name, array $arguments): mixed
    {
        throw new MemberAccessException(
            sprintf('Call to undefined method %s::%s()', static::class, $name)
        );
    }

    /**
     * Handle calls to undefined static methods.
     *
     * @param string $name The name of the method being called
     * @param array $arguments Enumerated array containing the parameters passed to the method
     * @return mixed
     * @throws MemberAccessException
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        throw new MemberAccessException(
            sprintf('Call to undefined method %s::%s()', static::class, $name)
        );
    }
}
