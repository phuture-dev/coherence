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
 * @copyright Copyright (c) 2026, Advandz Technologies, LLC
 * @license https://opensource.org/licenses/MIT MIT License
 * @link https://www.phuture.dev/ Phuture
 */
trait ArgumentExtractor
{
    /**
     * Extracts a trailing boolean value from the end of an arguments array.
     *
     * When the last element of `$args` is a bool, it is removed from the array
     * and returned. When no trailing bool is present the default value is returned
     * and the array is left unchanged.
     *
     * @param array $args The arguments array to process, passed by reference
     * @param bool $default Value to return when no trailing bool is found (default: false)
     * @return bool The extracted boolean, or `$default` when none was present
     */
    private static function getBoolFromArguments(array &$args, bool $default = false): bool
    {
        if ($args === []) {
            return $default;
        }

        $last = end($args);

        if (!is_bool($last)) {
            return $default;
        }

        array_pop($args);

        return $last;
    }
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
     * @param array $args The arguments array to process, passed by reference
     * @param int $limit Maximum number of callbacks to extract and return. Default: 2
     * @return array Array of extracted callback functions, maintaining original order
     */
    private static function getCallbacksFromArguments(array &$args, int $limit = 2): array
    {
        $callables = [];
        foreach (array_reverse($args, true) as $index => $argument) {
            if (!is_callable($argument)) {
                break;
            }

            $callables[] = $argument;
            unset($args[$index]);
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
     * @param array $args The arguments array to search through (passed by reference)
     * @param string $enum The fully qualified enum class name
     * @param int $limit Maximum number of enum values to extract and return (default: 1)
     * @return array Array of extracted enum values in original order
     */
    private static function getEnumsFromArguments(array &$args, string $enum, int $limit = 1): array
    {
        if (!enum_exists($enum)) {
            throw new InvalidArgumentException(
                "Invalid Argument: {$enum} is not a valid enum class name"
            );
        }

        $enums = [];
        foreach (array_reverse($args, true) as $index => $argument) {
            if (!in_array($argument, $enum::cases(), true)) {
                break;
            }

            $enums[] = $argument;
            unset($args[$index]);
        }

        if ($enums === []) {
            return [];
        }

        return array_reverse(array_slice($enums, 0, $limit));
    }
}
