<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayGetKeyValueTest extends TestCase 
{
    public function testArrayGetKeyValue()
    {
        $path = "var1";
        $array = array(
            "var1" => "value1",
            "var2" => [
                "foo" => "bar"
            ]
        );
        $result = array_get_key_value($path, $array);
        $expectedResult = $array["var1"];
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeyValueNested()
    {
        $path = "var2.foo";
        $array = array(
            "var1" => "value1",
            "var2" => [
                "foo" => "bar"
            ]
        );
        $result = array_get_key_value($path, $array);
        $expectedResult = $array["var2"]["foo"];
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayGetKeyValueEmpty()
    {
        $path = "var2.foo.bar";
        $array = array(
            "var1" => "value1",
            "var2" => [
                "foo" => "bar"
            ]
        );
        $result = array_get_key_value($path, $array);
        $expectedResult = null;
        $this->assertEquals($expectedResult, $result);
    }
}