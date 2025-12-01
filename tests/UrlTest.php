<?php

declare(strict_types=1);

namespace Advandz\Kernel\Tests;

use Advandz\Kernel\Url;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/bootstrap.php';

/**
 * Test case for the Url utility class.
 */
class UrlTest extends TestCase
{
    public function testEncode(): void
    {
        $subject = 'hello world test';
        Assert::equal(urlencode($subject), Url::encode($subject));
    }

    public function testDecode(): void
    {
        $subject = 'hello+world+test';
        Assert::equal(urldecode($subject), Url::decode($subject));
    }

    public function testEncodeRaw(): void
    {
        $subject = 'hello world test';
        Assert::equal(rawurlencode($subject), Url::encodeRaw($subject));
    }

    public function testDecodeRaw(): void
    {
        $subject = 'hello%20world%20test';
        Assert::equal(rawurldecode($subject), Url::decodeRaw($subject));
    }

    public function testParse(): void
    {
        $url = 'https://example.com:8080/path?query=value#fragment';
        Assert::equal(parse_url($url), Url::parse($url));
    }

    public function testBuildQuery(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        Assert::equal(http_build_query($data), Url::buildQuery($data));
    }

    public function testBase64Encode(): void
    {
        $subject = 'test data';
        $expected = rtrim(strtr(base64_encode($subject), '+/', '-_'), '=');
        Assert::equal($expected, Url::base64Encode($subject));
    }

    public function testBase64Decode(): void
    {
        $subject = 'dGVzdCBkYXRh';
        $expected = base64_decode(strtr($subject, '-_', '+/'));
        Assert::equal($expected, Url::base64Decode($subject));
    }

    public function testBase64Roundtrip(): void
    {
        $original = 'test data with special chars: +/=';
        $encoded = Url::base64Encode($original);
        $decoded = Url::base64Decode($encoded);
        Assert::equal($original, $decoded);
    }
}

// Run the test
(new UrlTest())->run();
