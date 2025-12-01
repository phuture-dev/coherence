<?php

declare(strict_types=1);

/**
 * These functions offer a convenient and more consistent procedural interface to
 * the native hash API.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */

if (!function_exists('hash_md2')) {
    /**
     * Generates an MD2 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_md2(string $data, bool $binary = false): string
    {
        return hash('md2', $data, $binary);
    }
}

if (!function_exists('hash_md4')) {
    /**
     * Generates an MD4 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_md4(string $data, bool $binary = false): string
    {
        return hash('md4', $data, $binary);
    }
}

if (!function_exists('hash_md5')) {
    /**
     * Generates an MD5 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_md5(string $data, bool $binary = false): string
    {
        return hash('md5', $data, $binary);
    }
}

if (!function_exists('hash_sha1')) {
    /**
     * Generates a SHA1 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_sha1(string $data, bool $binary = false): string
    {
        return hash('sha1', $data, $binary);
    }
}

if (!function_exists('hash_sha256')) {
    /**
     * Generates a SHA256 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_sha256(string $data, bool $binary = false): string
    {
        return hash('sha256', $data, $binary);
    }
}

if (!function_exists('hash_sha384')) {
    /**
     * Generates a SHA384 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_sha384(string $data, bool $binary = false): string
    {
        return hash('sha384', $data, $binary);
    }
}

if (!function_exists('hash_sha512')) {
    /**
     * Generates a SHA512 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_sha512(string $data, bool $binary = false): string
    {
        return hash('sha512', $data, $binary);
    }
}

if (!function_exists('hash_adler32')) {
    /**
     * Generates an Adler-32 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_adler32(string $data, bool $binary = false): string
    {
        return hash('adler32', $data, $binary);
    }
}

if (!function_exists('hash_crc32')) {
    /**
     * Generates a CRC32 hash of the given data.
     *
     * Provides a consistent wrapper around the native function hash.
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the hash as a hex string or raw binary data
     * @see https://www.php.net/manual/en/function.hash.php
     */
    function hash_crc32(string $data, bool $binary = false): string
    {
        return hash('crc32', $data, $binary);
    }
}
