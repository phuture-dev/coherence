<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use stdClass;
use HashContext;
use Phuture\Coherence\Hash;
use Tester\{Assert, Environment, TestCase};
use Phuture\Coherence\Enum\PasswordAlgorithm;
use Phuture\Coherence\Exception\{InvalidArgumentException, RuntimeException};

require __DIR__ . '/bootstrap.php';

class HashTest extends TestCase
{
    public function testAdler32(): void
    {
        $hash = Hash::adler32('Hello, World!');
        Assert::same('1f9e046a', $hash);

        // Test binary output
        $binary = Hash::adler32('Hello, World!', true);
        Assert::true(strlen($binary) === 4); // Adler-32 is 4 bytes
    }

    public function testAlgorithmConsistency(): void
    {
        $data = 'test data';

        // Generic hash method should produce same results as specific methods
        Assert::same(Hash::make($data, false, 'md5'), Hash::md5($data));
        Assert::same(Hash::make($data, false, 'sha1'), Hash::sha1($data));
        Assert::same(Hash::make($data, false, 'sha256'), Hash::sha256($data));
        Assert::same(Hash::make($data, false, 'sha512'), Hash::sha512($data));
    }

    public function testAlgorithms(): void
    {
        $algorithms = Hash::algorithms();
        Assert::type('array', $algorithms);
        Assert::true(in_array('sha256', $algorithms));
        Assert::true(in_array('md5', $algorithms));
    }

    public function testAllHmacAlgorithmsSupport(): void
    {
        $algorithms = Hash::hmacAlgorithms();
        $data = 'test';
        $key = 'key';

        foreach ($algorithms as $algorithm) {
            if (Hash::hmacSupports($algorithm)) {
                $hmac = Hash::hmac($data, $key, false, $algorithm);
                Assert::true(strlen($hmac) > 0, "HMAC with algorithm {$algorithm} should produce output");
                Assert::true(Hash::hmacCheck($data, $key, $hmac, $algorithm), "HMAC check should pass for algorithm {$algorithm}");
            }
        }
    }

    public function testArray(): void
    {
        $data = ['name' => 'John', 'age' => 30, 'active' => true];
        $hash = Hash::array($data);
        Assert::true(strlen($hash) === 64); // SHA256 hash length

        // Test with different algorithm
        $hashMd5 = Hash::array($data, false, 'md5');
        Assert::true(strlen($hashMd5) === 32); // MD5 hash length
    }

    public function testBinaryOutput(): void
    {
        $data = 'test';

        $hex = Hash::sha256($data, false);
        $binary = Hash::sha256($data, true);

        Assert::same(bin2hex($binary), $hex);
        Assert::same(32, strlen($binary)); // SHA256 produces 32 bytes
    }

    public function testBlake2b(): void
    {
        if (!in_array('blake2b512', hash_algos())) {
            Environment::skip('Blake2b not supported');
        }

        $hash = Hash::blake2b('Hello, World!');
        Assert::same(128, strlen($hash)); // Blake2b-512 produces 128 hex chars
        Assert::true(ctype_xdigit($hash));

        // Cross-verify with direct hash() call
        Assert::same(hash('blake2b512', 'Hello, World!'), $hash);

        // Empty string
        Assert::same(hash('blake2b512', ''), Hash::blake2b(''));

        // Binary output: 64 bytes
        $binary = Hash::blake2b('Hello, World!', true);
        Assert::same(64, strlen($binary));
        Assert::same(bin2hex($binary), $hash);
    }

    public function testBlake2bFileMethod(): void
    {
        if (!in_array('blake2b512', hash_algos())) {
            Environment::skip('Blake2b not supported');
        }

        $content = 'Test data for Blake2b file hashing';
        $tempFile = tempnam(sys_get_temp_dir(), 'blake2b_test_');
        file_put_contents($tempFile, $content);

        try {
            Assert::same(Hash::blake2b($content), Hash::fileBlake2b($tempFile));
        } finally {
            unlink($tempFile);
        }
    }

    public function testBlake2bFileMethodNotSupported(): void
    {
        if (in_array('blake2b512', hash_algos())) {
            Environment::skip('Blake2b is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::fileBlake2b('/tmp/any-path');
        }, InvalidArgumentException::class, 'Invalid Argument: blake2b512 is not a valid hash algorithm');
    }

    public function testBlake2bHmacMethod(): void
    {
        if (!in_array('blake2b512', hash_algos())) {
            Environment::skip('Blake2b not supported');
        }

        if (!in_array('blake2b512', hash_hmac_algos())) {
            Environment::skip('Blake2b HMAC not supported');
        }

        $data = 'message';
        $key = 'secret';
        $hmac = Hash::hmacBlake2b($data, $key);

        Assert::same(128, strlen($hmac)); // Blake2b-512 HMAC produces 128 hex chars
        Assert::same(hash_hmac('blake2b512', $data, $key), $hmac);
    }

    public function testBlake2bHmacMethodNotSupported(): void
    {
        if (in_array('blake2b512', hash_hmac_algos())) {
            Environment::skip('Blake2b HMAC is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::hmacBlake2b('message', 'secret');
        }, InvalidArgumentException::class, 'Invalid Argument: blake2b512 is not a valid HMAC hash algorithm');
    }

    public function testBlake2bNotSupported(): void
    {
        if (in_array('blake2b512', hash_algos())) {
            Environment::skip('Blake2b is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::blake2b('test');
        }, RuntimeException::class, 'Runtime Error: Blake2b is not supported by the current PHP installation');
    }

