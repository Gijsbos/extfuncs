<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class SortListStringTest extends TestCase 
{
    public function testSortListStringAsc()
    {
        $input = "last|veryfirst|second";
        $result = sort_list_string($input, "|", false);
        $expectedResult = "last|second|veryfirst";
        $this->assertEquals($expectedResult, $result);
    }

    public function testSortListStringDesc()
    {
        $input = "last|veryfirst|second";
        $result = sort_list_string($input, "|", true);
        $expectedResult = "veryfirst|second|last";
        $this->assertEquals($expectedResult, $result);
    }
}