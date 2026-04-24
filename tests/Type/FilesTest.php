<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests\Type;

use Phuture\Coherence\Files;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\RuntimeException;
use Phuture\Coherence\Type\Files as FluentFiles;

require __DIR__ . '/../bootstrap.php';

class FilesTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/coherence_type_files_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testOfReturnsFluentInstance(): void
    {
        $file = $this->tempDir . '/test.txt';
        file_put_contents($file, 'content');

        $fluent = Files::of($file);

        Assert::type(FluentFiles::class, $fluent);
        Assert::same(realpath($file), $fluent->get());
    }

    public function testOfResolvesRealPath(): void
    {
        $file = $this->tempDir . '/real.txt';
        file_put_contents($file, 'content');

        $fluent = Files::of($file);

        Assert::same(realpath($file), $fluent->path());
    }

    public function testOfThrowsForNonExistentPath(): void
    {
        Assert::exception(
            static fn () => Files::of('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testOfThrowsForDirectory(): void
    {
        $dir = $this->tempDir;
        Assert::exception(
            static fn () => Files::of($dir),
            RuntimeException::class
        );
    }

    public function testFromReturnsFluentInstance(): void
    {
        $path = $this->tempDir . '/from.txt';
        $fluent = FluentFiles::from($path);

        Assert::type(FluentFiles::class, $fluent);
        Assert::same($path, $fluent->get());
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

    public function testDeleteRemovesFile(): void
    {
        $file = $this->tempDir . '/to_delete.txt';
        file_put_contents($file, 'gone');

        Files::of($file)->delete();

        Assert::false(file_exists($file));
    }

    public function testMakeWritable(): void
    {
        $file = $this->tempDir . '/locked.txt';
        file_put_contents($file, 'content');
        chmod($file, 0444);

        Files::of($file)->makeWritable();

        Assert::true(is_writable($file));
    }

    public function testNameReturnsFileName(): void
    {
        $file = $this->tempDir . '/test.txt';
        file_put_contents($file, 'content');

        Assert::same('test.txt', Files::of($file)->name());
    }

    public function testExtensionReturnsExtension(): void
    {
        $file = $this->tempDir . '/archive.tar.gz';
        file_put_contents($file, 'content');

        Assert::same('gz', Files::of($file)->extension());
    }

    public function testPathReturnsFullPath(): void
    {
        $file = $this->tempDir . '/path_test.txt';
        file_put_contents($file, 'content');

        Assert::same(realpath($file), Files::of($file)->path());
    }

    public function testSizeReturnsBytes(): void
    {
        $file = $this->tempDir . '/sized.txt';
        file_put_contents($file, str_repeat('x', 100));

        Assert::same(100, Files::of($file)->size());
    }

    public function testLastModifiedReturnsTimestamp(): void
    {
        $file = $this->tempDir . '/modified.txt';
        file_put_contents($file, 'content');
        $expected = filemtime($file);

        Assert::same($expected, Files::of($file)->lastModified());
    }

    public function testMimeTypeReturnsType(): void
    {
        $file = $this->tempDir . '/text.txt';
        file_put_contents($file, 'plain text');

        $mimeType = Files::of($file)->mimeType();

        Assert::true(str_starts_with($mimeType, 'text/'));
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

    public function testInvokeReturnsPath(): void
    {
        $file = $this->tempDir . '/invoke.txt';
        file_put_contents($file, 'test');

        $fluent = Files::of($file);

        Assert::same(realpath($file), $fluent());
    }

    private function removeDirectory(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
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
