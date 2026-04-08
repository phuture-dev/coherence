<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for array comparison strategies.
 *
 * This enum defines different ways to compare arrays when performing operations
 * like checking equality, differences, or intersections. Each case represents
 * a specific comparison focus.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum ArrayComparator
{
    /**
     * Compare arrays based on both keys and values.
     *
     * When using this comparator, both the keys and their associated values are
     * considered during comparison operations. This provides the most comprehensive
     * comparison, requiring both the structure (keys) and content (values) to match.
     */
    case Both;

    /**
     * Compare arrays based on their keys only.
     *
     * When using this comparator, only the keys of the arrays are considered
     * during comparison operations. Values associated with those keys are ignored.
     */
    case Key;

    /**
     * Compare arrays based on their values only.
     *
     * When using this comparator, only the values within the arrays are considered
     * during comparison operations. The order and keys of those values may or may not
     * be relevant depending on the specific operation.
     */
    case Value;
}
