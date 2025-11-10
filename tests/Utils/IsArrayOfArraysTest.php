<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class IsArrayOfArraysTest extends TestCase 
{
    public function testIsArrayOfArrays()
    {
        $input = array(
            ["var1" => "value1"],
            ["var2" => "value2"],
            ["var3" => "value3"]
        );
        $result = is_array_of_arrays($input);
        $this->assertTrue($result);
    }

    public function testIsArrayOfArraysFalse()
    {
        $input = array(
            ["var1" => "value1"],
            ["var2" => "value2"],
            "var3" => "value3"
        );
        $result = is_array_of_arrays($input);
        $this->assertFalse($result);
    }

    public function testIsArrayOfArraysFalseEmpty()
    {
        $input = array();
        $result = is_array_of_arrays($input);
        $this->assertFalse($result);
    }
}