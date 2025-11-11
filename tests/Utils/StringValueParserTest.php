<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class StringValueParserTest extends TestCase 
{
    public function testParseTrue()
    {
        $input = "true";
        $result = StringValueParser::parse($input);
        $expectedResult = true;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseFalse()
    {
        $input = "false";
        $result = StringValueParser::parse($input);
        $expectedResult = false;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseNull1()
    {
        $input = "null";
        $result = StringValueParser::parse($input);
        $expectedResult = null;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseNull2()
    {
        $input = "NULL";
        $result = StringValueParser::parse($input);
        $expectedResult = null;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseInt()
    {
        $input = "1";
        $result = StringValueParser::parse($input);
        $expectedResult = 1;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseFloat()
    {
        $input = "1.1";
        $result = StringValueParser::parse($input);
        $expectedResult = 1.1;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringSingleQuotes()
    {
        $input = "'this is a string'";
        $result = StringValueParser::parse($input);
        $expectedResult = "this is a string";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringSpecialQuotes()
    {
        $input = "`this is a string`";
        $result = StringValueParser::parse($input);
        $expectedResult = "this is a string";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringDoubleQuotes()
    {
        $input = '"this is a string"';
        $result = StringValueParser::parse($input);
        $expectedResult = "this is a string";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArray()
    {
        $input = "array('strval' => 'str', 'intval' => 1, 'boolval' => true, 'arrayval' => ['item1', 'item2'] )";
        $result = StringValueParser::parse($input);
        $expectedResult = [
            "strval" => "str",
            "intval" => 1,
            "boolval" => true,
            "arrayval" => [
                "item1", "item2"
            ]
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayEmptyArray()
    {
        $input = "array()";
        $result = StringValueParser::parse($input);
        $expectedResult = [];
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayEmpty()
    {
        $input = "[]";
        $result = StringValueParser::parse($input);
        $expectedResult = [];
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseMultiArrayEmpty()
    {
        $input = "[value=>[],'string value']";
        $result = StringValueParser::parse($input);
        $expectedResult = ["value" => [], "string value"];
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayMergeArray()
    {
        $input = "array('strval' => 'str', 'intval' => 1, 'boolval' => true, 'arrayval' => ['item1', 'item2'] ) | ['strval' => 'newstr', 'merge']";
        $result = StringValueParser::parse($input);
        $expectedResult = [
            "strval" => "newstr",
            "intval" => 1,
            "boolval" => true,
            "arrayval" => [
                "item1", "item2"
            ],
            "merge"
        ];
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseArrayHandler()
    {
        $input = "Hello";
        $result = StringValueParser::parse($input, [
            function(&$input)
            {
                if($input === "Hello")
                {
                    $input = "Hello World";

                    return true;
                }
                return false;
            }
        ]);
        $expectedResult = "Hello World";
        $this->assertEquals($expectedResult, $result);
    }
}