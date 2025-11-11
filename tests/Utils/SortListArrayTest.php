<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class SortListArrayTest extends TestCase 
{
    public function testSortListArrayAsc()
    {
        $input = explode("|", "last|veryfirst|second");
        $result = sort_list_array($input, false);
        $expectedResult = explode("|", "last|second|veryfirst");
        $this->assertEquals($expectedResult, $result);
    }

    public function testSortListArrayDesc()
    {
        $input = explode("|", "last|veryfirst|second");
        $result = sort_list_array($input, true);
        $expectedResult = explode("|", "veryfirst|second|last");
        $this->assertEquals($expectedResult, $result);
    }
}