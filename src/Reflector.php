<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Closure;
use ReflectionEnum;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionProperty;
use ReflectionParameter;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\{InvalidArgumentException, ReflectionException};

/**
 * PHP reflection utility class for inspecting classes, methods, properties, and functions at runtime.
 *
 * This utility class wraps PHP's native Reflection API into a clean, consistent static interface.
 * It enables runtime inspection of class hierarchies, method and property visibility, callable
 * signatures, and structural metadata — all with a uniform error-handling layer that converts
 * native PHP reflection exceptions into framework-aware exceptions.
 *
 * Key features:
 *
 * - **Class Inspection**: Retrieve class names, namespaces, parent classes, and traits
 * - **Method & Property Introspection**: Check existence and visibility of methods and properties
 * - **Callable Analysis**: Inspect parameter lists and arity of any callable value
 * - **Safe Function Aliasing**: Wrap an existing function as a reusable Closure without eval()
 * - **Class & Enum Aliasing**: Register class and enum aliases at runtime
 * - **Reflection Instantiation**: Obtain ReflectionClass, ReflectionEnum, ReflectionFunction,
 *   ReflectionMethod, ReflectionParameter, and ReflectionProperty instances with unified error handling
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Reflector extends StaticClass
{
    /**
     * Registers a runtime alias for an existing class.
     *
     * This method creates an alternative name that points to the same class definition.
     * After aliasing, the alias name can be used anywhere the original class name would be
     * used, including in type hints, instanceof checks, and object instantiation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::alias(\App\Services\UserService::class, 'UserService');
     * // Returns: true
     *
     * // Both names now refer to the same class
     * $service = new UserService();
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance to alias.
     * @param string $alias The new alias name to register for the class.
     * @return bool Returns true when the alias is successfully registered
     * @throws InvalidArgumentException If the alias name is already in use or the class does not exist
     */
    public static function alias(object|string $class, string $alias): bool
    {
        $className = is_object($class) ? get_class($class) : $class;

        if (class_exists($alias)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given alias already exists"
            );
        }

        if (!class_exists($className)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class does not exist"
            );
        }

        return class_alias($className, $alias, true);
    }

    /**
     * Returns a Closure that wraps an existing named function.
     *
     * This method converts a named PHP function into a first-class Closure object. The returned
     * Closure forwards all arguments to the original function and returns its result unchanged.
     * It is a safe alternative to dynamically registering global function aliases with eval().
     *
     * The closure can be stored in a variable, passed as a callback, or invoked directly with
     * the same argument signature as the original function.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $trim = Reflector::aliasFunction('trim');
     * $trim('  hello world  ');
     * // Returns: 'hello world'
     *
     * $pad = Reflector::aliasFunction('str_pad');
     * $pad('hello', 10, '-', STR_PAD_BOTH);
     * // Returns: '--hello---'
     * ```
     *
     * @param string $function The name of the existing PHP function to wrap.
     * @return Closure Returns a Closure that delegates all calls to the named function
     * @throws InvalidArgumentException If the named function does not exist
     */
    public static function aliasFunction(string $function): Closure
    {
        if (!function_exists($function)) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given function does not exist"
            );
        }

        return Closure::fromCallable($function);
    }

    /**
     * Returns the total number of parameters a callable accepts.
     *
     * This method inspects the parameter list of any callable value — closures, named functions,
     * instance methods, static methods, and invokable objects — and returns the count of all
     * declared parameters, both required and optional.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::arity('strlen');
     * // Returns: 1
     *
     * Reflector::arity(function (string $a, int $b = 0): void {});
     * // Returns: 2
     *
     * Reflector::arity([new MyClass(), 'myMethod']);
     * // Returns: (the number of parameters declared on myMethod)
     * ```
     *
     * @param callable $method The callable to inspect.
     * @return int Returns the total number of declared parameters
     * @throws ReflectionException If the callable cannot be reflected
     * @see Reflector::parameters()
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
     * Returns the short (unqualified) name of a class.
     *
     * This method extracts the class name without its namespace prefix. For example,
     * a class declared as \App\Services\UserService returns 'UserService'. Anonymous
     * classes are not supported and will throw an exception.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::basename(\App\Services\UserService::class);
     * // Returns: 'UserService'
     *
     * Reflector::basename(new \App\Models\Post());
     * // Returns: 'Post'
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return string Returns the unqualified class name without the namespace prefix
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::name()
     * @see Reflector::namespace()
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
     * Checks whether a class declares or inherits a method with the given name.
     *
     * This method returns true when the named method exists anywhere in the class hierarchy,
     * including inherited and trait-provided methods. Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::hasMethod(\App\Models\User::class, 'save');
     * // Returns: true (if User or a parent declares save())
     *
     * Reflector::hasMethod(\App\Models\User::class, 'nonExistentMethod');
     * // Returns: false
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name to look up.
     * @return bool Returns true if the method exists on the class or any of its parents
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::methods()
     * @see Reflector::methodVisibility()
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
     * Checks whether a class declares a property with the given name.
     *
     * This method returns true when the named property exists on the class, including
     * properties inherited from parent classes. Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::hasProperty(\App\Models\User::class, 'name');
     * // Returns: true (if User declares $name)
     *
     * Reflector::hasProperty(\App\Models\User::class, 'undeclaredProp');
     * // Returns: false
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $property The property name to look up.
     * @return bool Returns true if the property exists on the class
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::properties()
     * @see Reflector::propertyVisibility()
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
     * Checks whether a class with the given name has been defined.
     *
     * This method returns true only when the name refers to a concrete class, abstract class,
     * or interface that can be reflected. It returns false for unknown names, interfaces that
     * fail reflection, and names that resolve only via autoloading errors.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isClass(\App\Models\User::class);
     * // Returns: true
     *
     * Reflector::isClass('NonExistentClass');
     * // Returns: false
     * ```
     *
     * @param string $class The fully-qualified class name to check.
     * @return bool Returns true if the class exists and can be reflected, false otherwise
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
     * Checks whether a method on the given class is declared as private.
     *
     * Private methods are only accessible within the class body that declares them.
     * This method delegates to methodVisibility() and compares the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isMethodPrivate(\App\Services\Hasher::class, 'generateSalt');
     * // Returns: true (if generateSalt is declared private)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name to check.
     * @return bool Returns true if the method is private
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or method cannot be reflected
     * @see Reflector::isMethodPublic()
     * @see Reflector::isMethodProtected()
     * @see Reflector::methodVisibility()
     */
    public static function isMethodPrivate(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'private';
    }

    /**
     * Checks whether a method on the given class is declared as protected.
     *
     * Protected methods are accessible within the declaring class and its subclasses.
     * This method delegates to methodVisibility() and compares the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isMethodProtected(\App\Models\BaseModel::class, 'boot');
     * // Returns: true (if boot is declared protected)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name to check.
     * @return bool Returns true if the method is protected
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or method cannot be reflected
     * @see Reflector::isMethodPublic()
     * @see Reflector::isMethodPrivate()
     * @see Reflector::methodVisibility()
     */
    public static function isMethodProtected(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'protected';
    }

    /**
     * Checks whether a method on the given class is declared as public.
     *
     * Public methods are accessible from anywhere. This method delegates to
     * methodVisibility() and compares the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isMethodPublic(\App\Models\User::class, 'getName');
     * // Returns: true (if getName is declared public)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name to check.
     * @return bool Returns true if the method is public
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or method cannot be reflected
     * @see Reflector::isMethodPrivate()
     * @see Reflector::isMethodProtected()
     * @see Reflector::methodVisibility()
     */
    public static function isMethodPublic(object|string $class, string $method): bool
    {
        return self::methodVisibility($class, $method) === 'public';
    }

    /**
     * Checks whether a property on the given class is declared as private.
     *
     * Private properties are only accessible within the class body that declares them.
     * This method delegates to propertyVisibility() and compares the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isPropertyPrivate(\App\Models\User::class, 'passwordHash');
     * // Returns: true (if passwordHash is declared private)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $property The property name to check.
     * @return bool Returns true if the property is private
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or property cannot be reflected
     * @see Reflector::isPropertyPublic()
     * @see Reflector::isPropertyProtected()
     * @see Reflector::propertyVisibility()
     */
    public static function isPropertyPrivate(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'private';
    }

    /**
     * Checks whether a property on the given class is declared as protected.
     *
     * Protected properties are accessible within the declaring class and its subclasses.
     * This method delegates to propertyVisibility() and compares the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isPropertyProtected(\App\Models\BaseModel::class, 'timestamps');
     * // Returns: true (if timestamps is declared protected)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $property The property name to check.
     * @return bool Returns true if the property is protected
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or property cannot be reflected
     * @see Reflector::isPropertyPublic()
     * @see Reflector::isPropertyPrivate()
     * @see Reflector::propertyVisibility()
     */
    public static function isPropertyProtected(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'protected';
    }

    /**
     * Checks whether a property on the given class is declared as public.
     *
     * Public properties are accessible from anywhere. This method delegates to
     * propertyVisibility() and compares the result.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::isPropertyPublic(\App\Models\Config::class, 'debug');
     * // Returns: true (if debug is declared public)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $property The property name to check.
     * @return bool Returns true if the property is public
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or property cannot be reflected
     * @see Reflector::isPropertyPrivate()
     * @see Reflector::isPropertyProtected()
     * @see Reflector::propertyVisibility()
     */
    public static function isPropertyPublic(object|string $class, string $property): bool
    {
        return self::propertyVisibility($class, $property) === 'public';
    }

    /**
     * Returns the names of all public methods declared on a class.
     *
     * This method returns a flat list of method names that are publicly accessible on the
     * given class, including methods inherited from parent classes. Anonymous classes are
     * not supported and will throw an exception.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::methods(\App\Models\User::class);
     * // Returns: ['save', 'delete', 'getName', ...]
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return array Returns a list of public method names available on the class
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::hasMethod()
     * @see Reflector::methodVisibility()
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
     * Returns the visibility level of a specific method on a class.
     *
     * This method inspects the method's declaration and returns one of three string values:
     * 'public', 'protected', or 'private'. Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::methodVisibility(\App\Models\User::class, 'save');
     * // Returns: 'public'
     *
     * Reflector::methodVisibility(\App\Services\Hasher::class, 'generateSalt');
     * // Returns: 'private'
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name to inspect.
     * @return string Returns 'public', 'protected', or 'private'
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or method cannot be reflected
     * @see Reflector::isMethodPublic()
     * @see Reflector::isMethodProtected()
     * @see Reflector::isMethodPrivate()
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
            $rm = new ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        return match (true) {
            $rm->isPublic() => 'public',
            $rm->isPrivate() => 'private',
            default => 'protected'
        };
    }

    /**
     * Returns the fully-qualified class name of an object instance.
     *
     * This method retrieves the complete class name including its namespace from an object.
     * It accepts only object instances (not class name strings) and will throw if the object
     * is an anonymous class, which has no stable name.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $user = new \App\Models\User();
     * Reflector::name($user);
     * // Returns: 'App\Models\User'
     * ```
     *
     * @param object $class The object instance to inspect.
     * @return string Returns the fully-qualified class name including namespace
     * @throws InvalidArgumentException If the object is an anonymous class
     * @throws ReflectionException If the object cannot be reflected
     * @see Reflector::basename()
     * @see Reflector::namespace()
     */
    public static function name(object $class): string
    {
        $reflection = new ReflectionClass($class);

        if ($reflection->isAnonymous()) {
            throw new InvalidArgumentException(
                "Invalid Argument: The given class is anonymous"
            );
        }

        return get_class($class);
    }

    /**
     * Returns the namespace portion of a class name.
     *
     * This method extracts the namespace without the short class name. For a class declared
     * as \App\Services\UserService it returns 'App\Services'. Classes that are not namespaced
     * return an empty string. Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::namespace(\App\Services\UserService::class);
     * // Returns: 'App\Services'
     *
     * Reflector::namespace(\stdClass::class);
     * // Returns: ''
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return string Returns the namespace of the class, or an empty string if not namespaced
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::basename()
     * @see Reflector::name()
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
     * Returns the parameter names of a callable as an ordered list.
     *
     * This method inspects the parameter list of any callable value — closures, named functions,
     * instance methods, static methods, and invokable objects — and returns their names in
     * declaration order.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::parameters('str_replace');
     * // Returns: ['search', 'replace', 'subject', 'count']
     *
     * Reflector::parameters(function (string $firstName, int $age = 0): void {});
     * // Returns: ['firstName', 'age']
     * ```
     *
     * @param callable $method The callable to inspect.
     * @return array Returns an ordered list of parameter names as strings
     * @throws ReflectionException If the callable cannot be reflected
     * @see Reflector::arity()
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
     * Returns the fully-qualified name of the parent class.
     *
     * This method returns the name of the class that the given class directly extends.
     * It throws an exception when the class has no parent (i.e., it extends nothing or only
     * extends a built-in type). Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::parent(\App\Models\AdminUser::class);
     * // Returns: 'App\Models\User' (if AdminUser extends User)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return string Returns the fully-qualified name of the parent class
     * @throws InvalidArgumentException If the class is anonymous or has no parent
     * @throws ReflectionException If the class cannot be reflected
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
     * Returns all public properties of a class as an associative array.
     *
     * This method returns the names and default values of all public properties declared
     * on the class. For object instances, the current runtime values are returned. For class
     * name strings, the default values defined in the class body are returned.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * class Config {
     *     public string $driver = 'mysql';
     *     public int $port = 3306;
     * }
     *
     * Reflector::properties(Config::class);
     * // Returns: ['driver' => 'mysql', 'port' => 3306]
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return array Returns an associative array of property names to their default values
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::hasProperty()
     * @see Reflector::propertyVisibility()
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
     * Returns the visibility level of a specific property on a class.
     *
     * This method inspects the property's declaration and returns one of three string values:
     * 'public', 'protected', or 'private'. Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::propertyVisibility(\App\Models\User::class, 'name');
     * // Returns: 'public'
     *
     * Reflector::propertyVisibility(\App\Models\User::class, 'passwordHash');
     * // Returns: 'private'
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $property The property name to inspect.
     * @return string Returns 'public', 'protected', or 'private'
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class or property cannot be reflected
     * @see Reflector::isPropertyPublic()
     * @see Reflector::isPropertyProtected()
     * @see Reflector::isPropertyPrivate()
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
            $rp = new ReflectionProperty($class, $property);
        } catch (\ReflectionException $e) {
            throw new ReflectionException(
                "Reflection Error: " . $e->getMessage()
            );
        }

        return match (true) {
            $rp->isPublic() => 'public',
            $rp->isPrivate() => 'private',
            default => 'protected'
        };
    }

    /**
     * Returns a ReflectionClass instance for the given class.
     *
     * This method wraps the ReflectionClass constructor and converts PHP's native
     * \ReflectionException into the framework's own ReflectionException, providing a
     * consistent error-handling experience across the codebase.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectClass(\App\Models\User::class);
     * $reflection->getMethods();
     * // Returns: array of ReflectionMethod instances
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return ReflectionClass Returns a ReflectionClass instance for the given class
     * @throws ReflectionException If the class cannot be reflected
     * @see Reflector::reflectMethod()
     * @see Reflector::reflectProperty()
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
     * Returns a ReflectionEnum instance for the given enum.
     *
     * This method wraps the ReflectionEnum constructor and converts PHP's native
     * \ReflectionException into the framework's own ReflectionException, providing a
     * consistent error-handling experience across the codebase.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectEnum(\App\Enums\Status::class);
     * $reflection->getCases();
     * // Returns: array of ReflectionEnumUnitCase instances
     * ```
     *
     * @param object|string $enum The fully-qualified enum class name or an enum instance.
     * @return ReflectionEnum Returns a ReflectionEnum instance for the given enum
     * @throws ReflectionException If the enum cannot be reflected
     * @see Reflector::reflectClass()
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
     * Returns a ReflectionFunction instance for the given callable.
     *
     * This method wraps the ReflectionFunction constructor and converts PHP's native
     * \ReflectionException into the framework's own ReflectionException. It accepts both
     * named functions and Closure instances.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectFunction('strlen');
     * $reflection->getNumberOfParameters();
     * // Returns: 1
     *
     * $reflection = Reflector::reflectFunction(fn (int $x) => $x * 2);
     * $reflection->getNumberOfParameters();
     * // Returns: 1
     * ```
     *
     * @param callable $function The named function or Closure to reflect.
     * @return ReflectionFunction Returns a ReflectionFunction instance for the given callable
     * @throws ReflectionException If the function cannot be reflected
     * @see Reflector::reflectMethod()
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
     * Returns a ReflectionMethod instance for a specific method on a class.
     *
     * This method wraps the ReflectionMethod constructor and converts PHP's native
     * \ReflectionException into the framework's own ReflectionException, providing a
     * consistent error-handling experience across the codebase.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectMethod(\App\Models\User::class, 'save');
     * $reflection->isPublic();
     * // Returns: true (if save is public)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name to reflect.
     * @return ReflectionMethod Returns a ReflectionMethod instance for the given method
     * @throws ReflectionException If the class or method cannot be reflected
     * @see Reflector::reflectClass()
     * @see Reflector::reflectParameter()
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
     * Returns a ReflectionParameter instance for a specific parameter on a class method.
     *
     * This method wraps the ReflectionParameter constructor and converts PHP's native
     * \ReflectionException into the framework's own ReflectionException. It identifies the
     * parameter by its declared name, not its position.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectParameter(\App\Models\User::class, 'setAge', 'age');
     * $reflection->getType()->getName();
     * // Returns: 'int' (if age is declared as int)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $method The method name that declares the parameter.
     * @param string $parameter The name of the parameter to reflect.
     * @return ReflectionParameter Returns a ReflectionParameter instance for the given parameter
     * @throws ReflectionException If the class, method, or parameter cannot be reflected
     * @see Reflector::reflectMethod()
     * @see Reflector::parameters()
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
     * Returns a ReflectionProperty instance for a specific property on a class.
     *
     * This method wraps the ReflectionProperty constructor and converts PHP's native
     * \ReflectionException into the framework's own ReflectionException, providing a
     * consistent error-handling experience across the codebase.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * $reflection = Reflector::reflectProperty(\App\Models\User::class, 'name');
     * $reflection->isPublic();
     * // Returns: true (if name is declared public)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @param string $property The property name to reflect.
     * @return ReflectionProperty Returns a ReflectionProperty instance for the given property
     * @throws ReflectionException If the class or property cannot be reflected
     * @see Reflector::reflectClass()
     * @see Reflector::reflectMethod()
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
     * Returns all traits used directly by a class.
     *
     * This method returns the names of traits that are directly used in the class body via
     * the `use` keyword. Traits used by parent classes or by traits themselves are not included.
     * Anonymous classes are not supported.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Reflector;
     *
     * Reflector::traits(\App\Models\User::class);
     * // Returns: ['App\Traits\HasTimestamps', 'App\Traits\SoftDeletes']
     * // (only the traits directly used in User, not inherited ones)
     * ```
     *
     * @param object|string $class The fully-qualified class name or an object instance.
     * @return array Returns an associative array of trait names used directly by the class
     * @throws InvalidArgumentException If the class is anonymous
     * @throws ReflectionException If the class cannot be reflected
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
