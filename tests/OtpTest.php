<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Hash;
use Phuture\Coherence\Otp;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\InvalidArgumentException;

require __DIR__ . '/bootstrap.php';

class OtpTest extends TestCase
{
    private const RFC4226_SECRET = '12345678901234567890';

    public function testHotpRfc4226TestVectorCounter0(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 0);
        Assert::same('755224', $code);
    }

    public function testHotpRfc4226TestVectorCounter1(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 1);
        Assert::same('287082', $code);
    }

    public function testHotpRfc4226TestVectorCounter2(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 2);
        Assert::same('359152', $code);
    }

    public function testHotpRfc4226TestVectorCounter3(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 3);
        Assert::same('969429', $code);
    }

    public function testHotpRfc4226TestVectorCounter4(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 4);
        Assert::same('338314', $code);
    }

    public function testHotpRfc4226TestVectorCounter5(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 5);
        Assert::same('254676', $code);
    }

    public function testHotpRfc4226TestVectorCounter6(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 6);
        Assert::same('287922', $code);
    }

    public function testHotpRfc4226TestVectorCounter7(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 7);
        Assert::same('162583', $code);
    }

    public function testHotpRfc4226TestVectorCounter8(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 8);
        Assert::same('399871', $code);
    }

    public function testHotpRfc4226TestVectorCounter9(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 9);
        Assert::same('520489', $code);
    }

    public function testHotpDeterministic(): void
    {
        $secret = Hash::random(20, true);

        $code1 = Otp::hotp($secret, 0);
        $code2 = Otp::hotp($secret, 0);
        Assert::same($code1, $code2);
    }

    public function testHotpDifferentCountersProduceDifferentCodes(): void
    {
        $secret = Hash::random(20, true);

        $code0 = Otp::hotp($secret, 0);
        $code1 = Otp::hotp($secret, 1);
        $code2 = Otp::hotp($secret, 2);

        Assert::notSame($code0, $code1);
        Assert::notSame($code1, $code2);
        Assert::notSame($code0, $code2);
    }

    public function testHotpDifferentSecretsProduceDifferentCodes(): void
    {
        $secret1 = Hash::random(20, true);
        $secret2 = Hash::random(20, true);

        $code1 = Otp::hotp($secret1, 0);
        $code2 = Otp::hotp($secret2, 0);

        Assert::notSame($code1, $code2);
    }

    public function testHotp7Digits(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 0, 7);
        Assert::same(7, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testHotp8Digits(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 0, 8);
        Assert::same(8, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testHotpSha256(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 0, 6, 'sha256');
        Assert::same(6, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testHotpSha512(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 0, 6, 'sha512');
        Assert::same(6, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testHotpInvalidDigits(): void
    {
        Assert::exception(function () {
            Otp::hotp('secret', 0, 5);
        }, InvalidArgumentException::class, 'Invalid Argument: Digits must be 6, 7, or 8');

        Assert::exception(function () {
            Otp::hotp('secret', 0, 9);
        }, InvalidArgumentException::class, 'Invalid Argument: Digits must be 6, 7, or 8');
    }

    public function testHotpNegativeCounter(): void
    {
        Assert::exception(function () {
            Otp::hotp('secret', -1);
        }, InvalidArgumentException::class, 'Invalid Argument: Counter must be a non-negative integer');
    }

    public function testHotpInvalidAlgorithm(): void
    {
        Assert::exception(function () {
            Otp::hotp('secret', 0, 6, 'md5');
        }, InvalidArgumentException::class, 'Invalid Argument: Algorithm md5 is not supported for OTP generation');
    }

    public function testTotpRfc6238Sha1(): void
    {
        $secret = '12345678901234567890';

        $code = Otp::totp($secret, 59, 30, 8, 'sha1');
        Assert::same('94287082', $code);
    }

    public function testTotpRfc6238Sha256(): void
    {
        $secret = '12345678901234567890123456789012';

        $code = Otp::totp($secret, 59, 30, 8, 'sha256');
        Assert::same('46119246', $code);
    }

    public function testTotpRfc6238Sha512(): void
    {
        $secret = '1234567890123456789012345678901234567890123456789012345678901234';

        $code = Otp::totp($secret, 59, 30, 8, 'sha512');
        Assert::same('90693936', $code);
    }

    public function testTotpRfc6238Timestamp59(): void
    {
        $secret = '12345678901234567890';

        $code = Otp::totp($secret, 59, 30, 8, 'sha1');
        Assert::same('94287082', $code);
    }

    public function testTotpRfc6238Timestamp1111111109(): void
    {
        $secret = '12345678901234567890';

        $code = Otp::totp($secret, 1111111109, 30, 8, 'sha1');
        Assert::same('07081804', $code);
    }

    public function testTotpRfc6238Timestamp1111111111(): void
    {
        $secret = '12345678901234567890';

        $code = Otp::totp($secret, 1111111111, 30, 8, 'sha1');
        Assert::same('14050471', $code);
    }

    public function testTotpRfc6238Timestamp1234567890(): void
    {
        $secret = '12345678901234567890';

        $code = Otp::totp($secret, 1234567890, 30, 8, 'sha1');
        Assert::same('89005924', $code);
    }

    public function testTotpRfc6238Timestamp2000000000(): void
    {
        $secret = '12345678901234567890';

        $code = Otp::totp($secret, 2000000000, 30, 8, 'sha1');
        Assert::same('69279037', $code);
    }

    public function testTotpUsesCurrentTimeByDefault(): void
    {
        $secret = Hash::random(20, true);

        $code1 = Otp::totp($secret);
        $code2 = Otp::totp($secret, time());

        Assert::same($code1, $code2);
    }

    public function testTotpDifferentTimeStepsProduceDifferentCodes(): void
    {
        $secret = Hash::random(20, true);

        $code30 = Otp::totp($secret, 100, 30);
        $code60 = Otp::totp($secret, 100, 60);

        Assert::notSame($code30, $code60);
    }

    public function testTotpSameTimeStepProducesSameCode(): void
    {
        $secret = Hash::random(20, true);

        $codeAt30 = Otp::totp($secret, 30, 30);
        $codeAt59 = Otp::totp($secret, 59, 30);

        Assert::same($codeAt30, $codeAt59);
    }

    public function testTotpInvalidTimeStep(): void
    {
        Assert::exception(function () {
            Otp::totp('secret', null, 0);
        }, InvalidArgumentException::class, 'Invalid Argument: Time step must be a positive integer');

        Assert::exception(function () {
            Otp::totp('secret', null, -30);
        }, InvalidArgumentException::class, 'Invalid Argument: Time step must be a positive integer');
    }

    public function testVerifyHotpValidCode(): void
    {
        $secret = Hash::random(20, true);
        $code = Otp::hotp($secret, 0);

        Assert::true(Otp::verifyHotp($secret, $code, 0));
    }

    public function testVerifyHotpWrongCounter(): void
    {
        $secret = Hash::random(20, true);
        $code = Otp::hotp($secret, 0);

        Assert::false(Otp::verifyHotp($secret, $code, 1));
    }

    public function testVerifyHotpWrongSecret(): void
    {
        $secret1 = Hash::random(20, true);
        $secret2 = Hash::random(20, true);
        $code = Otp::hotp($secret1, 0);

        Assert::false(Otp::verifyHotp($secret2, $code, 0));
    }

    public function testVerifyHotpRfc4226TestVector(): void
    {
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '755224', 0));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '287082', 1));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '359152', 2));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '969429', 3));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '338314', 4));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '254676', 5));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '287922', 6));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '162583', 7));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '399871', 8));
        Assert::true(Otp::verifyHotp(self::RFC4226_SECRET, '520489', 9));
    }

    public function testVerifyTotpCurrentCode(): void
    {
        $secret = Hash::random(20, true);
        $code = Otp::totp($secret);

        Assert::true(Otp::verifyTotp($secret, $code));
    }

    public function testVerifyTotpExactTimestamp(): void
    {
        $secret = Hash::random(20, true);
        $timestamp = 1700000000;
        $code = Otp::totp($secret, $timestamp);

        Assert::true(Otp::verifyTotp($secret, $code, $timestamp));
    }

    public function testVerifyTotpWithinWindow(): void
    {
        $secret = Hash::random(20, true);
        $timestamp = 1700000090;
        $code = Otp::totp($secret, 1700000060);

        Assert::true(Otp::verifyTotp($secret, $code, $timestamp, 30, 1));
    }

    public function testVerifyTotpOutsideWindow(): void
    {
        $secret = Hash::random(20, true);
        $timestamp = 1700000200;
        $code = Otp::totp($secret, 1700000000);

        Assert::false(Otp::verifyTotp($secret, $code, $timestamp, 30, 1));
    }

    public function testVerifyTotpWindow0(): void
    {
        $secret = Hash::random(20, true);
        $timestamp = 1700000030;
        $code = Otp::totp($secret, 1700000000);

        Assert::false(Otp::verifyTotp($secret, $code, $timestamp, 30, 0));
    }

    public function testVerifyTotpNegativeWindowRejected(): void
    {
        Assert::exception(function () {
            Otp::verifyTotp('secret', '000000', null, 30, -1);
        }, InvalidArgumentException::class, 'Invalid Argument: Window must be a non-negative integer');
    }

    public function testVerifyTotpInvalidTimeStep(): void
    {
        Assert::exception(function () {
            Otp::verifyTotp('secret', '000000', null, 0);
        }, InvalidArgumentException::class, 'Invalid Argument: Time step must be a positive integer');
    }

    public function testVerifyTotpWith8Digits(): void
    {
        $secret = '12345678901234567890';
        $code = Otp::totp($secret, 59, 30, 8, 'sha1');

        Assert::true(Otp::verifyTotp($secret, $code, 59, 30, 1, 8, 'sha1'));
    }

    public function testVerifyTotpWithDifferentAlgorithm(): void
    {
        $secret = '12345678901234567890123456789012';
        $code = Otp::totp($secret, 59, 30, 8, 'sha256');

        Assert::true(Otp::verifyTotp($secret, $code, 59, 30, 1, 8, 'sha256'));
    }

    public function testOutputAlwaysNumeric(): void
    {
        $secret = Hash::random(20, true);

        for ($i = 0; $i < 10; $i++) {
            $code = Otp::hotp($secret, $i);
            Assert::true(ctype_digit($code), "HOTP code should be numeric for counter {$i}");
            Assert::same(6, strlen($code));
        }
    }

    public function testOutputAlwaysZeroPadded(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 7);
        Assert::same(6, strlen($code));
        Assert::same('162583', $code);
    }

    public function testLargeCounter(): void
    {
        $code = Otp::hotp(self::RFC4226_SECRET, 1000000);
        Assert::same(6, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testHotpWithDefaultParameters(): void
    {
        $secret = Hash::random(20, true);
        $code = Otp::hotp($secret, 0);

        Assert::same(6, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testTotpWithDefaultParameters(): void
    {
        $secret = Hash::random(20, true);
        $code = Otp::totp($secret);

        Assert::same(6, strlen($code));
        Assert::true(ctype_digit($code));
    }

    public function testTotpRfc6238Sha256Timestamp1234567890(): void
    {
        $secret = '12345678901234567890123456789012';

        $code = Otp::totp($secret, 1234567890, 30, 8, 'sha256');
        Assert::same('91819424', $code);
    }

    public function testTotpRfc6238Sha512Timestamp1234567890(): void
    {
        $secret = '1234567890123456789012345678901234567890123456789012345678901234';

        $code = Otp::totp($secret, 1234567890, 30, 8, 'sha512');
        Assert::same('93441116', $code);
    }

    public function testVerifyTotpRfc6238Vectors(): void
    {
        $secret = '12345678901234567890';

        Assert::true(Otp::verifyTotp($secret, '94287082', 59, 30, 1, 8, 'sha1'));
        Assert::true(Otp::verifyTotp($secret, '07081804', 1111111109, 30, 1, 8, 'sha1'));
        Assert::true(Otp::verifyTotp($secret, '14050471', 1111111111, 30, 1, 8, 'sha1'));
    }
}

(new OtpTest())->run();
