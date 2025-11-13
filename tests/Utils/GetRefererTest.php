<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class GetRefererTest extends TestCase 
{
    public function testGetRefererWithQuery()
    {
        $_SERVER['HTTP_REFERER'] = "https://www.testwebsite.com/path?query=value";
        $result = get_referer(true);
        $expectedResult = "https://www.testwebsite.com/path?query=value";
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetRefererFull()
    {
        $_SERVER['HTTP_REFERER'] = "https://www.testwebsite.com/path?query=value";
        $result = get_referer(false);
        $expectedResult = "https://www.testwebsite.com/path";
        $this->assertEquals($expectedResult, $result);
    }
}