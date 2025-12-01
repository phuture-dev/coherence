<?php

namespace Advandz\Kernel;

use Advandz\Kernel\Class\StaticClass;

/**
 * Hash utility class providing consistent wrappers around native PHP hash functions.
 *
 * This class offers static methods for common hash algorithms including MD2, MD4, MD5,
 * SHA family (SHA1, SHA256, SHA384, SHA512), and checksums (Adler-32, CRC32).
 * All methods follow camelCase naming conventions and provide a clean, object-oriented
 * interface to PHP's native hash functions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.advandz.com/ Advandz
 */
class Hash extends StaticClass
{
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
    public static function md2(string $data, bool $binary = false): string
    {
        return hash('md2', $data, $binary);
    }

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
    public static function md4(string $data, bool $binary = false): string
    {
        return hash('md4', $data, $binary);
    }

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
    public static function md5(string $data, bool $binary = false): string
    {
        return hash('md5', $data, $binary);
    }

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
    public static function sha1(string $data, bool $binary = false): string
    {
        return hash('sha1', $data, $binary);
    }

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
    public static function sha256(string $data, bool $binary = false): string
    {
        return hash('sha256', $data, $binary);
    }

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
    public static function sha384(string $data, bool $binary = false): string
    {
        return hash('sha384', $data, $binary);
    }

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
    public static function sha512(string $data, bool $binary = false): string
    {
        return hash('sha512', $data, $binary);
    }

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
    public static function adler32(string $data, bool $binary = false): string
    {
        return hash('adler32', $data, $binary);
    }

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
    public static function crc32(string $data, bool $binary = false): string
    {
        return hash('crc32', $data, $binary);
    }
}