    public function testBlake2s(): void
    {
        if (!in_array('blake2s256', hash_algos())) {
            Environment::skip('Blake2s not supported');
        }

        $hash = Hash::blake2s('Hello, World!');
        Assert::same(64, strlen($hash)); // Blake2s-256 produces 64 hex chars
        Assert::true(ctype_xdigit($hash));

        // Cross-verify with direct hash() call
        Assert::same(hash('blake2s256', 'Hello, World!'), $hash);

        // Empty string
        Assert::same(hash('blake2s256', ''), Hash::blake2s(''));

        // Binary output: 32 bytes
        $binary = Hash::blake2s('Hello, World!', true);
        Assert::same(32, strlen($binary));
        Assert::same(bin2hex($binary), $hash);
    }

    public function testBlake2sFileMethod(): void
    {
        if (!in_array('blake2s256', hash_algos())) {
            Environment::skip('Blake2s not supported');
        }

        $content = 'Test data for Blake2s file hashing';
        $tempFile = tempnam(sys_get_temp_dir(), 'blake2s_test_');
        file_put_contents($tempFile, $content);

        try {
            Assert::same(Hash::blake2s($content), Hash::fileBlake2s($tempFile));
        } finally {
            unlink($tempFile);
        }
    }

    public function testBlake2sFileMethodNotSupported(): void
    {
        if (in_array('blake2s256', hash_algos())) {
            Environment::skip('Blake2s is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::fileBlake2s('/tmp/any-path');
        }, InvalidArgumentException::class, 'Invalid Argument: blake2s256 is not a valid hash algorithm');
    }

    public function testBlake2sHmacMethod(): void
    {
        if (!in_array('blake2s256', hash_algos())) {
            Environment::skip('Blake2s not supported');
        }

        if (!in_array('blake2s256', hash_hmac_algos())) {
            Environment::skip('Blake2s HMAC not supported');
        }

        $data = 'message';
        $key = 'secret';
        $hmac = Hash::hmacBlake2s($data, $key);

        Assert::same(64, strlen($hmac)); // Blake2s-256 HMAC produces 64 hex chars
        Assert::same(hash_hmac('blake2s256', $data, $key), $hmac);
    }

    public function testBlake2sHmacMethodNotSupported(): void
    {
        if (in_array('blake2s256', hash_hmac_algos())) {
            Environment::skip('Blake2s HMAC is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::hmacBlake2s('message', 'secret');
        }, InvalidArgumentException::class, 'Invalid Argument: blake2s256 is not a valid HMAC hash algorithm');
    }

    public function testBlake2sNotSupported(): void
    {
        if (in_array('blake2s256', hash_algos())) {
            Environment::skip('Blake2s is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::blake2s('test');
        }, RuntimeException::class, 'Runtime Error: Blake2s is not supported by the current PHP installation');
    }

    public function testCheck(): void
    {
        $data = 'important message';
        $hash = Hash::sha256($data);

        Assert::true(Hash::check($data, $hash));
        Assert::false(Hash::check('different data', $hash));
        Assert::false(Hash::check($data, ''));
    }

    public function testCheckWithSalt(): void
    {
        $data = 'password123';
        $result = Hash::makeWithSalt($data);

        Assert::true(Hash::checkWithSalt($data, $result['hash'], $result['salt']));
        Assert::false(Hash::checkWithSalt('wrongpassword', $result['hash'], $result['salt']));
        Assert::false(Hash::checkWithSalt($data, '', $result['salt']));
    }

    public function testCrc32(): void
    {
        $hash = Hash::crc32('Hello, World!');
        Assert::same('dffed8e6', $hash);

        // Test binary output
        $binary = Hash::crc32('Hello, World!', true);
        Assert::true(strlen($binary) === 4); // CRC32 is 4 bytes
    }

