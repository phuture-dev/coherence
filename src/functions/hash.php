<?php

declare(strict_types=1);

if (!function_exists('hash_md2')) {
    function hash_md2(string $data, bool $binary = false): string
    {
        return hash('md2', $data, $binary);
    }
}

if (!function_exists('hash_md4')) {
    function hash_md4(string $data, bool $binary = false): string
    {
        return hash('md4', $data, $binary);
    }
}

if (!function_exists('hash_md5')) {
    function hash_md5(string $data, bool $binary = false): string
    {
        return hash('md5', $data, $binary);
    }
}

if (!function_exists('hash_sha1')) {
    function hash_sha1(string $data, bool $binary = false): string
    {
        return hash('sha1', $data, $binary);
    }
}

if (!function_exists('hash_sha256')) {
    function hash_sha256(string $data, bool $binary = false): string
    {
        return hash('sha256', $data, $binary);
    }
}

if (!function_exists('hash_sha384')) {
    function hash_sha384(string $data, bool $binary = false): string
    {
        return hash('sha384', $data, $binary);
    }
}

if (!function_exists('hash_sha512')) {
    function hash_sha512(string $data, bool $binary = false): string
    {
        return hash('sha512', $data, $binary);
    }
}

if (!function_exists('hash_adler32')) {
    function hash_adler32(string $data, bool $binary = false): string
    {
        return hash('adler32', $data, $binary);
    }
}

if (!function_exists('hash_crc32')) {
    function hash_crc32(string $data, bool $binary = false): string
    {
        return hash('crc32', $data, $binary);
    }
}
