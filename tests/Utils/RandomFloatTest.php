<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomFloatTest extends TestCase 
{
    public function testRandomFloat()
    {
        $arg1 = 0;
        $arg2 = 2;
        $result = random_float($arg1, $arg2);
        $expectedResult = $result >= $arg1 && $result <= $arg2;
        $this->assertTrue($expectedResult);
    }
}