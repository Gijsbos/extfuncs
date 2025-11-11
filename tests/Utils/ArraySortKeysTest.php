<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArraySortKeysTest extends TestCase 
{
    public function testArraySortKeys()
    {
        $input = [
            0 => "value1",
            "assoc" => "value2",
            3 => "value3"
        ];
        $result = array_sort_keys($input);
        $expectedResult = [
            0 => "value1",
            1 => "value3",
            "assoc" => "value2",
        ];
        $this->assertEquals($expectedResult, $result);
    } 
}