<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

use Phuture\Coherence\Interface\Exception;

/**
 * Exception thrown for reflection errors.
 * This exception is used when reflection operations fail, such as trying to
 * access non-existent classes, methods, or properties.
 *
 * Common scenarios:
 * - Attempting to reflect on a non-existent class or method
 * - Trying to access inaccessible properties or methods through reflection
 * - Invalid parameter types passed to reflection methods
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class ReflectionException extends \ReflectionException implements Exception
{
}
