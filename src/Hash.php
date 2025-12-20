<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use HashContext;
use Random\RandomException;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\{InvalidArgumentException, RuntimeException};

/**
 * Comprehensive cryptographic hash utility class.
 *
 * This utility class offers a complete toolkit for cryptographic hash operations,
 * supporting multiple algorithms and use cases including data integrity verification,
 * password security, HMAC generation, file checksums, and key derivation.
 *
 * Key features:
 *
 * - **Basic Hashing**: Support for MD2, MD4, MD5, SHA1, SHA256, SHA384, SHA512, and Adler-32/CRC32 algorithms
 * - **Password Security**: Secure password hashing with automatic salt generation and verification
 * - **HMAC Operations**: Message authentication codes for data integrity and authenticity
 * - **File Integrity**: Efficient file hashing for integrity verification and checksums
 * - **Streaming Support**: Memory-efficient streaming for large data processing
 * - **Random Generation**: Cryptographically secure random strings, UUIDs, tokens, and salts
 * - **Salt Management**: Automatic salt generation and salt-based hash operations
 * - **Key Derivation**: PBKDF2 implementation for secure key stretching
 * - **Serialization Support**: Hashing of PHP arrays and objects
 * - **Binary Conversion**: Binary string to hexadecimal conversion utilities
 *
 * Security considerations:
 *
 * - This class includes legacy algorithms (MD2, MD4, MD5, SHA1) for compatibility only
 * - For password hashing, use the dedicated password methods with automatic salt
 * - For new applications, prefer SHA256, SHA384, or SHA512 for better security
 * - Always use HMAC methods when authentication is required
 * - PBKDF2 provides key stretching for password-derived encryption keys
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Hash extends StaticClass
{
    /**
     * Maximum recursion depth for nested operations to prevent infinite recursion.
     */
    public const RECURSION_LIMIT = 100000;

    /**
     * Generates an Adler-32 hash of the given data.
     *
     * This method creates an Adler-32 checksum, which is a fast algorithm for detecting
     * data corruption and verifying file integrity. It's commonly used for quick integrity
     * checks and is faster than CRC32 but less reliable for error detection.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $checksum = Hash::adler32('Hello, World!');
     *
     * // Returns: '1e38733c'
     * ```
     *
     * @param string $data The data to generate a checksum for
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Adler-32 checksum as a hex string or raw binary data
     */
    public static function adler32(string $data, bool $binary = false): string
    {
        return hash('adler32', $data, $binary);
    }

    /**
     * Returns a list of all supported hash algorithms.
     *
     * This method retrieves an array of all hash algorithms supported by the current
     * PHP installation. This is useful for checking algorithm availability before
     * using them or for providing users with algorithm selection options.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $algorithms = Hash::algorithms();
     *
     * // Returns: ['md2', 'md4', 'md5', 'sha1', 'sha256', 'sha384', 'sha512', ...]
     * ```
     *
     * @return array Returns an array of supported hash algorithm names
     */
    public static function algorithms(): array
    {
        return hash_algos();
    }

    /**
     * Generates a hash of a PHP array by serializing it first.
     *
     * This method serializes a PHP array into a string representation and then
     * hashes the serialized data. This is useful for detecting changes in
     * array structures, validating configuration arrays, or creating signatures
     * for complex data.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = ['name' => 'John', 'age' => 30, 'active' => true];
     * $hash = Hash::array($data);
     *
     * // Returns: hash of serialized array
     * ```
     *
     * @param array $data The array to serialize and hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return string Returns the hash of the serialized array as a hex string or raw binary data
     */
    public static function array(array $data, bool $binary = false, string $algo = 'sha256'): string
    {
        return self::hash(serialize($data), $binary, $algo);
    }

    /**
     * Verifies data against a hash by comparing the computed hash with the provided hash.
     *
     * This method is a convenient way to verify that data hasn't been tampered with.
     * It computes the hash of the provided data and compares it securely against
     * the expected hash.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'important message';
     * $expectedHash = Hash::sha256($data);
     * $isValid = Hash::check($data, $expectedHash);
     *
     * // Returns: true
     * ```
     *
     * @param string $data The original data to verify
     * @param string $hash The expected hash to compare against
     * @param string $algo The hash algorithm used (default: 'sha256')
     * @return bool Returns true if the data matches the hash, false otherwise
     */
    public static function check(string $data, string $hash, string $algo = 'sha256'): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }

        return hash_equals($hash, self::hash($data, false, $algo));
    }

    /**
     * Verifies data against a salted hash.
     *
     * This method checks if data matches a previously created salted hash by
     * recombining the data with the same salt and comparing the resulting hashes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $result = Hash::withSalt('password123');
     * $isValid = Hash::checkWithSalt('password123', $result['hash'], $result['salt']);
     *
     * // Returns: true
     * ```
     *
     * @param string $data The original data to verify
     * @param string $hash The salted hash to verify against
     * @param string $salt The salt that was used to create the original hash
     * @param string $algo The hash algorithm that was used (default: 'sha256')
     * @return bool Returns true if the data and salt match the hash, false otherwise
     */
    public static function checkWithSalt(string $data, string $hash, string $salt, string $algo = 'sha256'): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }

        return self::equals($hash, self::hash(hash('sha256', $salt, true) . $data, false, $algo));
    }

    /**
     * Generates a CRC32 hash of the given data.
     *
     * This method creates a CRC32 checksum, which is widely used for error detection
     * in network communications and file transfers. It's more reliable than Adler-32
     * for detecting errors but still not suitable for cryptographic security.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $checksum = Hash::crc32('Hello, World!');
     *
     * // Returns: '0d4a1185'
     * ```
     *
     * @param string $data The data to generate a checksum for
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the CRC32 checksum as a hex string or raw binary data
     */
    public static function crc32(string $data, bool $binary = false): string
    {
        return hash('crc32', $data, $binary);
    }

    /**
     * Generates a CRC32b hash of the given data.
     *
     * This method creates a CRC32b checksum, which is commonly used for error
     * checking and data integrity verification. It's fast but not suitable for
     * cryptographic security purposes.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $checksum = Hash::crc32b('some data for checksum');
     * ```
     *
     * @param string $data The data to generate a checksum for
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the CRC32b checksum as a hex string or raw binary data
     */
    public static function crc32b(string $data, bool $binary = false): string
    {
        return hash('crc32b', $data, $binary);
    }

    /**
     * Generates a CRC32c hash of the given data.
     *
     * This method creates a CRC32c checksum using the Castagnoli polynomial,
     * which is optimized for certain use cases and commonly used in storage
     * and networking protocols.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $checksum = Hash::crc32c('network packet data');
     * ```
     *
     * @param string $data The data to generate a checksum for
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the CRC32c checksum as a hex string or raw binary data
     */
    public static function crc32c(string $data, bool $binary = false): string
    {
        return hash('crc32c', $data, $binary);
    }

    /**
     * Securely compares two hash strings to prevent timing attacks.
     *
     * This method compares two strings in a way that prevents timing attacks.
     * It's important for security-sensitive comparisons like password verification
     * or API signature validation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash1 = 'abc123';
     * $hash2 = 'abc123';
     * $isMatch = Hash::equals($hash1, $hash2);
     *
     * // Returns: true
     * ```
     *
     * @param string $hash The first hash string to compare
     * @param string $secondHash The second hash string to compare
     * @return bool Returns true if the strings are identical, false otherwise
     */
    public static function equals(string $hash, string $secondHash): bool
    {
        return hash_equals($hash, $secondHash);
    }

    /**
     * Generates a hash of a file's contents using memory-efficient streaming.
     *
     * This method uses PHP's optimized hash_file() function which processes files
     * in small chunks, making it suitable for hashing very large files (GB+ sizes)
     * without memory issues. The streaming approach ensures constant memory usage
     * regardless of file size.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::file('/path/to/large-video.mp4');
     *
     * // Returns: SHA256 hash of the file contents (64-character hex string)
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return string Returns the file hash as a hex string or raw binary data
     * @throws InvalidArgumentException When the specified algorithm is not supported
     * @throws RuntimeException When the file does not exist or is not readable
     */
    public static function file(string $file, bool $binary = false, string $algo = 'sha256'): string
    {
        if (!in_array($algo, hash_algos())) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$algo} is not a valid hash algorithm"
            );
        }

        if (!file_exists($file)) {
            throw new RuntimeException(
                "Runtime Error: File {$file} does not exist"
            );
        }

        if (!is_readable($file)) {
            throw new RuntimeException(
                "Runtime Error: File {$file} is not readable"
            );
        }

        return hash_file($algo, $file, $binary);
    }

    /**
     * Generates an MD2 hash of a file's contents.
     *
     * This method reads a file and generates an MD2 hash of its contents.
     * Note: MD2 is considered cryptographically weak and should only be used
     * for compatibility with legacy systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileMd2('/path/to/legacy-file.dat');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD2 file hash as a hex string or raw binary data
     */
    public static function fileMd2(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'md2');
    }

    /**
     * Generates an MD4 hash of a file's contents.
     *
     * This method reads a file and generates an MD4 hash of its contents.
     * Note: MD4 is considered cryptographically weak and should only be used
     * for compatibility with legacy systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileMd4('/path/to/legacy-file.dat');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD4 file hash as a hex string or raw binary data
     */
    public static function fileMd4(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'md4');
    }

    /**
     * Generates an MD5 hash of a file's contents.
     *
     * This method reads a file and generates an MD5 hash of its contents.
     * MD5 is commonly used for file integrity checks and duplicate detection,
     * but should not be used for security-critical applications.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileMd5('/path/to/document.pdf');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD5 file hash as a hex string or raw binary data
     */
    public static function fileMd5(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'md5');
    }

    /**
     * Generates a SHA1 hash of a file's contents.
     *
     * This method reads a file and generates a SHA1 hash of its contents.
     * SHA1 provides better security than MD5 but is still considered weak
     * for new security applications. Consider using SHA256 or stronger.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileSha1('/path/to/archive.zip');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA1 file hash as a hex string or raw binary data
     */
    public static function fileSha1(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'sha1');
    }

    /**
     * Generates a SHA256 hash of a file's contents.
     *
     * This method reads a file and generates a SHA256 hash of its contents.
     * SHA256 is currently recommended for most security applications and provides
     * a good balance of security and performance for file integrity verification.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileSha256('/path/to/important-file.exe');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA256 file hash as a hex string or raw binary data
     */
    public static function fileSha256(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'sha256');
    }

    /**
     * Generates a SHA384 hash of a file's contents.
     *
     * This method reads a file and generates a SHA384 hash of its contents.
     * SHA384 provides stronger security than SHA256 and is suitable for
     * high-security applications requiring 384-bit hash output.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileSha384('/path/to/sensitive-data.dat');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA384 file hash as a hex string or raw binary data
     */
    public static function fileSha384(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'sha384');
    }

    /**
     * Generates a SHA512 hash of a file's contents.
     *
     * This method reads a file and generates a SHA512 hash of its contents.
     * SHA512 provides the strongest security among the SHA2 family and is suitable
     * for maximum security applications requiring 512-bit hash output.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileSha512('/path/to/critical-file.bin');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA512 file hash as a hex string or raw binary data
     */
    public static function fileSha512(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'sha512');
    }

    /**
     * Finalizes the incremental hash calculation and returns the result.
     *
     * This method completes the incremental hashing process and returns the final
     * hash string. After calling this method, the hash context cannot be used
     * for further updates.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $context = Hash::init('sha256');
     * Hash::update($context, 'Large file data...');
     * $finalHash = Hash::final($context);
     *
     * // Returns: SHA256 hash of all data
     * ```
     *
     * @param HashContext $context The hash context to finalize
     * @return string Returns the final hash as a hexadecimal string
     */
    public static function final(HashContext $context): string
    {
        return hash_final($context);
    }

    /**
     * Converts binary data to its hexadecimal representation.
     *
     * This method converts binary data (raw bytes) to a readable hexadecimal string.
     * This is useful for displaying binary hashes, debugging, or storing binary data
     * in text-based formats like JSON or databases.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $binary = "\x48\x65\x6C\x6C\x6F"; // Binary "Hello"
     * $hex = Hash::fromBinary($binary);
     *
     * // Returns: '48656c6c6f'
     * ```
     *
     * @param string $data The binary data to convert to hexadecimal
     * @return string Returns the hexadecimal representation of the binary data
     */
    public static function fromBinary(string $data): string
    {
        return bin2hex($data);
    }

    /**
     * Generates a hash using the specified algorithm.
     *
     * This method provides a flexible way to create hashes using any supported algorithm.
     * It's a convenient wrapper around PHP's hash() function with built-in validation
     * for supported algorithms. Choose from algorithms like 'md5', 'sha1', 'sha256', etc.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::hash('Hello, World!', false, 'md5');
     *
     * // Returns: '6cd3556deb0da54bca060b4c39479839'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to return raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (e.g., 'sha256', 'md5', 'sha1', default: 'sha256')
     * @return string Returns the hash as a hex string or raw binary data
     * @throws InvalidArgumentException When the specified algorithm is not supported
     */
    public static function hash(string $data, bool $binary = false, string $algo = 'sha256'): string
    {
        if (!in_array($algo, hash_algos())) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$algo} is not a valid hash algorithm"
            );
        }

        return hash($algo, $data, $binary);
    }

    /**
     * Generates a keyed hash message authentication code (HMAC).
     *
     * This method creates a secure hash using both your data and a secret key.
     * HMAC is used to verify both the data integrity and authenticity of a message.
     * It's commonly used for API signatures and secure data transmission.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'important message';
     * $secretKey = 'my-secret-key';
     * $signature = Hash::hmac($data, $secretKey);
     *
     * // Returns: secure hash that can only be verified with the same key
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key used for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return string Returns the HMAC as a hex string or raw binary data
     * @throws InvalidArgumentException When the specified algorithm is not supported
     */
    public static function hmac(string $data, string $key, bool $binary = false, string $algo = 'sha256'): string
    {
        if (!in_array($algo, hash_hmac_algos())) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$algo} is not a valid HMAC hash algorithm"
            );
        }

        return hash_hmac($algo, $data, $key, $binary);
    }

    /**
     * Returns a list of all supported HMAC hash algorithms.
     *
     * This method retrieves an array of all hash algorithms that can be used with
     * HMAC operations. This is useful for checking HMAC algorithm availability
     * or for providing users with secure algorithm selection options for authentication.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hmacAlgos = Hash::hmacAlgorithms();
     *
     * // Returns: ['md5', 'sha1', 'sha256', 'sha384', 'sha512', ...]
     * ```
     *
     * @return array Returns an array of supported HMAC hash algorithm names
     */
    public static function hmacAlgorithms(): array
    {
        return hash_hmac_algos();
    }

    /**
     * Verifies data against an HMAC hash using a secret key.
     *
     * This method verifies that data was signed with a specific secret key.
     * It's commonly used to verify API requests, webhooks, or secure data transmission.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'important message';
     * $secretKey = 'my-secret-key';
     * $signature = Hash::hmac($data, $secretKey);
     * $isValid = Hash::hmacCheck($data, $secretKey, $signature);
     *
     * // Returns: true
     * ```
     *
     * @param string $data The original data to verify
     * @param string $key The secret key used to create the original HMAC
     * @param string $hash The expected HMAC hash to compare against
     * @param string $algo The hash algorithm used (default: 'sha256')
     * @return bool Returns true if the data and key match the HMAC, false otherwise
     */
    public static function hmacCheck(string $data, string $key, string $hash, string $algo = 'sha256'): bool
    {
        if ($hash === null || $hash === '') {
            return false;
        }

        return hash_equals($hash, self::hmac($data, $key, false, $algo));
    }

    /**
     * Verifies data against a salted HMAC.
     *
     * This method checks if data matches a previously created salted HMAC by
     * recombining the data with the same salt and comparing the resulting HMACs.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $result = Hash::hmacWithSalt('message', 'secret-key');
     * $isValid = Hash::hmacCheckWithSalt('message', 'secret-key', $result['hmac'], $result['salt']);
     *
     * // Returns: true
     * ```
     *
     * @param string $data The original data to verify
     * @param string $key The secret key that was used to create the original HMAC
     * @param string $hash The salted HMAC to verify against
     * @param string $salt The salt that was used to create the original HMAC
     * @param string $algo The HMAC algorithm that was used (default: 'sha256')
     * @return bool Returns true if the data, key, and salt match the HMAC, false otherwise
     */
    public static function hmacCheckWithSalt(
        string $data,
        string $key,
        string $hash,
        string $salt,
        string $algo = 'sha256'
    ): bool {
        if ($hash === null || $hash === '') {
            return false;
        }

        return self::equals($hash, self::hmac(hash('sha256', $salt, true) . $data, $key, false, $algo));
    }

    /**
     * Generates an HMAC of a file's contents using memory-efficient streaming.
     *
     * This method uses PHP's optimized hash_hmac_file() function which processes files
     * in small chunks, making it suitable for generating HMACs of very large files (GB+ sizes)
     * without memory issues. The streaming approach ensures constant memory usage
     * regardless of file size while providing both integrity and authenticity verification.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHmac = Hash::hmacFile('/path/to/large-video.mp4', 'secret-key');
     *
     * // Returns: HMAC-SHA256 of the file contents (64-character hex string)
     * ```
     *
     * @param string $file The path to the file to HMAC
     * @param string $key The secret key used for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The HMAC algorithm to use (default: 'sha256')
     * @return string Returns the HMAC as a hex string or raw binary data
     * @throws InvalidArgumentException When the specified algorithm is not supported
     * @throws RuntimeException When the file does not exist or is not readable
     */
    public static function hmacFile(string $file, string $key, bool $binary = false, string $algo = 'sha256'): string
    {
        if (!in_array($algo, hash_hmac_algos())) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$algo} is not a valid HMAC hash algorithm"
            );
        }

        if (!file_exists($file)) {
            throw new RuntimeException(
                "Runtime Error: File {$file} does not exist"
            );
        }

        if (!is_readable($file)) {
            throw new RuntimeException(
                "Runtime Error: File {$file} is not readable"
            );
        }

        return hash_hmac_file($algo, $file, $key, $binary);
    }

    /**
     * Generates an HMAC using the MD2 algorithm.
     *
     * This method creates an HMAC using the MD2 hash algorithm with your data and secret key.
     * Note: MD2 is considered cryptographically weak and should only be used for compatibility
     * with legacy systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacMd2($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD2 HMAC as a hex string or raw binary data
     */
    public static function hmacMd2(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'md2');
    }

    /**
     * Generates an HMAC using the MD4 algorithm.
     *
     * This method creates an HMAC using the MD4 hash algorithm with your data and secret key.
     * Note: MD4 is considered cryptographically weak and should only be used for compatibility
     * with legacy systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacMd4($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD4 HMAC as a hex string or raw binary data
     */
    public static function hmacMd4(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'md4');
    }

    /**
     * Generates an HMAC using the MD5 algorithm.
     *
     * This method creates an HMAC using the MD5 hash algorithm with your data and secret key.
     * Note: MD5 is considered cryptographically weak and should only be used for compatibility
     * with legacy systems or non-security-critical applications.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacMd5($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD5 HMAC as a hex string or raw binary data
     */
    public static function hmacMd5(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'md5');
    }

    /**
     * Generates an HMAC using the SHA1 algorithm.
     *
     * This method creates an HMAC using the SHA1 hash algorithm with your data and secret key.
     * SHA1 provides better security than MD5 but is still considered weak for new applications.
     * Consider using SHA256 or stronger algorithms for new implementations.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacSha1($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA1 HMAC as a hex string or raw binary data
     */
    public static function hmacSha1(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'sha1');
    }

    /**
     * Generates an HMAC using the SHA256 algorithm.
     *
     * This method creates an HMAC using the SHA256 hash algorithm with your data and secret key.
     * SHA256 is currently recommended for most security applications and provides
     * a good balance of security and performance.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacSha256($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA256 HMAC as a hex string or raw binary data
     */
    public static function hmacSha256(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'sha256');
    }

    /**
     * Generates an HMAC using the SHA384 algorithm.
     *
     * This method creates an HMAC using the SHA384 hash algorithm with your data and secret key.
     * SHA384 provides stronger security than SHA256 and is suitable for high-security
     * applications requiring 384-bit hash output.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacSha384($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA384 HMAC as a hex string or raw binary data
     */
    public static function hmacSha384(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'sha384');
    }

    /**
     * Generates an HMAC using the SHA512 algorithm.
     *
     * This method creates an HMAC using the SHA512 hash algorithm with your data and secret key.
     * SHA512 provides the strongest security among the SHA2 family and is suitable for
     * maximum security applications requiring 512-bit hash output.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacSha512($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA512 HMAC as a hex string or raw binary data
     */
    public static function hmacSha512(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'sha512');
    }

    /**
     * Checks if an HMAC hash algorithm is supported by the current PHP installation.
     *
     * This method provides a convenient way to verify that a specific hash algorithm
     * can be used for HMAC operations before attempting to create an HMAC. This is
     * useful for feature detection and graceful fallbacks in authentication systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $isSupported = Hash::hmacSupports('sha256');
     *
     * // Returns: true
     * ```
     *
     * @param string $algo The hash algorithm to check for HMAC support (e.g., 'sha256', 'md5', 'sha1')
     * @return bool Returns true if the algorithm is supported for HMAC operations, false otherwise
     */
    public static function hmacSupports(string $algo): bool
    {
        return in_array($algo, hash_hmac_algos());
    }

    /**
     * Compare two HMAC values using timing-safe comparison
     *
     * This method provides timing-safe comparison of two pieces of data by generating
     * HMACs for both and comparing them using hash_equals(). This prevents timing attacks
     * that could reveal information about the data being compared.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $key = 'secret-key';
     * $data1 = 'user_input_1';
     * $data2 = 'user_input_2';
     *
     * // Securely compare if both inputs produce the same HMAC
     * $isValid = Hash::hmacTimingSafe($data1, $data2, $key);
     *
     * // Returns: true if both inputs are identical, false otherwise
     * ```
     *
     * @param string $data1 The first data to HMAC
     * @param string $data2 The second data to HMAC
     * @param string $key The secret key for HMAC generation
     * @return bool True if both HMACs are equal, false otherwise
     */
    public static function hmacTimingSafe(string $data1, string $data2, string $key): bool
    {
        return hash_equals(
            self::hmac($data1, $key),
            self::hmac($data2, $key)
        );
    }

    /**
     * Generates a salted HMAC using the specified algorithm.
     *
     * This method creates an HMAC by combining a salted hash of the salt with the data
     * before authenticating with a secret key. This provides additional security by
     * making each HMAC unique even for identical inputs with the same key, while also
     * preventing length extension attacks.
     *
     * The method uses a secure construction: HMAC(hash('sha256', $salt, true) . $data, key)
     * instead of the insecure HMAC($data . $salt, key) concatenation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $result = Hash::hmacWithSalt('message', 'secret-key');
     *
     * // Returns: ['hmac' => '...', 'salt' => '...']
     * // The HMAC is: HMAC(hash('sha256', $salt, true) . 'message', 'secret-key')
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param string|null $salt Optional custom salt (default: null to generate random salt)
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The HMAC algorithm to use (default: 'sha256')
     * @return array Returns an array with 'hmac' and 'salt' keys
     * @throws InvalidArgumentException When the specified algorithm is not supported
     */
    public static function hmacWithSalt(
        string $data,
        string $key,
        string $salt = null,
        bool $binary = false,
        string $algo = 'sha256'
    ): array {
        $salt = $salt ?? self::salt();
        $hmac = self::hmac(hash('sha256', $salt, true) . (string) $data, $key, $binary, $algo);

        return [
            'hmac' => $hmac,
            'salt' => $salt
        ];
    }

    /**
     * Initializes an incremental hashing context for streaming data.
     *
     * This method creates a new hash context that allows you to hash large amounts
     * of data in chunks without loading everything into memory. This is ideal for
     * processing large files, streams, or data that arrives over time.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $context = Hash::init('sha256');
     * Hash::update($context, 'First chunk of data');
     * Hash::update($context, 'Second chunk of data');
     * $hash = Hash::final($context);
     *
     * // Returns: hash of combined data
     * ```
     *
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return HashContext Returns a hash context for incremental hashing
     */
    public static function init(string $algo = 'sha256'): HashContext
    {
        return hash_init($algo);
    }

    /**
     * Generates an MD2 hash of the given data.
     *
     * This method creates an MD2 hash, which is part of the MD family of hash functions.
     * Note: MD2 is considered cryptographically weak and should only be used for
     * compatibility with legacy systems or non-security applications.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::md2('Hello, World!');
     *
     * // Returns: 'd1cde6a0'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD2 hash as a hex string or raw binary data
     */
    public static function md2(string $data, bool $binary = false): string
    {
        return hash('md2', $data, $binary);
    }

    /**
     * Generates an MD4 hash of the given data.
     *
     * This method creates an MD4 hash, which was designed for high-speed 32-bit
     * processing but is now considered cryptographically broken and insecure.
     * Should only be used for compatibility with legacy systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::md4('Hello, World!');
     *
     * // Returns: '3bb38598'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD4 hash as a hex string or raw binary data
     */
    public static function md4(string $data, bool $binary = false): string
    {
        return hash('md4', $data, $binary);
    }

    /**
     * Generates an MD5 hash of the given data.
     *
     * This method creates an MD5 hash, which produces a 128-bit hash value.
     * MD5 is widely used for file integrity checks and checksums but should not be
     * used for password storage or security-critical applications due to vulnerabilities.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::md5('Hello, World!');
     *
     * // Returns: '6cd3556deb0da54bca060b4c39479839'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD5 hash as a hex string or raw binary data
     */
    public static function md5(string $data, bool $binary = false): string
    {
        return hash('md5', $data, $binary);
    }

    /**
     * Generates a hash of a PHP object by serializing it first.
     *
     * This method serializes a PHP object into a string representation and then
     * hashes the serialized data. This is useful for detecting changes in object
     * state, validating data transfer objects, or creating signatures for
     * complex object structures.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $user = new User('John', 'john@example.com');
     * $hash = Hash::object($user);
     *
     * // Returns: hash of serialized object
     * ```
     *
     * @param object $object The object to serialize and hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return string Returns the hash of the serialized object as a hex string or raw binary data
     */
    public static function object(object $object, bool $binary = false, string $algo = 'sha256'): string
    {
        return self::hash(serialize($object), $binary, $algo);
    }

    /**
     * Creates a secure password hash.
     *
     * This method generates a strong hash for storing passwords securely. It uses PHP's
     * built-in password hashing functions which automatically handle salt generation and
     * use cryptographically secure algorithms.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $password = 'user123';
     * $hash = Hash::password($password);
     *
     * // Returns: $2y$10$AbCdEfGhIjKlMnOpQrStU.vWxYz1234567890abcdefg
     * ```
     *
     * @param string $password The plain text password to hash
     * @param bool $bcrypt If true, forces the use of BCRYPT algorithm (default: false for PHP's default)
     * @param array $options Additional options like 'cost' for BCRYPT (default: [])
     * @return string Returns the hashed password string
     */
    public static function password(string $password, bool $bcrypt = false, array $options = []): string
    {
        if ($bcrypt && !isset($options['cost'])) {
            $options['cost'] = PASSWORD_BCRYPT_DEFAULT_COST;
        }

        return $bcrypt
            ? password_hash($password, PASSWORD_BCRYPT, $options)
            : password_hash($password, PASSWORD_DEFAULT, $options);
    }

    /**
     * Verifies a password against its hash.
     *
     * This method checks if a plain text password matches a previously generated hash.
     * It's the secure way to verify user login credentials without ever storing or
     * exposing the actual password.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $password = 'user123';
     * $hash = Hash::password($password);
     * $isValid = Hash::passwordCheck($password, $hash);
     *
     * // Returns: true
     * ```
     *
     * @param string $password The plain text password to verify
     * @param string $hash The hash to verify against
     * @return bool Returns true if the password matches the hash, false otherwise
     */
    public static function passwordCheck(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Retrieves information about a password hash.
     *
     * This method analyzes a password hash and returns details about the algorithm used,
     * cost factor, and other relevant information. It's useful for understanding how a
     * password was hashed.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = '$2y$10$AbCdEfGhIjKlMnOpQrStU.vWxYz1234567890abcdefg';
     * $info = Hash::passwordInfo($hash);
     *
     * // Returns: ['algo' => 1, 'algoName' => 'bcrypt', 'options' => ['cost' => 10]]
     * ```
     *
     * @param string $hash The password hash to analyze
     * @return array Returns an array with algorithm details and options
     */
    public static function passwordInfo(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * Checks if a password hash needs to be rehashed with a stronger algorithm.
     *
     * This method determines if a password hash was created using an outdated
     * algorithm or options. It's useful for upgrading password hashes when you
     * change your hashing parameters or when PHP updates its default algorithm.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $oldHash = '$2y$10$AbCdEfGhIjKlMnOpQrStU.vWxYz1234567890abcdefg';
     * $needsRehash = Hash::passwordNeedsRehash($oldHash, true, ['cost' => 12]);
     *
     * // Returns: true if cost should be increased to 12
     * ```
     *
     * @param string $hash The password hash to check
     * @param bool $bcrypt If true, forces checking against BCRYPT algorithm (default: false for PHP's default)
     * @param array $options Options to compare against, like 'cost' for BCRYPT (default: [])
     * @return bool Returns true if the hash needs to be rehashed, false otherwise
     */
    public static function passwordNeedsRehash(string $hash, bool $bcrypt = false, array $options = []): bool
    {
        if ($bcrypt && !isset($options['cost'])) {
            $options['cost'] = PASSWORD_BCRYPT_DEFAULT_COST;
        }

        return $bcrypt
            ? password_needs_rehash($hash, PASSWORD_BCRYPT, $options)
            : password_needs_rehash($hash, PASSWORD_DEFAULT, $options);
    }

    /**
     * Derives a cryptographic key from a password using PBKDF2.
     *
     * This method implements the Password-Based Key Derivation Function 2 (PBKDF2),
     * which securely derives cryptographic keys from passwords. PBKDF2 applies a hash
     * function repeatedly (iterations) to make the derivation computationally expensive
     * and resistant to brute force attacks.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $key = Hash::pbkdf2('user-password', 'salt-value', 10000, 32);
     *
     * // Returns: 32-byte derived key
     * ```
     *
     * @param string $password The password to derive the key from
     * @param string|null $salt Optional salt value (default: null to generate random salt)
     * @param int $iterations Number of hash iterations (default: 100000)
     * @param int $length Desired length of derived key in bytes (default: 32)
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return string Returns the derived key as raw binary data
     */
    public static function pbkdf2(
        string $password,
        string $salt = null,
        int $iterations = self::RECURSION_LIMIT,
        int $length = 32,
        string $algo = 'sha256'
    ): string {
        if ($iterations <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The amount of iterations must be positive"
            );
        }

        if ($length <= 0 || $length > self::RECURSION_LIMIT) {
            throw new InvalidArgumentException(
                "Invalid Argument: Length must be between 1 and " . self::RECURSION_LIMIT
            );
        }

        if (!in_array($algo, self::pbkdf2Algorithms())) {
            throw new InvalidArgumentException(
                "Invalid Argument: Algorithm {$algo} is not supported for PBKDF2"
            );
        }

        $salt = $salt ?? self::salt();

        return hash_pbkdf2($algo, $password, $salt, $iterations, $length);
    }

    /**
     * Returns a list of PBKDF2-supported hash algorithms.
     *
     * This method returns an array of hash algorithms that are both supported by the
     * current PHP installation for HMAC operations and are suitable for use with
     * PBKDF2 key derivation. Only the SHA family of algorithms are included.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $algorithms = Hash::pbkdf2Algorithms();
     *
     * // Returns: ['sha1', 'sha256', 'sha384', 'sha512'] (depending on system support)
     * ```
     *
     * @return array Returns an array of PBKDF2-compatible hash algorithm names
     */
    public static function pbkdf2Algorithms(): array
    {
        return array_intersect(['sha1', 'sha256', 'sha384', 'sha512'], hash_hmac_algos());
    }

    /**
     * Checks if a hash algorithm is supported for PBKDF2 operations.
     *
     * This method provides a convenient way to verify that a specific hash algorithm
     * can be used with PBKDF2 key derivation before attempting to use it. This is
     * useful for feature detection and graceful fallbacks in key derivation systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $isSupported = Hash::pbkdf2Supports('sha256');
     *
     * // Returns: true if SHA256 is available for PBKDF2
     * ```
     *
     * @param string $algo The hash algorithm to check for PBKDF2 support (e.g., 'sha256', 'sha512')
     * @return bool Returns true if the algorithm is supported for PBKDF2 operations, false otherwise
     */
    public static function pbkdf2Supports(string $algo): bool
    {
        return in_array($algo, self::pbkdf2Algorithms());
    }

    /**
     * Generates cryptographically secure random data.
     *
     * This method creates random data suitable for cryptographic purposes. By default,
     * it returns a hexadecimal string, but can also return raw binary data. This is
     * useful for generating encryption keys, salts, nonces, and other security data.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $randomHex = Hash::random(32); // 32 character hex string
     * $randomBinary = Hash::random(16, true); // 16 bytes of raw binary data
     * ```
     *
     * @param int $length The desired length of the output (default: 128)
     * @param bool $binary Whether to return raw binary data (default: false for hex string)
     * @return string Returns random data as hex string or raw binary
     * @throws RuntimeException When unable to generate random bytes
     */
    public static function random(int $length = 128, bool $binary = false): string
    {
        try {
            return $binary ? random_bytes($length) : mb_substr(bin2hex(random_bytes($length)), 0, $length);
        } catch (RandomException $e) {
            throw new RuntimeException(
                "Runtime Error: Unable to generate random bytes"
            );
        }
    }

    /**
     * Generates a cryptographically secure random salt for hashing purposes.
     *
     * This method creates a random salt string suitable for password hashing,
     * key derivation, or other cryptographic purposes. The salt is returned
     * as a hexadecimal string for easy storage and use.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $salt = Hash::salt(16);
     *
     * // Returns: 'a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456'
     * ```
     *
     * @param int $length The desired length of the salt in bytes (default: 16)
     * @return string Returns a hexadecimal string representing the random salt
     * @throws RuntimeException When unable to generate random bytes
     */
    public static function salt(int $length = 16): string
    {
        try {
            $data = random_bytes($length);
        } catch (RandomException $e) {
            throw new RuntimeException(
                "Runtime Error: Unable to generate random salt"
            );
        }

        return bin2hex($data);
    }

    /**
     * Generates a SHA1 hash of the given data.
     *
     * This method creates a SHA1 hash, which produces a 160-bit hash value.
     * SHA1 provides better security than MD5 but is still considered weak for new
     * security applications. Consider using SHA256 or stronger algorithms.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::sha1('Hello, World!');
     *
     * // Returns: '0a4d55a5d7e277a849a0f18b891c2b56c'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA1 hash as a hex string or raw binary data
     */
    public static function sha1(string $data, bool $binary = false): string
    {
        return hash('sha1', $data, $binary);
    }

    /**
     * Generates a SHA256 hash of the given data.
     *
     * This method creates a SHA256 hash, which produces a 256-bit hash value.
     * SHA256 is currently recommended for most security applications and provides
     * an excellent balance of security and performance for digital signatures,
     * certificate fingerprints, and password storage (with proper salting).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::sha256('Hello, World!');
     *
     * // Returns: 'dffd6021bb2bd5b0af676290809ec3a53191dd81c7f70a4b28688a362182986f'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA256 hash as a hex string or raw binary data
     */
    public static function sha256(string $data, bool $binary = false): string
    {
        return hash('sha256', $data, $binary);
    }

    /**
     * Generates a SHA384 hash of the given data.
     *
     * This method creates a SHA384 hash, which produces a 384-bit hash value.
     * SHA384 provides stronger security than SHA256 and is suitable for high-security
     * applications requiring more robust protection against collision attacks.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::sha384('Hello, World!');
     *
     * // Returns: '86958c9f7c7cdbc8c09a0d23b3a4142d9dd3b7793713d86ec017a236b0b009c4a4e'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA384 hash as a hex string or raw binary data
     */
    public static function sha384(string $data, bool $binary = false): string
    {
        return hash('sha384', $data, $binary);
    }

    /**
     * Generates a SHA512 hash of the given data.
     *
     * This method creates a SHA512 hash, which produces a 512-bit hash value.
     * SHA512 provides the strongest security among the SHA2 family and is suitable
     * for maximum security applications requiring the highest level of protection
     * against collision attacks and for future-proofing cryptographic systems.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::sha512('Hello, World!');
     *
     * // Returns: '2ef7bde608ce5404e97d5f042f95f89f1c232871'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA512 hash as a hex string or raw binary data
     */
    public static function sha512(string $data, bool $binary = false): string
    {
        return hash('sha512', $data, $binary);
    }

    /**
     * Checks if a hash algorithm is supported by the current PHP installation.
     *
     * This method provides a convenient way to verify that a specific hash algorithm
     * is available before attempting to use it. This is useful for feature detection
     * and graceful fallbacks in applications.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $isSupported = Hash::supports('sha256');
     *
     * // Returns: true
     * ```
     *
     * @param string $algo The hash algorithm to check (e.g., 'sha256', 'md5', 'sha1')
     * @return bool Returns true if the algorithm is supported, false otherwise
     */
    public static function supports(string $algo): bool
    {
        return in_array($algo, hash_algos());
    }

    /**
     * Generates a cryptographically secure random token.
     *
     * This method creates a secure random token suitable for API keys, authentication
     * tokens, session identifiers, and other security-sensitive purposes. The token
     * is 64 characters long (32 bytes converted to hexadecimal).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $token = Hash::token();
     *
     * // Returns: 'a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456'
     * ```
     *
     * @return string Returns a 64-character hexadecimal token
     * @throws RuntimeException When unable to generate random bytes
     */
    public static function token(): string
    {
        try {
            return bin2hex(random_bytes(32));
        } catch (RandomException $e) {
            throw new RuntimeException(
                "Runtime Error: Unable to generate random bytes"
            );
        }
    }

    /**
     * Generates a unique identifier combining timestamp and random data.
     *
     * This method creates a unique identifier by combining a high-resolution timestamp
     * with cryptographically secure random bytes, then hashing the result with SHA256.
     * This provides both uniqueness and unpredictability.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $uniqueId = Hash::unique();
     *
     * // Returns: 64-character SHA256 hash
     * ```
     *
     * @return string Returns a unique SHA256 hash (64 hexadecimal characters)
     * @throws RuntimeException When unable to generate random bytes
     */
    public static function unique(): string
    {
        try {
            return self::sha256(uniqid('', true) . random_bytes(16));
        } catch (RandomException $e) {
            throw new RuntimeException(
                "Runtime Error: Unable to generate random bytes"
            );
        }
    }

    /**
     * Adds data to an incremental hashing context.
     *
     * This method appends data to an existing hash context, allowing you to process
     * data in chunks. Multiple calls to update() will accumulate all the data for
     * the final hash calculation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $context = Hash::init('md5');
     * Hash::update($context, 'Hello, ');
     * Hash::update($context, 'World!');
     * $hash = Hash::final($context);
     *
     * // Returns: MD5 of "Hello, World!"
     * ```
     *
     * @param HashContext $context The hash context to update
     * @param string $data The data to add to the hash calculation
     * @return void
     */
    public static function update(HashContext $context, string $data): void
    {
        hash_update($context, $data);
    }

    /**
     * Generates a UUID (Universally Unique Identifier) version 4.
     *
     * This method creates a random UUID v4, which is a 36-character string in the format
     * xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx. UUIDs are commonly used for unique identifiers
     * in databases, distributed systems, and as keys for data records.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $uuid = Hash::uuid();
     *
     * // Returns: '550e8400-e29b-41d4-a716-446655440000'
     * ```
     *
     * @return string Returns a UUID v4 string
     * @throws RuntimeException When unable to generate random bytes
     */
    public static function uuid(): string
    {
        if (function_exists('uuid_create')) {
            return uuid_create(UUID_TYPE_RANDOM);
        }

        try {
            $data = random_bytes(16);
        } catch (RandomException $e) {
            throw new RuntimeException(
                "Runtime Error: Unable to generate random bytes"
            );
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Generates a salted hash using the specified algorithm.
     *
     * This method creates a hash by combining a salted hash of the salt with the data
     * before final hashing. This prevents length extension attacks and makes identical
     * inputs produce different hashes. Returns both the hash and salt for storage.
     *
     * The method uses a secure construction: hash(hash('sha256', $salt, true) . $data)
     * instead of the insecure $data . $salt concatenation.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $result = Hash::withSalt('password123');
     *
     * // Returns: ['hash' => '...', 'salt' => '...']
     * // The hash is: hash(hash('sha256', $salt, true) . 'password123')
     * ```
     *
     * @param string $data The data to hash with salt
     * @param string|null $salt Optional custom salt (default: null to generate random salt)
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return array Returns an array with 'hash' and 'salt' keys
     */
    public static function withSalt(
        string $data,
        string $salt = null,
        bool $binary = false,
        string $algo = 'sha256'
    ): array {
        $salt = $salt ?? self::salt();
        $hash = self::hash(hash('sha256', $salt, true) . (string) $data, $binary, $algo);

        return [
            'hash' => $hash,
            'salt' => $salt
        ];
    }
}
