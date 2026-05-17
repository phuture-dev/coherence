<?php

declare(strict_types=1);

namespace Phuture\Coherence\Tests;

use Phuture\Coherence\Files;
use RecursiveIteratorIterator;
use Tester\{Assert, TestCase};
use RecursiveDirectoryIterator;
use Phuture\Coherence\Enum\CompressionFormat;
use Phuture\Coherence\Exception\{InvalidArgumentException, RuntimeException};

require __DIR__ . '/bootstrap.php';

class FilesTest extends TestCase
{
    private string $tempDir;

    public function testAppendCreatesFile(): void
    {
        $file = $this->tempDir . '/append_new.txt';

        Files::append($file, 'first line');

        Assert::same('first line', file_get_contents($file));
    }

    public function testAppendToExistingFile(): void
    {
        $file = $this->tempDir . '/append.txt';
        file_put_contents($file, 'first');

        Files::append($file, ' second');

        Assert::same('first second', file_get_contents($file));
    }

    public function testChgrpNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::chgrp('/non/existent/file.txt', 'root'),
            RuntimeException::class
        );
    }

    public function testChmod(): void
    {
        $file = $this->tempDir . '/chmod.txt';
        file_put_contents($file, 'content');

        Files::chmod($file, 0755);

        clearstatcache(true, $file);
        Assert::same(0755, fileperms($file) & 0777);
    }

    public function testChmodNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::chmod('/non/existent/file.txt', 0755),
            RuntimeException::class
        );
    }

    public function testChownNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::chown('/non/existent/file.txt', 'root'),
            RuntimeException::class
        );
    }

    public function testCompressGzipDirectory(): void
    {
        $srcDir = $this->tempDir . '/gzipdir';
        mkdir($srcDir);
        file_put_contents($srcDir . '/note.txt', 'gzip dir content');

        $archive = $this->tempDir . '/gzipdir.tar.gz';
        Files::compress($srcDir, $archive, CompressionFormat::Gzip);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
    }

    public function testCompressGzipFile(): void
    {
        $source = $this->tempDir . '/data.txt';
        file_put_contents($source, 'hello gzip');

        $archive = $this->tempDir . '/data.tar.gz';
        Files::compress($source, $archive, CompressionFormat::Gzip);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
    }

    public function testCompressTarDirectory(): void
    {
        $srcDir = $this->tempDir . '/tardir';
        mkdir($srcDir);
        file_put_contents($srcDir . '/c.txt', 'gamma');

        $archive = $this->tempDir . '/dir.tar';
        Files::compress($srcDir, $archive, CompressionFormat::Tar);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
    }

    public function testCompressTarFile(): void
    {
        $source = $this->tempDir . '/note.txt';
        file_put_contents($source, 'hello tar');

        $archive = $this->tempDir . '/out.tar';
        Files::compress($source, $archive, CompressionFormat::Tar);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
    }

    public function testCompressThrowsOnMissingSource(): void
    {
        Assert::exception(
            fn () => Files::compress($this->tempDir . '/nonexistent.txt', $this->tempDir . '/out.zip', CompressionFormat::Zip),
            InvalidArgumentException::class
        );
    }

    public function testCompressZipDirectory(): void
    {
        $srcDir = $this->tempDir . '/zipdir';
        mkdir($srcDir);
        file_put_contents($srcDir . '/a.txt', 'alpha');
        file_put_contents($srcDir . '/b.txt', 'beta');

        $archive = $this->tempDir . '/dir.zip';
        Files::compress($srcDir, $archive, CompressionFormat::Zip);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
    }

    public function testCompressZipFile(): void
    {
        $source = $this->tempDir . '/hello.txt';
        file_put_contents($source, 'hello zip');

        $archive = $this->tempDir . '/out.zip';
        Files::compress($source, $archive, CompressionFormat::Zip);

        Assert::true(file_exists($archive));
        Assert::true(filesize($archive) > 0);
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

    public function testCopyDirectoryWithTrailingSlash(): void
    {
        $sourceDir = $this->tempDir . '/source_dir/';
        $destDir = $this->tempDir . '/dest_dir';
        mkdir($sourceDir);
        file_put_contents($sourceDir . 'file1.txt', 'one');
        mkdir($sourceDir . 'sub');
        file_put_contents($sourceDir . 'sub/file2.txt', 'two');

        Files::copy($sourceDir, $destDir);

        Assert::true(file_exists($destDir . '/file1.txt'));
        Assert::same('one', file_get_contents($destDir . '/file1.txt'));
        Assert::true(file_exists($destDir . '/sub/file2.txt'));
        Assert::same('two', file_get_contents($destDir . '/sub/file2.txt'));
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

    public function testCopyFileIntoExistingDirectory(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destDir = $this->tempDir . '/target_dir';
        file_put_contents($source, 'file content');
        mkdir($destDir);

        Files::copy($source, $destDir);

        Assert::true(file_exists($destDir . '/source.txt'));
        Assert::same('file content', file_get_contents($destDir . '/source.txt'));
        Assert::true(file_exists($source));
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

    public function testCopyFileOverwrite(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destination = $this->tempDir . '/destination.txt';
        file_put_contents($source, 'new content');
        file_put_contents($destination, 'old content');

        Files::copy($source, $destination);

        Assert::same('new content', file_get_contents($destination));
    }

    public function testCopyNonExistentSourceThrows(): void
    {
        Assert::exception(
            static fn () => Files::copy('/non/existent/file.txt', '/tmp/dest.txt'),
            RuntimeException::class
        );
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

    public function testCreateAlreadyExisting(): void
    {
        $file = $this->tempDir . '/existing.txt';
        file_put_contents($file, 'data');

        Files::create($file);

        Assert::same('data', file_get_contents($file));
    }

    public function testCreateDirectory(): void
    {
        $dir = $this->tempDir . '/new/directory/structure/';

        Files::create($dir);

        Assert::true(is_dir(rtrim($dir, '/')));
    }

    public function testCreateDirectoryAlreadyExists(): void
    {
        Files::create($this->tempDir . '/');

        Assert::true(is_dir($this->tempDir));
    }

    public function testCreateDirectoryWithMode(): void
    {
        $dir = $this->tempDir . '/mode_test/';
        Files::create($dir, 0755);

        Assert::true(is_dir(rtrim($dir, '/')));
    }

    public function testCreateFile(): void
    {
        $file = $this->tempDir . '/created.txt';

        Files::create($file);

        Assert::true(file_exists($file));
        Assert::same('', file_get_contents($file));
    }

    public function testDecompressGzip(): void
    {
        $srcDir = $this->tempDir . '/gzip_src';
        mkdir($srcDir);
        file_put_contents($srcDir . '/hello.txt', 'hello gzip round-trip');

        $archive = $this->tempDir . '/gzip_src.tar.gz';
        Files::compress($srcDir, $archive, CompressionFormat::Gzip);

        $outDir = $this->tempDir . '/gzip_out';
        Files::decompress($archive, $outDir, CompressionFormat::Gzip);

        Assert::true(is_dir($outDir));
        Assert::true(file_exists($outDir . '/hello.txt'));
        Assert::same('hello gzip round-trip', file_get_contents($outDir . '/hello.txt'));
    }

    public function testDecompressTar(): void
    {
        $srcDir = $this->tempDir . '/tarsrc';
        mkdir($srcDir);
        file_put_contents($srcDir . '/msg.txt', 'hello from tar');

        $archive = $this->tempDir . '/msg.tar';
        Files::compress($srcDir, $archive, CompressionFormat::Tar);

        $outDir = $this->tempDir . '/tar_out';
        Files::decompress($archive, $outDir, CompressionFormat::Tar);

        Assert::true(is_dir($outDir));
        Assert::true(file_exists($outDir . '/msg.txt'));
        Assert::same('hello from tar', file_get_contents($outDir . '/msg.txt'));
    }

    public function testDecompressThrowsOnMissingArchive(): void
    {
        Assert::exception(
            fn () => Files::decompress($this->tempDir . '/missing.zip', $this->tempDir . '/out', CompressionFormat::Zip),
            InvalidArgumentException::class
        );
    }

    public function testDecompressZip(): void
    {
        $source = $this->tempDir . '/greet.txt';
        file_put_contents($source, 'hello from zip');

        $archive = $this->tempDir . '/greet.zip';
        Files::compress($source, $archive, CompressionFormat::Zip);

        $outDir = $this->tempDir . '/zip_out';
        Files::decompress($archive, $outDir, CompressionFormat::Zip);

        Assert::true(is_dir($outDir));
        Assert::true(file_exists($outDir . '/greet.txt'));
        Assert::same('hello from zip', file_get_contents($outDir . '/greet.txt'));
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

    public function testDeleteFile(): void
    {
        $file = $this->tempDir . '/to_delete.txt';
        file_put_contents($file, 'content');

        Files::delete($file);

        Assert::false(file_exists($file));
    }

    public function testDeleteNonExistentDoesNotThrow(): void
    {
        Files::delete($this->tempDir . '/non_existent');
        Assert::true(true);
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

    public function testExtension(): void
    {
        Assert::same('txt', Files::extension('/path/to/file.txt'));
        Assert::same('gz', Files::extension('/path/to/archive.tar.gz'));
        Assert::same('', Files::extension('/path/to/README'));
        Assert::same('php', Files::extension('script.php'));
    }

    public function testFind(): void
    {
        mkdir($this->tempDir . '/dir1');
        file_put_contents($this->tempDir . '/file.txt', 'a');

        $results = Files::find('*', $this->tempDir);

        Assert::count(2, $results);
    }

    public function testFindByTypeNonRecursiveFindsDotfiles(): void
    {
        file_put_contents($this->tempDir . '/.env', 'APP_KEY=secret');
        file_put_contents($this->tempDir . '/regular.txt', 'normal');

        $results = Files::find('*', $this->tempDir);

        $basenames = array_map(static fn ($p) => basename($p), $results);
        Assert::true(in_array('.env', $basenames, true));
        Assert::true(in_array('regular.txt', $basenames, true));
    }

    public function testFindDirectories(): void
    {
        mkdir($this->tempDir . '/dir1');
        mkdir($this->tempDir . '/dir2');
        file_put_contents($this->tempDir . '/file.txt', 'a');

        $results = Files::findDirectories('*', $this->tempDir);

        Assert::count(2, $results);
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

    public function testFindFilesDefaultMask(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');

        $results = Files::findFiles(directory: $this->tempDir);

        Assert::count(2, $results);
    }

    public function testFindFilesNoMatch(): void
    {
        file_put_contents($this->tempDir . '/file.txt', 'a');

        $results = Files::findFiles('*.php', $this->tempDir);

        Assert::count(0, $results);
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

    public function testFindNonExistentDirectoryThrows(): void
    {
        Assert::exception(
            static fn () => Files::findFiles('*', '/non/existent/directory'),
            RuntimeException::class
        );
    }

    public function testGetLink(): void
    {
        $target = $this->tempDir . '/getlink_target.txt';
        $link = $this->tempDir . '/getlink';
        file_put_contents($target, 'content');
        symlink($target, $link);

        Assert::same($target, Files::getLink($link));
    }

    public function testGetLinkNonLinkThrows(): void
    {
        $file = $this->tempDir . '/not_a_link.txt';
        file_put_contents($file, 'content');

        Assert::exception(
            static fn () => Files::getLink($file),
            RuntimeException::class
        );
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

    public function testIsAbsoluteWindowsRootPath(): void
    {
        Assert::true(Files::isAbsolute('C:/'));
        Assert::true(Files::isAbsolute('D:\\'));
    }

    public function testIsDirectory(): void
    {
        Assert::true(Files::isDirectory($this->tempDir));
        Assert::false(Files::isDirectory($this->tempDir . '/non_existent.txt'));
        Assert::false(Files::isDirectory('/non/existent/directory'));
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

    public function testIsLink(): void
    {
        $file = $this->tempDir . '/target.txt';
        $link = $this->tempDir . '/link_to_target';
        file_put_contents($file, 'content');
        symlink($file, $link);

        Assert::true(Files::isLink($link));
        Assert::false(Files::isLink($file));
    }

    public function testIsLockedNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::isLocked('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testIsLockedUnlockedFile(): void
    {
        $file = $this->tempDir . '/unlocked.txt';
        file_put_contents($file, 'content');

        Assert::false(Files::isLocked($file));
    }

    public function testIsReadable(): void
    {
        $file = $this->tempDir . '/readable.txt';
        file_put_contents($file, 'content');

        Assert::true(Files::isReadable($file));
        Assert::false(Files::isReadable('/non/existent/file.txt'));
    }

    public function testIsWritable(): void
    {
        $file = $this->tempDir . '/writable_check.txt';
        file_put_contents($file, 'content');

        Assert::true(Files::isWritable($file));
    }

    public function testJoinPaths(): void
    {
        Assert::same('a/b/file.txt', Files::joinPaths('a', 'b', 'file.txt'));
        Assert::same('/a/b/', Files::joinPaths('/a/', '/b/'));
        Assert::same('/b', Files::joinPaths('/a/', '/../b'));
        Assert::same('file.txt', Files::joinPaths('file.txt'));
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

    public function testLink(): void
    {
        $target = $this->tempDir . '/link_target.txt';
        $link = $this->tempDir . '/symlink';
        file_put_contents($target, 'content');

        Files::link($target, $link);

        Assert::true(is_link($link));
        Assert::same($target, readlink($link));
    }

    public function testListing(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');
        mkdir($this->tempDir . '/subdir');

        $results = Files::listing($this->tempDir);

        Assert::count(3, $results);
    }

    public function testListingCallbackReceivesEntryName(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');

        $capturedNames = [];
        Files::listing($this->tempDir, function ($fullPath, $entryName) use (&$capturedNames): bool {
            $capturedNames[] = $entryName;

            return true;
        });

        Assert::true(in_array('file1.txt', $capturedNames, true));
        Assert::true(in_array('file2.php', $capturedNames, true));
    }

    public function testListingEmptyDirectory(): void
    {
        $emptyDir = $this->tempDir . '/empty';
        mkdir($emptyDir);

        $results = Files::listing($emptyDir);

        Assert::count(0, $results);
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

    public function testListingWithCallbackFilter(): void
    {
        file_put_contents($this->tempDir . '/small.txt', 'a');
        file_put_contents($this->tempDir . '/large.txt', str_repeat('x', 1024));

        $results = Files::listing($this->tempDir, fn ($path) => is_file($path) && filesize($path) > 100);

        Assert::count(1, $results);
        Assert::true(str_ends_with($results[0], 'large.txt'));
    }

    public function testListingWithGlobFilter(): void
    {
        file_put_contents($this->tempDir . '/file1.txt', 'a');
        file_put_contents($this->tempDir . '/file2.php', 'b');
        file_put_contents($this->tempDir . '/file3.txt', 'c');

        $results = Files::listing($this->tempDir, '*.txt');

        Assert::count(2, $results);
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

    public function testMimeType(): void
    {
        $file = $this->tempDir . '/text.txt';
        file_put_contents($file, 'plain text content');

        $mimeType = Files::mimeType($file);

        Assert::true(str_starts_with($mimeType, 'text/'));
    }

    public function testMimeTypeNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::mimeType('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testMimeTypePhpFile(): void
    {
        $file = $this->tempDir . '/script.php';
        file_put_contents($file, '<?php echo "hello";');

        $mimeType = Files::mimeType($file);

        Assert::true(str_contains($mimeType, 'php') || str_contains($mimeType, 'text'));
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

    public function testMoveDirectoryOverDirectory(): void
    {
        $sourceDir = $this->tempDir . '/source_dir';
        $destDir = $this->tempDir . '/dest_dir';
        mkdir($sourceDir);
        file_put_contents($sourceDir . '/file.txt', 'content');
        mkdir($destDir);
        file_put_contents($destDir . '/old.txt', 'old');

        Files::move($sourceDir, $destDir);

        Assert::false(file_exists($sourceDir));
        Assert::true(file_exists($destDir . '/file.txt'));
        Assert::same('content', file_get_contents($destDir . '/file.txt'));
        Assert::false(file_exists($destDir . '/old.txt'));
    }

    public function testMoveDirectoryOverFileThrows(): void
    {
        $sourceDir = $this->tempDir . '/source_dir';
        $destFile = $this->tempDir . '/target_file.txt';
        mkdir($sourceDir);
        file_put_contents($sourceDir . '/inner.txt', 'data');
        file_put_contents($destFile, 'existing file');

        Assert::exception(
            static fn () => Files::move($sourceDir, $destFile),
            RuntimeException::class
        );
    }

    public function testMoveFileIntoExistingDirectoryNoOverwrite(): void
    {
        $source = $this->tempDir . '/source.txt';
        $destDir = $this->tempDir . '/target_dir';
        file_put_contents($source, 'moved content');
        mkdir($destDir);

        Files::move($source, $destDir, overwrite: false);

        Assert::false(file_exists($source));
        Assert::true(file_exists($destDir . '/source.txt'));
        Assert::same('moved content', file_get_contents($destDir . '/source.txt'));
    }

    public function testMoveNonExistentSourceThrows(): void
    {
        Assert::exception(
            static fn () => Files::move('/non/existent/file.txt', '/tmp/dest.txt'),
            RuntimeException::class
        );
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

    public function testMoveRemovesSource(): void
    {
        $source = $this->tempDir . '/before_move.txt';
        $destination = $this->tempDir . '/after_move.txt';
        file_put_contents($source, 'moving content');

        Files::move($source, $destination);

        Assert::false(file_exists($source));
        Assert::true(file_exists($destination));
    }

    public function testName(): void
    {
        Assert::same('file.txt', Files::name('/path/to/file.txt'));
        Assert::same('file', Files::name('/path/to/file.txt', includeExtension: false));
        Assert::same('directory', Files::name('/path/to/directory/'));
    }

    public function testNameWithoutExtension(): void
    {
        Assert::same('file', Files::name('/path/to/file.txt', includeExtension: false));
        Assert::same('file.txt', Files::name('/path/to/file.txt', includeExtension: true));
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

    public function testOfChaining(): void
    {
        $file = $this->tempDir . '/fluent_chain.txt';
        file_put_contents($file, 'original');

        $content = Files::of($file)
            ->write('updated')
            ->read();

        Assert::same('updated', $content);
    }

    public function testOfReturnsFluentFiles(): void
    {
        $file = $this->tempDir . '/fluent_test.txt';
        file_put_contents($file, 'test content');

        $result = Files::of($file);

        Assert::type(\Phuture\Coherence\Type\Files::class, $result);
    }

    public function testOfThrowsForDirectory(): void
    {
        Assert::exception(
            fn () => Files::of($this->tempDir),
            RuntimeException::class,
        );
    }

    public function testOfThrowsForNonExistentFile(): void
    {
        Assert::exception(
            fn () => Files::of('/nonexistent/path/file.txt'),
            RuntimeException::class,
        );
    }

    public function testPlatformSlashes(): void
    {
        $expected = 'path' . DIRECTORY_SEPARATOR . 'to' . DIRECTORY_SEPARATOR . 'file.txt';
        Assert::same($expected, Files::platformSlashes('path/to/file.txt'));
        Assert::same($expected, Files::platformSlashes('path\\to\\file.txt'));
    }

    public function testPrependCreatesFile(): void
    {
        $file = $this->tempDir . '/prepend_new.txt';

        Files::prepend($file, 'content');

        Assert::same('content', file_get_contents($file));
    }

    public function testPrependToExistingFile(): void
    {
        $file = $this->tempDir . '/prepend.txt';
        file_put_contents($file, 'trailer');

        Files::prepend($file, 'header ');

        Assert::same('header trailer', file_get_contents($file));
    }

    public function testRead(): void
    {
        $file = $this->tempDir . '/read.txt';
        file_put_contents($file, 'file content');

        Assert::same('file content', Files::read($file));
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

    public function testReadLinesNonExistentThrows(): void
    {
        Assert::exception(function () {
            iterator_to_array(Files::readLines('/non/existent/file.txt'));
        }, RuntimeException::class);
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

    public function testReadNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::read('/non/existent/file.txt'),
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

    public function testRenameEmptyNameThrows(): void
    {
        $file = $this->tempDir . '/file.txt';
        file_put_contents($file, 'content');

        Assert::exception(
            static fn () => Files::rename($file, ''),
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

    public function testReplaceInFile(): void
    {
        $file = $this->tempDir . '/replace.txt';
        file_put_contents($file, 'Hello old world, old friend');

        Files::replaceInFile($file, 'old', 'new');

        Assert::same('Hello new world, new friend', file_get_contents($file));
    }

    public function testReplaceInFileWithArrays(): void
    {
        $file = $this->tempDir . '/replace_multi.txt';
        file_put_contents($file, 'Hello {{name}}, your email is {{email}}');

        Files::replaceInFile($file, ['{{name}}', '{{email}}'], ['John', 'john@example.com']);

        Assert::same('Hello John, your email is john@example.com', file_get_contents($file));
    }

    public function testSize(): void
    {
        $file = $this->tempDir . '/sized.txt';
        $content = str_repeat('x', 1024);
        file_put_contents($file, $content);

        Assert::same(1024, Files::size($file));
    }

    public function testSizeDirectory(): void
    {
        $dir = $this->tempDir . '/sized_dir';
        mkdir($dir);
        file_put_contents($dir . '/file1.txt', str_repeat('a', 100));
        file_put_contents($dir . '/file2.txt', str_repeat('b', 200));

        Assert::same(300, Files::size($dir));
    }

    public function testSizeNonExistentThrows(): void
    {
        Assert::exception(
            static fn () => Files::size('/non/existent/file.txt'),
            RuntimeException::class
        );
    }

    public function testUnixSlashes(): void
    {
        Assert::same('path/to/file.txt', Files::unixSlashes('path\\to\\file.txt'));
        Assert::same('path/to/file.txt', Files::unixSlashes('path/to/file.txt'));
    }

    public function testUnlink(): void
    {
        $target = $this->tempDir . '/unlink_target.txt';
        $link = $this->tempDir . '/unlink_link';
        file_put_contents($target, 'content');
        symlink($target, $link);

        Files::unlink($link);

        Assert::false(is_link($link));
        Assert::true(file_exists($target));
    }

    public function testUnlinkNonLinkThrows(): void
    {
        $file = $this->tempDir . '/unlink_file.txt';
        file_put_contents($file, 'content');

        Assert::exception(
            static fn () => Files::unlink($file),
            RuntimeException::class
        );
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

    public function testUploadMissingKeyReturnsFalse(): void
    {
        $result = Files::upload('nonexistent', $this->tempDir, []);

        Assert::false($result);
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

    public function testWrite(): void
    {
        $file = $this->tempDir . '/write.txt';

        Files::write($file, 'written content');

        Assert::same('written content', file_get_contents($file));
    }

    public function testWriteAndReadRoundTrip(): void
    {
        $file = $this->tempDir . '/roundtrip.txt';
        $content = 'Hello, World! Special chars: àéïôù ñ 你好';

        Files::write($file, $content);

        Assert::same($content, Files::read($file));
    }

    public function testWriteCreatesParentDirectories(): void
    {
        $file = $this->tempDir . '/new/sub/dir/write.txt';

        Files::write($file, 'deep content');

        Assert::same('deep content', file_get_contents($file));
    }

    public function testWriteWithLock(): void
    {
        $file = $this->tempDir . '/locked_write.txt';

        Files::write($file, 'locked content', 0666, true);

        Assert::same('locked content', file_get_contents($file));
    }

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
