<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomArrayItemTest extends TestCase 
{
    public function testRandomArray()
    {
        $arg1 = array("item1", "item2");
        $result = random_array_item($arg1);
        $expectedResult = $result == "item1" || $result === "item2";
        $this->assertTrue($expectedResult);
    }
}