    public function testCrc32b(): void
    {
        $hash = Hash::crc32b('Hello, World!');
        Assert::same('ec4ac3d0', $hash);

        // Binary output: 4 bytes that hex-encode back to the hex digest
        $binary = Hash::crc32b('Hello, World!', true);
        Assert::same(4, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testCrc32c(): void
    {
        $hash = Hash::crc32c('Hello, World!');
        Assert::same('4d551068', $hash);

        // Binary output: 4 bytes that hex-encode back to the hex digest
        $binary = Hash::crc32c('Hello, World!', true);
        Assert::same(4, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testDeterministicBehavior(): void
    {
        $data = 'deterministic test';

        // Same input should always produce same output
        $hash1 = Hash::sha256($data);
        $hash2 = Hash::sha256($data);

        Assert::same($hash1, $hash2);
    }

    public function testEmptyData(): void
    {
        Assert::same('e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855', Hash::sha256('')); // Empty string SHA256
        Assert::same('d41d8cd98f00b204e9800998ecf8427e', Hash::md5('')); // Empty string MD5
    }

    public function testEquals(): void
    {
        $hash1 = 'abc123';
        $hash2 = 'abc123';
        $hash3 = 'different';

        Assert::true(Hash::equals($hash1, $hash2));
        Assert::false(Hash::equals($hash1, $hash3));
    }

    public function testFile(): void
    {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'hash_test_');
        file_put_contents($tempFile, 'Hello, World!');

        try {
            $hash = Hash::file($tempFile);
            Assert::same('dffd6021bb2bd5b0af676290809ec3a53191dd81c7f70a4b28688a362182986f', $hash);

            // Test file-specific methods
            Assert::same(Hash::fileMd5($tempFile), Hash::md5('Hello, World!'));
            Assert::same(Hash::fileSha1($tempFile), Hash::sha1('Hello, World!'));
            Assert::same(Hash::fileSha256($tempFile), Hash::sha256('Hello, World!'));
        } finally {
            unlink($tempFile);
        }
    }

    public function testFileSpecificMethods(): void
    {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'hash_test_file_');
        file_put_contents($tempFile, 'Test data for file hashing');

        try {
            $data = 'Test data for file hashing';

            Assert::same(Hash::md2($data), Hash::fileMd2($tempFile));
            Assert::same(Hash::md4($data), Hash::fileMd4($tempFile));
            Assert::same(Hash::md5($data), Hash::fileMd5($tempFile));
            Assert::same(Hash::sha1($data), Hash::fileSha1($tempFile));
            Assert::same(Hash::sha256($data), Hash::fileSha256($tempFile));
            Assert::same(Hash::sha384($data), Hash::fileSha384($tempFile));
            Assert::same(Hash::sha512($data), Hash::fileSha512($tempFile));
        } finally {
            unlink($tempFile);
        }
    }

    public function testFileWithNonExistentFile(): void
    {
        Assert::exception(function () {
            Hash::file('/non/existent/file.txt');
        }, RuntimeException::class, 'Runtime Error: File /non/existent/file.txt does not exist');
    }

    public function testFromBinary(): void
    {
        $binary = "\x48\x65\x6C\x6C\x6F"; // "Hello" in binary
        $hex = Hash::fromBinary($binary);
        Assert::same('48656c6c6f', $hex);
    }

    public function testHash(): void
    {
        $data = 'Hello, World!';

        // Without salt: SHA-256 (64 hex chars) joined with SHA-512 (128 hex chars)
        $hash = Hash::hash($data);
        Assert::same(Hash::make($data, false, 'sha256') . Hash::make($data, false, 'sha512'), $hash);
        Assert::same(192, strlen($hash));

        // With salt: both halves are salted, result is still a single joined string
        $salt = 'fixed-salt';
        $salted = Hash::hash($data, $salt);
        Assert::type('string', $salted);
        Assert::same(
            Hash::makeWithSalt($data, $salt, false, 'sha256')['hash']
                . Hash::makeWithSalt($data, $salt, false, 'sha512')['hash'],
            $salted
        );
        Assert::same(192, strlen($salted));

        // Salting changes the output and never leaks an "Array to string" artifact
        Assert::notSame($hash, $salted);
        Assert::false(str_contains($salted, 'Array'));
    }

    public function testHmac(): void
    {
        $data = 'important message';
        $key = 'my-secret-key';

        $hmac = Hash::hmac($data, $key);
        Assert::true(strlen($hmac) === 64); // HMAC-SHA256 length

        // Test with different algorithms
        $hmacMd5 = Hash::hmac($data, $key, false, 'md5');
        Assert::true(strlen($hmacMd5) === 32); // HMAC-MD5 length
    }

    public function testHmacAlgorithms(): void
    {
        $algorithms = Hash::hmacAlgorithms();
        Assert::type('array', $algorithms);
        Assert::true(in_array('sha256', $algorithms));
        Assert::true(in_array('md5', $algorithms));
    }

    public function testHmacCheck(): void
    {
        $data = 'important message';
        $key = 'my-secret-key';
        $hmac = Hash::hmac($data, $key);

        Assert::true(Hash::hmacCheck($data, $key, $hmac));
        Assert::false(Hash::hmacCheck('different data', $key, $hmac));
        Assert::false(Hash::hmacCheck($data, 'different key', $hmac));
        Assert::false(Hash::hmacCheck($data, $key, ''));
    }

    public function testHmacCheckWithSaltEmptyHash(): void
    {
        Assert::false(Hash::hmacCheckWithSalt('data', 'key', '', 'salt'));
    }

    public function testHmacCheckWithSaltInvalid(): void
    {
        $data = 'important message';
        $key = 'secret-key';
        $salt = 'random-salt-value';

        Assert::false(Hash::hmacCheckWithSalt($data, $key, 'wrong-hash', $salt));
    }

    public function testHmacCheckWithSaltValid(): void
    {
        $data = 'important message';
        $key = 'secret-key';
        $salt = 'random-salt-value';

        $hash = Hash::hmacWithSalt($data, $key, $salt);

        Assert::true(Hash::hmacCheckWithSalt($data, $key, $hash['hash'], $salt, $hash['algo']));
    }

    public function testHmacCheckWithSaltWrongData(): void
    {
        $data = 'original data';
        $key = 'secret-key';
        $salt = 'random-salt';

        $hash = Hash::hmacWithSalt($data, $key, $salt);

        Assert::false(Hash::hmacCheckWithSalt('tampered data', $key, $hash['hash'], $salt, $hash['algo']));
    }

    public function testHmacCheckWithSaltWrongKey(): void
    {
        $data = 'important message';
        $key = 'secret-key';
        $salt = 'random-salt-value';

        $hash = Hash::hmacWithSalt($data, $key, $salt);

        Assert::false(Hash::hmacCheckWithSalt($data, 'wrong-key', $hash['hash'], $salt, $hash['algo']));
    }

    public function testHmacConsistency(): void
    {
        $data = 'test data';
        $key = 'test key';

        // Generic HMAC method should produce same results as specific methods
        Assert::same(Hash::hmac($data, $key, false, 'md5'), Hash::hmacMd5($data, $key));
        Assert::same(Hash::hmac($data, $key, false, 'sha1'), Hash::hmacSha1($data, $key));
        Assert::same(Hash::hmac($data, $key, false, 'sha256'), Hash::hmacSha256($data, $key));
        Assert::same(Hash::hmac($data, $key, false, 'sha512'), Hash::hmacSha512($data, $key));
    }

    public function testHmacFile(): void
    {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'hmac_test_');
        $key = 'secret-key';
        $content = 'Important file content for HMAC testing';

        try {
            file_put_contents($tempFile, $content);

            // Test basic HMAC file functionality
            $hmac = Hash::hmacFile($tempFile, $key);
            Assert::true(strlen($hmac) === 64); // HMAC-SHA256 length

            // Verify against manual HMAC of the same content
            $expectedHmac = Hash::hmac($content, $key);
            Assert::same($expectedHmac, $hmac);

            // Test with different algorithms
            $hmacMd5 = Hash::hmacFile($tempFile, $key, false, 'md5');
            Assert::same(32, strlen($hmacMd5)); // HMAC-MD5 length

            $expectedHmacMd5 = Hash::hmac($content, $key, false, 'md5');
            Assert::same($expectedHmacMd5, $hmacMd5);

            // Test binary output
            $binaryHmac = Hash::hmacFile($tempFile, $key, true);
            Assert::same(32, strlen($binaryHmac)); // HMAC-SHA256 binary length

            // Verify binary converts to hex correctly
            Assert::same(bin2hex($binaryHmac), $hmac);

            // Test with different keys
            $hmac2 = Hash::hmacFile($tempFile, 'different-key');
            Assert::notSame($hmac, $hmac2);

            // Test with empty file
            $emptyFile = tempnam(sys_get_temp_dir(), 'empty_test_');
            file_put_contents($emptyFile, '');

            $emptyHmac = Hash::hmacFile($emptyFile, $key);
            $expectedEmptyHmac = Hash::hmac('', $key);
            Assert::same($expectedEmptyHmac, $emptyHmac);

            unlink($emptyFile);

            // Test consistency with file HMAC verification
            $hmac = Hash::hmacFile($tempFile, $key);
            $content = file_get_contents($tempFile);
            $manualHmac = Hash::hmac($content, $key);

            Assert::same($manualHmac, $hmac);

            // Test file HMAC algorithm consistency
            $algorithms = ['md5', 'sha1', 'sha256', 'sha384', 'sha512'];
            foreach ($algorithms as $algo) {
                if (Hash::hmacSupports($algo)) {
                    $fileHmac = Hash::hmacFile($tempFile, $key, false, $algo);
                    $manualHmac = Hash::hmac($content, $key, false, $algo);
                    Assert::same($manualHmac, $fileHmac, "File HMAC should match manual HMAC for algorithm {$algo}");
                }
            }
        } finally {
            unlink($tempFile);
        }
    }

    public function testHmacFileWithInvalidAlgorithm(): void
    {
        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'hmac_algo_test_');
        file_put_contents($tempFile, 'test content');

        try {
            Assert::exception(function () use ($tempFile) {
                Hash::hmacFile($tempFile, 'key', false, 'invalid_algorithm');
            }, InvalidArgumentException::class, 'Invalid Argument: invalid_algorithm is not a valid HMAC hash algorithm');
        } finally {
            unlink($tempFile);
        }
    }

