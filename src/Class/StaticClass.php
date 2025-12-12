<?php

declare(strict_types=1);

namespace Phuture\Coherence\Class;

use Phuture\Coherence\Exception\MemberAccessException;

/**
 * Static base class that prevents instantiation and enforces static-only usage.
 *
 * This class is designed to be extended by classes that should only contain static methods and never be instantiated.
 *
 * **Important:** Use static classes sparingly and only when strictly necessary (e.g., utility/helper functions).
 * Static methods can make code harder to test and maintain due to:
 * - Inability to mock in unit tests
 * - Hidden dependencies and tight coupling
 * - Lack of polymorphism and interface implementation
 *
 * Consider using regular classes with dependency injection for better testability and flexibility.
 * Reserve static classes for simple utility functions that have no state or dependencies.
 *
 * Example:
 * ```php
 * namespace Phuture\Coherence;
 *
 * use Phuture\Coherence\Class\StaticClass;
 *
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
 * @link https://www.phuture.dev/ Phuture
 */
abstract class StaticClass
{
    use \Nette\StaticClass {
        __callStatic as protected callStatic;
    }

    /**
     * Call to undefined static method.
     */
    public static function __callStatic(string $name, array $args): mixed
    {
        try {
            return static::callStatic($name, $args);
        } catch (\Throwable $e) {
            throw new MemberAccessException($e->getMessage(), $e->getCode());
        }
    }
}
