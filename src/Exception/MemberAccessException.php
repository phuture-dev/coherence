<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

/**
 * Exception thrown when attempting to access a class member (method or property) that is not accessible.
 *
 * Common scenarios where this exception is thrown:
 * - Attempting to call a private or protected method from outside the class
 * - Attempting to access a class member that should not be publicly accessible
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class MemberAccessException extends BadMethodCallException implements Exception
{
}
