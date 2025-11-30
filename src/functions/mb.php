<?php

declare(strict_types=1);

if (!function_exists('mb_str_str')) {
    function mb_str_str(string $subject, string $search, bool $before = false, ?string $encoding = null): string|false
    {
        return mb_strstr($subject, $search, $before, $encoding);
    }
}

if (!function_exists('mb_str_istr')) {
    function mb_str_istr(string $subject, string $search, bool $before = false, ?string $encoding = null): string|false
    {
        return mb_stristr($subject, $search, $before, $encoding);
    }
}

if (!function_exists('mb_str_contains')) {
    function mb_str_contains(string $subject, string $search, ?string $encoding = null): bool
    {
        return mb_strpos($subject, $search, 0, $encoding) !== false;
    }
}

if (!function_exists('mb_str_icontains')) {
    function mb_str_icontains(string $subject, string $search, ?string $encoding = null): bool
    {
        return mb_stripos($subject, $search, 0, $encoding) !== false;
    }
}

if (!function_exists('mb_str_pos')) {
    function mb_str_pos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_strpos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_ipos')) {
    function mb_str_ipos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_stripos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_last_pos')) {
    function mb_str_last_pos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_strrpos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_last_ipos')) {
    function mb_str_last_ipos(string $subject, string $search, int $offset = 0, ?string $encoding = null): int|false
    {
        return mb_strripos($subject, $search, $offset, $encoding);
    }
}

if (!function_exists('mb_str_len')) {
    function mb_str_len(string $subject, ?string $encoding = null): int
    {
        return mb_strlen($subject, $encoding);
    }
}

if (!function_exists('mb_str_lower')) {
    function mb_str_lower(string $subject, ?string $encoding = null): string
    {
        return mb_strtolower($subject, $encoding);
    }
}

if (!function_exists('mb_str_upper')) {
    function mb_str_upper(string $subject, ?string $encoding = null): string
    {
        return mb_strtoupper($subject, $encoding);
    }
}

if (!function_exists('mb_str_upper_first')) {
    function mb_str_upper_first(string $subject, ?string $encoding = null): string
    {
        $first = mb_substr($subject, 0, 1, $encoding);
        $rest = mb_substr($subject, 1, null, $encoding);
        return mb_strtoupper($first, $encoding) . $rest;
    }
}

if (!function_exists('mb_str_upper_words')) {
    function mb_str_upper_words(string $subject, ?string $encoding = null): string
    {
        return mb_convert_case($subject, MB_CASE_TITLE, $encoding);
    }
}

if (!function_exists('mb_str_parse')) {
    function mb_str_parse(string $subject, &$result): void
    {
        mb_parse_str($subject, $result);
    }
}

if (!function_exists('mb_str_sub')) {
    function mb_str_sub(string $subject, int $offset, ?int $length = null, ?string $encoding = null): string
    {
        return mb_substr($subject, $offset, $length, $encoding);
    }
}

if (!function_exists('mb_str_count')) {
    function mb_str_count(string $subject, string $search, ?string $encoding = null): int
    {
        return mb_substr_count($subject, $search, $encoding);
    }
}

if (!function_exists('mb_str_last_chr')) {
    function mb_str_last_chr(string $subject, string $search, bool $before = false, ?string $encoding = null): string|false
    {
        return mb_strrchr($subject, $search, $before, $encoding);
    }
}

if (!function_exists('mb_str_convert_case')) {
    function mb_str_convert_case(string $subject, int $mode, ?string $encoding = null): string
    {
        return mb_convert_case($subject, $mode, $encoding);
    }
}

if (!function_exists('mb_str_detect_encoding')) {
    function mb_str_detect_encoding(string $subject, array|string|null $encodings = null, bool $strict = false): string|false
    {
        return mb_detect_encoding($subject, $encodings, $strict);
    }
}

if (!function_exists('mb_str_convert_encoding')) {
    function mb_str_convert_encoding(array|string $subject, string $to_encoding, array|string|null $from_encoding = null): array|string|false
    {
        return mb_convert_encoding($subject, $to_encoding, $from_encoding);
    }
}

if (!function_exists('mb_str_chr')) {
    function mb_str_chr(int $codepoint, ?string $encoding = null): string|false
    {
        return mb_chr($codepoint, $encoding);
    }
}

if (!function_exists('mb_str_ord')) {
    function mb_str_ord(string $subject, ?string $encoding = null): int|false
    {
        return mb_ord($subject, $encoding);
    }
}

if (!function_exists('mb_str_scrub')) {
    function mb_str_scrub(string $subject, ?string $encoding = null): string
    {
        return mb_scrub($subject, $encoding);
    }
}

if (!function_exists('mb_str_width')) {
    function mb_str_width(string $subject, ?string $encoding = null): int
    {
        return mb_strwidth($subject, $encoding);
    }
}

if (!function_exists('mb_str_cut')) {
    function mb_str_cut(string $subject, int $start, int $width, string $trim_marker = '', ?string $encoding = null): string
    {
        return mb_strimwidth($subject, $start, $width, $trim_marker, $encoding);
    }
}
