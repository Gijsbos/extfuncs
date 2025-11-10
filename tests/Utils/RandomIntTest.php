<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomIntTest extends TestCase 
{
    public function testRandomInt()
    {
        $arg1 = 1;
        $arg2 = 5;
        $result = random_int($arg1, $arg2);
        $expectedResult = $result >= $arg1 && $result <= $arg2;
        $this->assertTrue($expectedResult);
    }
}