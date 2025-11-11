<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayHasKeysTest extends TestCase 
{
    public function testArrayHasKeysLooseTrue() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => "value4",
            ]
        ];
        $result = array_has_keys($input, ["key2" => ["key3"]], false, false);
        $this->assertTrue($result);
    }

    public function testArrayHasKeysLooseTrueAsList() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                ["key3" => "value3"],
                ["key3" => "value4"],
            ]
        ];
        $result = array_has_keys($input, ["key2" => ["key3"]], true, false);
        $this->assertTrue($result);
    }

    public function testArrayHasKeysLooseFalse() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => "value4",
            ]
        ];
        $result = array_has_keys($input, ["key4"], false, false);
        $this->assertFalse($result);
    }

    public function testArrayHasKeysLooseFalseThrows() 
    {
        $this->expectExceptionMessage("array_has_keys failed: [missing => [0 => key4], invalid => [key2 => [0 => key3, 1 => key4]]]");
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => "value4",
            ]
        ];
        $result = array_has_keys($input, ["key1","key2","key4"], false, false, true);
    }

    public function testArrayHasKeysLooseFalseAsList() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => "value4",
            ]
        ];
        $result = array_has_keys($input, ["key4"], true, false);
        $this->assertFalse($result);
    }

    public function testArrayHasKeysLooseFalseAsListThrows() 
    {
        $this->expectExceptionMessage("array_has_keys failed: [missing => [key2 => [0 => key4]], invalid => []]");

        $input = [
            "key1" => "value1",
            "key2" => [
                ["key3" => "value3"],
            ]
        ];
        $result = array_has_keys($input, ["key1","key2" => ["key3","key4"]], true, false, true);
    }

    public function testArrayHasKeysStrictTrue() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => "value4",
            ]
        ];
        $result = array_has_keys($input, ["key1", "key2" => ["key3","key4"]], false, true);
        $this->assertTrue($result);
    }

    public function testArrayHasKeysStrictTrueAsList() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                ["key3" => "value3"],
                ["key3" => "value4"],
            ]
        ];
        $result = array_has_keys($input, ["key1", "key2" => ["key3"]], true, true);
        $this->assertTrue($result);
    }
    
    public function testArrayHasKeysStrictFalse() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3",
                "key4" => "value4",
            ]
        ];
        $result = array_has_keys($input, ["key1", "key2" => ["key3"]], false, true);
        $this->assertFalse($result);
    }

    public function testArrayHasKeysStrictFalseAsList() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                ["key3" => "value3"],
                ["key4" => "value4"],
            ]
        ];
        $result = array_has_keys($input, ["key1", "key2" => ["key3"]], false, true);
        $this->assertFalse($result);
    }

    public function testArrayHasKeysStrictFalseAsListThrows() 
    {
        $this->expectExceptionMessage("array_has_keys failed: [missing => [], invalid => [key2 => [0 => key4]]]");

        $input = [
            "key1" => "value1",
            "key2" => [
                ["key3" => "value3", "key4" => "value3"],
            ]
        ];
        $result = array_has_keys($input, ["key1", "key2" => ["key3"]], true, true, true);
    }
}