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

    # @bugfix positions of later groups shifted after replacing the first group
    public function testConstantParseMultipleGroups()
    {
        $input = "(E_ERROR | E_WARNING) | (E_NOTICE | E_DEPRECATED)";
        $result = constant_parse($input);
        $expectedResult = (E_ERROR | E_WARNING) | (E_NOTICE | E_DEPRECATED);
        $this->assertEquals($expectedResult, $result);
    }

    public function testConstantParseUndefined()
    {
        $this->expectException(\Exception::class);
        constant_parse("E_ERROR | NO_SUCH_CONSTANT");
    }
}