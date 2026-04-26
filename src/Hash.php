<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use HashContext;
use Random\RandomException;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Enum\PasswordAlgorithm;
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
 * - **Blake2 Hashing**: Support for Blake2b (512-bit) and Blake2s (256-bit) modern cryptographic hash algorithms
 * - **Password Security**: Secure password hashing with automatic salt generation and verification
 * - **Argon2ID Password Hashing**: Memory-hard password hashing with resistance to GPU and side-channel attacks
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
 * - For modern password hashing, prefer Argon2ID via the dedicated passwordArgon2id() method when available
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Hash extends StaticClass
{
    /**
     * Default number of iterations for PBKDF2 key derivation.
     *
     * This value provides a reasonable balance between security and performance.
     * Higher values increase security but slow down the derivation process.
     *
     * @see \Phuture\Coherence\Hash::pbkdf2()
     */
    public const DEFAULT_PBKDF2_ITERATIONS = 100000;

    /**
     * Maximum allowed length in bytes for a derived key.
     *
     * Prevents excessively large key derivation requests that could consume
     * excessive memory or computation time.
     *
     * @see \Phuture\Coherence\Hash::pbkdf2()
     */
    public const MAX_DERIVED_KEY_LENGTH = 100000;

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
     * // Returns: '1f9e046a'
     * ```
     *
     * @param string $data The data to generate a checksum for
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Adler-32 checksum as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::crc32() For generating a CRC32 checksum
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
     * @see \Phuture\Coherence\Hash::supports() For checking if a specific algorithm is supported
     * @see \Phuture\Coherence\Hash::hmacAlgorithms() For listing HMAC-supported algorithms
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
     * @see \Phuture\Coherence\Hash::object() For hashing serialized objects
     * @see \Phuture\Coherence\Hash::hash() For hashing string data with a configurable algorithm
     */
    public static function array(array $data, bool $binary = false, string $algo = 'sha256'): string
    {
        return self::hash(serialize($data), $binary, $algo);
    }

    /**
     * Generates a Blake2b hash of the given data.
     *
     * This method creates a Blake2b (512-bit variant) hash, which is a modern cryptographic
     * hash function designed to be faster than MD5 and SHA families while providing security
     * at least equal to SHA-3. Blake2b is optimized for 64-bit platforms and produces a
     * 512-bit output represented as a 128 hexadecimal character string.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::blake2b('Hello, World!');
     *
     * // Returns: 128-character hex string (Blake2b-512 hash)
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Blake2b hash as a 128-character hex string or 64 bytes of raw binary data
     * @throws \Phuture\Coherence\Exception\RuntimeException When Blake2b is not supported by this PHP installation
     * @see \Phuture\Coherence\Hash::fileBlake2b() For hashing file contents with Blake2b
     * @see \Phuture\Coherence\Hash::hmacBlake2b() For generating HMAC with Blake2b
     * @see \Phuture\Coherence\Hash::blake2s() For the 256-bit Blake2s variant
     */
    public static function blake2b(string $data, bool $binary = false): string
    {
        if (!in_array('blake2b512', hash_algos())) {
            throw new RuntimeException(
                "Runtime Error: Blake2b is not supported by the current PHP installation"
            );
        }

        return hash('blake2b512', $data, $binary);
    }

    /**
     * Generates a Blake2s hash of the given data.
     *
     * This method creates a Blake2s (256-bit variant) hash, which is a modern cryptographic
     * hash function designed as a faster and more secure alternative to MD5 and SHA-1. Blake2s
     * is optimized for 8- to 32-bit platforms and produces a 256-bit output represented as a
     * 64 hexadecimal character string, matching the output size of SHA256.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $hash = Hash::blake2s('Hello, World!');
     *
     * // Returns: 64-character hex string (Blake2s-256 hash)
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Blake2s hash as a 64-character hex string or 32 bytes of raw binary data
     * @throws \Phuture\Coherence\Exception\RuntimeException When Blake2s is not supported by this PHP installation
     * @see \Phuture\Coherence\Hash::fileBlake2s() For hashing file contents with Blake2s
     * @see \Phuture\Coherence\Hash::hmacBlake2s() For generating HMAC with Blake2s
     * @see \Phuture\Coherence\Hash::blake2b() For the 512-bit Blake2b variant
     */
    public static function blake2s(string $data, bool $binary = false): string
    {
        if (!in_array('blake2s256', hash_algos())) {
            throw new RuntimeException(
                "Runtime Error: Blake2s is not supported by the current PHP installation"
            );
        }

        return hash('blake2s256', $data, $binary);
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
     * @see \Phuture\Coherence\Hash::checkWithSalt() For verifying data against a salted hash
     * @see \Phuture\Coherence\Hash::hmacCheck() For verifying HMAC hashes
     */
    public static function check(string $data, string $hash, string $algo = 'sha256'): bool
    {
        if ($hash === '') {
            return false;
        }

        return self::equals($hash, self::hash($data, false, $algo));
    }

    /**
     * Verifies data against a salted hash.
     *
     * This method checks if data matches a previously created salted hash by
     * recombining the data with the same salt and comparing the resulting hashes.
     * The salt is internally hashed using SHA-256 regardless of the chosen algorithm
     * to ensure a consistent and secure salt preprocessing step.
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
     * @see \Phuture\Coherence\Hash::withSalt() For generating a salted hash
     * @see \Phuture\Coherence\Hash::check() For verifying data against an unsalted hash
     */
    public static function checkWithSalt(string $data, string $hash, string $salt, string $algo = 'sha256'): bool
    {
        if ($hash === '') {
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
     * // Returns: 'dffed8e6'
     * ```
     *
     * @param string $data The data to generate a checksum for
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the CRC32 checksum as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::crc32b() For generating a CRC32b checksum
     * @see \Phuture\Coherence\Hash::crc32c() For generating a CRC32c checksum
     * @see \Phuture\Coherence\Hash::adler32() For generating an Adler-32 checksum
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
     * @see \Phuture\Coherence\Hash::crc32() For generating a CRC32 checksum
     * @see \Phuture\Coherence\Hash::crc32c() For generating a CRC32c checksum
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
     * @see \Phuture\Coherence\Hash::crc32() For generating a CRC32 checksum
     * @see \Phuture\Coherence\Hash::crc32b() For generating a CRC32b checksum
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
     * @see \Phuture\Coherence\Hash::hmacTimingSafe() For timing-safe comparison using HMAC
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
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the specified algorithm is not supported
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or is not readable
     * @see \Phuture\Coherence\Hash::hash() For hashing string data with a configurable algorithm
     * @see \Phuture\Coherence\Hash::hmacFile() For generating HMAC of file contents
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
     * Generates a Blake2b hash of a file's contents.
     *
     * This method reads a file and generates a Blake2b-512 hash of its contents using
     * memory-efficient streaming. It is suitable for hashing very large files (GB+ sizes)
     * without memory issues. The streaming approach ensures constant memory usage
     * regardless of file size.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileBlake2b('/path/to/file.dat');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Blake2b file hash as a 128-character hex string or raw binary data
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When Blake2b is not supported
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or is not readable
     * @see \Phuture\Coherence\Hash::blake2b() For hashing string data with Blake2b
     * @see \Phuture\Coherence\Hash::hmacBlake2b() For generating HMAC with Blake2b
     */
    public static function fileBlake2b(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'blake2b512');
    }

    /**
     * Generates a Blake2s hash of a file's contents.
     *
     * This method reads a file and generates a Blake2s-256 hash of its contents using
     * memory-efficient streaming. It is suitable for hashing very large files (GB+ sizes)
     * without memory issues. The streaming approach ensures constant memory usage
     * regardless of file size.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $fileHash = Hash::fileBlake2s('/path/to/file.dat');
     * ```
     *
     * @param string $file The path to the file to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Blake2s file hash as a 64-character hex string or raw binary data
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When Blake2s is not supported
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or is not readable
     * @see \Phuture\Coherence\Hash::blake2s() For hashing string data with Blake2s
     * @see \Phuture\Coherence\Hash::hmacBlake2s() For generating HMAC with Blake2s
     */
    public static function fileBlake2s(string $file, bool $binary = false): string
    {
        return self::file($file, $binary, 'blake2s256');
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
     * @see \Phuture\Coherence\Hash::md2() For hashing string data with MD2
     * @see \Phuture\Coherence\Hash::hmacMd2() For generating HMAC with MD2
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
     * @see \Phuture\Coherence\Hash::md4() For hashing string data with MD4
     * @see \Phuture\Coherence\Hash::hmacMd4() For generating HMAC with MD4
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
     * @see \Phuture\Coherence\Hash::md5() For hashing string data with MD5
     * @see \Phuture\Coherence\Hash::hmacMd5() For generating HMAC with MD5
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
     * @see \Phuture\Coherence\Hash::sha1() For hashing string data with SHA1
     * @see \Phuture\Coherence\Hash::hmacSha1() For generating HMAC with SHA1
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
     * @see \Phuture\Coherence\Hash::sha256() For hashing string data with SHA256
     * @see \Phuture\Coherence\Hash::hmacSha256() For generating HMAC with SHA256
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
     * @see \Phuture\Coherence\Hash::sha384() For hashing string data with SHA384
     * @see \Phuture\Coherence\Hash::hmacSha384() For generating HMAC with SHA384
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
     * @see \Phuture\Coherence\Hash::sha512() For hashing string data with SHA512
     * @see \Phuture\Coherence\Hash::hmacSha512() For generating HMAC with SHA512
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
     * @see \Phuture\Coherence\Hash::init() For creating a hash context
     * @see \Phuture\Coherence\Hash::update() For adding data to the context
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
     * @see \Phuture\Coherence\Hash::hash() For generating hashes with binary output option
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
     * // Returns: '65a8e27d8879283831b664bd8b7f0ad4'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to return raw binary data (default: false for hex string)
     * @param string $algo The hash algorithm to use (e.g., 'sha256', 'md5', 'sha1', default: 'sha256')
     * @return string Returns the hash as a hex string or raw binary data
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the specified algorithm is not supported
     * @see \Phuture\Coherence\Hash::check() For verifying data against a hash
     * @see \Phuture\Coherence\Hash::file() For hashing file contents
     * @see \Phuture\Coherence\Hash::hmac() For generating authenticated hashes
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
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the specified algorithm is not supported
     * @see \Phuture\Coherence\Hash::hmacCheck() For verifying an HMAC
     * @see \Phuture\Coherence\Hash::hmacFile() For generating HMAC of file contents
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
     * @see \Phuture\Coherence\Hash::hmacSupports() For checking if a specific algorithm is supported for HMAC
     * @see \Phuture\Coherence\Hash::algorithms() For listing all supported hash algorithms
     */
    public static function hmacAlgorithms(): array
    {
        return hash_hmac_algos();
    }

    /**
     * Generates an HMAC using the Blake2b algorithm.
     *
     * This method creates an HMAC using the Blake2b-512 hash algorithm with your data and
     * secret key. Blake2b provides strong authentication with a 512-bit output and is
     * significantly faster than SHA-512 based HMAC on 64-bit platforms.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacBlake2b($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Blake2b HMAC as a 128-character hex string or raw binary data
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When Blake2b is not supported for HMAC
     * @see \Phuture\Coherence\Hash::blake2b() For hashing string data with Blake2b
     * @see \Phuture\Coherence\Hash::fileBlake2b() For hashing file contents with Blake2b
     */
    public static function hmacBlake2b(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'blake2b512');
    }

    /**
     * Generates an HMAC using the Blake2s algorithm.
     *
     * This method creates an HMAC using the Blake2s-256 hash algorithm with your data and
     * secret key. Blake2s provides strong authentication with a 256-bit output and is
     * optimized for 8- to 32-bit platforms while remaining suitable for all environments.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $data = 'message';
     * $key = 'secret';
     * $hmac = Hash::hmacBlake2s($data, $key);
     * ```
     *
     * @param string $data The data to authenticate
     * @param string $key The secret key for authentication
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the Blake2s HMAC as a 64-character hex string or raw binary data
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When Blake2s is not supported for HMAC
     * @see \Phuture\Coherence\Hash::blake2s() For hashing string data with Blake2s
     * @see \Phuture\Coherence\Hash::fileBlake2s() For hashing file contents with Blake2s
     */
    public static function hmacBlake2s(string $data, string $key, bool $binary = false): string
    {
        return self::hmac($data, $key, $binary, 'blake2s256');
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
     * @see \Phuture\Coherence\Hash::hmac() For generating an HMAC
     * @see \Phuture\Coherence\Hash::hmacCheckWithSalt() For verifying salted HMACs
     */
    public static function hmacCheck(string $data, string $key, string $hash, string $algo = 'sha256'): bool
    {
        if ($hash === '') {
            return false;
        }

        return self::equals($hash, self::hmac($data, $key, false, $algo));
    }

    /**
     * Verifies data against a salted HMAC.
     *
     * This method checks if data matches a previously created salted HMAC by
     * recombining the data with the same salt and comparing the resulting HMACs.
     * The salt is internally hashed using SHA-256 regardless of the chosen algorithm
     * to ensure a consistent and secure salt preprocessing step.
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
     * @see \Phuture\Coherence\Hash::hmacWithSalt() For generating a salted HMAC
     * @see \Phuture\Coherence\Hash::hmacCheck() For verifying unsalted HMACs
     */
    public static function hmacCheckWithSalt(
        string $data,
        string $key,
        string $hash,
        string $salt,
        string $algo = 'sha256'
    ): bool {
        if ($hash === '') {
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
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the specified algorithm is not supported
     * @throws \Phuture\Coherence\Exception\RuntimeException When the file does not exist or is not readable
     * @see \Phuture\Coherence\Hash::file() For hashing file contents without authentication
     * @see \Phuture\Coherence\Hash::hmac() For generating HMAC of string data
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
     * Finalizes an incremental HMAC calculation and returns the result.
     *
     * This method completes the incremental HMAC process started with hmacInit() and
     * returns the final authentication code. After calling this method, the context
     * cannot be used for further updates.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $context = Hash::hmacInit('secret-key');
     * Hash::hmacUpdate($context, 'First chunk');
     * Hash::hmacUpdate($context, 'Second chunk');
     * $hmac = Hash::hmacFinal($context);
     *
     * // Returns: HMAC-SHA256 of all data combined
     * ```
     *
     * @param HashContext $context The HMAC hash context to finalize
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the final HMAC as a hexadecimal string or raw binary data
     * @see \Phuture\Coherence\Hash::hmacInit() For creating an HMAC hash context
     * @see \Phuture\Coherence\Hash::hmacUpdate() For adding data to the context
     */
    public static function hmacFinal(HashContext $context, bool $binary = false): string
    {
        return hash_final($context, $binary);
    }

    /**
     * Initializes an incremental HMAC hashing context for streaming authentication.
     *
     * This method creates a new HMAC hash context that allows you to authenticate
     * large amounts of data in chunks without loading everything into memory. This
     * is ideal for processing large files, streams, or data that arrives over time,
     * while still benefiting from authentication with a secret key.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $context = Hash::hmacInit('secret-key');
     * Hash::hmacUpdate($context, 'First chunk of data');
     * Hash::hmacUpdate($context, 'Second chunk of data');
     * $hmac = Hash::hmacFinal($context);
     *
     * // Returns: HMAC of combined data, identical to Hash::hmac('First chunkSecond chunk', 'secret-key')
     * ```
     *
     * @param string $key The secret key for HMAC authentication
     * @param string $algo The hash algorithm to use (default: 'sha256')
     * @return HashContext Returns an HMAC hash context for incremental hashing
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the algorithm is not supported for HMAC
     * @see \Phuture\Coherence\Hash::hmacUpdate() For adding data to the context
     * @see \Phuture\Coherence\Hash::hmacFinal() For completing the HMAC calculation
     * @see \Phuture\Coherence\Hash::init() For non-authenticated incremental hashing
     */
    public static function hmacInit(string $key, string $algo = 'sha256'): HashContext
    {
        if (!in_array($algo, hash_hmac_algos())) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$algo} is not a valid HMAC hash algorithm"
            );
        }

        return hash_init($algo, HASH_HMAC, $key);
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
     * @see \Phuture\Coherence\Hash::md2() For hashing string data with MD2
     * @see \Phuture\Coherence\Hash::fileMd2() For hashing file contents with MD2
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
     * @see \Phuture\Coherence\Hash::md4() For hashing string data with MD4
     * @see \Phuture\Coherence\Hash::fileMd4() For hashing file contents with MD4
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
     * @see \Phuture\Coherence\Hash::md5() For hashing string data with MD5
     * @see \Phuture\Coherence\Hash::fileMd5() For hashing file contents with MD5
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
     * @see \Phuture\Coherence\Hash::sha1() For hashing string data with SHA1
     * @see \Phuture\Coherence\Hash::fileSha1() For hashing file contents with SHA1
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
     * @see \Phuture\Coherence\Hash::sha256() For hashing string data with SHA256
     * @see \Phuture\Coherence\Hash::fileSha256() For hashing file contents with SHA256
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
     * @see \Phuture\Coherence\Hash::sha384() For hashing string data with SHA384
     * @see \Phuture\Coherence\Hash::fileSha384() For hashing file contents with SHA384
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
     * @see \Phuture\Coherence\Hash::sha512() For hashing string data with SHA512
     * @see \Phuture\Coherence\Hash::fileSha512() For hashing file contents with SHA512
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
     * @see \Phuture\Coherence\Hash::hmacAlgorithms() For listing all supported HMAC algorithms
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
     * @see \Phuture\Coherence\Hash::equals() For direct timing-safe hash comparison
     */
    public static function hmacTimingSafe(string $data1, string $data2, string $key): bool
    {
        return hash_equals(
            self::hmac($data1, $key),
            self::hmac($data2, $key)
        );
    }

    /**
     * Adds data to an incremental HMAC hashing context.
     *
     * This method appends data to an existing HMAC context created by hmacInit(), allowing
     * you to process large data in chunks. Multiple calls to hmacUpdate() accumulate all
     * data for the final HMAC calculation performed by hmacFinal().
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     *
     * $context = Hash::hmacInit('secret-key');
     * Hash::hmacUpdate($context, 'First chunk');
     * Hash::hmacUpdate($context, 'Second chunk');
     * $hmac = Hash::hmacFinal($context);
     *
     * // Returns: HMAC of "First chunkSecond chunk"
     * ```
     *
     * @param HashContext $context The HMAC context to update, created by hmacInit()
     * @param string $data The data to add to the HMAC calculation
     * @return void
     * @see \Phuture\Coherence\Hash::hmacInit() For creating an HMAC context
     * @see \Phuture\Coherence\Hash::hmacFinal() For completing the HMAC calculation
     */
    public static function hmacUpdate(HashContext $context, string $data): void
    {
        self::update($context, $data);
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
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the specified algorithm is not supported
     * @see \Phuture\Coherence\Hash::hmacCheckWithSalt() For verifying a salted HMAC
     * @see \Phuture\Coherence\Hash::withSalt() For generating a salted hash
     */
    public static function hmacWithSalt(
        string $data,
        string $key,
        ?string $salt = null,
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
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When the specified algorithm is not supported
     * @see \Phuture\Coherence\Hash::update() For adding data to the context
     * @see \Phuture\Coherence\Hash::final() For completing the hash calculation
     */
    public static function init(string $algo = 'sha256'): HashContext
    {
        if (!in_array($algo, hash_algos())) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$algo} is not a valid hash algorithm"
            );
        }

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
     * // Returns: '1c8f1e6a94aaa7145210bf90bb52871a'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD2 hash as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::fileMd2() For hashing file contents with MD2
     * @see \Phuture\Coherence\Hash::hmacMd2() For generating HMAC with MD2
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
     * // Returns: '94e3cb0fa9aa7a5ee3db74b79e915989'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD4 hash as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::fileMd4() For hashing file contents with MD4
     * @see \Phuture\Coherence\Hash::hmacMd4() For generating HMAC with MD4
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
     * // Returns: '65a8e27d8879283831b664bd8b7f0ad4'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the MD5 hash as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::fileMd5() For hashing file contents with MD5
     * @see \Phuture\Coherence\Hash::hmacMd5() For generating HMAC with MD5
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
     * @see \Phuture\Coherence\Hash::array() For hashing serialized arrays
     * @see \Phuture\Coherence\Hash::hash() For hashing string data with a configurable algorithm
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
     * use cryptographically secure algorithms. Use the \Phuture\Coherence\Enum\PasswordAlgorithm
     * enum to choose between PHP's recommended default, BCrypt, or Argon2ID.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     * use Phuture\Coherence\Enum\PasswordAlgorithm;
     *
     * // PHP's recommended default
     * $hash = Hash::password('user123');
     *
     * // BCrypt
     * $hash = Hash::password('user123', PasswordAlgorithm::Bcrypt);
     *
     * // Argon2ID
     * $hash = Hash::password('user123', PasswordAlgorithm::Argon2id);
     *
     * // Returns: hashed password string
     * ```
     *
     * @param string $password The plain text password to hash
     * @param PasswordAlgorithm $algo The algorithm to use (default: PasswordAlgorithm::Default)
     * @param array $options Algorithm options: 'cost' for BCrypt; 'memory_cost', 'time_cost', 'threads' for Argon2ID
     * @return string Returns the hashed password string
     * @throws \Phuture\Coherence\Exception\RuntimeException When Argon2ID is requested but not supported
     * @see \Phuture\Coherence\Hash::passwordCheck() For verifying a password against its hash
     * @see \Phuture\Coherence\Hash::passwordNeedsRehash() For checking if a hash needs updating
     */
    public static function password(
        string $password,
        PasswordAlgorithm $algo = PasswordAlgorithm::Default,
        array $options = []
    ): string {
        if ($algo === PasswordAlgorithm::Argon2id && !in_array('argon2id', password_algos())) {
            throw new RuntimeException(
                "Runtime Error: Argon2ID is not supported by the current PHP installation"
            );
        }

        return match ($algo) {
            PasswordAlgorithm::Argon2id => password_hash($password, PASSWORD_ARGON2ID, $options),
            PasswordAlgorithm::Bcrypt => password_hash($password, PASSWORD_BCRYPT, $options),
            PasswordAlgorithm::Default => password_hash($password, PASSWORD_DEFAULT, $options),
        };
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
     * @see \Phuture\Coherence\Hash::password() For creating a password hash
     * @see \Phuture\Coherence\Hash::passwordNeedsRehash() For checking if a hash needs updating
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
     * @see \Phuture\Coherence\Hash::password() For creating a password hash
     * @see \Phuture\Coherence\Hash::passwordNeedsRehash() For checking if a hash needs updating
     */
    public static function passwordInfo(string $hash): array
    {
        return password_get_info($hash);
    }

    /**
     * Checks if a password hash needs to be rehashed with a stronger algorithm or updated options.
     *
     * This method determines if a password hash was created using an outdated algorithm or
     * options. It's useful for upgrading password hashes when you change your hashing parameters
     * or when PHP updates its default algorithm. Use the \Phuture\Coherence\Enum\PasswordAlgorithm
     * enum to specify which algorithm the hash should be checked against.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Hash;
     * use Phuture\Coherence\Enum\PasswordAlgorithm;
     *
     * // BCrypt cost upgrade
     * $hash = Hash::password('user123', PasswordAlgorithm::Bcrypt, ['cost' => 10]);
     * $needsRehash = Hash::passwordNeedsRehash($hash, PasswordAlgorithm::Bcrypt, ['cost' => 12]);
     *
     * // Argon2ID memory upgrade
     * $hash = Hash::password('user123', PasswordAlgorithm::Argon2id, ['memory_cost' => 65536]);
     * $needsRehash = Hash::passwordNeedsRehash($hash, PasswordAlgorithm::Argon2id, ['memory_cost' => 131072]);
     *
     * // Returns: true if the hash was created with lower cost parameters
     * ```
     *
     * @param string $hash The password hash to check
     * @param PasswordAlgorithm $algo The algorithm to check against (default: PasswordAlgorithm::Default)
     * @param array $options Options to compare against (default: [])
     * @return bool Returns true if the hash needs to be rehashed, false otherwise
     * @throws \Phuture\Coherence\Exception\RuntimeException When Argon2ID is requested but not supported
     * @see \Phuture\Coherence\Hash::password() For creating a password hash
     * @see \Phuture\Coherence\Hash::passwordCheck() For verifying a password
     */
    public static function passwordNeedsRehash(
        string $hash,
        PasswordAlgorithm $algo = PasswordAlgorithm::Default,
        array $options = []
    ): bool {
        if ($algo === PasswordAlgorithm::Argon2id && !in_array('argon2id', password_algos())) {
            throw new RuntimeException(
                "Runtime Error: Argon2ID is not supported by the current PHP installation"
            );
        }

        return match ($algo) {
            PasswordAlgorithm::Argon2id => password_needs_rehash($hash, PASSWORD_ARGON2ID, $options),
            PasswordAlgorithm::Bcrypt => password_needs_rehash($hash, PASSWORD_BCRYPT, $options),
            PasswordAlgorithm::Default => password_needs_rehash($hash, PASSWORD_DEFAULT, $options),
        };
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
     * @see \Phuture\Coherence\Hash::pbkdf2Algorithms() For listing supported PBKDF2 algorithms
     * @see \Phuture\Coherence\Hash::pbkdf2Supports() For checking if an algorithm is supported for PBKDF2
     */
    public static function pbkdf2(
        string $password,
        ?string $salt = null,
        int $iterations = self::DEFAULT_PBKDF2_ITERATIONS,
        int $length = 32,
        string $algo = 'sha256'
    ): string {
        if ($iterations <= 0) {
            throw new InvalidArgumentException(
                "Invalid Argument: The amount of iterations must be positive"
            );
        }

        if ($length <= 0 || $length > self::MAX_DERIVED_KEY_LENGTH) {
            throw new InvalidArgumentException(
                "Invalid Argument: Length must be between 1 and " . self::MAX_DERIVED_KEY_LENGTH
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
     * @see \Phuture\Coherence\Hash::pbkdf2Supports() For checking if a specific algorithm is supported for PBKDF2
     * @see \Phuture\Coherence\Hash::algorithms() For listing all supported hash algorithms
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
     * @see \Phuture\Coherence\Hash::pbkdf2Algorithms() For listing all PBKDF2-compatible algorithms
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
     * @param int $length The desired length of the output in characters for hex, or bytes for binary (default: 128)
     * @param bool $binary Whether to return raw binary data (default: false for hex string)
     * @return string Returns random data as hex string or raw binary
     * @throws \Phuture\Coherence\Exception\RuntimeException When unable to generate random bytes
     * @see \Phuture\Coherence\Hash::salt() For generating a random salt
     * @see \Phuture\Coherence\Hash::token() For generating a random token
     */
    public static function random(int $length = 128, bool $binary = false): string
    {
        try {
            if ($binary) {
                return random_bytes($length);
            }

            return substr(bin2hex(random_bytes((int) ceil($length / 2))), 0, $length);
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
     * // Returns: 'a1b2c3d4e5f678901234567890123456' (32 hex characters for 16 bytes)
     * ```
     *
     * @param int $length The desired length of the salt in bytes (default: 16)
     * @return string Returns a hexadecimal string representing the random salt
     * @throws \Phuture\Coherence\Exception\RuntimeException When unable to generate random bytes
     * @see \Phuture\Coherence\Hash::random() For generating random data
     * @see \Phuture\Coherence\Hash::withSalt() For generating a salted hash
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
     * // Returns: '0a0a9f2a6772942557ab5355d76af442f8f65e01'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA1 hash as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::fileSha1() For hashing file contents with SHA1
     * @see \Phuture\Coherence\Hash::hmacSha1() For generating HMAC with SHA1
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
     * @see \Phuture\Coherence\Hash::fileSha256() For hashing file contents with SHA256
     * @see \Phuture\Coherence\Hash::hmacSha256() For generating HMAC with SHA256
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
     * // Returns: '5485cc9b3365b4305dfb4e8337e0a598a574f8242bf17289e0dd6c20a3cd44a089de16ab4ab308f63e44b1170eb5f515'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA384 hash as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::fileSha384() For hashing file contents with SHA384
     * @see \Phuture\Coherence\Hash::hmacSha384() For generating HMAC with SHA384
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
     * // Returns: '374d794a95cdcfd8b35993185fef9ba368f160d8daf432d08ba9f1ed1e5abe6cc69291e0fa2fe0006a52570ef18c19def4e617c33ce52ef0a6e5fbe318cb0387'
     * ```
     *
     * @param string $data The data to hash
     * @param bool $binary Whether to output raw binary data (default: false for hex string)
     * @return string Returns the SHA512 hash as a hex string or raw binary data
     * @see \Phuture\Coherence\Hash::fileSha512() For hashing file contents with SHA512
     * @see \Phuture\Coherence\Hash::hmacSha512() For generating HMAC with SHA512
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
     * @see \Phuture\Coherence\Hash::algorithms() For listing all supported hash algorithms
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
     * @throws \Phuture\Coherence\Exception\RuntimeException When unable to generate random bytes
     * @see \Phuture\Coherence\Hash::random() For generating random data
     * @see \Phuture\Coherence\Hash::unique() For generating a unique identifier
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
     * @throws \Phuture\Coherence\Exception\RuntimeException When unable to generate random bytes
     * @see \Phuture\Coherence\Hash::token() For generating a random token
     * @see \Phuture\Coherence\Hash::uuid() For generating a UUID v4
     */
    public static function unique(): string
    {
        try {
            return self::sha256(random_bytes(32));
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
     * @see \Phuture\Coherence\Hash::init() For creating a hash context
     * @see \Phuture\Coherence\Hash::final() For completing the hash calculation
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
     * @throws \Phuture\Coherence\Exception\RuntimeException When unable to generate random bytes
     * @see \Phuture\Coherence\Hash::unique() For generating a unique identifier
     * @see \Phuture\Coherence\Hash::token() For generating a random token
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
     * @see \Phuture\Coherence\Hash::checkWithSalt() For verifying a salted hash
     * @see \Phuture\Coherence\Hash::hmacWithSalt() For generating a salted HMAC
     */
    public static function withSalt(
        string $data,
        ?string $salt = null,
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
