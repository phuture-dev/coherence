<?php

namespace Advandz\Kernel\Class;

use Advandz\Kernel\Exception\MemberAccessException;

/**
 * Static base class that prevents instantiation and enforces static-only usage.
 *
 * This class is designed to be extended by classes that should only contain static methods and never be instantiated.
 * It provides protection against instantiation and improves error handling for static method calls by:
 * - Making the constructor private to prevent creating instances
 * - Implementing __callStatic() to provide clear error messages when calling non-public static methods
 *
 * The __callStatic() magic method intercepts calls to undefined or non-public static methods and throws a
 * MemberAccessException with detailed information about why the method cannot be accessed.
 *
 * **Important:** Use static classes sparingly and only when strictly necessary (e.g., utility/helper functions).
 * Static methods can make code harder to test and maintain due to:
 * - Inability to mock in unit tests
 * - Hidden dependencies and tight coupling
 * - Lack of polymorphism and interface implementation
 *
 * Consider using regular classes with dependency injection for better testability and flexibility, especially
 * for business logic. Reserve static classes for simple utility functions that have no state or dependencies.
 *
 * Example usage:
 * ```php
 * class StringHelper extends StaticClass
 * {
 *     public static function slugify(string $text): string { ... }
 * }
 *
 * $slug = StringHelper::slugify('Hello World');
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class StaticClass
{
    /**
     * Class is static and cannot be instantiated.
     */
    private function __construct()
    {
        return false;
    }

    /**
     * Call to undefined static method.
     */
    public static function __callStatic(string $name, array $args): void
    {
        $class = static::class;

        if (method_exists($class, $name)) {
            $reflection = new \ReflectionMethod($class, $name);
            $visibility = $reflection->isPrivate() ? 'private' : 'protected';

            if (!$reflection->isPublic()) {
                throw new MemberAccessException("Call to the method {$class}::{$name}() is not allowed, as is a {$visibility} method.");
            }
        }
    }
}
