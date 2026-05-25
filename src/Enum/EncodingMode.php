<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for HTML entity encoding strategies.
 *
 * This enum defines how HTML encoding and decoding operations handle characters.
 * Each case represents a different level of encoding strictness. Use this enum
 * with \Phuture\Coherence\Html::entityEncode() and \Phuture\Coherence\Html::entityDecode()
 * to control which characters are converted to or from HTML entities.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum EncodingMode
{
    /**
     * Encodes or decodes all characters that have HTML entity equivalents.
     *
     * When encoding, every character that can be represented as an HTML entity
     * (such as accented letters, currency symbols, etc.) is converted to its
     * entity form. When decoding, all HTML entities are converted back to their
     * original characters.
     */
    case All;

    /**
     * Encodes or decodes only the characters that have special meaning in HTML.
     *
     * When encoding, only the five characters with special meaning in HTML
     * (`&`, `"`, `'`, `<`, `>`) are converted to their entity forms.
     * When decoding, only these special character entities are restored.
     * All other characters (like accented letters) remain unchanged.
     */
    case SpecialChars;
}
