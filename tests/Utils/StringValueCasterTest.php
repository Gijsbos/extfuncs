<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class StringValueCasterTest extends TestCase
{
    public function testCastInt()
    {
        $this->assertSame(10, StringValueCaster::cast("10"));
        $this->assertSame(-2, StringValueCaster::cast("-2"));
        $this->assertSame(7, StringValueCaster::cast("007"));
    }

    # @bugfix floats were cast to int, e.g. "1.5" became 1
    public function testCastFloat()
    {
        $this->assertSame(1.5, StringValueCaster::cast("1.5"));
        $this->assertSame(-0.25, StringValueCaster::cast("-0.25"));
        $this->assertSame(1.0, StringValueCaster::cast("1.0"));
        $this->assertSame(1000.0, StringValueCaster::cast("1e3"));
    }

    # @bugfix integers beyond PHP_INT_MAX were clamped to PHP_INT_MAX
    public function testCastIntOverflowKeepsString()
    {
        $this->assertSame("99999999999999999999", StringValueCaster::cast("99999999999999999999"));
        $this->assertSame(PHP_INT_MAX, StringValueCaster::cast((string) PHP_INT_MAX));
    }

    public function testCastQuotedString()
    {
        $this->assertSame("1.5", StringValueCaster::cast('"1.5"'));
        $this->assertSame("true", StringValueCaster::cast("'true'"));
    }

    # @bugfix empty quoted strings kept their quotes
    public function testCastEmptyQuotedString()
    {
        $this->assertSame("", StringValueCaster::cast('""'));
        $this->assertSame("", StringValueCaster::cast("''"));
    }

    public function testCastSingleQuoteCharacter()
    {
        $this->assertSame('"', StringValueCaster::cast('"'));
    }

    public function testCastBoolean()
    {
        $this->assertTrue(StringValueCaster::cast("true"));
        $this->assertFalse(StringValueCaster::cast("FALSE"));
    }

    public function testCastNull()
    {
        $this->assertNull(StringValueCaster::cast("null"));
    }

    public function testCastConstant()
    {
        $this->assertSame(PHP_INT_SIZE, StringValueCaster::cast("PHP_INT_SIZE"));
    }

    public function testCastString()
    {
        $this->assertSame("hello world", StringValueCaster::cast("hello world"));
    }
}
