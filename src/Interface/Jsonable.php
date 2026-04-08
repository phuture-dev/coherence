<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

/**
 * Interface for objects that can be converted to JSON strings.
 *
 * This interface provides a standardized way for objects to be converted
 * to their JSON representation, making it easier to serialize objects
 * for API responses, storage, or transmission.
 *
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Jsonable
{
    /**
     * Convert the object to a JSON string representation.
     *
     * This method should return a valid JSON string that represents
     * the object's data. The implementation should handle proper
     * JSON encoding and error handling.
     *
     * @return string The JSON string representation of the object
     */
    public function toJson(): string;
}
