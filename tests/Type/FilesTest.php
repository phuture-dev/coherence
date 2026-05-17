<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use Phuture\Coherence\Files;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Tester\{Assert, Environment, TestCase};
use Phuture\Coherence\Enum\CompressionFormat;
use Phuture\Coherence\Type\Files as FluentFiles;
use Phuture\Coherence\Exception\RuntimeException;

require __DIR__ . '/../bootstrap.php';

class FilesTest extends TestCase
{
    private string $tempDir;

    public function testChgrpReturnsSelf(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            Environment::skip('chgrp is not available on Windows');
        }

        $file = $this->tempDir . '/chgrp_test.txt';
        file_put_contents($file, 'test');
        $currentGroup = posix_getgrgid(filegroup($file))['name'] ?? '';

        if ($currentGroup === '') {
            Environment::skip('Cannot determine current group');
        }

        $result = Files::of($file)->chgrp($currentGroup);

        Assert::type(FluentFiles::class, $result);
    }

    public function testChmodChangesPermissions(): void
    {
        $file = $this->tempDir . '/chmod_test.txt';
        file_put_contents($file, 'test');

        $result = Files::of($file)->chmod(0644);

        Assert::type(FluentFiles::class, $result);
        clearstatcache(true, $file);
        Assert::same(0644, fileperms($file) & 0777);
    }

    public function testChownReturnsSelf(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            Environment::skip('chown is not available on Windows');
        }

        $file = $this->tempDir . '/chown_test.txt';
        file_put_contents($file, 'test');
        $currentOwner = posix_getpwuid(fileowner($file))['name'] ?? '';

        if ($currentOwner === '') {
            Environment::skip('Cannot determine current owner');
        }

        $result = Files::of($file)->chown($currentOwner);

        Assert::type(FluentFiles::class, $result);
    }

    public function testCompressChainingPathIsArchive(): void
    {
        $file = $this->tempDir . '/chain.txt';
        file_put_contents($file, 'chaining test');

        $archive = $this->tempDir . '/chain.zip';
        $path = Files::of($file)
            ->compress($archive)
            ->path();

        Assert::same($archive, $path);
    }

    public function testCompressReturnsSelfAndSwitchesPath(): void
    {
        $file = $this->tempDir . '/data.txt';
        file_put_contents($file, 'hello fluent compress');

        $archive = $this->tempDir . '/data.zip';
        $result = Files::of($file)->compress($archive);

        Assert::type(FluentFiles::class, $result);
        Assert::true(file_exists($archive));
        Assert::same($archive, $result->path());
    }

    public function testCompressWithExplicitFormat(): void
    {
        $file = $this->tempDir . '/data.txt';
        file_put_contents($file, 'hello tar');

        $archive = $this->tempDir . '/data.tar';
        Files::of($file)->compress($archive, CompressionFormat::Tar);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
    }

    public function testCopyKeepsOriginalPath(): void
    {
        $source = $this->tempDir . '/original.txt';
        $destination = $this->tempDir . '/copy.txt';
        file_put_contents($source, 'data');

        $path = Files::of($source)
            ->copy($destination)
            ->get();

        Assert::same(realpath($source), $path);
        Assert::true(file_exists($destination));
        Assert::true(file_exists($source));
    }

    public function testCopyToIntoDirectoryTracksResolvedPath(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destDir = $this->tempDir . '/target_dir';
        file_put_contents($source, 'copying');
        mkdir($destDir);

        $path = Files::of($source)
            ->copyTo($destDir)
            ->get();

        Assert::same($destDir . '/source.txt', $path);
        Assert::true(file_exists($source));
        Assert::true(file_exists($destDir . '/source.txt'));
    }

    public function testCopyToSwitchesPath(): void
    {
        $source = $this->tempDir . '/original.txt';
        $destination = $this->tempDir . '/copy.txt';
        file_put_contents($source, 'data');

        $path = Files::of($source)
            ->copyTo($destination)
            ->get();

        Assert::same($destination, $path);
        Assert::true(file_exists($destination));
        Assert::true(file_exists($source));
    }

    public function testCopyToWriteReadWorkflow(): void
    {
        $original = $this->tempDir . '/source.txt';
        $copy = $this->tempDir . '/destination.txt';
        file_put_contents($original, 'old');

        $result = Files::of($original)
            ->write('original data')
            ->copyTo($copy)
            ->write('modified copy')
            ->read();

        Assert::same('modified copy', $result);
        Assert::same('original data', file_get_contents($original));
    }

    public function testDecompressGzipFluent(): void
    {
        $srcDir = $this->tempDir . '/gz_src';
        mkdir($srcDir);
        file_put_contents($srcDir . '/msg.txt', 'gz fluent round-trip');

        $gz = $this->tempDir . '/gz_src.tar.gz';
        Files::compress($srcDir, $gz, CompressionFormat::Gzip);

        $outDir = $this->tempDir . '/gz_out';
        Files::of($gz)->decompress($outDir, CompressionFormat::Gzip);

        Assert::true(is_dir($outDir));
        Assert::same('gz fluent round-trip', file_get_contents($outDir . '/msg.txt'));
    }

    public function testDecompressReturnsSelf(): void
    {
        $source = $this->tempDir . '/src.txt';
        file_put_contents($source, 'decompress me');

        $archive = $this->tempDir . '/src.zip';
        Files::compress($source, $archive);

        $outDir = $this->tempDir . '/out';
        $result = Files::of($archive)->decompress($outDir);

        Assert::type(FluentFiles::class, $result);
        Assert::true(is_dir($outDir));
        Assert::same('decompress me', file_get_contents($outDir . '/src.txt'));
    }

    public function testDeleteClearsInternalData(): void
    {
        $file = $this->tempDir . '/to_delete.txt';
        file_put_contents($file, 'gone');

        $fluent = Files::of($file);
        $fluent->delete();

        Assert::same('', $fluent->get());
        Assert::false(file_exists($file));
    }

    public function testDeleteRemovesFile(): void
    {
        $file = $this->tempDir . '/to_delete.txt';
        file_put_contents($file, 'gone');

        Files::of($file)->delete();

        Assert::false(file_exists($file));
    }

    public function testExtensionReturnsExtension(): void
    {
        $file = $this->tempDir . '/archive.tar.gz';
        file_put_contents($file, 'content');

        Assert::same('gz', Files::of($file)->extension());
    }

    public function testFromReturnsFluentInstance(): void
    {
        $path = $this->tempDir . '/from.txt';
        $fluent = FluentFiles::from($path);

        Assert::type(FluentFiles::class, $fluent);
        Assert::same($path, $fluent->get());
    }

    public function testFullChainingWorkflow(): void
    {
        $file = $this->tempDir . '/draft.txt';
        file_put_contents($file, 'initial');

        $path = Files::of($file)
            ->write('updated content')
            ->rename('final.txt')
            ->get();

        $content = Files::of($path)->read();

        Assert::same($this->tempDir . '/final.txt', $path);
        Assert::same('updated content', $content);
    }

    public function testInvokeReturnsPath(): void
    {
        $file = $this->tempDir . '/invoke.txt';
        file_put_contents($file, 'test');

        $fluent = Files::of($file);

        Assert::same(realpath($file), $fluent());
    }

    public function testLastModifiedReturnsTimestamp(): void
    {
        $file = $this->tempDir . '/modified.txt';
        file_put_contents($file, 'content');
        $expected = filemtime($file);

        Assert::same($expected, Files::of($file)->lastModified());
    }

    public function testMakeWritable(): void
    {
        $file = $this->tempDir . '/locked.txt';
        file_put_contents($file, 'content');
        chmod($file, 0444);

        Files::of($file)->makeWritable();

        Assert::true(is_writable($file));
    }

    public function testMimeTypeReturnsType(): void
    {
        $file = $this->tempDir . '/text.txt';
        file_put_contents($file, 'plain text');

        $mimeType = Files::of($file)->mimeType();

        Assert::true(str_starts_with($mimeType, 'text/'));
    }

    public function testMoveIntoDirectoryTracksResolvedPath(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destDir = $this->tempDir . '/target_dir';
        file_put_contents($source, 'moving');
        mkdir($destDir);

        $path = Files::of($source)
            ->move($destDir)
            ->get();

        Assert::same($destDir . '/source.txt', $path);
        Assert::false(file_exists($source));
        Assert::true(file_exists($destDir . '/source.txt'));
    }

    public function testMoveSwitchesPath(): void
    {
        $source = $this->tempDir . '/before.txt';
        $destination = $this->tempDir . '/after.txt';
        file_put_contents($source, 'moving');

        $path = Files::of($source)
            ->move($destination)
            ->get();

        Assert::same($destination, $path);
        Assert::false(file_exists($source));
        Assert::true(file_exists($destination));
    }

    public function testNameReturnsFileName(): void
    {
        $file = $this->tempDir . '/test.txt';
        file_put_contents($file, 'content');

        Assert::same('test.txt', Files::of($file)->name());
    }

    public function testOfResolvesRealPath(): void
    {
        $file = $this->tempDir . '/real.txt';
        file_put_contents($file, 'content');

        $fluent = Files::of($file);

        Assert::same(realpath($file), $fluent->path());
    }

    public function testOfReturnsFluentInstance(): void
    {
        $file = $this->tempDir . '/test.txt';
        file_put_contents($file, 'content');

        $fluent = Files::of($file);

        Assert::type(FluentFiles::class, $fluent);
        Assert::same(realpath($file), $fluent->get());
    }

    public function testOfThrowsForDirectory(): void
    {
        $dir = $this->tempDir;
        Assert::exception(
            static fn () => Files::of($dir),
            RuntimeException::class
        );
    }

    public function testOfThrowsForNonExistentPath(): void
    {
        Assert::exception(
            static fn () => Files::of('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testPathReturnsFullPath(): void
    {
        $file = $this->tempDir . '/path_test.txt';
        file_put_contents($file, 'content');

        Assert::same(realpath($file), Files::of($file)->path());
    }

    public function testPrependAddsContentToStart(): void
    {
        $file = $this->tempDir . '/prepend_test.txt';
        file_put_contents($file, 'world');

        $content = Files::of($file)
            ->prepend('hello ')
            ->read();

        Assert::same('hello world', $content);
    }

    public function testPrependToEmptyFile(): void
    {
        $file = $this->tempDir . '/prepend_empty.txt';
        file_put_contents($file, '');

        $content = Files::of($file)
            ->prepend('first')
            ->read();

        Assert::same('first', $content);
    }

    public function testRenameUpdatesPath(): void
    {
        $file = $this->tempDir . '/old.txt';
        file_put_contents($file, 'content');

        $path = Files::of($file)
            ->rename('new.txt')
            ->get();

        Assert::same($this->tempDir . '/new.txt', $path);
        Assert::false(file_exists($file));
        Assert::true(file_exists($this->tempDir . '/new.txt'));
    }

    public function testReplaceInFileWithArrays(): void
    {
        $file = $this->tempDir . '/replace_array_test.txt';
        file_put_contents($file, 'foo bar baz');

        $content = Files::of($file)
            ->replaceInFile(['foo', 'bar'], ['one', 'two'])
            ->read();

        Assert::same('one two baz', $content);
    }

    public function testReplaceInFileWithString(): void
    {
        $file = $this->tempDir . '/replace_test.txt';
        file_put_contents($file, 'Hello world');

        $content = Files::of($file)
            ->replaceInFile('world', 'universe')
            ->read();

        Assert::same('Hello universe', $content);
    }

    public function testSizeReturnsBytes(): void
    {
        $file = $this->tempDir . '/sized.txt';
        file_put_contents($file, str_repeat('x', 100));

        Assert::same(100, Files::of($file)->size());
    }

    public function testWriteAndRead(): void
    {
        $file = $this->tempDir . '/write.txt';
        file_put_contents($file, 'initial');

        $content = Files::of($file)
            ->write('Hello, fluent!')
            ->read();

        Assert::same('Hello, fluent!', $content);
    }

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/coherence_type_files_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        $this->tempDir = (string) realpath($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }
}

(new FilesTest())->run();
