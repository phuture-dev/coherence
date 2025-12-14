<?php

declare(strict_types=1);

namespace Phuture\Coherence\Support;

use Phuture\Coherence\Exception\InvalidArgumentException;

/**
 * Provides argument parsing utilities for extracting callbacks and enums from function arguments.
 * This trait contains helper methods for parsing variable argument lists, particularly useful
 * for methods that accept flexible parameter orders with optional callbacks and enum values.
 *
 * It enables clean extraction of callable functions and enum values from the end of argument
 * arrays while preserving the remaining arguments for further processing.
 *
 * These utilities are commonly used in data manipulation classes and other libraries
 * that need to support flexible method signatures with optional parameters that can
 * appear at the end of the argument list.
 *
 * @copyright Copyright (c) 2025, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
trait ArgumentExtractor
{
    /**
     * Extracts callback functions from the end of an arguments array.
     *
     * This method iterates through the provided arguments array in reverse order,
     * extracting callable functions from the end until it encounters a non-callable.
     * The extracted callbacks are removed from the original arguments array.
     *
     * Note: ALL trailing callbacks at the end of the array will be removed, regardless of the limit.
     * The limit only affects how many callbacks are returned in the result.
     *
     * @param array $arguments The arguments array to process, passed by reference
     * @param int $limit Maximum number of callbacks to extract and return. Default: 2
     * @return array Array of extracted callback functions, maintaining original order
     */
    private static function getCallbacksFromArguments(array &$arguments, int $limit = 2): array
    {
        $callables = [];
        foreach (array_reverse($arguments, true) as $index => $argument) {
            if (!is_callable($argument)) {
                break;
            }

            $callables[] = $argument;
            unset($arguments[$index]);
        }

        if ($callables === []) {
            return [];
        }

        return array_reverse(array_slice($callables, 0, $limit));
    }

    /**
     * Extracts enum values from the end of an arguments array.
     *
     * This method searches through the provided arguments array from the end
     * and extracts enum values that match the specified enum type. The extracted
     * enums are removed from the original arguments array.
     *
     * Note: ALL trailing enums at the end of the array will be removed, regardless of the limit.
     * The limit only affects how many enums are returned in the result.
     *
     * @param array $arguments The arguments array to search through (passed by reference)
     * @param string $enum The fully qualified enum class name
     * @param int $limit Maximum number of enum values to extract and return (default: 1)
     * @return array Array of extracted enum values in original order
     */
    private static function getEnumsFromArguments(array &$arguments, string $enum, int $limit = 1): array
    {
        if (!enum_exists($enum)) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$enum} is not a valid enum class name"
            );
        }

        $enums = [];
        foreach (array_reverse($arguments, true) as $index => $argument) {
            if (!in_array($argument, $enum::cases(), true)) {
                break;
            }

            $enums[] = $argument;
            unset($arguments[$index]);
        }

        if ($enums === []) {
            return [];
        }

        return array_reverse(array_slice($enums, 0, $limit));
    }
}