    public function testHmacFileWithNonExistentFile(): void
    {
        Assert::exception(function () {
            Hash::hmacFile('/non/existent/file.txt', 'key');
        }, RuntimeException::class, 'Runtime Error: File /non/existent/file.txt does not exist');
    }

    public function testHmacFinal(): void
    {
        $context = Hash::hmacInit('secret-key');
        Hash::hmacUpdate($context, 'Hello, ');
        Hash::hmacUpdate($context, 'World!');
        $hmac = Hash::hmacFinal($context);

        // Must match a one-shot HMAC of the same combined data
        Assert::same(Hash::hmac('Hello, World!', 'secret-key'), $hmac);

        // Binary output should be 32 bytes for SHA-256
        $contextBin = Hash::hmacInit('secret-key');
        Hash::hmacUpdate($contextBin, 'data');
        $binary = Hash::hmacFinal($contextBin, true);
        Assert::same(32, strlen($binary));
    }

    public function testHmacInit(): void
    {
        $context = Hash::hmacInit('my-key');
        Assert::type(HashContext::class, $context);

        // Different algorithms
        $contextMd5 = Hash::hmacInit('key', 'md5');
        Hash::hmacUpdate($contextMd5, 'test');
        $hmacMd5 = Hash::hmacFinal($contextMd5);
        Assert::same(Hash::hmacMd5('test', 'key'), $hmacMd5);

        // Invalid algorithm must throw
        Assert::exception(function () {
            Hash::hmacInit('key', 'invalid_algo');
        }, InvalidArgumentException::class, 'Invalid Argument: invalid_algo is not a valid HMAC hash algorithm');
    }

