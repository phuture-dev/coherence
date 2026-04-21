<?php

declare(strict_types=1);

namespace Phuture\Coherence\Support;

use Phuture\Coherence\Exception\{MemberAccessException, SerializationException};

/**
 * Singleton base class that ensures only one instance of a class exists throughout the application lifecycle.
 *
 * This class implements the Singleton design pattern, which restricts the instantiation of a class to a single
 * instance and provides global access to that instance. The pattern works by:
 * - Making the constructor private to prevent direct instantiation
 * - Preventing cloning via a private __clone() method
 * - Preventing unserialization via __wakeup() to avoid creating duplicate instances
 * - Providing a static getInstance() method that returns the single instance
 *
 * **Important:** Use this pattern only when strictly necessary. The Singleton pattern can make code harder to test
 * and maintain due to hidden dependencies and global state. In most cases, a Dependency Injection container is
 * the recommended approach as it provides better testability, flexibility, and follows SOLID principles.
 *
 * Example:
 * ```php
 * namespace Phuture\Coherence;
 *
 * use Phuture\Coherence\Class\SingletonClass;
 *
 * class MyService extends SingletonClass
 * {
 *     public function doSomething() { ... }
 * }
 *
 * $service = MyService::getInstance();
 * ```
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 * @phpstan-consistent-constructor
 */
abstract class SingletonClass
{
    /**
     * Holds the single instance of the current class.
     *
     * @var static|null
     */
    protected static ?SingletonClass $instance = null;

    /**
     * Class is static and cannot be instantiated.
     */
    private function __construct()
    {
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

    /**
     * Class is singleton and cannot be cloned.
     *
     * @return void
     */
    private function __clone(): void
    {
    }

    /**
     * Class is singleton and cannot be serialized.
     *
     * @return array
     * @throws SerializationException
     */
    public function __serialize(): array
    {
        $class = get_class($this);
        throw new SerializationException(
            "You cannot serialize an instance of {$class}"
        );
    }

    /**
     * Class is singleton and cannot be unserialized.
     *
     * @param array $data
     * @return void
     * @throws SerializationException
     */
    public function __unserialize(array $data): void
    {
        $class = get_class($this);
        throw new SerializationException(
            "You cannot unserialize an instance of {$class}"
        );
    }

    /**
     * This static method controls the access to the singleton class.
     * On the first run, it creates a singleton instance and places it into a private static field.
     * On subsequent runs, it returns the existing instance previously stored in the static field.
     *
     * @return self A single instance of the current class
     */
    public static function getInstance(): self
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
