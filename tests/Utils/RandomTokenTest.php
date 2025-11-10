<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomTokenTest extends TestCase 
{
    public function testRandomToken()
    {
        $result = random_token(10);
        $expectedResult = preg_match("/[a-z0-9]{10}/", $result) === 1;
        $this->assertTrue($expectedResult);
    }
}