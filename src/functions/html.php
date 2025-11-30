<?php

declare(strict_types=1);

if (!function_exists('html_entity_encode')) {
    function html_entity_encode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        ?string $encoding = null,
        bool $double_encode = true
    ): string|false
    {
        return htmlentities($string, $flags, $encoding, $double_encode);
    }
}

if (!function_exists('html_special_chars_encode')) {
    function html_special_chars_encode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        ?string $encoding = null,
        bool $double_encode = true
    ): string|false
    {
        return htmlspecialchars($string, $flags, $encoding, $double_encode);
    }
}

if (!function_exists('html_special_chars_decode')) {
    function html_special_chars_decode(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
    ): string|false
    {
        return htmlspecialchars_decode($string, $flags);
    }
}

if (!function_exists('html_strip_tags')) {
    function html_strip_tags(string $string, array|string|null $allowed_tags = null): string
    {
        return strip_tags($string, $allowed_tags);
    }
}