    public function testHmacMd2(): void
    {
        if (!in_array('md2', hash_hmac_algos())) {
            Environment::skip('MD2 HMAC not supported');
        }

        $hmac = Hash::hmacMd2('test', 'key');
        Assert::same(32, strlen($hmac)); // MD2 HMAC length

        // Binary output: 16 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacMd2('test', 'key', true);
        Assert::same(16, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacMd4(): void
    {
        if (!in_array('md4', hash_hmac_algos())) {
            Environment::skip('MD4 HMAC not supported');
        }

        $hmac = Hash::hmacMd4('test', 'key');
        Assert::same(32, strlen($hmac)); // MD4 HMAC length

        // Binary output: 16 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacMd4('test', 'key', true);
        Assert::same(16, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacMd5(): void
    {
        $hmac = Hash::hmacMd5('test', 'key');
        Assert::same(32, strlen($hmac)); // MD5 HMAC length

        // Binary output: 16 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacMd5('test', 'key', true);
        Assert::same(16, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacSha1(): void
    {
        $hmac = Hash::hmacSha1('test', 'key');
        Assert::same(40, strlen($hmac)); // SHA1 HMAC length

        // Binary output: 20 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacSha1('test', 'key', true);
        Assert::same(20, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacSha256(): void
    {
        $hmac = Hash::hmacSha256('test', 'key');
        Assert::same(64, strlen($hmac)); // SHA256 HMAC length

        // Binary output: 32 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacSha256('test', 'key', true);
        Assert::same(32, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacSha384(): void
    {
        $hmac = Hash::hmacSha384('test', 'key');
        Assert::same(96, strlen($hmac)); // SHA384 HMAC length

        // Binary output: 48 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacSha384('test', 'key', true);
        Assert::same(48, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacSha512(): void
    {
        $hmac = Hash::hmacSha512('test', 'key');
        Assert::same(128, strlen($hmac)); // SHA512 HMAC length

        // Binary output: 64 bytes that hex-encode back to the hex digest
        $binary = Hash::hmacSha512('test', 'key', true);
        Assert::same(64, strlen($binary));
        Assert::same($hmac, bin2hex($binary));
    }

    public function testHmacSupports(): void
    {
        Assert::true(Hash::hmacSupports('sha256'));
        Assert::true(Hash::hmacSupports('md5'));
        Assert::false(Hash::hmacSupports('invalid_algorithm'));
    }

    public function testHmacTimingSafe(): void
    {
        $key = 'secret-key';
        $data1 = 'message one';
        $data2 = 'message two';
        $data3 = 'message one'; // Same as data1

        // Test identical data - should return true
        Assert::true(Hash::hmacTimingSafe($data1, $data3, $key));

        // Test different data - should return false
        Assert::false(Hash::hmacTimingSafe($data1, $data2, $key));

        // Test that different keys produce different HMACs
        $hmacWithKey1 = Hash::hmac($data1, 'different-key');
        $hmacWithKey2 = Hash::hmac($data3, 'secret-key');
        Assert::false(hash_equals($hmacWithKey1, $hmacWithKey2));

        // Test with empty data
        Assert::true(Hash::hmacTimingSafe('', '', $key));
        Assert::false(Hash::hmacTimingSafe('', 'non-empty', $key));

        // Test with longer data
        $longData1 = str_repeat('A', 1000);
        $longData2 = str_repeat('A', 1000);
        $longData3 = str_repeat('B', 1000);

        Assert::true(Hash::hmacTimingSafe($longData1, $longData2, $key));
        Assert::false(Hash::hmacTimingSafe($longData1, $longData3, $key));

        // Verify timing-safe comparison is actually used internally
        // The method should behave identically to manual HMAC comparison using hash_equals
        $key = 'test-key';
        $hmac1 = Hash::hmac('test-data', $key);
        $hmac2 = Hash::hmac('test-data', $key);
        $hmac3 = Hash::hmac('different-data', $key);

        $result1 = hash_equals($hmac1, $hmac2);
        $result2 = hash_equals($hmac1, $hmac3);

        Assert::same($result1, Hash::hmacTimingSafe('test-data', 'test-data', $key));
        Assert::same($result2, Hash::hmacTimingSafe('test-data', 'different-data', $key));
    }

    public function testHmacUpdate(): void
    {
        // Single update
        $context = Hash::hmacInit('secret');
        Hash::hmacUpdate($context, 'hello');
        Assert::same(Hash::hmac('hello', 'secret'), Hash::hmacFinal($context));

        // Multiple updates concatenate data
        $context = Hash::hmacInit('secret');
        Hash::hmacUpdate($context, 'hel');
        Hash::hmacUpdate($context, 'lo');
        Assert::same(Hash::hmac('hello', 'secret'), Hash::hmacFinal($context));

        // Empty update is a no-op
        $context = Hash::hmacInit('secret');
        Hash::hmacUpdate($context, '');
        Hash::hmacUpdate($context, 'hello');
        Assert::same(Hash::hmac('hello', 'secret'), Hash::hmacFinal($context));
    }

    public function testHmacWithInvalidAlgorithm(): void
    {
        Assert::exception(function () {
            Hash::hmac('test', 'key', false, 'invalid_algorithm');
        }, InvalidArgumentException::class, 'Invalid Argument: invalid_algorithm is not a valid HMAC hash algorithm');
    }

    public function testHmacWithSalt(): void
    {
        $data = 'message';
        $key = 'secret-key';

        $result = Hash::hmacWithSalt($data, $key);
        Assert::true(isset($result['hmac']));
        Assert::true(isset($result['salt']));
        Assert::true(strlen($result['salt']) === 32); // 16 bytes = 32 hex chars

        // Verify the salted HMAC
        Assert::true(Hash::hmacCheckWithSalt($data, $key, $result['hmac'], $result['salt']));
    }

    public function testInitWithInvalidAlgorithm(): void
    {
        Assert::exception(function () {
            Hash::init('invalid_algorithm');
        }, InvalidArgumentException::class, 'Invalid Argument: invalid_algorithm is not a valid hash algorithm');
    }

    public function testLargeData(): void
    {
        // Test with larger data
        $largeData = str_repeat('A', 10000);
        $hash = Hash::sha256($largeData);

        Assert::same(64, strlen($hash));
        Assert::true(ctype_xdigit($hash));
    }

    // Generic Hash Method Tests

    public function testMake(): void
    {
        $hash = Hash::make('Hello, World!', false, 'sha256');
        Assert::same('dffd6021bb2bd5b0af676290809ec3a53191dd81c7f70a4b28688a362182986f', $hash);
    }

    public function testMakeWithInvalidAlgorithm(): void
    {
        Assert::exception(function () {
            Hash::make('test', false, 'invalid_algorithm');
        }, InvalidArgumentException::class, 'Invalid Argument: invalid_algorithm is not a valid hash algorithm');
    }

    public function testMakeWithSalt(): void
    {
        $data = 'password123';
        $result = Hash::makeWithSalt($data);

        Assert::true(isset($result['hash']));
        Assert::true(isset($result['salt']));
        Assert::true(strlen($result['salt']) === 32); // 16 bytes = 32 hex chars

        // Verify the salted hash
        Assert::true(Hash::checkWithSalt($data, $result['hash'], $result['salt']));
    }

    public function testMd2(): void
    {
        if (!in_array('md2', hash_algos())) {
            Environment::skip('MD2 not supported');
        }

        $hash = Hash::md2('Hello, World!');
        Assert::same('1c8f1e6a94aaa7145210bf90bb52871a', $hash);

        // Binary output: 16 bytes that hex-encode back to the hex digest
        $binary = Hash::md2('Hello, World!', true);
        Assert::same(16, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testMd4(): void
    {
        if (!in_array('md4', hash_algos())) {
            Environment::skip('MD4 not supported');
        }

        $hash = Hash::md4('Hello, World!');
        Assert::same('94e3cb0fa9aa7a5ee3db74b79e915989', $hash);

        // Binary output: 16 bytes that hex-encode back to the hex digest
        $binary = Hash::md4('Hello, World!', true);
        Assert::same(16, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testMd5(): void
    {
        $hash = Hash::md5('Hello, World!');
        Assert::same('65a8e27d8879283831b664bd8b7f0ad4', $hash);

        // Binary output: 16 bytes that hex-encode back to the hex digest
        $binary = Hash::md5('Hello, World!', true);
        Assert::same(16, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testObject(): void
    {
        $obj = new stdClass();
        $obj->name = 'John';
        $obj->age = 30;

        $hash = Hash::object($obj);
        Assert::true(strlen($hash) === 64); // SHA256 hash length
    }

    public function testPassword(): void
    {
        $password = 'user123';
        $hash = Hash::password($password);

        Assert::true(strlen($hash) >= 60); // Password hashes are typically 60+ chars
        Assert::true(Hash::passwordCheck($password, $hash));
        Assert::false(Hash::passwordCheck('wrongpassword', $hash));

        // BCrypt
        $bcryptHash = Hash::password($password, PasswordAlgorithm::Bcrypt);
        Assert::true(strpos($bcryptHash, '$2y$') === 0); // Bcrypt hashes start with $2y$
        Assert::true(Hash::passwordCheck($password, $bcryptHash));

        // Argon2ID
        if (in_array('argon2id', password_algos())) {
            $argonHash = Hash::password($password, PasswordAlgorithm::Argon2id);
            Assert::true(strpos($argonHash, '$argon2id$') === 0);
            Assert::true(Hash::passwordCheck($password, $argonHash));
            Assert::false(Hash::passwordCheck('wrongpassword', $argonHash));

            // Two calls must produce different hashes (distinct salts)
            $argonHash2 = Hash::password($password, PasswordAlgorithm::Argon2id);
            Assert::notSame($argonHash, $argonHash2);
        }

        // Argon2I
        if (in_array('argon2i', password_algos())) {
            $argon2iHash = Hash::password($password, PasswordAlgorithm::Argon2i);
            Assert::true(strpos($argon2iHash, '$argon2i$') === 0);
            Assert::true(Hash::passwordCheck($password, $argon2iHash));
            Assert::false(Hash::passwordCheck('wrongpassword', $argon2iHash));

            $argon2iHash2 = Hash::password($password, PasswordAlgorithm::Argon2i);
            Assert::notSame($argon2iHash, $argon2iHash2);
        }
    }

    public function testPasswordArgon2idNotSupported(): void
    {
        if (in_array('argon2id', password_algos())) {
            Environment::skip('Argon2ID is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::password('test', PasswordAlgorithm::Argon2id);
        }, RuntimeException::class, 'Runtime Error: Argon2ID is not supported by the current PHP installation');
    }

    public function testPasswordArgon2iNotSupported(): void
    {
        if (in_array('argon2i', password_algos())) {
            Environment::skip('Argon2I is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::password('test', PasswordAlgorithm::Argon2i);
        }, RuntimeException::class, 'Runtime Error: Argon2I is not supported by the current PHP installation');
    }

    public function testPasswordCheck(): void
    {
        $password = 'test123';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        Assert::true(Hash::passwordCheck($password, $hash));
        Assert::false(Hash::passwordCheck('wrong', $hash));
    }

    public function testPasswordHashOptions(): void
    {
        $password = 'testpassword';

        // BCrypt with custom cost
        $hash = Hash::password($password, PasswordAlgorithm::Bcrypt, ['cost' => 10]);
        Assert::true(Hash::passwordCheck($password, $hash));

        $info = Hash::passwordInfo($hash);
        Assert::same(10, $info['options']['cost']);

        // Argon2ID with custom options
        if (in_array('argon2id', password_algos())) {
            $argonHash = Hash::password($password, PasswordAlgorithm::Argon2id, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
            ]);

            Assert::true(Hash::passwordCheck($password, $argonHash));

            $argonInfo = Hash::passwordInfo($argonHash);
            Assert::same('argon2id', $argonInfo['algoName']);
        }

        // Argon2I with custom options
        if (in_array('argon2i', password_algos())) {
            $argon2iHash = Hash::password($password, PasswordAlgorithm::Argon2i, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
                'threads'     => PASSWORD_ARGON2_DEFAULT_THREADS,
            ]);

            Assert::true(Hash::passwordCheck($password, $argon2iHash));

            $argon2iInfo = Hash::passwordInfo($argon2iHash);
            Assert::same('argon2i', $argon2iInfo['algoName']);
        }
    }

    public function testPasswordInfo(): void
    {
        $hash = password_hash('password', PASSWORD_BCRYPT);
        $info = Hash::passwordInfo($hash);

        Assert::true(isset($info['algo']));
        Assert::true(isset($info['algoName']));
        Assert::true(isset($info['options']));
        Assert::same('2y', $info['algo']); // Bcrypt algorithm identifier
        Assert::same('bcrypt', $info['algoName']);
    }

    public function testPasswordNeedsRehash(): void
    {
        // BCrypt: lower cost → rehash needed
        $hash = Hash::password('password', PasswordAlgorithm::Bcrypt, ['cost' => 10]);
        Assert::true(Hash::passwordNeedsRehash($hash, PasswordAlgorithm::Bcrypt, ['cost' => 12]));
        Assert::false(Hash::passwordNeedsRehash($hash, PasswordAlgorithm::Bcrypt, ['cost' => 10]));

        // Argon2ID: lower memory cost → rehash needed
        if (in_array('argon2id', password_algos())) {
            $argonHash = Hash::password('password', PasswordAlgorithm::Argon2id, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            ]);

            Assert::false(Hash::passwordNeedsRehash($argonHash, PasswordAlgorithm::Argon2id, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            ]));

            Assert::true(Hash::passwordNeedsRehash($argonHash, PasswordAlgorithm::Argon2id, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST * 2,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            ]));
        }

        // Argon2I: lower memory cost → rehash needed
        if (in_array('argon2i', password_algos())) {
            $argon2iHash = Hash::password('password', PasswordAlgorithm::Argon2i, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            ]);

            Assert::false(Hash::passwordNeedsRehash($argon2iHash, PasswordAlgorithm::Argon2i, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            ]));

            Assert::true(Hash::passwordNeedsRehash($argon2iHash, PasswordAlgorithm::Argon2i, [
                'memory_cost' => PASSWORD_ARGON2_DEFAULT_MEMORY_COST * 2,
                'time_cost'   => PASSWORD_ARGON2_DEFAULT_TIME_COST,
            ]));
        }
    }

    public function testPasswordNeedsRehashArgon2idNotSupported(): void
    {
        if (in_array('argon2id', password_algos())) {
            Environment::skip('Argon2ID is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::passwordNeedsRehash('$argon2id$dummy', PasswordAlgorithm::Argon2id);
        }, RuntimeException::class, 'Runtime Error: Argon2ID is not supported by the current PHP installation');
    }

    public function testPasswordNeedsRehashArgon2iNotSupported(): void
    {
        if (in_array('argon2i', password_algos())) {
            Environment::skip('Argon2I is supported; skipping unavailability test');
        }

        Assert::exception(function () {
            Hash::passwordNeedsRehash('$argon2i$dummy', PasswordAlgorithm::Argon2i);
        }, RuntimeException::class, 'Runtime Error: Argon2I is not supported by the current PHP installation');
    }

    public function testPbkdf2(): void
    {
        $key = Hash::pbkdf2('password', 'salt', 1000, 32);
        Assert::same(32, strlen($key)); // Should return exactly 32 bytes

        // Test with auto-generated salt
        $key2 = Hash::pbkdf2('password', null, 1000, 32);
        Assert::same(32, strlen($key2));
        Assert::notSame($key, $key2); // Different salts should produce different keys

        // Test different algorithms
        $keySha1 = Hash::pbkdf2('password', 'salt', 1000, 32, 'sha1');
        $keySha256 = Hash::pbkdf2('password', 'salt', 1000, 32, 'sha256');
        Assert::notSame($keySha1, $keySha256);

        // Test different iterations
        $key1000 = Hash::pbkdf2('password', 'salt', 1000, 32);
        $key2000 = Hash::pbkdf2('password', 'salt', 2000, 32);
        Assert::notSame($key1000, $key2000);
    }

    public function testPbkdf2Algorithms(): void
    {
        $algorithms = Hash::pbkdf2Algorithms();
        Assert::true(is_array($algorithms));
        Assert::true(count($algorithms) > 0);

        // Check that it only includes expected algorithms
        $expected = ['sha1', 'sha256', 'sha384', 'sha512'];
        foreach ($algorithms as $algo) {
            Assert::true(in_array($algo, $expected), "Algorithm {$algo} should be in expected list");
        }

        // Check that all returned algorithms are actually supported for HMAC
        foreach ($algorithms as $algo) {
            Assert::true(Hash::hmacSupports($algo), "Algorithm {$algo} should be supported for HMAC");
        }

        // Verify at least sha256 is available (most common)
        Assert::true(in_array('sha256', $algorithms), 'SHA256 should be available for PBKDF2');
    }

    public function testPbkdf2ErrorCases(): void
    {
        // Test invalid iterations
        Assert::exception(function () {
            Hash::pbkdf2('password', 'salt', 0, 32);
        }, InvalidArgumentException::class, 'Invalid Argument: The amount of iterations must be positive');

        Assert::exception(function () {
            Hash::pbkdf2('password', 'salt', -1000, 32);
        }, InvalidArgumentException::class, 'Invalid Argument: The amount of iterations must be positive');

        // Test invalid length
        Assert::exception(function () {
            Hash::pbkdf2('password', 'salt', 1000, 0);
        }, InvalidArgumentException::class, 'Invalid Argument: Length must be between 1 and 100000');

        Assert::exception(function () {
            Hash::pbkdf2('password', 'salt', 1000, -10);
        }, InvalidArgumentException::class, 'Invalid Argument: Length must be between 1 and 100000');

        // Test unsupported algorithm
        Assert::exception(function () {
            Hash::pbkdf2('password', 'salt', 1000, 32, 'md5');
        }, InvalidArgumentException::class, 'Invalid Argument: Algorithm md5 is not supported for PBKDF2');

        Assert::exception(function () {
            Hash::pbkdf2('password', 'salt', 1000, 32, 'invalid_algorithm');
        }, InvalidArgumentException::class, 'Invalid Argument: Algorithm invalid_algorithm is not supported for PBKDF2');
    }

    public function testPbkdf2Supports(): void
    {
        // Test supported algorithms
        Assert::true(Hash::pbkdf2Supports('sha256'));
        Assert::true(Hash::pbkdf2Supports('sha1'));
        Assert::true(Hash::pbkdf2Supports('sha512'));

        // Test unsupported algorithms
        Assert::false(Hash::pbkdf2Supports('md5'));
        Assert::false(Hash::pbkdf2Supports('invalid_algorithm'));
        Assert::false(Hash::pbkdf2Supports(''));

        // Test case sensitivity
        Assert::false(Hash::pbkdf2Supports('SHA256')); // Should be lowercase
    }

    public function testRandom(): void
    {
        $random = Hash::random(32);
        Assert::same(64, strlen($random)); // 32 bytes = 64 hex characters
        Assert::true(ctype_xdigit($random));

        // Test binary output
        $binary = Hash::random(16, true);
        Assert::same(16, strlen($binary));

        // Test default length
        $defaultHex = Hash::random();
        Assert::same(256, strlen($defaultHex)); // 128 bytes = 256 hex characters

        // Test default binary length
        $defaultBinary = Hash::random(128, true);
        Assert::same(128, strlen($defaultBinary));

        // Verify different calls produce different values
        $another = Hash::random(32);
        Assert::notSame($random, $another);
    }

    public function testSalt(): void
    {
        $salt = Hash::salt(16);
        Assert::same(32, strlen($salt)); // 16 bytes = 32 hex chars
        Assert::true(ctype_xdigit($salt));

        // Different calls should produce different salts
        $salt2 = Hash::salt(16);
        Assert::notSame($salt, $salt2);
    }

    public function testSaltUniqueness(): void
    {
        $salts = [];
        for ($i = 0; $i < 10; $i++) {
            $salt = Hash::salt(16);
            Assert::false(in_array($salt, $salts), 'Generated salt should be unique');
            $salts[] = $salt;
        }
    }

    public function testSha1(): void
    {
        $hash = Hash::sha1('Hello, World!');
        Assert::same('0a0a9f2a6772942557ab5355d76af442f8f65e01', $hash);

        // Binary output: 20 bytes that hex-encode back to the hex digest
        $binary = Hash::sha1('Hello, World!', true);
        Assert::same(20, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testSha256(): void
    {
        $hash = Hash::sha256('Hello, World!');
        Assert::same('dffd6021bb2bd5b0af676290809ec3a53191dd81c7f70a4b28688a362182986f', $hash);

        // Binary output: 32 bytes that hex-encode back to the hex digest
        $binary = Hash::sha256('Hello, World!', true);
        Assert::same(32, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testSha384(): void
    {
        $hash = Hash::sha384('Hello, World!');
        Assert::same('5485cc9b3365b4305dfb4e8337e0a598a574f8242bf17289e0dd6c20a3cd44a089de16ab4ab308f63e44b1170eb5f515', $hash);

        // Binary output: 48 bytes that hex-encode back to the hex digest
        $binary = Hash::sha384('Hello, World!', true);
        Assert::same(48, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testSha512(): void
    {
        $hash = Hash::sha512('Hello, World!');
        Assert::same('374d794a95cdcfd8b35993185fef9ba368f160d8daf432d08ba9f1ed1e5abe6cc69291e0fa2fe0006a52570ef18c19def4e617c33ce52ef0a6e5fbe318cb0387', $hash);
        Assert::true(strlen($hash) === 128); // SHA512 produces 128 hex chars

        // Binary output: 64 bytes that hex-encode back to the hex digest
        $binary = Hash::sha512('Hello, World!', true);
        Assert::same(64, strlen($binary));
        Assert::same($hash, bin2hex($binary));
    }

    public function testStreamingHash(): void
    {
        $context = Hash::init('sha256');
        Hash::update($context, 'Hello, ');
        Hash::update($context, 'World!');
        $hash = Hash::final($context);

        Assert::same(Hash::sha256('Hello, World!'), $hash);
    }

    public function testSupports(): void
    {
        Assert::true(Hash::supports('sha256'));
        Assert::true(Hash::supports('md5'));
        Assert::false(Hash::supports('invalid_algorithm'));
    }

    public function testToken(): void
    {
        $token = Hash::token();
        Assert::same(64, strlen($token)); // 32 bytes = 64 hex chars
        Assert::true(ctype_xdigit($token));

        // Different calls should produce different tokens
        $token2 = Hash::token();
        Assert::notSame($token, $token2);
    }

    public function testTokenUniqueness(): void
    {
        $tokens = [];
        for ($i = 0; $i < 10; $i++) {
            $token = Hash::token();
            Assert::false(in_array($token, $tokens), 'Generated token should be unique');
            $tokens[] = $token;
        }
    }

    public function testUnique(): void
    {
        $unique1 = Hash::unique();
        $unique2 = Hash::unique();

        Assert::same(64, strlen($unique1)); // SHA256 hash length
        Assert::notSame($unique1, $unique2);
    }

    public function testUuid(): void
    {
        $uuid = Hash::uuid();
        Assert::true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid) === 1);

        // Different calls should produce different UUIDs
        $uuid2 = Hash::uuid();
        Assert::notSame($uuid, $uuid2);
    }

    public function testUuidFormat(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $uuid = Hash::uuid();
            Assert::match('#^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$#i', $uuid);
        }
    }
}

// Run the tests
(new HashTest())->run();
