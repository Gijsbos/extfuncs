<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class GetUriTest extends TestCase 
{
    public function testGetURI()
    {
        $_SERVER["HTTPS"] = "on";
        $_SERVER["HTTP_HOST"] = "localhost/";
        $_SERVER["REQUEST_URI"] = "website/";
        $result = get_uri();
        $expectedResult = "https://localhost/website/";
        $this->assertEquals($expectedResult, $result);
    }

    public function testGetURIUseHTTPS()
    {
        $_SERVER["HTTPS"] = "off";
        $_SERVER["HTTP_HOST"] = "localhost/";
        $_SERVER["REQUEST_URI"] = "website/";
        $result = get_uri(true);
        $expectedResult = "https://localhost/website/";
        $this->assertEquals($expectedResult, $result);
    }
}