<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayFilterKeysTest extends TestCase 
{
    public function testArrayFilterKeysExclude() 
    {
        $input = array(
            "key1" => "value1",
            "key2" => "value2",
            "key3" => array(
                "key4" => "value4",
                "key5" => "value5",
            )
        );
        $keys = array(
            "key1",
            "key3" => array(
                "key5"
            )
        );
        $result = array_filter_keys($input, $keys, false, true);
        $expectedResult = array(
            "key2" => "value2",
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysInclude() 
    {
        $input = array(
            "key1" => "value1",
            "key2" => "value2",
            "key3" => array(
                "key4" => "value4",
                "key5" => "value5",
            )
        );
        $keys = array(
            "key1",
            "key3" => array(
                "key5"
            )
        );
        $result = array_filter_keys($input, $keys, false, false);
        $expectedResult = array(
            "key1" => "value1",
            "key3" => array(
                "key4" => "value4",
                "key5" => "value5",
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysAsListExclude() 
    {
        $input = array(
            ["key1" => "value1", "key2" => "value2"],
            ["key1" => "value1"],
        );
        $keys = array(
            "key1",
        );
        $result = array_filter_keys($input, $keys, true, true);
        $expectedResult = array(
            ["key2" => "value2"],
            []
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysAsListInclude() 
    {
        $input = array(
            ["key1" => "value1", "key2" => "value2"],
            ["key1" => "value1"],
        );
        $keys = array(
            "key1",
        );
        $result = array_filter_keys($input, $keys, true, false);
        $expectedResult = array(
            ["key1" => "value1"],
            ["key1" => "value1"],
        );
        $this->assertEquals($expectedResult, $result);
    }
}