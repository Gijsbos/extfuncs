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
}