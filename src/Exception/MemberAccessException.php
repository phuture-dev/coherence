<?php

namespace Advandz\Kernel\Exception;

/**
 * Exception thrown when attempting to access a class member (method or property) that is not accessible.
 *
 * This exception is typically thrown when:
 * - Attempting to call a private or protected static method from outside the class
 * - Attempting to access a class member that should not be publicly accessible
 * - Violating access control rules enforced by classes like StaticClass
 *
 * Example scenarios:
 * - Calling a protected static method: `MyStaticClass::protectedMethod()`
 * - Attempting to instantiate a class that should not be instantiated
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class MemberAccessException extends \RuntimeException
{
}
