<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for array key letter casing.
 *
 * Controls the case that string keys are converted to. Use this enum with
 * \Phuture\Coherence\Arrays::changeKeyCase() to specify whether keys are
 * normalized to lowercase or uppercase.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum KeyCase
{
    /**
     * Convert string keys to lowercase.
     *
     * This is the default case. Every alphabetic character in a string key is
     * lowercased, while numeric keys are left untouched.
     */
    case Lower;

    /**
     * Convert string keys to uppercase.
     *
     * Every alphabetic character in a string key is uppercased, while numeric
     * keys are left untouched.
     */
    case Upper;
}
