<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayToAssocTest extends TestCase 
{
    public function testKeysArrayToAssoc() 
    {
        $input = [
            "key1" => "value1",
            "key2" => [
                "key3" => "value3"
            ]
        ];
        $keysArray = array_get_keys($input);
        $result = keys_array_to_assoc($keysArray);
        $expectedResult = [
            "key1" => "key1",
            "key2" => [
                "key3" => "key3"
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }
}