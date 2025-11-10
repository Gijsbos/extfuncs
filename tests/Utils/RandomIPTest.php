<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomIPTest extends TestCase 
{
    public function testRandomIp()
    {
        $ip = random_ip();
        $result = preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $ip) == 1;
        $this->assertTrue($result);
    }
}