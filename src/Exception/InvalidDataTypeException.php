<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

use Phuture\Coherence\Interface\Exception;

/**
 * Exception thrown when an invalid data type is provided.
 * This exception is used when a function or method receives a value of the wrong
 * data type. It provides a more specific exception than the generic InvalidArgumentException
 * to help developers quickly identify type-related errors in their code.
 * Common scenarios:
 * - Passing a string when an array is expected
 * - Providing an object when a primitive type is required
 * - Supplying null when a non-nullable value is expected
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class InvalidDataTypeException extends InvalidArgumentException implements Exception
{
}
