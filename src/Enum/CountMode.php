<?php

declare(strict_types=1);

namespace Phuture\Coherence\Enum;

/**
 * Enumeration for array element counting modes.
 *
 * Controls whether nested arrays are descended into when counting elements. Use this
 * enum with \Phuture\Coherence\Arrays::length() to choose between counting only the
 * top level and counting every nested element.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
enum CountMode
{
    /**
     * Count only the elements at the top level of the array.
     *
     * This is the default mode. A nested array counts as a single element regardless
     * of how many elements it holds.
     */
    case Normal;

    /**
     * Count every element recursively, including those in nested arrays.
     *
     * Each nested array is counted as an element in its own right and its elements are
     * then counted as well, so a multidimensional array yields a larger total than the
     * number of entries at its top level.
     */
    case Recursive;
}
