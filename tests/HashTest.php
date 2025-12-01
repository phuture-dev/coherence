<?php

declare(strict_types=1);

namespace Advandz\Kernel\Tests;

use Advandz\Kernel\Hash;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Test case for the Hash utility class.
 */
class HashTest extends TestCase
{
    public function testMd2(): void
    {
        $data = 'test data';
        Assert::equal(hash('md2', $data), Hash::md2($data));
    }

    public function testMd4(): void
    {
        $data = 'test data';
        Assert::equal(hash('md4', $data), Hash::md4($data));
    }

    public function testMd5(): void
    {
        $data = 'test data';
        Assert::equal(hash('md5', $data), Hash::md5($data));
    }

    public function testSha1(): void
    {
        $data = 'test data';
        Assert::equal(hash('sha1', $data), Hash::sha1($data));
    }

    public function testSha256(): void
    {
        $data = 'test data';
        Assert::equal(hash('sha256', $data), Hash::sha256($data));
    }

    public function testSha384(): void
    {
        $data = 'test data';
        Assert::equal(hash('sha384', $data), Hash::sha384($data));
    }

    public function testSha512(): void
    {
        $data = 'test data';
        Assert::equal(hash('sha512', $data), Hash::sha512($data));
    }

    public function testAdler32(): void
    {
        $data = 'test data';
        Assert::equal(hash('adler32', $data), Hash::adler32($data));
    }

    public function testCrc32(): void
    {
        $data = 'test data';
        Assert::equal(hash('crc32', $data), Hash::crc32($data));
    }
}

// Run the test
(new HashTest())->run();
