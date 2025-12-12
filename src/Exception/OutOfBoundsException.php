<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

/**
 * Exception thrown when a value is not a valid key or when accessing an array
 * with an invalid offset or index.
 *
 * Common scenarios where this exception is thrown:
 * - Accessing an array with an offset that doesn't exist
 * - Using negative indices with collections that don't support them
 * - Attempting to access an element beyond the collection's size
 * - Providing an invalid key type for indexed collections
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class OutOfBoundsException extends \OutOfBoundsException implements Exception
{
}
