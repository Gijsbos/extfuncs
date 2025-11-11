<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ParseStringValueTest extends TestCase 
{
    public function testParseStringValueDouble()
    {
        $string = '"hi"';
        $result = parse_string_value($string);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringValueNullInput()
    {
        $string = null;
        $result = parse_string_value($string);
        $expectedResult = null;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringValueSingle()
    {
        $string = "'hi'";
        $result = parse_string_value($string);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringValueSpecial()
    {
        $string = '`hi`';
        $result = parse_string_value($string);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringValueAssumeString()
    {
        $string = "hi";
        $result = parse_string_value($string);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringConstant()
    {
        $string = "CURLINFO_PRIMARY_IP";
        $result = parse_string_value($string);
        $expectedResult = CURLINFO_PRIMARY_IP;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseClassConstantClassNotFound()
    {
        $this->expectExceptionMessage("Could not parse class constant/static property 'AppNotFound::REQUIRED_APP_KEYS', class 'AppNotFound' does not exist");
        $string = "AppNotFound::REQUIRED_APP_KEYS";
        $result = parse_string_value($string);
    }

    public function testParseClassConstantPropertyNotFound()
    {
        $this->expectExceptionMessage("Could not parse class constant/static property 'gijsbos\ExtFuncs\Utils\App::REQUIRED_APP_KEYS', constant/property 'REQUIRED_APP_KEYS' does not exist");
        $string = "gijsbos\ExtFuncs\Utils\App::REQUIRED_APP_KEYS";
        $result = parse_string_value($string);
    }

    public function testParseClassConstant()
    {
        $string = "ReflectionClass::IS_EXPLICIT_ABSTRACT";
        $result = parse_string_value($string);
        $expectedResult = ReflectionClass::IS_EXPLICIT_ABSTRACT;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringNumericInt()
    {
        $string = "1";
        $result = parse_string_value($string);
        $expectedResult = 1;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringNumericFloat()
    {
        $string = "1.1";
        $result = parse_string_value($string);
        $expectedResult = 1.1;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringTrue()
    {
        $string = "true";
        $result = parse_string_value($string);
        $expectedResult = true;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringFalse()
    {
        $string = "false";
        $result = parse_string_value($string);
        $expectedResult = false;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringNull()
    {
        $string = "null";
        $result = parse_string_value($string);
        $expectedResult = null;
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringArray()
    {
        $string = "array('test1','test2')";
        $result = parse_string_value($string);
        $expectedResult = array("test1","test2");
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringInlineArray()
    {
        $string = "['test1','test2']";
        $result = parse_string_value($string);
        $expectedResult = array("test1","test2");
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringArrayAssoc()
    {
        $string = "array('key' => 'value')";
        $result = parse_string_value($string);
        $expectedResult = array("key" => "value");
        $this->assertEquals($expectedResult, $result);
    }

    public function testParseStringValue()
    {
        $string = "'hi'";
        $result = parse_string_value($string);
        $expectedResult = "hi";
        $this->assertEquals($expectedResult, $result);
    }
}