<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayIsAssocTest extends TestCase 
{
    public function testArrayIsAssocTrue1()
    {
        $input = array(
            "key-1" => "value-1",
            "key-2" => "value-2"
        );
        $result = array_is_assoc($input);
        $expectedResult = true;
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayIsAssocTrue2()
    {
        $input = array(
            "key-1" => "value-1", "value-2"
        );
        $result = array_is_assoc($input);
        $expectedResult = true;
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayIsAssocFalse()
    {
        $input = array(
            "value-1", "value-2"
        );
        $result = array_is_assoc($input);
        $expectedResult = false;
        $this->assertEquals($expectedResult, $result);
    }
}