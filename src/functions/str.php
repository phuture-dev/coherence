<?php

declare(strict_types=1);

if (!function_exists('str_str')) {
    function str_str(string $subject, string $search, bool $before = false): string|false
    {
        return strstr($subject, $search, $before);
    }
}

if (!function_exists('str_istr')) {
    function str_istr(string $subject, string $search, bool $before = false): string|false
    {
        return stristr($subject, $search, $before);
    }
}

if (!function_exists('str_icontains')) {
    function str_icontains(string $subject, string $search): bool
    {
        return stripos($subject, $search) !== false;
    }
}

if (!function_exists('str_pos')) {
    function str_pos(string $subject, string $search, int $offset = 0): int|false
    {
        return strpos($subject, $search, $offset);
    }
}

if (!function_exists('str_ipos')) {
    function str_ipos(string $subject, string $search, int $offset = 0): int|false
    {
        return stripos($subject, $search, $offset);
    }
}

if (!function_exists('str_last_pos')) {
    function str_last_pos(string $subject, string $search, int $offset = 0): int|false
    {
        return strrpos($subject, $search, $offset);
    }
}

if (!function_exists('str_last_ipos')) {
    function str_last_ipos(string $subject, string $search, int $offset = 0): int|false
    {
        return strripos($subject, $search, $offset);
    }
}

if (!function_exists('str_len')) {
    function str_len(string $subject): int
    {
        return strlen($subject);
    }
}

if (!function_exists('str_lower')) {
    function str_lower(string $subject): string
    {
        return strtolower($subject);
    }
}

if (!function_exists('str_upper')) {
    function str_upper(string $subject): string
    {
        return strtoupper($subject);
    }
}

if (!function_exists('str_upper_first')) {
    function str_upper_first(string $subject): string
    {
        return ucfirst($subject);
    }
}

if (!function_exists('str_lower_first')) {
    function str_lower_first(string $subject): string
    {
        return lcfirst($subject);
    }
}

if (!function_exists('str_upper_words')) {
    function str_upper_words(string $subject): string
    {
        return ucwords($subject);
    }
}

if (!function_exists('str_lower_words')) {
    function str_lower_words(string $subject): string
    {
        return preg_replace_callback('/\b\w/', function ($matches) {
            return strtolower($matches[0]);
        }, $subject);
    }
}

if (!function_exists('str_parse')) {
    function str_parse(string $subject, &$result): void
    {
        parse_str($subject, $result);
    }
}

if (!function_exists('str_sub')) {
    function str_sub(string $subject, int $offset, ?int $length = null): string
    {
        return substr($subject, $offset, $length);
    }
}

if (!function_exists('str_trim')) {
    function str_trim(string $subject, string $characters = " \n\r\t\v\0"): string
    {
        return trim($subject, $characters);
    }
}

if (!function_exists('str_ltrim')) {
    function str_ltrim(string $subject, string $characters = " \n\r\t\v\0"): string
    {
        return ltrim($subject, $characters);
    }
}

if (!function_exists('str_rtrim')) {
    function str_rtrim(string $subject, string $characters = " \n\r\t\v\0"): string
    {
        return rtrim($subject, $characters);
    }
}

if (!function_exists('str_explode')) {
    function str_explode(string $separator, string $subject, int $limit = PHP_INT_MAX): array
    {
        return explode($separator, $subject, $limit);
    }
}

if (!function_exists('str_implode')) {
    function str_implode(string $separator, array $array): string
    {
        return implode($separator, $array);
    }
}

if (!function_exists('str_count')) {
    function str_count(string $subject, string $search, int $offset = 0, ?int $length = null): int
    {
        return substr_count($subject, $search, $offset, $length);
    }
}

if (!function_exists('str_rep')) {
    function str_rep(string $subject, string|array $search, string|array $replace, int &$count = null): string|array
    {
        return str_replace($search, $replace, $subject, $count);
    }
}

if (!function_exists('str_irep')) {
    function str_irep(string $subject, string|array $search, string|array $replace, int &$count = null): string|array
    {
        return str_ireplace($search, $replace, $subject, $count);
    }
}

if (!function_exists('str_reverse')) {
    function str_reverse(string $subject): string
    {
        return strrev($subject);
    }
}

