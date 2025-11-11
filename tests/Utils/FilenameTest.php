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
}