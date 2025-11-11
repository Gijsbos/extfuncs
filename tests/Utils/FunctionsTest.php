<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class FunctionsTest extends TestCase 
{
    public function testExecutable()
    {
        $result = Functions::executable("gijsbos\ExtFuncs\Utils\FunctionsTest->testExecutable");
        $this->assertTrue($result);
    }

    public function testExecutableFalse()
    {
        $result = Functions::executable("gijsbos\ExtFuncs\Utils\FunctionsTest->testExecutableFalser");
        $this->assertFalse($result);
    }

    public function testExecutableStatic()
    {
        $result = Functions::executable("gijsbos\ExtFuncs\Utils\Functions::executable");
        $this->assertTrue($result);
    }

    public function testExecutableStaticFalse()
    {
        $result = Functions::executable("gijsbos\ExtFuncs\Utils\Functions::executabler");
        $this->assertFalse($result);
    }

    public function testExecutableSingle()
    {
        $result = Functions::executable("explode");
        $this->assertTrue($result);
    }

    public function testExecutableSingleFalse()
    {
        $result = Functions::executable("exploder");
        $this->assertFalse($result);
    }

    public function testExecutable2Args()
    {
        $result = Functions::executable("gijsbos\ExtFuncs\Utils\FunctionsTest", "testExecutable");
        $this->assertTrue($result);
    }

    public function testExecutable2ArgsFalse()
    {
        $result = Functions::executable("gijsbos\ExtFuncs\Utils\FunctionsTest", "testExecutabler");
        $this->assertFalse($result);
    }

    public function testExecute()
    {
        $result = Functions::execute("strtoupper", ["now"]);
        $expectedResult = "NOW";
        $this->assertEquals($expectedResult, $result);
    }

    public function testExecuteClassMethod()
    {
        $date = Functions::execute("DateTime->format", [], ["Y-m-d"]);
        $result = preg_match("/[0-9]{4}\-[0-9]{2}\-[0-9]{2}/", $date) === 1;
        $this->assertTrue($result);
    }

    public function testExecuteStaticClassMethod()
    {
        $result = Functions::execute("gijsbos\ExtFuncs\Utils\Functions::executable", ["gijsbos\ExtFuncs\Utils\FunctionsTest", "testExecutable"]);
        $this->assertTrue($result);
    }
}