if (!function_exists('str_chunk_split')) {
    function str_chunk_split(string $subject, int $length = 76, string $separator = "\r\n"): string
    {
        return chunk_split($subject, $length, $separator);
    }
}

if (!function_exists('str_compare')) {
    function str_compare(string $string1, string $string2): int
    {
        return strcmp($string1, $string2);
    }
}

if (!function_exists('str_icompare')) {
    function str_icompare(string $string1, string $string2): int
    {
        return strcasecmp($string1, $string2);
    }
}

if (!function_exists('str_ncompare')) {
    function str_ncompare(string $string1, string $string2, int $length): int
    {
        return strncmp($string1, $string2, $length);
    }
}

if (!function_exists('str_incompare')) {
    function str_incompare(string $string1, string $string2, int $length): int
    {
        return strncasecmp($string1, $string2, $length);
    }
}

if (!function_exists('str_last_chr')) {
    function str_last_chr(string $subject, string $search): string|false
    {
        return strrchr($subject, $search);
    }
}

if (!function_exists('str_replace_sub')) {
    function str_replace_sub(string|array $subject, string|array $replace, int $offset, ?int $length = null): string|array
    {
        return substr_replace($subject, $replace, $offset, $length);
    }
}

if (!function_exists('str_wrap')) {
    function str_wrap(string $subject, int $width = 75, string $break = "\n", bool $cut_long_words = false): string
    {
        return wordwrap($subject, $width, $break, $cut_long_words);
    }
}

if (!function_exists('str_translate')) {
    function str_translate(string $subject, array|string $from, ?string $to = null): string
    {
        return strtr($subject, $from, $to);
    }
}

if (!function_exists('str_ord')) {
    function str_ord(string $subject): int
    {
        return ord($subject);
    }
}

if (!function_exists('str_chr')) {
    function str_chr(int $codepoint): string
    {
        return chr($codepoint);
    }
}

if (!function_exists('str_nl2br')) {
    function str_nl2br(string $subject, bool $use_xhtml = true): string
    {
        return nl2br($subject, $use_xhtml);
    }
}

if (!function_exists('str_quote_meta')) {
    function str_quote_meta(string $subject): string
    {
        return quotemeta($subject);
    }
}

if (!function_exists('str_format')) {
    function str_format(string $format, mixed ...$values): string
    {
        return sprintf($format, ...$values);
    }
}

if (!function_exists('str_similar')) {
    function str_similar(string $string1, string $string2, float &$percent = null): int
    {
        return similar_text($string1, $string2, $percent);
    }
}

if (!function_exists('str_levenshtein')) {
    function str_levenshtein(
        string $string1,
        string $string2,
        int $insertion_cost = 1,
        int $replacement_cost = 1,
        int $deletion_cost = 1
    ): int
    {
        return levenshtein($string1, $string2, $insertion_cost, $replacement_cost, $deletion_cost);
    }
}

if (!function_exists('str_soundex')) {
    function str_soundex(string $subject): string
    {
        return soundex($subject);
    }
}

if (!function_exists('str_metaphone')) {
    function str_metaphone(string $subject, int $max_phonemes = 0): string|false
    {
        return metaphone($subject, $max_phonemes);
    }
}

if (!function_exists('str_locale_compare')) {
    function str_locale_compare(string $string1, string $string2): int
    {
        return strcoll($string1, $string2);
    }
}

if (!function_exists('str_printf')) {
    function str_printf(string $format, array $values): int
    {
        return vprintf($format, $values);
    }
}

if (!function_exists('str_format_sprintf')) {
    function str_format_sprintf(string $format, array $values): string
    {
        return vsprintf($format, $values);
    }
}

if (!function_exists('str_convert_uuencode')) {
    function str_convert_uuencode(string $subject): string
    {
        return convert_uuencode($subject);
    }
}

if (!function_exists('str_convert_uudecode')) {
    function str_convert_uudecode(string $subject): string|false
    {
        return convert_uudecode($subject);
    }
}

if (!function_exists('str_tok')) {
    function str_tok(string $subject, string $token): string|false
    {
        return strtok($subject, $token);
    }
}
