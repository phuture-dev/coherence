<?php

declare(strict_types=1);

if (!function_exists('array_contains')) {
    function array_contains(array $array, mixed $value, bool $strict = false): bool
    {
        return in_array($value, $array, $strict);
    }
}

if (!function_exists('array_contains_key')) {
    function array_contains_key(array $array, string|int $key): bool
    {
        return array_key_exists($key, $array);
    }
}

if (!function_exists('array_intersect_keys')) {
    function array_intersect_keys(array $array, array ...$arrays): array
    {
        return array_intersect_key($array, ...$arrays);
    }
}

if (!function_exists('array_sort')) {
    function array_sort(array &$array, int $flags = SORT_REGULAR): bool
    {
        return sort($array, $flags);
    }
}

if (!function_exists('array_sort_reverse')) {
    function array_sort_reverse(array &$array, int $flags = SORT_REGULAR): bool
    {
        return rsort($array, $flags);
    }
}

if (!function_exists('array_sort_keys')) {
    function array_sort_keys(array &$array, int $flags = SORT_REGULAR): bool
    {
        return ksort($array, $flags);
    }
}

if (!function_exists('array_sort_keys_reverse')) {
    function array_sort_keys_reverse(array &$array, int $flags = SORT_REGULAR): bool
    {
        return krsort($array, $flags);
    }
}

if (!function_exists('array_sort_assoc')) {
    function array_sort_assoc(array &$array, int $flags = SORT_REGULAR): bool
    {
        return asort($array, $flags);
    }
}

if (!function_exists('array_sort_assoc_reverse')) {
    function array_sort_assoc_reverse(array &$array, int $flags = SORT_REGULAR): bool
    {
        return arsort($array, $flags);
    }
}

if (!function_exists('array_sort_natural')) {
    function array_sort_natural(array &$array, bool $case_insensitive = false): bool
    {
        return $case_insensitive ? natcasesort($array) : natsort($array);
    }
}

if (!function_exists('array_sort_user')) {
    function array_sort_user(array &$array, callable $callback): bool
    {
        return usort($array, $callback);
    }
}

if (!function_exists('array_sort_keys_user')) {
    function array_sort_keys_user(array &$array, callable $callback): bool
    {
        return uksort($array, $callback);
    }
}

if (!function_exists('array_sort_assoc_user')) {
    function array_sort_assoc_user(array &$array, callable $callback): bool
    {
        return uasort($array, $callback);
    }
}

if (!function_exists('array_random')) {
    function array_random(array $array): mixed
    {
        $key = array_rand($array);

        return $array[$key];
    }
}

if (!function_exists('array_random_keys')) {
    function array_random_keys(array $array, int $num = 1): string|int|array
    {
        return array_rand($array, $num);
    }
}

if (!function_exists('array_shuffle')) {
    function array_shuffle(array &$array): bool
    {
        return shuffle($array);
    }
}

if (!function_exists('array_len')) {
    function array_len(array $array, int $mode = COUNT_NORMAL): int
    {
        return count($array, $mode);
    }
}

if (!function_exists('array_collapse')) {
    function array_collapse(array $array, string $key = 'key', string|array $value = 'value'): array
    {
        if (empty($array)) {
            return [];
        }

        $list = [];
        foreach ($array as $element) {
            if (!isset($element->{$key})) {
                continue;
            }

            if (is_string($value)) {
                $list[$element->{$key}] = $element->{$value};
            } elseif (is_array($value)) {
                $composed_value = '';
                foreach ($value as $value_name) {
                    $composed_value .= $element->{$value_name} . ' ';
                }

                $list[$element->{$key}] = trim($composed_value);
            }
        }

        return $list;
    }
}

if (!function_exists('array_find')) {
    function array_find(array $array, mixed $value, bool $strict = false): int|string|false
    {
        return array_search($value, $array, $strict);
    }
}

if (!function_exists('array_apply')) {
    function array_apply(array $array, ?callable $callback, array ...$arrays): array
    {
        return array_map($callback, $array, ...$arrays);
    }
}

if (!function_exists('array_join')) {
    function array_join(array $array, string $separator = ''): string
    {
        return implode($separator, $array);
    }
}
