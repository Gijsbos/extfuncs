<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class IsJsonTest extends TestCase 
{
    public function testIsJsonTrue()
    {
        $input = json_encode(array("test"));
        $result = \is_json($input);
        $this->assertTrue($result);
    }

    public function testIsJsonFalseArray()
    {
        $input = array("test");
        $result = \is_json($input);
        $this->assertFalse($result);
    }

    public function testIsJsonFalseString()
    {
        $input = "test";
        $result = \is_json($input);
        $this->assertFalse($result);
    }

    # @bugfix is_json returns true on string input "null"
    public function testIsJsonFalseNull()
    {
        $input = "null";
        $result = \is_json($input);
        $this->assertFalse($result);
    }
}