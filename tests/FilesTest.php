<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Files;
use Tester\{Assert, TestCase};
use Phuture\Coherence\Exception\{InvalidArgumentException, RuntimeException};

require __DIR__ . '/bootstrap.php';

class FilesTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/coherence_files_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    public function testCopyFile(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'Hello, World!');

        Files::copy($source, $destination);

        Assert::true(file_exists($destination));
        Assert::same('Hello, World!', file_get_contents($destination));
    }

    public function testCopyFileOverwrite(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'new content');
        file_put_contents($destination, 'old content');

        Files::copy($source, $destination);

        Assert::same('new content', file_get_contents($destination));
    }

    public function testCopyFileNoOverwriteThrows(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'content');
        file_put_contents($destination, 'existing');

        Assert::exception(
            static fn () => Files::copy($source, $destination, overwrite: false),
            RuntimeException::class
        );
    }

    public function testCopyDirectory(): void
    {
        $sourceDir = $this->tempDir . '/source_dir';
        $destDir = $this->tempDir . '/dest_dir';
        mkdir($sourceDir);
        file_put_contents($sourceDir . '/file1.txt', 'one');
        mkdir($sourceDir . '/sub');
        file_put_contents($sourceDir . '/sub/file2.txt', 'two');

        Files::copy($sourceDir, $destDir);

        Assert::true(is_dir($destDir));
        Assert::true(file_exists($destDir . '/file1.txt'));
        Assert::same('one', file_get_contents($destDir . '/file1.txt'));
        Assert::true(file_exists($destDir . '/sub/file2.txt'));
        Assert::same('two', file_get_contents($destDir . '/sub/file2.txt'));
    }

    public function testCopyNonExistentSourceThrows(): void
    {
        Assert::exception(
            static fn () => Files::copy('/non/existent/file.txt', '/tmp/dest.txt'),
            RuntimeException::class
        );
    }

    public function testCreateDirectory(): void
    {
        $dir = $this->tempDir . '/new/directory/structure';

        Files::createDirectory($dir);

        Assert::true(is_dir($dir));
    }

    public function testCreateDirectoryAlreadyExists(): void
    {
        Files::createDirectory($this->tempDir);

        Assert::true(is_dir($this->tempDir));
    }

    public function testDeleteFile(): void
    {
        $file = $this->tempDir . '/to_delete.txt';
        file_put_contents($file, 'content');

        Files::delete($file);

        Assert::false(file_exists($file));
    }

    public function testDeleteDirectory(): void
    {
        $dir = $this->tempDir . '/to_delete';
        mkdir($dir);
        file_put_contents($dir . '/file.txt', 'content');
        mkdir($dir . '/subdir');
        file_put_contents($dir . '/subdir/nested.txt', 'nested content');

        Files::delete($dir);

        Assert::false(file_exists($dir));
    }

    public function testDeleteNonExistentDoesNotThrow(): void
    {
        Files::delete($this->tempDir . '/non_existent');
        Assert::true(true);
    }

    public function testMakeWritable(): void
    {
        $file = $this->tempDir . '/writable.txt';
        file_put_contents($file, 'content');
        chmod($file, 0444);

        Files::makeWritable($file);

        Assert::true(is_writable($file));
    }

    public function testMakeWritableDirectory(): void
    {
        $dir = $this->tempDir . '/writable_dir';
        mkdir($dir, 0777);
        file_put_contents($dir . '/file.txt', 'content');
        chmod($dir . '/file.txt', 0444);
        chmod($dir, 0555);

        Files::makeWritable($dir, 0777, 0666);

        Assert::true(is_writable($dir));
        Assert::true(is_writable($dir . '/file.txt'));
    }

    public function testMakeWritableNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::makeWritable('/non/existent/path'),
            RuntimeException::class
        );
    }

    public function testMove(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'move me');

        Files::move($source, $destination);

        Assert::false(file_exists($source));
        Assert::true(file_exists($destination));
        Assert::same('move me', file_get_contents($destination));
    }

    public function testMoveNoOverwriteThrows(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'content');
        file_put_contents($destination, 'existing');

        Assert::exception(
            static fn () => Files::move($source, $destination, overwrite: false),
            RuntimeException::class
        );
    }

    public function testMoveNonExistentSourceThrows(): void
    {
        Assert::exception(
            static fn () => Files::move('/non/existent/file.txt', '/tmp/dest.txt'),
            RuntimeException::class
        );
    }

    public function testRename(): void
    {
        $file = $this->tempDir . '/old.txt';
        file_put_contents($file, 'content');

        Files::rename($file, 'new.txt');

        Assert::false(file_exists($file));
        Assert::true(file_exists($this->tempDir . '/new.txt'));
        Assert::same('content', file_get_contents($this->tempDir . '/new.txt'));
    }

    public function testRenameDirectory(): void
    {
        $dir = $this->tempDir . '/old_dir';
        mkdir($dir);
        file_put_contents($dir . '/file.txt', 'data');

        Files::rename($dir, 'new_dir');

        Assert::false(file_exists($dir));
        Assert::true(is_dir($this->tempDir . '/new_dir'));
        Assert::true(file_exists($this->tempDir . '/new_dir/file.txt'));
    }

    public function testRenameNoOverwriteThrows(): void
    {
        $file = $this->tempDir . '/source.txt';
        $existing = $this->tempDir . '/target.txt';
        file_put_contents($file, 'content');
        file_put_contents($existing, 'existing');

        Assert::exception(
            static fn () => Files::rename($file, 'target.txt', overwrite: false),
            RuntimeException::class
        );
    }

    public function testRenameNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::rename('/non/existent/file.txt', 'new.txt'),
            RuntimeException::class
        );
    }

    public function testRenameEmptyNameThrows(): void
    {
        $file = $this->tempDir . '/file.txt';
        file_put_contents($file, 'content');

        Assert::exception(
            static fn () => Files::rename($file, ''),
            RuntimeException::class
        );
    }

    public function testRead(): void
    {
        $file = $this->tempDir . '/read.txt';
        file_put_contents($file, 'file content');

        Assert::same('file content', Files::read($file));
    }

    public function testReadNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::read('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testReadLines(): void
    {
        $file = $this->tempDir . '/lines.txt';
        file_put_contents($file, "line1\nline2\nline3");

        $lines = [];
        foreach (Files::readLines($file) as $lineNumber => $line) {
            $lines[$lineNumber] = $line;
        }

        Assert::same([0 => 'line1', 1 => 'line2', 2 => 'line3'], $lines);
    }

    public function testReadLinesWithNewlines(): void
    {
        $file = $this->tempDir . '/lines_crlf.txt';
        file_put_contents($file, "line1\r\nline2\r\nline3");

        $lines = [];
        foreach (Files::readLines($file, stripNewLines: false) as $line) {
            $lines[] = $line;
        }

        Assert::same("line1\r\n", $lines[0]);
        Assert::same("line2\r\n", $lines[1]);
        Assert::same('line3', $lines[2]);
    }

    public function testReadLinesNonExistentThrows(): void
    {
        Assert::exception(function () {
            iterator_to_array(Files::readLines('/non/existent/file.txt'));
        }, RuntimeException::class);
    }

    public function testWrite(): void
    {
        $file = $this->tempDir . '/write.txt';

        Files::write($file, 'written content');

        Assert::same('written content', file_get_contents($file));
    }

    public function testWriteCreatesParentDirectories(): void
    {
        $file = $this->tempDir . '/new/sub/dir/write.txt';

        Files::write($file, 'deep content');

        Assert::same('deep content', file_get_contents($file));
    }

    public function testExtension(): void
    {
        Assert::same('txt', Files::extension('/path/to/file.txt'));
        Assert::same('gz', Files::extension('/path/to/archive.tar.gz'));
        Assert::same('', Files::extension('/path/to/README'));
        Assert::same('php', Files::extension('script.php'));
    }

    public function testIsAbsolute(): void
    {
        Assert::true(Files::isAbsolute('/usr/local/bin'));
        Assert::true(Files::isAbsolute('/'));
        Assert::false(Files::isAbsolute('relative/path'));
        Assert::false(Files::isAbsolute('./relative'));
        Assert::true(Files::isAbsolute('C:/Windows'));
        Assert::true(Files::isAbsolute('D:\\data'));
    }

    public function testJoinPaths(): void
    {
        Assert::same('a/b/file.txt', Files::joinPaths('a', 'b', 'file.txt'));
        Assert::same('/a/b/', Files::joinPaths('/a/', '/b/'));
        Assert::same('/b', Files::joinPaths('/a/', '/../b'));
        Assert::same('file.txt', Files::joinPaths('file.txt'));
    }

    public function testNormalizePath(): void
    {
        $ds = DIRECTORY_SEPARATOR;

        Assert::same($ds . 'file', Files::normalizePath('/file/.'));
        Assert::same($ds, Files::normalizePath('\\file\\..'));
        Assert::same($ds . '..', Files::normalizePath('/file/../..'));
        Assert::same('..' . $ds . 'bar', Files::normalizePath('file/../../bar'));
        Assert::same('', Files::normalizePath(''));
        Assert::same($ds . 'a' . $ds . 'b' . $ds, Files::normalizePath('/a//b/'));
    }

    public function testUnixSlashes(): void
    {
        Assert::same('path/to/file.txt', Files::unixSlashes('path\\to\\file.txt'));
        Assert::same('path/to/file.txt', Files::unixSlashes('path/to/file.txt'));
    }

    public function testPlatformSlashes(): void
    {
        $expected = 'path' . DIRECTORY_SEPARATOR . 'to' . DIRECTORY_SEPARATOR . 'file.txt';
        Assert::same($expected, Files::platformSlashes('path/to/file.txt'));
        Assert::same($expected, Files::platformSlashes('path\\to\\file.txt'));
    }

    public function testFindFiles(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');
        file_put_contents($this->tempDir . '/file3.txt', 'c');

        $results = Files::findFiles('*.txt', $this->tempDir);

        Assert::count(2, $results);
        Assert::true(str_ends_with($results[0], 'file1.txt'));
        Assert::true(str_ends_with($results[1], 'file3.txt'));
    }

    public function testFindFilesRecursive(): void
    {
        mkdir($this->tempDir . '/sub');
        file_put_contents($this->tempDir . '/root.txt', 'a');
        file_put_contents($this->tempDir . '/sub/nested.txt', 'b');
        file_put_contents($this->tempDir . '/sub/other.php', 'c');

        $results = Files::findFiles('*.txt', $this->tempDir, recursive: true);

        Assert::count(2, $results);
    }

    public function testFindFilesWithMultipleMasks(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');
        file_put_contents($this->tempDir . '/file3.md', 'c');

        $results = Files::findFiles(['*.txt', '*.php'], $this->tempDir);

        Assert::count(2, $results);
    }

    public function testFindDirectories(): void
    {
        mkdir($this->tempDir . '/dir1');
        mkdir($this->tempDir . '/dir2');
        file_put_contents($this->tempDir . '/file.txt', 'a');

        $results = Files::findDirectories('*', $this->tempDir);

        Assert::count(2, $results);
    }

    public function testFind(): void
    {
        mkdir($this->tempDir . '/dir1');
        file_put_contents($this->tempDir . '/file.txt', 'a');

        $results = Files::find('*', $this->tempDir);

        Assert::count(2, $results);
    }

    public function testFindNonExistentDirectoryThrows(): void
    {
        Assert::exception(
            static fn () => Files::findFiles('*', '/non/existent/directory'),
            RuntimeException::class
        );
    }

    public function testName(): void
    {
        Assert::same('file.txt', Files::name('/path/to/file.txt'));
        Assert::same('file', Files::name('/path/to/file.txt', '.txt'));
        Assert::same('directory', Files::name('/path/to/directory/'));
    }

    public function testDirectory(): void
    {
        Assert::same('/path/to', Files::directory('/path/to/file.txt'));
        Assert::same('/path', Files::directory('/path/to/file.txt', 2));
    }

    public function testExists(): void
    {
        $file = $this->tempDir . '/exists.txt';
        file_put_contents($file, 'content');

        Assert::true(Files::exists($file));
        Assert::false(Files::exists($this->tempDir . '/non_existent.txt'));
        Assert::true(Files::exists($this->tempDir));
    }

    public function testIsEmpty(): void
    {
        $emptyDir = $this->tempDir . '/empty';
        mkdir($emptyDir);

        Assert::true(Files::isEmpty($emptyDir));

        file_put_contents($emptyDir . '/file.txt', 'content');
        Assert::false(Files::isEmpty($emptyDir));
    }

    public function testIsEmptyNonDirectoryThrows(): void
    {
        $file = $this->tempDir . '/not_a_dir.txt';
        file_put_contents($file, 'content');

        Assert::exception(
            static fn () => Files::isEmpty($file),
            InvalidArgumentException::class
        );
    }

    public function testIsFile(): void
    {
        $file = $this->tempDir . '/file.txt';
        file_put_contents($file, 'content');

        Assert::true(Files::isFile($file));
        Assert::false(Files::isFile($this->tempDir));
        Assert::false(Files::isFile('/non/existent/file.txt'));
    }

    public function testIsDirectory(): void
    {
        Assert::true(Files::isDirectory($this->tempDir));
        Assert::false(Files::isDirectory($this->tempDir . '/non_existent.txt'));
        Assert::false(Files::isDirectory('/non/existent/directory'));
    }

    public function testLastModified(): void
    {
        $file = $this->tempDir . '/modified.txt';
        file_put_contents($file, 'content');
        $expectedTime = filemtime($file);

        $result = Files::lastModified($file);

        Assert::same($expectedTime, $result);
    }

    public function testLastModifiedNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::lastModified('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testSize(): void
    {
        $file = $this->tempDir . '/sized.txt';
        $content = str_repeat('x', 1024);
        file_put_contents($file, $content);

        Assert::same(1024, Files::size($file));
    }

    public function testSizeNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::size('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testSizeDirectoryThrows(): void
    {
        $dir = $this->tempDir;
        Assert::exception(
            static fn () => Files::size($dir),
            RuntimeException::class
        );
    }

    public function testMimeType(): void
    {
        $file = $this->tempDir . '/text.txt';
        file_put_contents($file, 'plain text content');

        $mimeType = Files::mimeType($file);

        Assert::true(str_starts_with($mimeType, 'text/'));
    }

    public function testMimeTypePhpFile(): void
    {
        $file = $this->tempDir . '/script.php';
        file_put_contents($file, '<?php echo "hello";');

        $mimeType = Files::mimeType($file);

        Assert::true(str_contains($mimeType, 'php') || str_contains($mimeType, 'text'));
    }

    public function testMimeTypeNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::mimeType('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testUploadSingleFile(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_');
        file_put_contents($tmpFile, 'uploaded content');

        $files = [
            'avatar' => [
                'name' => 'photo.jpg',
                'type' => 'image/jpeg',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_OK,
                'size' => 16,
            ],
        ];

        $destination = $this->tempDir . '/uploads';
        $result = Files::upload('avatar', $destination, $files);

        Assert::notSame(false, $result);
        Assert::same('photo.jpg', $result['name']);
        Assert::true(str_ends_with($result['path'], 'photo.jpg'));
        Assert::same(16, $result['size']);
        Assert::same('image/jpeg', $result['type']);
        Assert::same('jpg', $result['extension']);
        Assert::true(file_exists($result['path']));
        Assert::same('uploaded content', file_get_contents($result['path']));
    }

    public function testUploadMultipleFiles(): void
    {
        $tmpFile1 = tempnam(sys_get_temp_dir(), 'upload1_');
        $tmpFile2 = tempnam(sys_get_temp_dir(), 'upload2_');
        file_put_contents($tmpFile1, 'file one');
        file_put_contents($tmpFile2, 'file two');

        $files = [
            'files' => [
                'name' => ['document1.pdf', 'document2.pdf'],
                'type' => ['application/pdf', 'application/pdf'],
                'tmp_name' => [$tmpFile1, $tmpFile2],
                'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
                'size' => [8, 8],
            ],
        ];

        $destination = $this->tempDir . '/uploads';
        $results = Files::upload('files', $destination, $files);

        Assert::count(2, $results);
        Assert::same('document1.pdf', $results[0]['name']);
        Assert::same('pdf', $results[0]['extension']);
        Assert::same('document2.pdf', $results[1]['name']);
        Assert::same('pdf', $results[1]['extension']);
    }

    public function testUploadMissingKeyReturnsFalse(): void
    {
        $result = Files::upload('nonexistent', $this->tempDir, []);

        Assert::false($result);
    }

    public function testUploadErrorReturnsFalse(): void
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_err_');
        file_put_contents($tmpFile, 'content');

        $files = [
            'file' => [
                'name' => 'error.txt',
                'type' => 'text/plain',
                'tmp_name' => $tmpFile,
                'error' => UPLOAD_ERR_PARTIAL,
                'size' => 7,
            ],
        ];

        $result = Files::upload('file', $this->tempDir, $files);

        Assert::false($result);
    }

    public function testListing(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');
        mkdir($this->tempDir . '/subdir');

        $results = Files::listing($this->tempDir);

        Assert::count(3, $results);
    }

    public function testListingWithGlobFilter(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');
        file_put_contents($this->tempDir . '/file3.txt', 'c');

        $results = Files::listing($this->tempDir, '*.txt');

        Assert::count(2, $results);
    }

    public function testListingWithCallbackFilter(): void
    {
        file_put_contents($this->tempDir . '/small.txt', 'a');
        file_put_contents($this->tempDir . '/large.txt', str_repeat('x', 1024));

        $results = Files::listing($this->tempDir, fn ($path) => is_file($path) && filesize($path) > 100);

        Assert::count(1, $results);
        Assert::true(str_ends_with($results[0], 'large.txt'));
    }

    public function testListingNonDirectoryThrows(): void
    {
        $file = $this->tempDir . '/not_dir.txt';
        file_put_contents($file, 'content');

        Assert::exception(
            static fn () => Files::listing($file),
            InvalidArgumentException::class
        );
    }

    public function testListingEmptyDirectory(): void
    {
        $emptyDir = $this->tempDir . '/empty';
        mkdir($emptyDir);

        $results = Files::listing($emptyDir);

        Assert::count(0, $results);
    }

    public function testWriteAndReadRoundTrip(): void
    {
        $file = $this->tempDir . '/roundtrip.txt';
        $content = 'Hello, World! Special chars: àéïôù ñ 你好';

        Files::write($file, $content);

        Assert::same($content, Files::read($file));
    }

    public function testCopyPreservesContent(): void
    {
        $source = $this->tempDir . '/original.txt';
        $destination = $this->tempDir . '/copy.txt';
        $content = 'copy test content with unicode: ñoño';

        file_put_contents($source, $content);
        Files::copy($source, $destination);

        Assert::same($content, Files::read($destination));
    }

    public function testMoveRemovesSource(): void
    {
        $source = $this->tempDir . '/before_move.txt';
        $destination = $this->tempDir . '/after_move.txt';
        file_put_contents($source, 'moving content');

        Files::move($source, $destination);

        Assert::false(file_exists($source));
        Assert::true(file_exists($destination));
    }

    public function testCreateDirectoryWithMode(): void
    {
        $dir = $this->tempDir . '/mode_test';
        Files::createDirectory($dir, 0755);

        Assert::true(is_dir($dir));
    }

    public function testFindFilesNoMatch(): void
    {
        file_put_contents($this->tempDir . '/file.txt', 'a');

        $results = Files::findFiles('*.php', $this->tempDir);

        Assert::count(0, $results);
    }

    public function testFindFilesDefaultMask(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');

        $results = Files::findFiles(directory: $this->tempDir);

        Assert::count(2, $results);
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
