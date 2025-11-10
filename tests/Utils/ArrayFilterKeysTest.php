<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayFilterKeysTest extends TestCase 
{
    public function testArrayFilterKeys() 
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
        $result = array_filter_keys($input, $keys);
        $expectedResult = array(
            "key1" => "value1",
            "key3" => array(
                "key4" => "value4",
                "key5" => "value5",
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysAsList() 
    {
        $input = array(
            ["key1" => "value1", "key2" => "value2"],
            ["key1" => "value1"],
        );
        $keys = array(
            "key1",
        );
        $result = array_filter_keys($input, $keys, true);
        $expectedResult = array(
            ["key1" => "value1"],
            ["key1" => "value1"],
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
        $result = array_filter_keys($input, $keys, true, false);
        $expectedResult = array(
            ["key2" => "value2"],
            [],
        );
        $this->assertEquals($expectedResult, $result);
    }
}