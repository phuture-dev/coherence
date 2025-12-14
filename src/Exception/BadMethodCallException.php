<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

use Phuture\Coherence\Interface\Exception;

/**
 * Exception thrown when a call to a method is not valid.
 * This exception is used when attempting to call a method that doesn't exist,
 * is not accessible, or when method arguments are invalid.
 * Common scenarios:
 * - Calling a method that doesn't exist on the object
 * - Calling methods with an incorrect number or type of arguments
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class BadMethodCallException extends \BadMethodCallException implements Exception
{
}
