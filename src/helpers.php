<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Literal;

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

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the standard library.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

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

        if ($error === false || $exception === false) {
            set_error_handler(null);
            set_exception_handler(null);
        }
    }
}

if (!function_exists('php_version')) {
    /**
     * Gets the current PHP version.
     *
     * Provides a consistent wrapper around the native function phpversion.
     *
     * @param string|null $extension Optional extension name (default: null for PHP version)
     * @return string|false Returns the version string or false on failure
     * @see https://www.php.net/manual/en/function.phpversion.php
     */
    function php_version(?string $extension = null): string|false
    {
        $version = phpversion($extension);

        if ($version === false) {
            throw new RuntimeException('There is no version information associated or the extension isn\'t enabled');
        }

        return $version;
    }
}

if (!function_exists('php_info')) {
    /**
     * Outputs information about PHP's configuration.
     *
     * Provides a consistent wrapper around the native function phpinfo.
     *
     * @param int $flags What information to show (default: INFO_ALL)
     * @return bool Returns true on success
     * @see https://www.php.net/manual/en/function.phpinfo.php
     */
    function php_info(int $flags = INFO_ALL): bool
    {
        return phpinfo($flags);
    }
}

if (!function_exists('php_credits')) {
    /**
     * Prints credits for PHP.
     *
     * Provides an enhanced wrapper around the native function phpcredits.
     *
     * @param int $flags What credits to show (default: CREDITS_ALL)
     * @return bool Returns true on success
     * @see https://www.php.net/manual/en/function.phpcredits.php
     */
    function php_credits(int $flags = CREDITS_ALL): bool
    {
        $year = date('Y');
        echo "Advandz Kernel\nCopyright (c) {$year} Advandz Technologies, LLC\n\n";

        return phpcredits($flags);
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
            return $_GET;
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
            return $_POST;
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
            return $_FILES;
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
            return $_SERVER;
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
            return $_COOKIE;
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
            return $_SESSION;
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
            return $_REQUEST;
        }

        if (!isset($_REQUEST[$key])) {
            return $default;
        }

        return filter_var($_REQUEST[$key], $filter);
    }
}

if (!function_exists('env')) {
    /**
     * Accesses $_ENV superglobal and environment variables with optional filtering.
     *
     * Provides safe access to environment variables with automatic sanitization.
     *
     * @param string|null $key The ENV parameter key (default: null to return entire array)
     * @param mixed $default Default value if key doesn't exist (default: null)
     * @param int $filter Filter to apply (default: FILTER_SANITIZE_SPECIAL_CHARS)
     * @return mixed Returns the filtered value, default, or entire $_ENV array
     * @see https://www.php.net/manual/en/function.filter-var.php
     * @see https://www.php.net/manual/en/function.getenv.php
     */
    function env(?string $key = null, mixed $default = null, int $filter = FILTER_SANITIZE_SPECIAL_CHARS): mixed
    {
        if ($key === null) {
            return $_ENV;
        }

        if (!isset($_ENV[$key])) {
            $value = getenv($key);
            if ($value === false) {
                return $default;
            }

            return filter_var($value, $filter);
        }

        return filter_var($_ENV[$key], $filter);
    }
}

if (!function_exists('object')) {
    /**
     * Creates an object from the provided arguments.
     *
     * Provides a convenient way to create objects from arrays or multiple arguments.
     *
     * @param mixed ...$args Values to convert to object properties
     * @return object Returns an object created from the arguments
     */
    function object(...$args): object
    {
        if (count($args) === 1 && array_is_list($args)) {
            return (object) $args[0];
        }

        return (object) $args;
    }

    /**
     * Laravel-style alias for the object method.
     */
    if (!function_exists('literal')) {
        function literal(): object
        {
            return call_user_func_array('object', func_get_args());
        }
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
