<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ParseArrayStringTest extends TestCase 
{
    public function testParseArrayString()
    {
        $args = 'CURLINFO_PRIMARY_IP, "limit", array("min_range" => 1, "max_range" => 2), @$args["limit"]';
        $result = parse_array_string($args);
        $expectedResult = array(
            CURLINFO_PRIMARY_IP,
            "limit",
            array(
                "min_range" => 1,
                "max_range" => 2
            ),
            "@\$args[\"limit\"]",
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayStringSingleQuoteEscape()
    {
        $args = "'argument 1, with \' a comma','argument 2', 3, CURLINFO_PRIMARY_IP";
        $result = parse_array_string($args);
        $expectedResult = array(
            "argument 1, with \' a comma",
            "argument 2",
            3,
            CURLINFO_PRIMARY_IP
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayStringDoubleQuoteEscape()
    {
        $args = '"argument 1, with \" a comma","argument 2", 3, CURLINFO_PRIMARY_IP';
        $result = parse_array_string($args);
        $expectedResult = array(
            'argument 1, with \" a comma',
            "argument 2",
            3,
            CURLINFO_PRIMARY_IP
        );
        $this->assertEquals($expectedResult, $result);
    }

    # Bug while build/dev
    public function testParseArrayStringBugFix1()
    {
        $result = parse_array_string("CURLINFO_PRIMARY_IP, 3");
        $expectedResult = array(
            CURLINFO_PRIMARY_IP, 3
        );
        $this->assertEquals($expectedResult, $result);
    }

    # Bug Double quotes not parsed right
    public function testParseArrayStringBugFix2()
    {
        $result = parse_array_string('",", array("hoi","doei")');
        $expectedResult = array(
            ",",
            array("hoi","doei")
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayStringArray()
    {
        $args = "array(0,1),1,'test',CURLINFO_PRIMARY_IP";
        $result = parse_array_string($args);
        $expectedResult = array(
            array(0,1),
            1,
            'test',
            CURLINFO_PRIMARY_IP
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayStringArrayOfStrings()
    {
        $args = '"test","value"';
        $result = parse_array_string($args);
        $expectedResult = array(
            "test",
            "value",
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayStringInlineArray()
    {
        $args = "CURLINFO_PRIMARY_IP, ['TV', 'Phone', 'Speakers']";
        $result = parse_array_string($args);
        $expectedResult = array(
            CURLINFO_PRIMARY_IP,
            ['TV', 'Phone', 'Speakers'],
        );
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayStringAssoc()
    {
        $args = '"key1" => "value1", "key2" => "value2"';
        $result = parse_array_string($args);
        $expectedResult = array(
            "key1" => "value1",
            "key2" => "value2",
        );
        $this->assertEquals($expectedResult, $result);
    }
}