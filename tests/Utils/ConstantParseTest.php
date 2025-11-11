<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ConstantParseTest extends TestCase 
{
    public function testConstantParse()
    {
        $input = "CURL_IPRESOLVE_V4";
        $result = constant_parse($input);
        $expectedResult = CURL_IPRESOLVE_V4;
        $this->assertEquals($expectedResult, $result);
    }

    public function testConstantParseAnd()
    {
        $input = "CURL_IPRESOLVE_V4 & CURL_IPRESOLVE_V6";
        $result = constant_parse($input);
        $expectedResult = CURL_IPRESOLVE_V4 & CURL_IPRESOLVE_V6;
        $this->assertEquals($expectedResult, $result);
    }

    public function testConstantParseOr()
    {
        $input = "CURL_IPRESOLVE_V4 | CURL_IPRESOLVE_V6";
        $result = constant_parse($input);
        $expectedResult = CURL_IPRESOLVE_V4 | CURL_IPRESOLVE_V6;
        $this->assertEquals($expectedResult, $result);
    }

    public function testConstantParseParentheses()
    {
        $input = "(CURL_IPRESOLVE_V4 | CURL_IPRESOLVE_V6)";
        $result = constant_parse($input);
        $expectedResult = CURL_IPRESOLVE_V4 | CURL_IPRESOLVE_V6;
        $this->assertEquals($expectedResult, $result);
    }

    public function testConstantParseParenthesesMulti()
    {
        $input = "CURL_IPRESOLVE_WHATEVER | (CURL_IPRESOLVE_V4 | CURL_IPRESOLVE_V6)";
        $result = constant_parse($input);
        $expectedResult = CURL_IPRESOLVE_WHATEVER | (CURL_IPRESOLVE_V4 | CURL_IPRESOLVE_V6);
        $this->assertEquals($expectedResult, $result);
    }

    public function testConstantParseNested()
    {
        $input = "CURLINFO_PRIMARY_IP | (CURL_IPRESOLVE_WHATEVER | (CURL_IPRESOLVE_V4 & CURL_IPRESOLVE_V6))";
        $result = constant_parse($input);
        $expectedResult = CURLINFO_PRIMARY_IP | (CURL_IPRESOLVE_WHATEVER | (CURL_IPRESOLVE_V4 & CURL_IPRESOLVE_V6));
        $this->assertEquals($expectedResult, $result);
    }
}