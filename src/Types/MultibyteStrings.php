<?php

declare(strict_types=1);

namespace Phuture\Coherence\Types;

use Phuture\Coherence\Class\FluentClass;

/**
 * Fluent wrapper for multibyte string manipulation operations.
 *
 * This class provides a fluent interface for chaining multibyte string operations from the MultibyteStrings helper class.
 * It extends FluentClass and automatically forwards chained method calls to the static methods in
 * \Phuture\Coherence\MultibyteStrings, allowing for elegant method chaining on multibyte string data.
 *
 * Example:
 * ```php
 * use Phuture\Coherence\Types\MultibyteStrings;
 *
 * // Chain multiple multibyte string operations
 * $result = (new MultibyteStrings('Héllo Wörld'))
 *     ->upper()
 *     ->substr(0, 10)
 *     ->get();
 * // Returns: "HÉLLO WÖRL" (properly handles accented characters)
 *
 * // Process international text
 * $japanese = (new MultibyteStrings('こんにちは世界'))
 *     ->length()  // Returns correct character count, not byte count
 *     ->get();
 *
 * // Convert encoding
 * $text = (new MultibyteStrings($input))
 *     ->convertEncoding('UTF-8', 'ISO-8859-1')
 *     ->get();
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 * @see \Phuture\Coherence\MultibyteStrings For available multibyte string manipulation methods
 * @see \Phuture\Coherence\Class\FluentClass For the base fluent interface implementation
 */
class MultibyteStrings extends FluentClass
{
    protected ?string $class = \Phuture\Coherence\MultibyteStrings::class;
}
