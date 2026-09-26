<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayOptionTest extends TestCase 
{
    public function testArrayOption()
    {
        $array = ["showErrors" => true];
        $result = array_option("showErrors", $array);
        $this->assertTrue($result);
    }

    public function testArrayOptionDefault()
    {
        $this->assertSame("default", array_option("missing", ["key" => 1], "default"));
        $this->assertSame("default", array_option("missing", null, "default"));
    }

    public function testArrayOptionThrowsClassName()
    {
        $this->expectException(\InvalidArgumentException::class);
        array_option("missing", [], false, \InvalidArgumentException::class);
    }

    # @bugfix exception instances were re-instantiated, losing their message
    public function testArrayOptionThrowsInstance()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("custom message");
        array_option("missing", [], false, new \InvalidArgumentException("custom message"));
    }
}