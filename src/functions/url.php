<?php

declare(strict_types=1);

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the native url API.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

if (!function_exists('url_encode')) {
    /**
     * URL-encodes a string.
     *
     * Provides a consistent wrapper around the native function urlencode.
     *
     * @param string $subject The string to encode
     * @return string Returns the URL-encoded string
     * @see https://www.php.net/manual/en/function.urlencode.php
     */
    function url_encode(string $subject): string
    {
        return urlencode($subject);
    }
}

if (!function_exists('url_decode')) {
    /**
     * Decodes a URL-encoded string.
     *
     * Provides a consistent wrapper around the native function urldecode.
     *
     * @param string $subject The string to decode
     * @return string Returns the decoded string
     * @see https://www.php.net/manual/en/function.urldecode.php
     */
    function url_decode(string $subject): string
    {
        return urldecode($subject);
    }
}

if (!function_exists('url_encode_raw')) {
    /**
     * URL-encodes a string according to RFC 3986.
     *
     * Provides a consistent wrapper around the native function rawurlencode.
     *
     * @param string $subject The string to encode
     * @return string Returns the URL-encoded string
     * @see https://www.php.net/manual/en/function.rawurlencode.php
     */
    function url_encode_raw(string $subject): string
    {
        return rawurlencode($subject);
    }
}

if (!function_exists('url_decode_raw')) {
    /**
     * Decodes a URL-encoded string according to RFC 3986.
     *
     * Provides a consistent wrapper around the native function rawurldecode.
     *
     * @param string $subject The string to decode
     * @return string Returns the decoded string
     * @see https://www.php.net/manual/en/function.rawurldecode.php
     */
    function url_decode_raw(string $subject): string
    {
        return rawurldecode($subject);
    }
}

if (!function_exists('url_parse')) {
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
    function url_parse(string $url, int $component = -1): int|string|array|null|false
    {
        return parse_url($url, $component);
    }
}

if (!function_exists('url_build_query')) {
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
    function url_build_query(
        array|object $data,
        string $numeric_prefix = '',
        ?string $arg_separator = null,
        int $encoding_type = PHP_QUERY_RFC1738
    ): string
    {
        return http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type);
    }
}

if (!function_exists('url_get_headers')) {
    /**
     * Fetches all headers sent by the server in response to an HTTP request.
     *
     * Provides a consistent wrapper around the native function get_headers.
     *
     * @param string $url The target URL
     * @param bool $associative Whether to return associative array (default: false)
     * @param mixed $context Stream context resource (default: null)
     * @return array|false Returns array of headers or false on failure
     * @see https://www.php.net/manual/en/function.get-headers.php
     */
    function url_get_headers(string $url, bool $associative = false, $context = null): array|false
    {
        return get_headers($url, $associative, $context);
    }
}

if (!function_exists('url_get_meta_tags')) {
    /**
     * Extracts all meta tag content attributes from a file.
     *
     * Provides a consistent wrapper around the native function get_meta_tags.
     *
     * @param string $filename The path or URL to the HTML file
     * @param bool $use_include_path Whether to search in include_path (default: false)
     * @return array|false Returns array of meta tags or false on failure
     * @see https://www.php.net/manual/en/function.get-meta-tags.php
     */
    function url_get_meta_tags(string $filename, bool $use_include_path = false): array|false
    {
        return get_meta_tags($filename, $use_include_path);
    }
}

if (!function_exists('url_base64_encode')) {
    /**
     * Encodes a string to URL-safe Base64.
     *
     * Encodes using Base64 with URL-safe characters (- and _ instead of + and /).
     *
     * @param string $subject The string to encode
     * @return string Returns the URL-safe Base64 encoded string
     * @see https://www.php.net/manual/en/function.base64-encode.php
     */
    function url_base64_encode(string $subject): string
    {
        return rtrim(strtr(base64_encode($subject), '+/', '-_'), '=');
    }
}

if (!function_exists('url_base64_decode')) {
    /**
     * Decodes a URL-safe Base64 encoded string.
     *
     * Decodes Base64 with URL-safe characters (- and _ instead of + and /).
     *
     * @param string $subject The URL-safe Base64 string to decode
     * @return string|false Returns the decoded string or false on failure
     * @see https://www.php.net/manual/en/function.base64-decode.php
     */
    function url_base64_decode(string $subject): string|false
    {
        return base64_decode(strtr($subject, '-_', '+/'));
    }
}
