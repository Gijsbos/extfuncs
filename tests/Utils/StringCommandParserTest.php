<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;
use PHPUnit\Framework\TestCase;

final class StringCommandParserTest extends TestCase
{
    const TEST_CONSTANT = "constant-value";

    public function testIsCommandSyntax()
    {
        $this->assertTrue(StringCommandParser::isCommandSyntax("<int;1>"));
        $this->assertFalse(StringCommandParser::isCommandSyntax("int;1"));
    }

    public function testParseScalars()
    {
        $this->assertSame("hello", StringCommandParser::parse("<string;'hello'>"));
        $this->assertSame("hello", StringCommandParser::parse("<string;hello>"));
        $this->assertSame(5, StringCommandParser::parse("<int;5>"));
        $this->assertSame(1.5, StringCommandParser::parse("<float;1.5>"));
        $this->assertNull(StringCommandParser::parse("<null>"));
    }

    # @bugfix boolval("false") returned true
    public function testParseBool()
    {
        $this->assertTrue(StringCommandParser::parse("<bool;true>"));
        $this->assertTrue(StringCommandParser::parse("<boolean;1>"));
        $this->assertFalse(StringCommandParser::parse("<bool;false>"));
        $this->assertFalse(StringCommandParser::parse("<bool;0>"));
    }

    public function testParseConstant()
    {
        $this->assertSame(PHP_INT_SIZE, StringCommandParser::parse("<constant;PHP_INT_SIZE>"));
        $this->assertSame(self::TEST_CONSTANT, StringCommandParser::parse("<constant;" . self::class . "::TEST_CONSTANT>"));
    }

    public function testParseRandomInt()
    {
        $result = StringCommandParser::parse("<random;int;5;10>");
        $this->assertGreaterThanOrEqual(5, $result);
        $this->assertLessThanOrEqual(10, $result);
    }

    # @bugfix random int/float without range threw a TypeError
    public function testParseRandomWithoutRange()
    {
        $this->assertIsInt(StringCommandParser::parse("<random;int>"));

        $result = StringCommandParser::parse("<random;float>");
        $this->assertGreaterThanOrEqual(0.0, $result);
        $this->assertLessThanOrEqual(1.0, $result);
    }

    # @bugfix random array returned the whole array instead of an item
    public function testParseRandomArray()
    {
        $this->assertContains(StringCommandParser::parse("<random;array;['a','b']>"), ["a", "b"]);
        $this->assertContains(StringCommandParser::parse("<random;array;'a','b'>"), ["a", "b"]);
    }

    # @bugfix random date passed the remaining arguments array instead of the dates
    public function testParseRandomDate()
    {
        $result = StringCommandParser::parse("<random;date;-2 days;-1 day;Y-m-d>");
        $this->assertContains($result, [
            (new \DateTime("-2 days"))->format("Y-m-d"),
            (new \DateTime("-1 day"))->format("Y-m-d"),
        ]);
    }

    public function testParseRandomIp()
    {
        $this->assertNotFalse(filter_var(StringCommandParser::parse("<random;ip;v4>"), FILTER_VALIDATE_IP));
    }

    public function testParseRandomUnknown()
    {
        $this->expectException(Exception::class);
        StringCommandParser::parse("<random;unknown>");
    }

    public function testParseToken()
    {
        $this->assertMatchesRegularExpression("/^[a-f0-9]{32}$/", StringCommandParser::parse("<token>"));
        $this->assertMatchesRegularExpression("/^[a-f0-9]{8}$/", StringCommandParser::parse("<token;8>"));
    }

    public function testParseUuid4()
    {
        $this->assertTrue(is_uuid4(StringCommandParser::parse("<uuid4>")));
    }

    public function testParseDefuseKey()
    {
        $this->assertNotEmpty(StringCommandParser::parse("<defuse-crypto-key>"));
    }

    public function testParseDate()
    {
        $this->assertSame((new \DateTime("+1 day"))->format("Y-m-d"), StringCommandParser::parse("<date;+1 day;Y-m-d>"));
        $this->assertMatchesRegularExpression("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/", StringCommandParser::parse("<date>"));
    }

    public function testParseFunction()
    {
        $this->assertSame("ABC", StringCommandParser::parse("<function;strtoupper;'abc'>"));
    }

    public function testParseUnknownCommandReturnsInput()
    {
        $this->assertSame("<unknown;x>", StringCommandParser::parse("<unknown;x>"));
    }
}
