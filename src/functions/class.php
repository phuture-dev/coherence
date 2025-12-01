<?php

declare(strict_types=1);

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the native class API.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

if (!function_exists('class_name')) {
    /**
     * Gets the fully qualified class name of an object.
     *
     * Provides a consistent wrapper around the native function get_class.
     *
     * @param object $class The object to get the class name from
     * @return string Returns the fully qualified class name
     * @see https://www.php.net/manual/en/function.get-class.php
     */
    function class_name(object $class): string
    {
        return get_class($class);
    }
}

if (!function_exists('class_basename')) {
    /**
     * Gets the class name without namespace.
     *
     * Provides a way to get just the short class name using ReflectionClass.
     *
     * @param object $class The object to get the basename from
     * @return string Returns the class name without namespace
     * @throws RuntimeException If reflection fails
     * @see https://www.php.net/manual/en/reflectionclass.getshortname.php
     */
    function class_basename(object $class): string
    {
        try {
            $reflection = new ReflectionClass($class);

            return $reflection->getShortName();
        } catch (ReflectionException $exception) {
            throw new RuntimeException($exception->getMessage());
        }
    }
}

if (!function_exists('class_namespace')) {
    /**
     * Gets the namespace of a class.
     *
     * Provides a way to get the namespace using ReflectionClass.
     *
     * @param object $class The object to get the namespace from
     * @return string Returns the namespace or '\\' if no namespace
     * @throws RuntimeException If reflection fails
     * @see https://www.php.net/manual/en/reflectionclass.getnamespacename.php
     */
    function class_namespace(object $class): string
    {
        try {
            $reflection = new ReflectionClass($class);

            return $reflection->getNamespaceName() ?? '\\';
        } catch (ReflectionException $exception) {
            throw new RuntimeException($exception->getMessage());
        }
    }
}

if (!function_exists('class_parent')) {
    /**
     * Gets the parent class name of an object.
     *
     * Provides a consistent wrapper around the native function get_parent_class.
     *
     * @param object $class The object to get the parent class from
     * @return string Returns the parent class name
     * @throws RuntimeException If the class has no parent or is not valid
     * @see https://www.php.net/manual/en/function.get-parent-class.php
     */
    function class_parent(object $class): string
    {
        $parent = get_parent_class($class);

        if ($parent === false) {
            throw new RuntimeException("{$class} does not have a child or is not a valid class");
        }

        return $parent;
    }
}

if (!function_exists('class_methods')) {
    /**
     * Gets the method names of a class.
     *
     * Provides a consistent wrapper around the native function get_class_methods.
     *
     * @param object $class The object to get methods from
     * @return array Returns an array of method names
     * @see https://www.php.net/manual/en/function.get-class-methods.php
     */
    function class_methods(object $class): array
    {
        return get_class_methods($class);
    }
}

if (!function_exists('class_vars')) {
    /**
     * Gets the default properties of a class.
     *
     * Provides a consistent wrapper around the native function get_class_vars.
     *
     * @param object $class The object to get properties from
     * @return array Returns an associative array of default properties
     * @see https://www.php.net/manual/en/function.get-class-vars.php
     */
    function class_vars(object $class): array
    {
        return get_class_vars(get_class($class));
    }
}

if (!function_exists('class_called')) {
    /**
     * Gets the name of the class a static method is called in.
     *
     * Provides a consistent wrapper around the native function get_called_class.
     *
     * @return string Returns the called class name
     * @see https://www.php.net/manual/en/function.get-called-class.php
     */
    function class_called(): string
    {
        return get_called_class();
    }
}

if (!function_exists('class_reflect')) {
    /**
     * Creates a ReflectionClass instance for an object.
     *
     * Provides a convenient way to create a ReflectionClass with error handling.
     *
     * @param object $class The object to create reflection from
     * @return ReflectionClass Returns a ReflectionClass instance
     * @throws RuntimeException If reflection creation fails
     * @see https://www.php.net/manual/en/class.reflectionclass.php
     */
    function class_reflect(object $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (ReflectionException $exception) {
            throw new RuntimeException($exception->getMessage());
        }
    }
}