<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class GetUserAgentTest extends TestCase 
{
    public function testGetUserAgent()
    {
        $_SERVER['HTTP_USER_AGENT'] = "User Agent";
        $result = get_user_agent();
        $expectedResult = "User Agent";
        $this->assertEquals($expectedResult, $result);
    }
}