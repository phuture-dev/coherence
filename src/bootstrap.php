<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Literal;

if (!function_exists('int')) {
    /**
     * Converts a value to an integer.
     *
     * @param mixed $value The value to convert to integer
     * @return int Returns the integer value
     */
    function int(mixed $value): int
    {
        return intval($value);
    }
}

if (!function_exists('float')) {
    /**
     * Converts a value to a float.
     *
     * @param mixed $value The value to convert to float
     * @return float Returns the float value
     */
    function float(mixed $value): float
    {
        return floatval($value);
    }
}

if (!function_exists('string')) {
    /**
     * Converts a value to a string.
     *
     * @param mixed $value The value to convert to string
     * @return string Returns the string value
     */
    function string(mixed $value): string
    {
        return strval($value);
    }
}

if (!function_exists('bool')) {
    /**
     * Converts a value to a boolean.
     *
     * @param mixed $value The value to convert to boolean
     * @return bool Returns the boolean value
     */
    function bool(mixed $value): bool
    {
        return boolval($value);
    }
}

if (!function_exists('get_type')) {
    /**
     * Gets the type of variable.
     *
     * @param mixed $data The variable to get the type of
     * @return string Returns the type of the variable
     */
    function get_type(mixed $data): string
    {
        return gettype($data);
    }
}

if (!function_exists('set_type')) {
    /**
     * Sets the type of variable.
     *
     * This function modifies the variable passed by reference.
     *
     * @param mixed $data The variable to set the type of (passed by reference)
     * @param string $type The target type (e.g., 'bool', 'int', 'float', 'string', 'array', 'object', 'null')
     * @return bool Returns true on success, false on failure
     */
    function set_type(mixed &$data, string $type): bool
    {
        return settype($data, $type);
    }
}

if (!function_exists('class_name')) {
    /**
     * Gets the fully qualified class name of an object.
     *
     * @param object $class The object to get the class name from
     * @return string Returns the fully qualified class name
     */
    function class_name(object $class): string
    {
        return get_class($class);
    }
}

