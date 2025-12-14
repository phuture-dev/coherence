<?php

declare(strict_types=1);

namespace Phuture\Coherence;

use Phuture\Coherence\Support\StaticClass;

/**
 * HTML utility class providing consistent wrappers around native PHP HTML functions.
 *
 * This class offers static methods for common HTML operations including entity encoding,
 * special character conversion, and tag stripping. All methods follow camelCase naming
 * conventions and provide a clean, object-oriented interface to PHP's native HTML functions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
class Html extends StaticClass
{
    /**
     * Converts all applicable characters to HTML entities.
     *
     * Provides a consistent wrapper around the native function htmlentities.
     *
     * @param string $string The input string
     * @param int $flags Flags for controlling conversion behavior (default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @param string|null $encoding Character encoding (default: null for ini default)
     * @param bool $double_encode Whether to encode existing HTML entities (default: true)
     * @return string Returns the encoded string
     * @see https://www.php.net/manual/en/function.htmlentities.php
     */
    public static function entityEncode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        ?string $encoding = null,
        bool $double_encode = true
    ): string {
        return htmlentities($string, $flags, $encoding, $double_encode);
    }

    /**
     * Converts special HTML entities back to characters.
     *
     * Provides a consistent wrapper around the native function htmlspecialchars_decode.
     *
     * @param string $string The string to decode
     * @param int $flags Flags for controlling conversion behavior (default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @return string Returns the decoded string
     * @see https://www.php.net/manual/en/function.htmlspecialchars-decode.php
     */
    public static function specialCharsDecode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
    ): string {
        return htmlspecialchars_decode($string, $flags);
    }

    /**
     * Converts special characters to HTML entities.
     *
     * Provides a consistent wrapper around the native function htmlspecialchars.
     *
     * @param string $string The input string
     * @param int $flags Flags for controlling conversion behavior (default: ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401)
     * @param string|null $encoding Character encoding (default: null for ini default)
     * @param bool $double_encode Whether to encode existing HTML entities (default: true)
     * @return string Returns the encoded string
     * @see https://www.php.net/manual/en/function.htmlspecialchars.php
     */
    public static function specialCharsEncode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        ?string $encoding = null,
        bool $double_encode = true
    ): string {
        return htmlspecialchars($string, $flags, $encoding, $double_encode);
    }

    /**
     * Strips HTML and PHP tags from a string.
     *
     * Provides a consistent wrapper around the native function strip_tags.
     *
     * @param string $string The input string
     * @param array|string|null $allowed_tags Tags that should not be stripped (default: null)
     * @return string Returns the stripped string
     * @see https://www.php.net/manual/en/function.strip-tags.php
     */
    public static function stripTags(string $string, array|string|null $allowed_tags = null): string
    {
        return strip_tags($string, $allowed_tags);
    }
}
