<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class FilenameTest extends TestCase 
{
    public function testFilename1()
    {
        $input = "test.php";
        $result = filename($input);
        $expectedResult = "test";
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilename2()
    {
        $input = "test";
        $result = filename($input);
        $expectedResult = "test";
        $this->assertEquals($expectedResult, $result);
    }

    # @bugfix names containing characters other than [a-zA-Z0-9_] were cut off
    public function testFilenameWithDashesAndPath()
    {
        $this->assertEquals("my-file", filename("/var/www/my-file.txt"));
        $this->assertEquals("my file", filename("C:\\dir\\my file.txt"));
    }

    public function testFilenameMultipleExtensions()
    {
        $this->assertEquals("archive.tar", filename("archive.tar.gz"));
    }
}