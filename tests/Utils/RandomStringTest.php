<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomStringTest extends TestCase 
{
    public function testRandomString()
    {
        $length = 2;
        $characterPool = "ab";
        $result = random_string($length, $characterPool);
        $expectedResult = preg_match("/[ab]{2}/", $result) === 1;
        $this->assertTrue($expectedResult);
    }

    public function testRandomStringDefaultCharacterPool()
    {
        $length = 2;
        $result = random_string($length);
        $expectedResult = preg_match("/[a-zA-Z]{2}/", $result) === 1;
        $this->assertTrue($expectedResult);
    }
}