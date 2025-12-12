<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

/**
 * Exception thrown when an invalid argument is provided to a method.
 *
 * Common scenarios where this exception is thrown:
 * - A required parameter is missing, and no default value was provided
 * - An argument value is outside the expected range or format
 * - A parameter fails validation checks
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{
}
