<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

use Phuture\Coherence\Interface\Exception;

/**
 * Exception thrown when a class cannot be found or loaded.
 * This exception indicates that an attempt was made to use a class that does
 * not exist, cannot be loaded, or is not available in the current execution context.
 *
 * Common scenarios where this exception is thrown:
 * - Attempting to instantiate a non-existent class
 * - Autoloader fails to locate a class file
 * - Missing dependencies or required extensions
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class ClassNotFoundException extends LogicException implements Exception
{
}
