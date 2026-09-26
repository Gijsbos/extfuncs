<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class FileSystemFunctionsTest extends TestCase
{
    private string $dir;

    protected function setUp() : void
    {
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "extfuncs_" . random_token(12);
        mkdir($this->dir . "/sub/deeper", 0777, true);
    }

    protected function tearDown() : void
    {
        rmdir_recursive($this->dir);
    }

    public function testRmdirRecursive()
    {
        file_put_contents($this->dir . "/a.txt", "a");
        file_put_contents($this->dir . "/sub/deeper/b.txt", "b");

        rmdir_recursive($this->dir);

        $this->assertDirectoryDoesNotExist($this->dir);
    }

    public function testRmdirRecursiveDoesNotFollowSymlinks()
    {
        $outside = $this->dir . "_outside";
        mkdir($outside);
        file_put_contents("$outside/keep.txt", "keep");
        symlink($outside, $this->dir . "/sub/link");

        rmdir_recursive($this->dir);

        $this->assertDirectoryDoesNotExist($this->dir);
        $this->assertFileExists("$outside/keep.txt");

        rmdir_recursive($outside);
    }

    public function testRmdirRecursiveMissingDir()
    {
        rmdir_recursive($this->dir . "/missing");
        $this->assertDirectoryExists($this->dir);
    }

    public function testIncludeRecursive()
    {
        file_put_contents($this->dir . "/sub/deeper/inc.php", '<?php $GLOBALS["extfuncs_inc_php"] = true;');
        file_put_contents($this->dir . "/sub/skip.txt", '<?php $GLOBALS["extfuncs_inc_skip"] = true;');

        include_recursive($this->dir);

        $this->assertTrue($GLOBALS["extfuncs_inc_php"] ?? false);
        $this->assertArrayNotHasKey("extfuncs_inc_skip", $GLOBALS);
    }

    # @bugfix custom extension was not passed down to subdirectories
    public function testIncludeRecursiveCustomExtension()
    {
        file_put_contents($this->dir . "/sub/deeper/inc.inc", '<?php $GLOBALS["extfuncs_inc_custom"] = true;');

        include_recursive($this->dir, ".inc");

        $this->assertTrue($GLOBALS["extfuncs_inc_custom"] ?? false);
    }
}
