<?php

declare(strict_types=1);

namespace Phuture\Coherence\Types;

use Phuture\Coherence\Class\FluentClass;

/**
 * Fluent wrapper for string manipulation operations.
 *
 * This class provides a fluent interface for chaining string operations from the Strings helper class.
 * It extends FluentClass and automatically forwards chained method calls to the static methods in
 * \Phuture\Coherence\Strings, allowing for elegant method chaining on string data.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Types\Strings;
 *
 * // Chain multiple string operations
 * $result = (new Strings('  Hello World  '))
 *     ->trim()
 *     ->lower()
 *     ->replace('world', 'PHP')
 *     ->get();
 * // Returns: "hello php"
 *
 * // Process user input
 * $username = (new Strings($userInput))
 *     ->trim()
 *     ->substr(0, 20)
 *     ->lower()
 *     ->get();
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 * @see \Phuture\Coherence\Strings For available string manipulation methods
 * @see \Phuture\Coherence\Class\FluentClass For the base fluent interface implementation
 */
class Strings extends FluentClass
{
    protected ?string $class = \Phuture\Coherence\Strings::class;
}
