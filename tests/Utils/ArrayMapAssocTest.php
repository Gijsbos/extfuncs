<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayMapAssocTest extends TestCase 
{
    public function testArrayMapAssoc()
    {
        $input = array(
            "value-1", "value-2"
        );
        $expectedResult = array(
            "key-0" => "value-1",
            "key-1" => "value-2"
        );
        $result = array_map_assoc(function($key, $value)
        {
            return array("key-" . $key, $value);
        }, $input);
        $this->assertEquals($expectedResult, $result);
    }
}