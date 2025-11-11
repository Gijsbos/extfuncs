<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayDiffKeysTest extends TestCase 
{
    public function testArrayDiffKeysEqualsArrayDiffKey() 
    {
        $test1 = [
            "key1" => "value"
        ];
        $test2 = [
            "key1" => "value",
            "key2" => "value"
        ];

        $result = array_diff_keys($test2, $test1);
        $expectedResult = [
            "key2" => "value"
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayDiffKeys()
    {
        $test1 = [
            "key1" => "value"
        ];
        $test2 = [
            "key1" => "value",
            "key2" => [
                "key3" => "value"
            ]
        ];

        $result = array_diff_keys($test2, $test1);
        $expectedResult = [
            "key2" => [
                "key3" => "value"
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayDiffKeysNested()
    {
        $test1 = [
            "key1" => "value",
            "key2" => [
                "key3" => "value"
            ]
        ];
        $test2 = [
            "key1" => "value",
            "key2" => [
                "key3" => "value"
            ]
        ];

        $result = array_diff_keys($test2, $test1);
        $expectedResult = [];
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayDiffKeysNestedDiff()
    {
        $test1 = [
            "key1" => "value",
            "key2" => [
                "key3" => "value"
            ]
        ];
        $test2 = [
            "key1" => "value",
            "key2" => [
                "key3" => "value",
                "key4" => "value"
            ]
        ];

        $result = array_diff_keys($test2, $test1);
        $expectedResult = [
            "key2" => [
                "key4" => "value"
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }
}