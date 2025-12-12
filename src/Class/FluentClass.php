<?php

declare(strict_types=1);

namespace Phuture\Coherence\Class;

use Phuture\Coherence\Exception\InvalidDataTypeException;
use Phuture\Coherence\Exception\BadMethodCallException;
use Phuture\Coherence\Exception\ClassNotFoundException;
use Phuture\Coherence\Exception\MemberAccessException;
use Phuture\Coherence\Exception\ReflectionException;
use ReflectionMethod;

/**
 * Base class for creating fluent interfaces that enable method chaining for data transformations.
 *
 * This class provides the foundation for implementing the fluent interface pattern, allowing for more readable,
 * expressive code by eliminating intermediate variables and creating a natural language-like syntax for
 * sequential operations on data.
 *
 * **Recommended Usage: Custom Methods**
 *
 * The intended and recommended way to use FluentClass is by extending it and implementing your own transformation
 * methods. Each method should transform the internal `$data` property and return `$this` to enable chaining.
 * This approach provides type safety, IDE autocomplete support, and better maintainability.
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
 * $result = (new FluentString('  hello  '))
 *     ->trim()
 *     ->upper()
 *     ->reverse()
 *     ->get();
 * // Returns "OLLEH"
 * ```
 *
 * **Alternative: Fallback Class**
 *
 * FluentClass can also function as a wrapper that automatically forwards undefined method calls to static methods
 * in another class. To use this pattern, extend FluentClass and set the `$class` property to the fully qualified
 * name of the fallback class. You can still implement custom methods alongside the fallback functionality.
 *
 * This approach is **not recommended** for general use as it hides dependencies, prevents IDE hints and autocomplete,
 * and can lead to runtime errors. Use this pattern only when absolutely necessary.
 *
 * Example:
 * ```php
 * namespace Phuture\Coherence\Types;
 *
 * use Phuture\Coherence\Class\FluentClass;
 *
 * class Arrays extends FluentClass
 * {
 *     protected ?string $class = \Phuture\Coherence\Arrays::class;
 *
 *     // You can still implement custom methods
 *     public function customMethod(): self
 *     {
 *         // Custom logic here
 *         return $this;
 *     }
 * }
 *
 * // Methods not found in Arrays class will fallback to \Phuture\Coherence\Arrays static methods
 * $result = (new Arrays([1, 2, 3]))
 *     ->reverse()
 *     ->get();
 * // Returns [3, 2, 1]
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
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
     * The fully qualified class name whose methods will be called.
     *
     * @var string|null
     */
    protected ?string $class = null;

    /**
     * Initializes the fluent wrapper with optional data.
     *
     * @param mixed $data The initial data to wrap
     */
    public function __construct(mixed $data = null)
    {
        $this->data = $data;
    }

    /**
     * Returns the final transformed data from the fluent chain.
     *
     * @return mixed The wrapped data after all transformations
     */
    final public function get(): mixed
    {
        return $this->data;
    }

    /**
     * Dynamically calls static methods from the configured class.
     *
     * This magic method intercepts method calls and forwards them to the class set in $this->class.
     * To maintain the fluent chain, the called method must meet strict type requirements:
     *
     * 1. The method must be public
     * 2. The first parameter type must match the initial data type (e.g., 'array' for array data)
     * 3. The return type must also match the initial data type
     *
     * This ensures type safety throughout the chain. For example, if you initialize with an array,
     * only methods that accept an array as the first parameter and return an array can be chained.
     * Methods that return other types (int, bool, string, etc.) will throw an exception.
     *
     * Example:
     * ```php
     * // Assuming Arrays class with methods that accept and return arrays
     * $result = (new \Phuture\Coherence\Types\Arrays([1, 2, 3]))
     *     ->filter(fn($v) => $v > 1) // ✓ array → array (chainable)
     *     ->reverse() // ✓ array → array (chainable)
     *     ->get();
     *
     * // This would fail:
     * $result = (new \Phuture\Coherence\Types\Arrays([1, 2, 3]))
     *     ->length() // ✗ array → int (NOT chainable, throws exception)
     *     ->get();
     * ```
     *
     * @param string $name The method name to call
     * @param array $arguments The arguments to pass to the method (first param is auto-injected)
     * @return FluentClass Returns $this for method chaining
     * @throws MemberAccessException If the method is private/protected or not accessible
     * @throws ClassNotFoundException If the configured fallback class doesn't exist
     * @throws BadMethodCallException If the method doesn't exist on this class
     * @throws ReflectionException If the fallback class fails to be reflected
     * @throws InvalidDataTypeException If method return/parameter types don't match the data type
     */
    public function __call(string $name, array $arguments): FluentClass
    {
        $class = class_name($this);

        // Throw an error if the method is private or protected
        if (class_method_exists($this, $name)) {
            try {
                $reflection = new ReflectionMethod($this, $name);
            } catch (\Throwable $e) {
                throw new ReflectionException($e->getMessage(), $e->getCode());
            }

            $visibility = $reflection->isPrivate() ? 'private' : 'protected';

            throw new MemberAccessException(
                "Call to method {$class}::{$name}() is not allowed, as it is a {$visibility} method"
            );
        }

        // Throw an error if the class is not valid
        if (!is_null($this->class) && !class_exists($this->class)) {
            throw new ClassNotFoundException(
                "{$this->class} is not a valid class or does not exist"
            );
        }

        // Fetch the return type and parameters from the method in the wrapping class
        if (!is_null($this->class) && !is_null($this->data)) {
            return $this->remoteStaticCall($this->class, $name, $arguments);
        }

        throw new BadMethodCallException(
            "Method {$class}::{$name}() does not exist"
        );
    }

    /**
     * Calls a static method on a remote class with type validation.
     *
     * This private helper method validates that the target method exists, is public,
     * and has compatible type signatures before calling it. It ensures type safety
     * by checking that both the first parameter and return type match the current data type.
     *
     * @param object|string $class The fully qualified class name to call the method on
     * @param string $method The method name to call
     * @param array $arguments Additional arguments to pass to the method (excluding the data)
     * @return static Returns $this for method chaining
     * @throws ReflectionException If reflection fails when analyzing the method
     * @throws BadMethodCallException If the method doesn't exist on the class
     * @throws MemberAccessException If the method is not public or accessible
     * @throws InvalidDataTypeException If method types don't match the current data type
     */
    private function remoteStaticCall(object|string $class, string $method, array $arguments): static
    {
        try {
            $reflection = new ReflectionMethod($class, $method);
        } catch (\Throwable $e) {
            throw new ReflectionException($e->getMessage(), $e->getCode());
        }

        // Throw error if the method or class doesn't exist
        if (!class_method_exists($class, $method) || !class_exists($class)) {
            throw new BadMethodCallException(
                "Method {$class}::{$method}() does not exist"
            );
        }

        // Throw error if the method is not public
        if (($visibility = class_method_visibility($class, $method)) !== 'public') {
            throw new MemberAccessException(
                "Call to method {$class}::{$method}() is not allowed, as it is a {$visibility} method"
            );
        }

        // Call the remote static method
        $type = get_type($this->data);
        if (
            $reflection->getReturnType()->getName() == $type
            && $reflection->getParameters()[0]?->getType() == $type
        ) {
            $this->data = call_user_func_array([$class, $method], array_merge([$this->data], $arguments));
        } else {
            throw new InvalidDataTypeException(
                "Method {$class}::{$method}() does not match the initial data type ({$type})"
            );
        }

        return $this;
    }
}
