<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayEqualsTest extends TestCase 
{
    public function testArrayEquals() 
    {
        $array1 = [
            "key1" => "value1",
            "key2" => "value2",
            "assoc" => [
                "k1" => "v1",
                "k2" => "v2",
            ]
        ];
        $array2 = [
            "assoc" => [
                "k2" => "v2",
                "k1" => "v1",
            ],
            "key2" => "value2",
            "key1" => "value1",
        ];
        $result = array_equals($array1, $array2);
        $this->assertTrue($result);
    }

    public function testArrayEquals2() 
    {
        $array1 = [
            0 => "id",
            "assoc" => [
                "k1",
                "k2",
            ],
            1 => "created",
        ];
        $array2 = [
            0 => "id",
            "assoc" => [
                "k1",
                "k2",
            ],
            2 => "created",
        ];
        $result = array_equals($array1, $array2);
        $this->assertTrue($result);
    }

    public function testArrayEquals3() 
    {
        $array1 = [
            0 => "name",
            1 => "userId",
        ];
        $array2 = [
            1 => "name",
            0 => "userId",
        ];
        $result = array_equals($array1, $array2);
        $this->assertTrue($result);
    }

    public function testArrayEquals4() 
    {
        $array1 = [
            0 => 1,
            "key" => "value",
            "data" => [
                ["name","userId"],
                ["name","userId"],
            ]
        ];
        $array2 = [
            0 => 1,
            "key" => "value",
            "data" => [
                ["userId","name"],
                ["name","userId"],
            ]
        ];
        $result = array_equals($array1, $array2);
        $this->assertTrue($result);
    }

    public function testArrayEqualsFalse() 
    {
        $array1 = [
            "key1" => "value1",
            "key2" => "value2",
        ];
        $array2 = [
            "key2" => "value2",
            "key3" => "value1",
        ];
        $result = array_equals($array1, $array2);
        $this->assertFalse($result);
    }
}