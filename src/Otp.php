<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use SensitiveParameter;
use Phuture\Coherence\Support\StaticClass;
use Phuture\Coherence\Exception\InvalidArgumentException;

/**
 * One-time password generation using HOTP and TOTP algorithms.
 *
 * This utility class generates and verifies one-time passwords following
 * the HOTP (HMAC-based One-Time Password, RFC 4226) and TOTP (Time-based
 * One-Time Password, RFC 6238) standards. These are the same algorithms
 * used by authenticator apps like Google Authenticator and Authy.
 *
 * Key features:
 *
 * - **HOTP Generation**: Counter-based one-time passwords that change with each use
 * - **TOTP Generation**: Time-based one-time passwords that change at regular intervals
 * - **Configurable Digits**: Support for 6, 7, or 8 digit codes
 * - **Multiple Hash Algorithms**: SHA1, SHA256, and SHA512 support
 * - **Verification with Window**: Time-drift-tolerant verification for TOTP codes
 * - **Timing-Safe Comparison**: Prevents timing attacks during code verification
 *
 * Security considerations:
 *
 * - The secret should be at least 160 bits (20 bytes) for SHA1, 256 bits for SHA256
 * - Secrets should be generated using a cryptographically secure random source
 * - Use \Phuture\Coherence\Hash::random() to generate secure secrets
 * - The default time step of 30 seconds matches most authenticator apps
 * - Use a verification window of 1 to tolerate minor clock drift without excessive risk
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Otp extends StaticClass
{
    /**
     * Default number of digits in generated one-time passwords.
     *
     * Most authenticator apps produce 6-digit codes, making this the
     * standard default for interoperability.
     *
     * @see \Phuture\Coherence\Otp::hotp()
     * @see \Phuture\Coherence\Otp::totp()
     */
    public const DEFAULT_DIGITS = 6;

    /**
     * Default time step in seconds for TOTP generation.
     *
     * A 30-second window balances usability (codes last long enough to type)
     * with security (codes rotate frequently). This matches the de facto
     * standard used by Google Authenticator, Authy, and similar apps.
     *
     * @see \Phuture\Coherence\Otp::totp()
     */
    public const DEFAULT_TIME_STEP = 30;

    /**
     * Default hash algorithm for one-time password generation.
     *
     * SHA1 is the most widely supported algorithm for OTP and is used
     * by the majority of authenticator applications. SHA256 and SHA512
     * provide stronger hashing but may not be supported by all clients.
     *
     * @see \Phuture\Coherence\Otp::hotp()
     * @see \Phuture\Coherence\Otp::totp()
     */
    public const DEFAULT_ALGORITHM = 'sha1';

    /**
     * Default verification window for TOTP code checking.
     *
     * A window of 1 means the code is checked against the current time step
     * plus one step before and one step after, allowing up to 30 seconds
     * of clock drift in either direction.
     *
     * @see \Phuture\Coherence\Otp::verifyTotp()
     */
    public const DEFAULT_WINDOW = 1;

    /**
     * Generates an HMAC-based one-time password (HOTP).
     *
     * This method creates a one-time password using a shared secret and a
     * counter value. Each time a password is used, the counter increments,
     * producing a completely different code. Both the client and server must
     * stay in sync on the counter value.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Otp;
     *
     * $secret = Hash::random(20, true);
     * $code = Otp::hotp($secret, 0);
     *
     * // Returns: '123456' (6-digit code)
     * ```
     *
     * @param string $secret The shared secret key as raw binary bytes
     * @param int $counter The counter value that increments with each use (must be non-negative)
     * @param int $digits The number of digits in the output code, either 6, 7, or 8 (default: 6)
     * @param string $algorithm The hash algorithm to use: 'sha1', 'sha256', or 'sha512' (default: 'sha1')
     * @return string The one-time password as a zero-padded numeric string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When digits is not 6, 7, or 8,
     *     when counter is negative, or when algorithm is unsupported
     * @see \Phuture\Coherence\Otp::totp() For time-based one-time password generation
     * @see \Phuture\Coherence\Otp::verifyHotp() For verifying an HOTP code
     */
    public static function hotp(
        #[SensitiveParameter] string $secret,
        int $counter,
        int $digits = self::DEFAULT_DIGITS,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): string {
        self::validateDigits($digits);
        self::validateCounter($counter);
        self::validateAlgorithm($algorithm);

        $counterBytes = self::packCounter($counter);
        $hmac = hash_hmac($algorithm, $counterBytes, $secret, true);

        return self::truncateToDigits($hmac, $digits);
    }

    /**
     * Generates a time-based one-time password (TOTP).
     *
     * This method creates a one-time password using a shared secret and the
     * current time. The password changes at regular intervals (time steps),
     * typically every 30 seconds. This is the algorithm used by most
     * authenticator apps for two-factor authentication.
     *
     * You can pass a specific timestamp to generate a code for a particular
     * moment, or omit it to use the current system time.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Otp;
     *
     * $secret = Hash::random(20, true);
     *
     * // Generate a code for the current time
     * $code = Otp::totp($secret);
     *
     * // Generate a code for a specific timestamp
     * $code = Otp::totp($secret, 1700000000);
     *
     * // 8-digit code with SHA256
     * $code = Otp::totp($secret, digits: 8, algorithm: 'sha256');
     * ```
     *
     * @param string $secret The shared secret key as raw binary bytes
     * @param int|null $timestamp The Unix timestamp to generate the code for,
     *     or null for current time (default: null)
     * @param int $timeStep The number of seconds each code lasts (default: 30)
     * @param int $digits The number of digits in the output code, either 6, 7, or 8 (default: 6)
     * @param string $algorithm The hash algorithm to use: 'sha1', 'sha256', or 'sha512' (default: 'sha1')
     * @return string The one-time password as a zero-padded numeric string
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When parameters are out of range
     *     or algorithm is unsupported
     * @see \Phuture\Coherence\Otp::hotp() For counter-based one-time password generation
     * @see \Phuture\Coherence\Otp::verifyTotp() For verifying a TOTP code
     */
    public static function totp(
        #[SensitiveParameter] string $secret,
        ?int $timestamp = null,
        int $timeStep = self::DEFAULT_TIME_STEP,
        int $digits = self::DEFAULT_DIGITS,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): string {
        if ($timeStep <= 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Time step must be a positive integer'
            );
        }

        $timestamp = $timestamp ?? time();
        $counter = intdiv($timestamp, $timeStep);

        return self::hotp($secret, $counter, $digits, $algorithm);
    }

    /**
     * Verifies an HOTP code against a shared secret and expected counter value.
     *
     * This method checks whether a provided one-time password matches the
     * expected code for the given counter value. It uses timing-safe
     * comparison to prevent timing attacks.
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Otp;
     *
     * $secret = Hash::random(20, true);
     * $code = Otp::hotp($secret, 42);
     *
     * $isValid = Otp::verifyHotp($secret, $code, 42);
     *
     * // Returns: true
     * ```
     *
     * @param string $secret The shared secret key as raw binary bytes
     * @param string $code The one-time password to verify
     * @param int $counter The expected counter value
     * @param int $digits The number of digits the code should have, either 6, 7, or 8 (default: 6)
     * @param string $algorithm The hash algorithm used: 'sha1', 'sha256', or 'sha512' (default: 'sha1')
     * @return bool Returns true if the code is valid for the given counter, false otherwise
     * @see \Phuture\Coherence\Otp::hotp() For generating an HOTP code
     * @see \Phuture\Coherence\Otp::verifyTotp() For verifying a TOTP code with clock drift tolerance
     */
    public static function verifyHotp(
        #[SensitiveParameter] string $secret,
        string $code,
        int $counter,
        int $digits = self::DEFAULT_DIGITS,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): bool {
        $expected = self::hotp($secret, $counter, $digits, $algorithm);

        return hash_equals($expected, $code);
    }

    /**
     * Verifies a TOTP code against a shared secret with clock drift tolerance.
     *
     * This method checks whether a provided one-time password is valid for
     * the current time step, plus or minus a configurable window. The window
     * parameter controls how many time steps before and after the current one
     * are also accepted, which helps handle clock drift between the client
     * and server.
     *
     * A window of 0 means only the exact current time step is accepted.
     * A window of 1 means the previous, current, and next time steps are all
     * accepted (a total of 3 codes).
     *
     * Example:
     * ```php
     * use Phuture\Coherence\Otp;
     *
     * $secret = Hash::random(20, true);
     * $code = Otp::totp($secret);
     *
     * // Verify with default 1-step window (accepts ±30 seconds)
     * $isValid = Otp::verifyTotp($secret, $code);
     *
     * // Verify with no tolerance (exact time step only)
     * $isValid = Otp::verifyTotp($secret, $code, window: 0);
     * ```
     *
     * @param string $secret The shared secret key as raw binary bytes
     * @param string $code The one-time password to verify
     * @param int|null $timestamp The Unix timestamp to verify against,
     *     or null for current time (default: null)
     * @param int $timeStep The number of seconds each code lasts (default: 30)
     * @param int $window The number of time steps to check before and after the current step (default: 1)
     * @param int $digits The number of digits the code should have, either 6, 7, or 8 (default: 6)
     * @param string $algorithm The hash algorithm used: 'sha1', 'sha256',
     *     or 'sha512' (default: 'sha1')
     * @return bool Returns true if the code is valid within the window, false otherwise
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When parameters are out of range
     *     or algorithm is unsupported
     * @see \Phuture\Coherence\Otp::totp() For generating a TOTP code
     * @see \Phuture\Coherence\Otp::verifyHotp() For verifying an HOTP code
     */
    public static function verifyTotp(
        #[SensitiveParameter] string $secret,
        string $code,
        ?int $timestamp = null,
        int $timeStep = self::DEFAULT_TIME_STEP,
        int $window = self::DEFAULT_WINDOW,
        int $digits = self::DEFAULT_DIGITS,
        string $algorithm = self::DEFAULT_ALGORITHM
    ): bool {
        if ($timeStep <= 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Time step must be a positive integer'
            );
        }

        if ($window < 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Window must be a non-negative integer'
            );
        }

        $timestamp = $timestamp ?? time();
        $currentCounter = intdiv($timestamp, $timeStep);

        for ($offset = -$window; $offset <= $window; $offset++) {
            $counter = $currentCounter + $offset;

            if ($counter < 0) {
                continue;
            }

            $expected = self::hotp($secret, $counter, $digits, $algorithm);

            if (hash_equals($expected, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Converts a counter integer to an 8-byte big-endian binary string.
     *
     * The HOTP algorithm requires the counter to be represented as an
     * 8-byte big-endian unsigned integer. This method handles the
     * packing on both 64-bit and 32-bit systems.
     *
     * @param int $counter The counter value to pack
     * @return string The 8-byte big-endian binary representation of the counter
     */
    private static function packCounter(int $counter): string
    {
        $result = '';
        for ($i = 7; $i >= 0; $i--) {
            $result .= chr(($counter >> ($i * 8)) & 0xff);
        }

        return $result;
    }

    /**
     * Extracts a numeric code of the specified length from an HMAC hash.
     *
     * This method implements the dynamic truncation algorithm from RFC 4226.
     * It takes the raw HMAC output, extracts a 31-bit integer using the
     * offset defined by the last nibble of the hash, then reduces it to
     * the requested number of digits using modulo.
     *
     * @param string $hmac The raw binary HMAC hash output
     * @param int $digits The number of digits to produce (6, 7, or 8)
     * @return string The zero-padded numeric code
     */
    private static function truncateToDigits(string $hmac, int $digits): string
    {
        $offset = ord($hmac[strlen($hmac) - 1]) & 0x0f;
        $binary = (
            ((ord($hmac[$offset]) & 0x7f) << 24) |
            ((ord($hmac[$offset + 1]) & 0xff) << 16) |
            ((ord($hmac[$offset + 2]) & 0xff) << 8) |
            (ord($hmac[$offset + 3]) & 0xff)
        );

        $otp = $binary % (10 ** $digits);

        return str_pad((string) $otp, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Validates that the digits parameter is a supported value.
     *
     * @param int $digits The number of digits to validate
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When digits is not 6, 7, or 8
     */
    private static function validateDigits(int $digits): void
    {
        if (!in_array($digits, [6, 7, 8], true)) {
            throw new InvalidArgumentException(
                'Invalid Argument: Digits must be 6, 7, or 8'
            );
        }
    }

    /**
     * Validates that the counter is a non-negative integer.
     *
     * @param int $counter The counter value to validate
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When counter is negative
     */
    private static function validateCounter(int $counter): void
    {
        if ($counter < 0) {
            throw new InvalidArgumentException(
                'Invalid Argument: Counter must be a non-negative integer'
            );
        }
    }

    /**
     * Validates that the algorithm is supported for OTP generation.
     *
     * @param string $algorithm The hash algorithm to validate
     * @throws \Phuture\Coherence\Exception\InvalidArgumentException When algorithm is not sha1, sha256, or sha512
     */
    private static function validateAlgorithm(string $algorithm): void
    {
        if (!in_array($algorithm, ['sha1', 'sha256', 'sha512'], true)) {
            throw new InvalidArgumentException(
                "Invalid Argument: Algorithm {$algorithm} is not supported for OTP generation"
            );
        }
    }
}
