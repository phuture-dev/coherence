<?php

declare(strict_types=1);

namespace Phuture\Coherence\Interface;

use Throwable;

/**
 * Interface for all library exceptions.
 *
 * This interface serves as a contract that all exception classes in the
 * library namespace should implement. It extends PHP's native Throwable
 * interface to maintain compatibility with standard exception handling while
 * providing a common type for catching library-specific exceptions.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
interface Exception extends Throwable
{
}
