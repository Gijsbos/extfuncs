<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class GetClientIpTest extends TestCase 
{
    public function testGetClientIp()
    {
        unset($_SERVER["REMOTE_ADDR"]);
        $result = get_client_ip();
        $expectedResult = getHostByName(getHostName());
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetClientIpRemoteAddr()
    {
        $_SERVER["REMOTE_ADDR"] = "127.0.0.1";
        $result = get_client_ip();
        $expectedResult = "127.0.0.1";
        $this->assertEquals($expectedResult, $result);
    }
}