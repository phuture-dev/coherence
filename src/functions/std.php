<?php

declare(strict_types=1);

use Nette\PhpGenerator\GlobalFunction;
use Nette\PhpGenerator\Literal;

if (!function_exists('php_version')) {
    function php_version(?string $extension = null): string|false
    {
        return phpversion($extension);
    }
}

if (!function_exists('php_info')) {
    function php_info(int $flags = INFO_ALL): bool
    {
        return phpinfo($flags);
    }
}

if (!function_exists('php_credits')) {
    function php_credits(int $flags = CREDITS_ALL): bool
    {
        $year = date('Y');
        echo "Advandz Kernel\nCopyright (c) {$year} Advandz Technologies, LLC\n\n";

        return phpcredits($flags);
    }
}

if (!function_exists('function_alias')) {
    function function_alias(
        string $function,
        string $alias
    ): bool
    {
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
