<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

/**
 * Interface for objects that can be converted to boolean values.
 *
 * This interface provides a standardized way for objects to be converted
 * to their boolean representation, making it easier to evaluate objects
 * in conditional statements or logical operations.
 *
 * Example:
 * ```php
 * namespace Phuture\Coherence;
 *
 * use Phuture\Coherence\Interface\Boolable;
 *
 * class Permission implements Boolable
 * {
 *     public function __construct(private bool $granted) {}
 *
 *     public function toBool(): bool
 *     {
 *         return $this->granted;
 *     }
 *
 *     public function isGranted(): bool
 *     {
 *         return $this->toBool();
 *     }
 * }
 *
 * $permission = new Permission(true);
 * if ($permission->toBool()) {
 *     echo "Access granted";
 * }
 * ```
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Boolable
{
    /**
     * Convert the object to a boolean representation.
     *
     * This method should return true or false based on the object's
     * state or data. This is useful for conditional statements,
     * validation checks, or logical operations.
     *
     * @return bool The boolean representation of the object
     */
    public function toBool(): bool;
}
