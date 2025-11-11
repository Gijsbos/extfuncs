<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayFilterKeysRecursiveTest extends TestCase 
{
    public function testArrayFilterKeysRecursiveExclude() 
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
        $result = array_filter_keys_recursive($input, $keys, false, false);
        $expectedResult = array(
            "key2" => "value2",
            "key3" => array(
                "key4" => "value4",
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysRecursiveInclude() 
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
        $result = array_filter_keys_recursive($input, $keys, false, true);
        $expectedResult = array(
            "key1" => "value1",
            "key3" => array(
                "key5" => "value5",
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysRecursiveAssoc() 
    {
        $input = array(
            "key1" => "value1",
            "key2" => "value2",
            "key3" => array(
                array(
                    "key4" => "value4",
                    "key5" => "value5",
                ),
                array(
                    "key4" => "value4",
                    "key5" => "value5",
                ),
            )
        );
        $keys = array(
            "key1",
            "key3" => array(
                "key5"
            )
        );
        $result = array_filter_keys_recursive($input, $keys, true, true);
        $expectedResult = array(
            "key1" => "value1",
            "key3" => array(
                array(
                    "key5" => "value5",
                ),
                array(
                    "key5" => "value5",
                ),
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysRecursiveAsList() 
    {
        $input = array(
            "key1" => "value1",
            "data" => array(
                array(
                    "key2" => "value2",
                    "key3" => "value2",
                ),
                array(
                    "key2" => "value2",
                ),
            )
        );
        $keys = array(
            "key1",
            "data" => array(
                "key2"
            )
        );
        $result = array_filter_keys_recursive($input, $keys, true, true);
        $expectedResult = array(
            "key1" => "value1",
            "data" => array(
                array(
                    "key2" => "value2",
                ),
                array(
                    "key2" => "value2",
                ),
            )
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayFilterKeysRecursiveAsListExclude() 
    {
        $input = array(
            "key1" => "value1",
            "data" => array(
                array(
                    "key2" => "value2",
                    "key3" => "value3",
                ),
                array(
                    "key2" => "value2",
                ),
            )
        );
        $keys = array(
            "key1",
            "data" => array(
                "key2"
            )
        );
        $result = array_filter_keys_recursive($input, $keys, true, false);
        $expectedResult = array(
            "data" => array(
                array(
                    "key3" => "value3",
                ),
                [],
            )
        );
        $this->assertEquals($expectedResult, $result);
    }
}