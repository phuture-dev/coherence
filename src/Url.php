<?php

namespace Advandz\Kernel;

use Advandz\Kernel\Class\StaticClass;

/**
 * URL utility class providing consistent wrappers around native PHP URL functions.
 *
 * This class offers static methods for common URL operations including encoding, decoding,
 * parsing, and query string manipulation. All methods follow camelCase naming conventions
 * and provide a clean, object-oriented interface to PHP's native URL functions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class Url extends StaticClass
{
    /**
     * URL-encodes a string.
     *
     * Provides a consistent wrapper around the native function urlencode.
     *
     * @param string $subject The string to encode
     * @return string Returns the URL-encoded string
     * @see https://www.php.net/manual/en/function.urlencode.php
     */
    public static function encode(string $subject): string
    {
        return urlencode($subject);
    }

    /**
     * Decodes a URL-encoded string.
     *
     * Provides a consistent wrapper around the native function urldecode.
     *
     * @param string $subject The string to decode
     * @return string Returns the decoded string
     * @see https://www.php.net/manual/en/function.urldecode.php
     */
    public static function decode(string $subject): string
    {
        return urldecode($subject);
    }

    /**
     * URL-encodes a string according to RFC 3986.
     *
     * Provides a consistent wrapper around the native function rawurlencode.
     *
     * @param string $subject The string to encode
     * @return string Returns the URL-encoded string
     * @see https://www.php.net/manual/en/function.rawurlencode.php
     */
    public static function encodeRaw(string $subject): string
    {
        return rawurlencode($subject);
    }

    /**
     * Decodes a URL-encoded string according to RFC 3986.
     *
     * Provides a consistent wrapper around the native function rawurldecode.
     *
     * @param string $subject The string to decode
     * @return string Returns the decoded string
     * @see https://www.php.net/manual/en/function.rawurldecode.php
     */
    public static function decodeRaw(string $subject): string
    {
        return rawurldecode($subject);
    }

    /**
     * Parses a URL and returns its components.
     *
     * Provides a consistent wrapper around the native function parse_url.
     *
     * @param string $url The URL to parse
     * @param int $component Specific component to retrieve (default: -1 for all)
     * @return int|string|array|null|false Returns the requested component(s) or false on failure
     * @see https://www.php.net/manual/en/function.parse-url.php
     */
    public static function parse(string $url, int $component = -1): int|string|array|null|false
    {
        return parse_url($url, $component);
    }

    /**
     * Generates a URL-encoded query string.
     *
     * Provides a consistent wrapper around the native function http_build_query.
     *
     * @param array|object $data The data to encode as query string
     * @param string $numeric_prefix Prefix for numeric indices (default: '')
     * @param string|null $arg_separator Argument separator (default: null for ini default)
     * @param int $encoding_type Encoding type (default: PHP_QUERY_RFC1738)
     * @return string Returns the URL-encoded query string
     * @see https://www.php.net/manual/en/function.http-build-query.php
     */
    public static function buildQuery(
        array|object $data,
        string $numeric_prefix = '',
        ?string $arg_separator = null,
        int $encoding_type = PHP_QUERY_RFC1738
    ): string {
        return http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type);
    }

    /**
     * Encodes a string to URL-safe Base64.
     *
     * Encodes using Base64 with URL-safe characters (- and _ instead of + and /).
     *
     * @param string $subject The string to encode
     * @return string Returns the URL-safe Base64 encoded string
     * @see https://www.php.net/manual/en/function.base64-encode.php
     */
    public static function base64Encode(string $subject): string
    {
        return rtrim(strtr(base64_encode($subject), '+/', '-_'), '=');
    }

    /**
     * Decodes a URL-safe Base64 encoded string.
     *
     * Decodes Base64 with URL-safe characters (- and _ instead of + and /).
     *
     * @param string $subject The URL-safe Base64 string to decode
     * @return string|false Returns the decoded string or false on failure
     * @see https://www.php.net/manual/en/function.base64-decode.php
     */
    public static function base64Decode(string $subject): string|false
    {
        return base64_decode(strtr($subject, '-_', '+/'));
    }
}
