<?php

namespace Advandz\Kernel\Exception;

/**
 * Exception thrown when attempting to unserialize a Singleton instance.
 *
 * This exception prevents the creation of duplicate instances of Singleton classes through deserialization.
 * Allowing unserialization would violate the Singleton pattern's fundamental guarantee of having only one
 * instance throughout the application lifecycle.
 *
 * The exception is thrown by the __wakeup() magic method in SingletonClass when:
 * - Attempting to unserialize a serialized Singleton object with unserialize()
 * - Restoring a Singleton instance from stored serialized data
 * - Any other deserialization operation that would create a second instance
 *
 * This protection ensures that the Singleton pattern's integrity is maintained even when objects are
 * serialized and deserialized.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class SingletonSerializationException extends \RuntimeException
{
}
