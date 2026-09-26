<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;
use gijsbos\ExtFuncs\Exceptions\FileNotFoundException;
use PHPUnit\Framework\TestCase;

final class FileHasherTest extends TestCase
{
    private string $dir;

    protected function setUp() : void
    {
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "extfuncs_hash_" . random_token(12);
        mkdir($this->dir . "/sub", 0777, true);
        file_put_contents($this->dir . "/a.txt", "a");
        file_put_contents($this->dir . "/sub/b.txt", "b");
    }

    protected function tearDown() : void
    {
        rmdir_recursive($this->dir);
    }

    public function testHashFile()
    {
        $file = $this->dir . "/a.txt";
        $this->assertSame(md5(md5("a")), FileHasher::hash($file));
        $this->assertSame(sha1(sha1("a")), FileHasher::hash($file, FileHasher::SHA1));
        $this->assertSame(hash("xxh3", hash("xxh3", "a")), FileHasher::hash($file, FileHasher::XXH3));
    }

    public function testHashDirectory()
    {
        $this->assertSame(md5(md5("a") . md5("b")), FileHasher::hash($this->dir));
    }

    public function testHashDirectoryChangesWithContent()
    {
        $before = FileHasher::hash($this->dir);
        file_put_contents($this->dir . "/sub/b.txt", "changed");
        $this->assertNotSame($before, FileHasher::hash($this->dir));
    }

    # @bugfix uppercase algorithm names passed validation but were hashed with xxh3
    public function testHashAlgorithmIsCaseInsensitive()
    {
        $this->assertSame(FileHasher::hash($this->dir, "md5"), FileHasher::hash($this->dir, "MD5"));
    }

    public function testHashUnknownAlgorithm()
    {
        $this->expectException(Exception::class);
        FileHasher::hash($this->dir, "crc32");
    }

    public function testHashMissingPath()
    {
        $this->expectException(FileNotFoundException::class);
        FileHasher::hash($this->dir . "/missing");
    }

    public function testHashFilePathArray()
    {
        $a = $this->dir . "/a.txt";
        $b = $this->dir . "/sub/b.txt";

        $this->assertSame(md5(FileHasher::hash($a) . FileHasher::hash($b)), FileHasher::hashFilePathArray([$a, $b]));
        $this->assertSame(md5(FileHasher::hash($a)), FileHasher::hashFilePathArray($a));
    }

    # @bugfix xxh3 path arrays were combined with md5
    public function testHashFilePathArrayXxh3()
    {
        $a = $this->dir . "/a.txt";
        $this->assertSame(hash("xxh3", FileHasher::hash($a, FileHasher::XXH3)), FileHasher::hashFilePathArray([$a], FileHasher::XXH3));
    }
}
