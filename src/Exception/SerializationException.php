<?php

declare(strict_types=1);

namespace Phuture\Coherence\Exception;

use Phuture\Coherence\Interface\Exception;

/**
 * Exception thrown when serialization or deserialization operations fail.
 *
 * Common scenarios where this exception is thrown:
 * - PHP serialization encounters non-serializable objects
 * - Unsupported data types cannot be serialized
 * - Malformed serialized data cannot be deserialized
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class SerializationException extends LogicException implements Exception
{
}
