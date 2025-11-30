<?php

declare(strict_types=1);

if (!function_exists('url_encode')) {
    function url_encode(string $subject): string
    {
        return urlencode($subject);
    }
}

if (!function_exists('url_decode')) {
    function url_decode(string $subject): string
    {
        return urldecode($subject);
    }
}

if (!function_exists('url_encode_raw')) {
    function url_encode_raw(string $subject): string
    {
        return rawurlencode($subject);
    }
}

if (!function_exists('url_decode_raw')) {
    function url_decode_raw(string $subject): string
    {
        return rawurldecode($subject);
    }
}

if (!function_exists('url_parse')) {
    function url_parse(string $url, int $component = -1): int|string|array|null|false
    {
        return parse_url($url, $component);
    }
}

if (!function_exists('url_build_query')) {
    function url_build_query(
        array|object $data,
        string $numeric_prefix = '',
        ?string $arg_separator = null,
        int $encoding_type = PHP_QUERY_RFC1738
    ): string
    {
        return http_build_query($data, $numeric_prefix, $arg_separator, $encoding_type);
    }
}

if (!function_exists('url_get_headers')) {
    function url_get_headers(string $url, bool $associative = false, $context = null): array|false
    {
        return get_headers($url, $associative, $context);
    }
}

if (!function_exists('url_get_meta_tags')) {
    function url_get_meta_tags(string $filename, bool $use_include_path = false): array|false
    {
        return get_meta_tags($filename, $use_include_path);
    }
}

if (!function_exists('url_base64_encode')) {
    function url_base64_encode(string $subject): string
    {
        return rtrim(strtr(base64_encode($subject), '+/', '-_'), '=');
    }
}

if (!function_exists('url_base64_decode')) {
    function url_base64_decode(string $subject): string|false
    {
        return base64_decode(strtr($subject, '-_', '+/'));
    }
}
