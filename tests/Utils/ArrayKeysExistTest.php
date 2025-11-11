<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayKeysExistTest extends TestCase 
{
    public function testArrayKeysExist() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3"
            ]
        ];
        $result = array_keys_exist(["key1","key2"=>["key3"]], $input);
        $this->assertTrue($result);
    }

    public function testArrayKeysExistAsListSequential() 
    {
        $input = [
            ["key3" => "value1"],
            ["key3" => "value2"],
            ["key3" => "value3"],
        ];
        $result = array_keys_exist(["key3"], $input, true);
        $this->assertTrue($result);
    }

    public function testArrayKeysExistAsListMixed() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                ["key3" => "value1"],
                ["key3" => "value2"],
                ["key3" => "value3"],
            ]
        ];
        $result = array_keys_exist(["key1","key2"=>["key3"]], $input, true);
        $this->assertTrue($result);
    }
    
    public function testArrayKeysExistAsListFalse() 
    {
        $input = [
            ["key1" => "value1", "key2" => "value2"],
            ["key1" => "value3"],
        ];
        $result = array_keys_exist($input, ["key1","key2"], true);
        $expectedResult = false;
        $this->assertEquals($expectedResult, $result);
    }

    public function testArrayKeysExist1() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3"
            ]
        ];
        $result = array_keys_exist(["key1"], $input);
        $this->assertTrue($result);
    }
    
    public function testArrayKeysExist2() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3"
            ]
        ];
        $result = array_keys_exist(["key2"=>["key3"]], $input);
        $this->assertTrue($result);
    }

    public function testArrayKeysExistFalse1() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3"
            ]
        ];
        $result = array_keys_exist(["key0"], $input);
        $this->assertFalse($result);
    }

    public function testArrayKeysExistFalse2() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3"
            ]
        ];
        $result = array_keys_exist(["key2"=>["key3","key4"]], $input);
        $this->assertFalse($result);
    }
}