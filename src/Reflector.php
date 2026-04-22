<?php

namespace Phuture\Coherence;

use Closure;
use ReflectionEnum;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionParameter;
use Phuture\Coherence\Support\StaticClass;
use Nette\PhpGenerator\{GlobalFunction, Literal};
use Phuture\Coherence\Exception\{InvalidArgumentException, ReflectionException};

/**
 * Reflection utility class for inspecting classes, methods, properties, and functions.
 *
 * This class provides a convenient wrapper around PHP's built-in reflection API,
 * making it easier to inspect classes, methods, properties, functions, and enums
 * without dealing directly with reflection objects and exceptions.
 *
 * Key features:
 *
 * - **Class Inspection**: Check class existence, get class names, namespaces, and parent classes
 * - **Method Inspection**: Check method existence, visibility, and list all methods
 * - **Property Inspection**: Check property existence, visibility, and list all properties
 * - **Function Inspection**: Get function arity and parameter names
 * - **Alias Management**: Create class and function aliases at runtime
 * - **Trait Inspection**: List traits used by a class
 * - **Direct Reflection**: Get raw reflection objects for classes, enums,
 *   functions, methods, properties, and parameters
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Reflector extends StaticClass
{
    /**
     * Creates an alias for an existing class.
     *
     * This method registers a new name for an existing class, allowing the class
     * to be referenced by either its original name or the alias. The alias becomes
     * available globally and can be used for instantiation, type hints, and instanceof checks.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::alias(\DateTime::class, 'DT');
     *
     * $date = new DT();
     * // The DateTime class is now also accessible as DT
     * ```
     *
     * @param object|string $class The class to create an alias for, either as a class name string or an instance
     * @param string $alias The new alias name for the class
     * @return bool Returns true if the alias was created successfully
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the alias already exists
     *     or the class does not exist
     */
    public static function alias(object|string $class, string $alias): bool
    {
        if (class_exists($alias)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given alias already exists"
            );
        }

        $className = is_object($class) ? get_class($class) : $class;

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class does not exist"
            );
        }

        return class_alias($className, $alias, true);
    }

    /**
     * Creates an alias for an existing function.
     *
     * This method creates a new function that forwards all arguments to the original
     * function. The alias behaves identically to the original function for all inputs.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::aliasFunction('strlen', 'str_len');
     *
     * $length = str_len('hello');
     * // Returns: 5 (same as strlen)
     * ```
     *
     * @param string $function The name of the existing function to alias
     * @param string $alias The new alias name for the function
     * @return bool Returns true if the alias was created successfully
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the alias already exists
     *     or the function does not exist
     */
    public static function aliasFunction(string $function, string $alias): bool
    {
        if (function_exists($alias)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given alias already exists"
            );
        }

        if (!function_exists($function)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given function does not exist"
            );
        }

        if (!function_exists($alias)) {
            $closure = (new GlobalFunction($alias))
                ->setBody(
                    (string) new Literal(
                        'return call_user_func_array(?, func_get_args());',
                        [$function]
                    )
                );
            eval($closure);

            return true;
        }

        return false;
    }

    /**
     * Returns the number of parameters a callable accepts.
     *
     * This method inspects a callable (closure, function, method, or invokable object)
     * and returns the total number of declared parameters, including optional ones.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $arity = Reflector::arity(fn ($a, $b, $c = null) => $a + $b);
     * // Returns: 3
     *
     * $arity = Reflector::arity('strlen');
     * // Returns: 1
     * ```
     *
     * @param callable $method The callable to inspect (closure, function name, method array, or invokable object)
     * @return int The number of declared parameters
     * @throws \Phuture\Coherence\Exception\ReflectionException When the callable cannot be reflected
     * @see \Phuture\Coherence\Reflector::parameters() For getting the names of a callable's parameters
     */
    public static function arity(callable $method): int
    {
        try {
            if ($method instanceof Closure) {
                $reflection = new ReflectionFunction($method);
            } elseif (is_array($method)) {
                $reflection = new ReflectionMethod($method[0], $method[1]);
            } elseif (is_string($method)) {
                $reflection = new ReflectionFunction($method);
            } else {
                $reflection = new ReflectionMethod($method, '__invoke');
            }

            return $reflection->getNumberOfParameters();
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Returns the short name of a class without its namespace.
     *
     * This method extracts the class name without the namespace prefix,
     * equivalent to calling (new ReflectionClass($class))->getShortName()
     * but with built-in validation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $name = Reflector::basename(\DateTimeImmutable::class);
     * // Returns: 'DateTimeImmutable'
     *
     * $name = Reflector::basename(new \DateTime());
     * // Returns: 'DateTime'
     * ```
     *
     * @param object|string $class The class to get the short name for, either as a class name string or an instance
     * @return string The short class name without namespace
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::name() For getting the fully qualified class name
     * @see \Phuture\Coherence\Reflector::namespace() For getting only the namespace portion
     */
    public static function basename(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return $reflection->getShortName();
    }

    /**
     * Checks if a class has a specific method.
     *
     * This method determines whether the given class defines or inherits
     * a method with the specified name, regardless of its visibility.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $hasMethod = Reflector::hasMethod(\DateTime::class, 'format');
     * // Returns: true
     *
     * $hasMethod = Reflector::hasMethod(\DateTime::class, 'nonExistent');
     * // Returns: false
     * ```
     *
     * @param object|string $class The class to check, either as a class name string or an instance
     * @param string $method The method name to look for
     * @return bool Returns true if the method exists on the class, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::methods() For listing all methods of a class
     * @see \Phuture\Coherence\Reflector::methodVisibility() For getting the visibility of a method
     */
    public static function hasMethod(object|string $class, string $method): bool
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return method_exists($class, $method);
    }

    /**
     * Checks if a class has a specific property.
     *
     * This method determines whether the given class defines or inherits
     * a property with the specified name, regardless of its visibility.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class User {
     *     public string $name = '';
     * }
     *
     * $hasProp = Reflector::hasProperty(User::class, 'name');
     * // Returns: true
     *
     * $hasProp = Reflector::hasProperty(User::class, 'email');
     * // Returns: false
     * ```
     *
     * @param object|string $class The class to check, either as a class name string or an instance
     * @param string $property The property name to look for
     * @return bool Returns true if the property exists on the class, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::properties() For listing all properties of a class
     * @see \Phuture\Coherence\Reflector::propertyVisibility() For getting the visibility of a property
     */
    public static function hasProperty(object|string $class, string $property): bool
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return property_exists($class, $property);
    }

    /**
     * Checks if a string refers to an existing class.
     *
     * This method verifies that the given string is a valid, existing class name.
     * It returns false for interfaces, traits, and non-existent classes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isClass(\DateTime::class);
     * // Returns: true
     *
     * Reflector::isClass('NonExistentClass');
     * // Returns: false
     * ```
     *
     * @param string $class The class name to check
     * @return bool Returns true if the string refers to an existing class, false otherwise
     */
    public static function isClass(string $class): bool
    {
        try {
            new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            return false;
        }

        return class_exists($class);
    }

    /**
     * Checks if a method is private.
     *
     * This method inspects the given method on a class and returns true
     * if it is declared as private.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class Service {
     *     private function internalProcess(): void {}
     * }
     *
     * Reflector::isMethodPrivate(Service::class, 'internalProcess');
     * // Returns: true
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $method The method name to check
     * @return bool Returns true if the method is private, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or method cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::methodVisibility() For getting the raw visibility string
     * @see \Phuture\Coherence\Reflector::isMethodPublic() For checking if a method is public
     * @see \Phuture\Coherence\Reflector::isMethodProtected() For checking if a method is protected
     */
    public static function isMethodPrivate(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'private';
    }

    /**
     * Checks if a method is protected.
     *
     * This method inspects the given method on a class and returns true
     * if it is declared as protected.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class Repository {
     *     protected function findRaw(): array { return []; }
     * }
     *
     * Reflector::isMethodProtected(Repository::class, 'findRaw');
     * // Returns: true
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $method The method name to check
     * @return bool Returns true if the method is protected, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or method cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::methodVisibility() For getting the raw visibility string
     * @see \Phuture\Coherence\Reflector::isMethodPublic() For checking if a method is public
     * @see \Phuture\Coherence\Reflector::isMethodPrivate() For checking if a method is private
     */
    public static function isMethodProtected(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'protected';
    }

    /**
     * Checks if a method is public.
     *
     * This method inspects the given method on a class and returns true
     * if it is declared as public.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isMethodPublic(\DateTime::class, 'format');
     * // Returns: true
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $method The method name to check
     * @return bool Returns true if the method is public, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or method cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::methodVisibility() For getting the raw visibility string
     * @see \Phuture\Coherence\Reflector::isMethodPrivate() For checking if a method is private
     * @see \Phuture\Coherence\Reflector::isMethodProtected() For checking if a method is protected
     */
    public static function isMethodPublic(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'public';
    }

    /**
     * Checks if a property is private.
     *
     * This method inspects the given property on a class and returns true
     * if it is declared as private.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class User {
     *     private string $password = '';
     * }
     *
     * Reflector::isPropertyPrivate(User::class, 'password');
     * // Returns: true
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $property The property name to check
     * @return bool Returns true if the property is private, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or property cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::propertyVisibility() For getting the raw visibility string
     * @see \Phuture\Coherence\Reflector::isPropertyPublic() For checking if a property is public
     * @see \Phuture\Coherence\Reflector::isPropertyProtected() For checking if a property is protected
     */
    public static function isPropertyPrivate(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'private';
    }

    /**
     * Checks if a property is protected.
     *
     * This method inspects the given property on a class and returns true
     * if it is declared as protected.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class Model {
     *     protected array $attributes = [];
     * }
     *
     * Reflector::isPropertyProtected(Model::class, 'attributes');
     * // Returns: true
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $property The property name to check
     * @return bool Returns true if the property is protected, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or property cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::propertyVisibility() For getting the raw visibility string
     * @see \Phuture\Coherence\Reflector::isPropertyPublic() For checking if a property is public
     * @see \Phuture\Coherence\Reflector::isPropertyPrivate() For checking if a property is private
     */
    public static function isPropertyProtected(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'protected';
    }

    /**
     * Checks if a property is public.
     *
     * This method inspects the given property on a class and returns true
     * if it is declared as public.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class User {
     *     public string $name = '';
     * }
     *
     * Reflector::isPropertyPublic(User::class, 'name');
     * // Returns: true
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $property The property name to check
     * @return bool Returns true if the property is public, false otherwise
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or property cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::propertyVisibility() For getting the raw visibility string
     * @see \Phuture\Coherence\Reflector::isPropertyPrivate() For checking if a property is private
     * @see \Phuture\Coherence\Reflector::isPropertyProtected() For checking if a property is protected
     */
    public static function isPropertyPublic(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'public';
    }

    /**
     * Returns all method names defined on a class.
     *
     * This method returns an array of all public method names available on the
     * given class, including inherited methods.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $methods = Reflector::methods(\DateTime::class);
     * // Returns: ['__construct', '__wakeup', '__set_state', ... , 'format', 'modify', ...]
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @return array An array of method names available on the class
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::hasMethod() For checking if a specific method exists
     */
    public static function methods(object|string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return get_class_methods($class);
    }

    /**
     * Returns the visibility level of a method as a string.
     *
     * This method inspects the given method on a class and returns its
     * visibility level as one of: 'public', 'protected', or 'private'.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class Service {
     *     public function handle(): void {}
     *     protected function validate(): void {}
     *     private function execute(): void {}
     * }
     *
     * Reflector::methodVisibility(Service::class, 'handle');
     * // Returns: 'public'
     *
     * Reflector::methodVisibility(Service::class, 'validate');
     * // Returns: 'protected'
     *
     * Reflector::methodVisibility(Service::class, 'execute');
     * // Returns: 'private'
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $method The method name to check
     * @return string The visibility as 'public', 'protected', or 'private'
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or method cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::isMethodPublic() For checking if a method is public
     * @see \Phuture\Coherence\Reflector::isMethodProtected() For checking if a method is protected
     * @see \Phuture\Coherence\Reflector::isMethodPrivate() For checking if a method is private
     */
    public static function methodVisibility(object|string $class, string $method): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        try {
            return match (true) {
                (new ReflectionMethod($class, $method))->isPublic() => 'public',
                (new ReflectionMethod($class, $method))->isPrivate() => 'private',
                (new ReflectionMethod($class, $method))->isProtected() => 'protected'
            };
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Returns the fully qualified class name of an object.
     *
     * This method returns the complete class name including its namespace,
     * extracted from an object instance.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $name = Reflector::name(new \DateTimeImmutable());
     * // Returns: 'DateTimeImmutable'
     *
     * $name = Reflector::name(new \Phuture\Coherence\Reflector());
     * // Throws InvalidArgumentException (Reflector extends StaticClass and cannot be instantiated)
     * ```
     *
     * @param object $class The object instance to get the class name from
     * @return string The fully qualified class name of the object
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::basename() For getting only the short class name
     * @see \Phuture\Coherence\Reflector::namespace() For getting only the namespace portion
     */
    public static function name(object $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return get_class($class);
    }

    /**
     * Returns the namespace of a class.
     *
     * This method extracts only the namespace portion of a fully qualified
     * class name, without the class name itself.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $ns = Reflector::namespace(\Phuture\Coherence\Hash::class);
     * // Returns: 'Phuture\Coherence'
     *
     * $ns = Reflector::namespace(\DateTime::class);
     * // Returns: '' (empty string, since DateTime is in the global namespace)
     * ```
     *
     * @param object|string $class The class to get the namespace for, either as a class name string or an instance
     * @return string The namespace of the class, or an empty string for global namespace classes
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::basename() For getting only the short class name
     * @see \Phuture\Coherence\Reflector::name() For getting the fully qualified class name
     */
    public static function namespace(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return $reflection->getNamespaceName();
    }

    /**
     * Returns the parameter names of a callable.
     *
     * This method inspects a callable and returns an array containing the names
     * of all declared parameters, in their declaration order.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $params = Reflector::parameters(fn ($name, $age, $active = true) => null);
     * // Returns: ['name', 'age', 'active']
     *
     * $params = Reflector::parameters('strlen');
     * // Returns: ['string']
     * ```
     *
     * @param callable $method The callable to inspect (closure, function name, method array, or invokable object)
     * @return array An array of parameter names in declaration order
     * @throws \Phuture\Coherence\Exception\ReflectionException When the callable cannot be reflected
     * @see \Phuture\Coherence\Reflector::arity() For getting the number of parameters
     */
    public static function parameters(callable $method): array
    {
        try {
            if ($method instanceof Closure) {
                $reflection = new ReflectionFunction($method);
            } elseif (is_array($method)) {
                $reflection = new ReflectionMethod($method[0], $method[1]);
            } elseif (is_string($method)) {
                $reflection = new ReflectionFunction($method);
            } else {
                $reflection = new ReflectionMethod($method, '__invoke');
            }

            return array_map(
                fn ($param) => $param->getName(),
                $reflection->getParameters()
            );
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Returns the fully qualified class name of the parent class.
     *
     * This method inspects the given class and returns the name of its
     * direct parent class.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $parent = Reflector::parent(\DateTimeImmutable::class);
     * // Returns: 'DateTime'
     *
     * $parent = Reflector::parent(\stdClass::class);
     * // Throws InvalidArgumentException (stdClass has no parent)
     * ```
     *
     * @param object|string $class The class to get the parent for, either as a class name string or an instance
     * @return string The fully qualified class name of the parent class
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous or has no parent
     * @see \Phuture\Coherence\Reflector::name() For getting the class name itself
     */
    public static function parent(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        $parent = get_parent_class($class);

        if ($parent === false) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$class} does not have a parent or is not a valid class"
            );
        }

        return $parent;
    }

    /**
     * Returns all public property names with their default values for a class.
     *
     * This method returns an associative array where keys are property names
     * and values are their default values. Only public properties are included.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class User {
     *     public string $name = 'John';
     *     public int $age = 30;
     *     private string $secret = '';
     * }
     *
     * $props = Reflector::properties(User::class);
     * // Returns: ['name' => 'John', 'age' => 30]
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @return array An associative array of property names and their default values
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::hasProperty() For checking if a specific property exists
     * @see \Phuture\Coherence\Reflector::propertyVisibility() For getting the visibility of a property
     */
    public static function properties(object|string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return is_string($class) ? get_class_vars($class) : get_class_vars(get_class($class));
    }

    /**
     * Returns the visibility level of a property as a string.
     *
     * This method inspects the given property on a class and returns its
     * visibility level as one of: 'public', 'protected', or 'private'.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class Config {
     *     public string $name = '';
     *     protected array $settings = [];
     *     private string $apiKey = '';
     * }
     *
     * Reflector::propertyVisibility(Config::class, 'name');
     * // Returns: 'public'
     *
     * Reflector::propertyVisibility(Config::class, 'settings');
     * // Returns: 'protected'
     *
     * Reflector::propertyVisibility(Config::class, 'apiKey');
     * // Returns: 'private'
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @param string $property The property name to check
     * @return string The visibility as 'public', 'protected', or 'private'
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class or property cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     * @see \Phuture\Coherence\Reflector::isPropertyPublic() For checking if a property is public
     * @see \Phuture\Coherence\Reflector::isPropertyProtected() For checking if a property is protected
     * @see \Phuture\Coherence\Reflector::isPropertyPrivate() For checking if a property is private
     */
    public static function propertyVisibility(object|string $class, string $property): string
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        try {
            return match (true) {
                (new ReflectionProperty($class, $property))->isPublic() => 'public',
                (new ReflectionProperty($class, $property))->isPrivate() => 'private',
                (new ReflectionProperty($class, $property))->isProtected() => 'protected'
            };
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Creates a ReflectionClass instance for the given class.
     *
     * This method returns a native PHP ReflectionClass object, providing
     * full access to the reflection API for detailed class inspection.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectClass(\DateTime::class);
     * $methods = $reflection->getMethods();
     * $properties = $reflection->getProperties();
     * ```
     *
     * @param object|string $class The class to reflect, either as a class name string or an instance
     * @return ReflectionClass A reflection object for the given class
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @see \Phuture\Coherence\Reflector::reflectMethod() For reflecting a specific method
     * @see \Phuture\Coherence\Reflector::reflectProperty() For reflecting a specific property
     */
    public static function reflectClass(object|string $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Creates a ReflectionEnum instance for the given enum.
     *
     * This method returns a native PHP ReflectionEnum object for inspecting
     * enum types, including their cases and backing values.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * enum Color: string {
     *     case Red = 'red';
     *     case Blue = 'blue';
     * }
     *
     * $reflection = Reflector::reflectEnum(Color::class);
     * $cases = $reflection->getCases();
     * ```
     *
     * @param object|string $enum The enum to reflect, either as a class name string or an enum instance
     * @return ReflectionEnum A reflection object for the given enum
     * @throws \Phuture\Coherence\Exception\ReflectionException When the enum cannot be reflected
     * @see \Phuture\Coherence\Reflector::reflectClass() For reflecting a regular class
     */
    public static function reflectEnum(object|string $enum): ReflectionEnum
    {
        try {
            return new ReflectionEnum($enum);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Creates a ReflectionFunction instance for the given callable.
     *
     * This method returns a native PHP ReflectionFunction object for inspecting
     * function definitions, including parameters, return types, and body.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectFunction('strlen');
     * $params = $reflection->getParameters();
     * $returnType = $reflection->getReturnType();
     * ```
     *
     * @param callable $function The function to reflect
     * @return ReflectionFunction A reflection object for the given function
     * @throws \Phuture\Coherence\Exception\ReflectionException When the function cannot be reflected
     * @see \Phuture\Coherence\Reflector::reflectMethod() For reflecting a class method
     * @see \Phuture\Coherence\Reflector::arity() For getting the parameter count
     */
    public static function reflectFunction(callable $function): ReflectionFunction
    {
        try {
            return new ReflectionFunction($function);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Creates a ReflectionMethod instance for a specific method on a class.
     *
     * This method returns a native PHP ReflectionMethod object for inspecting
     * method definitions, including visibility, parameters, return types, and body.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectMethod(\DateTime::class, 'format');
     * $isPublic = $reflection->isPublic();
     * $params = $reflection->getParameters();
     * ```
     *
     * @param object|string $class The class containing the method, either as a class name string or an instance
     * @param string $method The method name to reflect
     * @return ReflectionMethod A reflection object for the given method
     * @throws \Phuture\Coherence\Exception\ReflectionException When the method cannot be reflected
     * @see \Phuture\Coherence\Reflector::reflectClass() For reflecting the entire class
     * @see \Phuture\Coherence\Reflector::methodVisibility() For getting method visibility
     */
    public static function reflectMethod(object|string $class, string $method): ReflectionMethod
    {
        try {
            return new ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Creates a ReflectionParameter instance for a specific method parameter.
     *
     * This method returns a native PHP ReflectionParameter object for inspecting
     * parameter definitions, including type, default value, and whether it is optional.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectParameter(\DateTime::class, 'format', 'format');
     * $type = $reflection->getType();
     * $hasDefault = $reflection->isDefaultValueAvailable();
     * ```
     *
     * @param object|string $class The class containing the method, either as a class name string or an instance
     * @param string $method The method name containing the parameter
     * @param string $parameter The parameter name to reflect
     * @return ReflectionParameter A reflection object for the given parameter
     * @throws \Phuture\Coherence\Exception\ReflectionException When the parameter cannot be reflected
     * @see \Phuture\Coherence\Reflector::reflectMethod() For reflecting the entire method
     * @see \Phuture\Coherence\Reflector::parameters() For getting all parameter names
     */
    public static function reflectParameter(
        object|string $class,
        string $method,
        string $parameter
    ): ReflectionParameter {
        try {
            return new ReflectionParameter([$class, $method], $parameter);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Creates a ReflectionProperty instance for a specific property on a class.
     *
     * This method returns a native PHP ReflectionProperty object for inspecting
     * property definitions, including visibility, type, and default value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class User {
     *     public string $name = '';
     * }
     *
     * $reflection = Reflector::reflectProperty(User::class, 'name');
     * $type = $reflection->getType();
     * $isPublic = $reflection->isPublic();
     * ```
     *
     * @param object|string $class The class containing the property, either as a class name string or an instance
     * @param string $property The property name to reflect
     * @return ReflectionProperty A reflection object for the given property
     * @throws \Phuture\Coherence\Exception\ReflectionException When the property cannot be reflected
     * @see \Phuture\Coherence\Reflector::reflectClass() For reflecting the entire class
     * @see \Phuture\Coherence\Reflector::propertyVisibility() For getting property visibility
     */
    public static function reflectProperty(object|string $class, string $property): ReflectionProperty
    {
        try {
            return new ReflectionProperty($class, $property);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }
    }

    /**
     * Returns all traits used by a class.
     *
     * This method returns an associative array of traits used by the given class,
     * where keys are the trait names and values are the trait names. Parent class
     * traits are not included.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * trait HasTimestamps {
     *     public function touch(): void {}
     * }
     *
     * class Model {
     *     use HasTimestamps;
     * }
     *
     * $traits = Reflector::traits(Model::class);
     * // Returns: ['HasTimestamps' => 'HasTimestamps']
     * ```
     *
     * @param object|string $class The class to inspect, either as a class name string or an instance
     * @return array An associative array of trait names used by the class
     * @throws \Phuture\Coherence\Exception\ReflectionException When the class cannot be reflected
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the given class is anonymous
     */
    public static function traits(object|string $class): array
    {
        try {
            $reflection = new ReflectionClass($class);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return class_uses($class);
    }
}
