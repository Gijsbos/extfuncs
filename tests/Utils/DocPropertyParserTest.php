<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Doc property fixture
 * Contact: me@example.com
 *
 * @route /users
 * @version 1.5
 * @limit 10
 * @enabled true
 * @method GET
 * @method POST
 * @method PUT
 * @deprecated
 */
class DocPropertyParserFixture
{
    /**
     * @column name
     */
    public $name;

    /**
     * @action list
     */
    public function list() {}
}

class DocPropertyParserNoComment {}

final class DocPropertyParserTest extends TestCase
{
    public function testParse()
    {
        $result = DocPropertyParser::parse(DocPropertyParserFixture::class);

        $this->assertSame("/users", $result["route"]);
        $this->assertSame(1.5, $result["version"]);
        $this->assertSame(10, $result["limit"]);
        $this->assertTrue($result["enabled"]);
        $this->assertSame("", $result["deprecated"]);
    }

    public function testParseRepeatedKeyBecomesArray()
    {
        $result = DocPropertyParser::parse(DocPropertyParserFixture::class);
        $this->assertSame(["GET", "POST", "PUT"], $result["method"]);
    }

    # @bugfix "@" inside a description line was parsed as a property
    public function testParseIgnoresInlineAt()
    {
        $result = DocPropertyParser::parse(DocPropertyParserFixture::class);
        $this->assertArrayNotHasKey("example", $result);
        $this->assertSame(["route", "version", "limit", "enabled", "method", "deprecated"], array_keys($result));
    }

    # @bugfix object instances threw "Call to undefined method getDocComment()"
    public function testParseObjectInstance()
    {
        $result = DocPropertyParser::parse(new DocPropertyParserFixture());
        $this->assertSame("/users", $result["route"]);
    }

    public function testParseReflectors()
    {
        $this->assertSame(["column" => "name"], DocPropertyParser::parse(new \ReflectionProperty(DocPropertyParserFixture::class, "name")));
        $this->assertSame(["action" => "list"], DocPropertyParser::parse(new \ReflectionMethod(DocPropertyParserFixture::class, "list")));
    }

    public function testParseCustomParser()
    {
        $result = DocPropertyParser::parse(DocPropertyParserFixture::class, [
            "route" => fn($value, $key) => "$key:$value",
        ]);
        $this->assertSame("route:/users", $result["route"]);
    }

    public function testParseNoDocComment()
    {
        $this->assertSame([], DocPropertyParser::parse(DocPropertyParserNoComment::class));
    }

    public function testParseUnknownClass()
    {
        $this->expectException(InvalidArgumentException::class);
        DocPropertyParser::parse("NoSuchClass");
    }
}
