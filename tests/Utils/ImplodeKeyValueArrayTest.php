<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ImplodeKeyValueArrayTest extends TestCase 
{
    public function testImplodeKeyValueArray()
    {
        $array = ["key" => "value"];
        $result = implode_key_value_array($array);
        $expectedResult = "key=value";
        $this->assertEquals($expectedResult, $result);
    }

    public function testImplodeKeyValueArrayEnclosed()
    {
        $array = ["key" => "value"];
        $result = implode_key_value_array($array, "=", ",", "[", "]");
        $expectedResult = "[key=value]";
        $this->assertEquals($expectedResult, $result);
    }
}