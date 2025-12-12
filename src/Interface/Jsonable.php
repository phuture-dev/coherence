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
 * Example:
 * ```php
 * namespace Phuture\Coherence;
 *
 * use Phuture\Coherence\Interface\Jsonable;
 *
 * class Product implements Jsonable
 * {
 *     public function __construct(private string $name, private float $price) {}
 *
 *     public function toJson(): string
 *     {
 *         return json_encode(['name' => $this->name, 'price' => $this->price]);
 *     }
 * }
 *
 * $product = new Product('Laptop', 999.99);
 * $json = $product->toJson(); // {"name":"Laptop","price":999.99}
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
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
