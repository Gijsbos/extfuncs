<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class FilterVarsTest extends TestCase 
{
    public function testFilterDefinedVarsArray()
    {
        $test = "value-1";
        $test2 = "value-2";
        $result = filter_vars(get_defined_vars(), array("test"));
        $expectedResult = array(
            "test2" => "value-2"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterDefinedVarsArrayIncludeKeys()
    {
        $test = "value-1";
        $test2 = "value-2";
        $result = filter_vars(get_defined_vars(), array("test"), false);
        $expectedResult = array(
            "test" => "value-1"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterDefinedVarsArrayExcludeKeys()
    {
        $test = "value-1";
        $test2 = "value-2";
        $result = filter_vars(get_defined_vars(), array("test"), true);
        $expectedResult = array(
            "test2" => "value-2"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterDefinedVarsString()
    {
        $test = "value-1";
        $test2 = "value-2";
        $result = filter_vars(get_defined_vars(), "test");
        $expectedResult = array(
            "test2" => "value-2"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterDefinedVarsInt1()
    {
        $result = filter_vars(array("value-1", "value-2"), 0);
        $expectedResult = array(
            "value-2"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterDefinedVarsInt2()
    {
        $result = filter_vars(array("value-1", "value-2"), array(0));
        $expectedResult = array(
            "value-2"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testFilterDefinedVarsIncorrectInputNumber()
    {
        $test = "value-1";
        $test2 = "value-2";
        $result = filter_vars(get_defined_vars(), "test");
        $expectedResult = array(
            "test2" => "value-2"
        );
        $this->assertEquals($expectedResult, $result);
    }
}