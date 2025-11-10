<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayInArrayTest extends TestCase 
{
    public function testArrayInArray()
    {
        $needle = ["v1","v2"];
        $haystack = ["v1","v2","v3"];
        $result = array_in_array($haystack, $needle);
        $this->assertTrue($result);
    }

    public function testArrayInArrayFalse()
    {
        $needle = ["v1","v4"];
        $haystack = ["v1","v2","v3"];
        $result = array_in_array($haystack, $needle);
        $this->assertFalse($result);
    }
}