<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

use Phuture\Coherence\Interface\Exception;

/**
 * Exception thrown for runtime errors that occur during program execution.
 * Common scenarios where this exception is thrown:
 * - Invalid method calls or argument combinations
 * - Runtime validation failures
 * - Resource constraints or unavailability
 * - Invalid state transitions
 * - External system integration failures
 * - Configuration errors detected at runtime
 * This exception should be used for errors that are not caused by programming
 * mistakes but rather by conditions that arise during normal program execution.
 * For programming errors, consider using more specific exception types.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class RuntimeException extends \RuntimeException implements Exception
{
}