if (!function_exists('class_basename')) {
    /**
     * Gets the class name without a namespace.
     *
     * @param object|string $class The object to get the basename from
     * @return string Returns the class name without a namespace
     */
    function class_basename(object|string $class): string
    {
        try {
            $reflection = new ReflectionClass($class);

            return $reflection->getShortName();
        } catch (Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}

if (!function_exists('class_namespace')) {
    /**
     * Gets the namespace of a class.
     *
     * @param object|string $class The object to get the namespace from
     * @return string Returns the namespace or '\\' if no namespace
     */
    function class_namespace(object|string $class): string
    {
        try {
            return (new ReflectionClass($class))->getNamespaceName();
        } catch (Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}

if (!function_exists('class_parent')) {
    /**
     * Gets the parent class name of an object.
     *
     * @param object|string $class The object to get the parent class from
     * @return string Returns the parent class name
     * @throws LogicException If the class has no parent or is not valid
     */
    function class_parent(object|string $class): string
    {
        $parent = get_parent_class($class);

        if ($parent === false) {
            throw new Error(
                "{$class} does not have a parent or is not a valid class"
            );
        }

        return $parent;
    }
}

if (!function_exists('class_methods')) {
    /**
     * Gets the method names of a class.
     *
     * @param object|string $class The object to get methods from
     * @return array Returns an array of method names
     */
    function class_methods(object|string $class): array
    {
        return get_class_methods($class);
    }
}

if (!function_exists('class_method_exists')) {
    /**
     * Checks if a class contains a specific method.
     *
     * @param object|string $class The class name or object instance to check
     * @param string $method The method name to check for existence
     * @return bool Returns true if the method exists, false otherwise
     */
    function class_method_exists(object|string $class, string $method): bool
    {
        return method_exists($class, $method);
    }
}

if (!function_exists('class_method_visibility')) {
    /**
     * Gets the visibility level of a class method.
     *
     * This function uses reflection to determine whether a method is public, private,
     * or protected. It's useful for debugging, logging, or when you need to check
     * method accessibility before calling it dynamically.
     *
     * @param object|string $class The class name or class instance
     * @param string $method The method name to check
     * @return string Returns 'public', 'private', or 'protected'
     * @throws Error If the method doesn't exist or reflection fails
     */
    function class_method_visibility(object|string $class, string $method): string
    {
        try {
            return match (true) {
                (new ReflectionMethod($class, $method))->isPublic() => 'public',
                (new ReflectionMethod($class, $method))->isPrivate() => 'private',
                (new ReflectionMethod($class, $method))->isProtected() => 'protected'
            };
        } catch (Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}

if (!function_exists('class_properties')) {
    /**
     * Gets the default properties of a class.
     *
     * @param object|string $class The object to get properties from
     * @return array Returns an associative array of default properties
     */
    function class_properties(object|string $class): array
    {
        return is_string($class) ? get_class_vars($class) : get_class_vars(get_class($class));
    }
}

if (!function_exists('class_property_exists')) {
    /**
     * Check if a property exists in a class (including private properties)
     *
     * @param object|string $class The class name or object instance
     * @param string $property The property name to check
     * @return bool True if property exists, false otherwise
     */
    function class_property_exists(object|string $class, string $property): bool
    {
        return property_exists($class, $property);
    }
}

if (!function_exists('class_property_visibility')) {
    /**
     * Gets the visibility level of a class property.
     *
     * This function uses reflection to determine whether a property is public, private,
     * or protected. It's useful for debugging, logging, or when you need to check
     * property accessibility before accessing it dynamically.
     *
     * @param object|string $class The class name or class instance
     * @param string $property The property name to check
     * @return string Returns 'public', 'private', or 'protected'
     * @throws Error If the property doesn't exist or reflection fails
     */
    function class_property_visibility(object|string $class, string $property): string
    {
        try {
            return match (true) {
                (new ReflectionProperty($class, $property))->isPublic() => 'public',
                (new ReflectionProperty($class, $property))->isPrivate() => 'private',
                (new ReflectionProperty($class, $property))->isProtected() => 'protected'
            };
        } catch (Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}

if (!function_exists('class_traits')) {
    /**
     * Gets all traits used by a class.
     *
     * @param object|string $class The object or class name to get traits from
     * @return array Returns an array of trait names used by the class
     */
    function class_traits(object|string $class): array
    {
        return class_uses($class);
    }
}

if (!function_exists('class_called')) {
    /**
     * Gets the name of the class a static method is called in.
     *
     * @return string Returns the called class name
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
     * @param object $class The object to create reflection from
     * @return ReflectionClass Returns a ReflectionClass instance
     */
    function class_reflect(object $class): ReflectionClass
    {
        try {
            return new ReflectionClass($class);
        } catch (Throwable $e) {
            throw new Error($e->getMessage());
        }
    }
}

if (!function_exists('error_handler')) {
    /**
     * Sets the error and exception handler.
     *
     * Provides a consistent wrapper around the native function set_error_handler and set_exception_handler.
     *
     * @param ?callable $handler The callable handler for errors and exceptions
     * @see https://www.php.net/manual/en/function.set-error-handler.php
     * @see https://www.php.net/manual/en/function.set-exception-handler.php
     */
    function error_handler(?callable $handler = null): void
    {
        $current_level = error_reporting();

        $error = set_error_handler(
            function (int $severity, string $message, string $file, int $line) use ($handler) {
                if ($handler) {
                    $exception = new ErrorException($message, 0, $severity, $file, $line);
                    $handler($exception);

                    return true;
                }

                return false;
            },
            $current_level
        );

        $exception = set_exception_handler(
            function (Throwable $exception) use ($handler) {
                if ($handler) {
                    $handler($exception);
                }
            }
        );

        $shutdown = register_shutdown_function(function () use ($handler) {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                if ($handler) {
                    $exception = new ErrorException(
                        $error['message'],
                        0,
                        $error['type'],
                        $error['file'],
                        $error['line']
                    );
                    $handler($exception);
                }
            }
        });

        if ($error === false || $exception === false || $shutdown === false) {
            set_error_handler(null);
            set_exception_handler(null);
        }
    }
}

if (!function_exists('function_alias')) {
    /**
     * Creates an alias for an existing function.
     *
     * Dynamically creates a new function that acts as an alias to an existing function.
     *
     * @param string $function The name of the existing function
     * @param string $alias The name of the alias to create
     * @return bool Returns true if alias was created, false if alias already exists
     */
    function function_alias(
        string $function,
        string $alias
    ): bool {
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
}

if (!function_exists('get')) {
    /**
     * Accesses $_GET superglobal with optional filtering.
     *
     * Provides safe access to GET parameters with automatic sanitization.
     *
     * @param string|null $key The GET parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_GET array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function get(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_GET);
        }

        if (!isset($_GET[$key])) {
            return $default;
        }

        return filter_var($_GET[$key], $filter);
    }
}

if (!function_exists('post')) {
    /**
     * Accesses $_POST superglobal with optional filtering.
     *
     * Provides safe access to POST parameters with automatic sanitization.
     *
     * @param string|null $key The POST parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_POST array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function post(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_POST);
        }

        if (!isset($_POST[$key])) {
            return $default;
        }

        return filter_var($_POST[$key], $filter);
    }
}

if (!function_exists('files')) {
    /**
     * Accesses $_FILES superglobal with optional filtering.
     *
     * Provides safe access to uploaded files with automatic sanitization for scalar values.
     *
     * @param string|null $key The FILES parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply to scalar values (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_FILES array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function files(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_FILES);
        }

        if (!isset($_FILES[$key])) {
            return $default;
        }

        return is_scalar($_FILES[$key]) ? filter_var($_FILES[$key], $filter) : $_FILES[$key];
    }
}

if (!function_exists('server')) {
    /**
     * Accesses $_SERVER superglobal with optional filtering.
     *
     * Provides safe access to server parameters with automatic sanitization.
     *
     * @param string|null $key The SERVER parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_SERVER array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function server(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_SERVER);
        }

        if (!isset($_SERVER[$key])) {
            return $default;
        }

        return filter_var($_SERVER[$key], $filter);
    }
}

if (!function_exists('cookie')) {
    /**
     * Accesses $_COOKIE superglobal with optional filtering.
     *
     * Provides safe access to cookies with automatic sanitization.
     *
     * @param string|null $key The COOKIE parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_COOKIE array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function cookie(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_COOKIE);
        }

        if (!isset($_COOKIE[$key])) {
            return $default;
        }

        return filter_var($_COOKIE[$key], $filter);
    }
}

if (!function_exists('session')) {
    /**
     * Accesses $_SESSION superglobal with optional filtering.
     *
     * Provides safe access to session data with automatic sanitization for scalar values.
     *
     * @param string|null $key The SESSION parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply to scalar values (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_SESSION array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function session(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_SESSION);
        }

        if (!isset($_SESSION[$key])) {
            return $default;
        }

        return is_scalar($_SESSION[$key]) ? filter_var($_SESSION[$key], $filter) : $_SESSION[$key];
    }
}

if (!function_exists('request')) {
    /**
     * Accesses $_REQUEST superglobal with optional filtering.
     *
     * Provides safe access to request parameters with automatic sanitization.
     *
     * @param string|null $key The REQUEST parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_REQUEST array
     * @see https://www.php.net/manual/en/function.filter-var.php
     */
    function request(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            $recursive = function ($array) use (&$recursive, $filter) {
                return array_map(fn($value) => is_array($value)
                    ? $recursive($value)
                    : (is_scalar($value) ? filter_var($value, $filter) : $value), $array);
            };
            return $recursive($_REQUEST);
        }

        if (!isset($_REQUEST[$key])) {
            return $default;
        }

        return filter_var($_REQUEST[$key], $filter);
    }
}

if (!function_exists('literal')) {
    /**
     * Creates an object from the provided arguments.
     *
     * Provides a convenient way to create objects from arrays or multiple arguments.
     *
     * @param mixed ...$args Values to convert to object properties
     * @return object Returns an object created from the arguments
     */
    function literal(...$args): object
    {
        if (count($args) === 1 && array_is_list($args)) {
            return (object) $args[0];
        }

        return (object) $args;
    }
}

if (!function_exists('blank')) {
    /**
     * Determines whether the given value is blank or not.
     *
     * Provides a consistent wrapper around the native function empty.
     *
     * @param mixed $value The value to evaluate
     * @return bool True if the given value is blank
     */
    function blank(mixed $value): bool
    {
        if (is_null($value)) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value) === '';
        }

        return empty($value);
    }
}

if (!function_exists('filled')) {
    /**
     * Determines whether the given value is filled or not.
     *
     * Provides a consistent wrapper around the native function empty, negated.
     *
     * @param mixed $value The value to evaluate
     * @return bool True if the given value is not blank
     */
    function filled(mixed $value): bool
    {
        return !blank($value);
    }
}

if (!function_exists('is_windows')) {
    /**
     * Determine whether the current environment is Windows.
     *
     * @return bool True if the current environment is Windows
     */
    function is_windows()
    {
        return PHP_OS_FAMILY === 'Windows';
    }
}
