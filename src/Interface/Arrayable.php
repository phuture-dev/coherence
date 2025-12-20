<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

/**
 * Interface for objects that can be converted to arrays.
 *
 * This interface provides a standardized way for objects to be converted
 * to their array representation, making it easier to work with different
 * data types consistently.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Arrayable
{
    /**
     * Convert the object to an array representation.
     *
     * This method should return a native PHP array that represents
     * the object's data in a serializable format.
     *
     * @return array The array representation of the object
     */
    public function toArray(): array;
}
