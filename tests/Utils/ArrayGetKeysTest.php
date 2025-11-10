<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayGetKeysTest extends TestCase 
{
    public function testArrayGetKeys()
    {
        $input = array(
            "var1" => "value1",
            "var2" => "value2",
            "var3" => "value3"
        );
        $result = array_get_keys($input);
        $expectedResult = array(
            "var1","var2","var3"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeysMultiDimensional()
    {
        $input = array(
            "var1" => "value1",
            "var2" => "value2",
            "var3" => array(
                "var4" => "value3"
            )
        );
        $result = array_get_keys($input);
        $expectedResult = array(
            "var1","var2","var3" => array("var4")
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeysAsList()
    {
        $input = array(
            ["var1" => "value1"],
            ["var2" => "value2"],
            ["var3" => "value3"],
        );
        $result = array_get_keys($input, true);
        $expectedResult = array(
            "var1","var2","var3"
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeysAsListNested()
    {
        $input = array(
            "var1" => "value1",
            "var2" => "value2",
            "var3" => array(
                ["key1" => "value1"],
                ["key2" => "value1"],
            )
        );
        $result = array_get_keys($input, true);
        $expectedResult = array(
            "var1","var2","var3" => ["key1", "key2"]
        );
        $this->assertEquals($expectedResult, $result);
    }
}