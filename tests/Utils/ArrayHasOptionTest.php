<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayHasOptionTest extends TestCase 
{
    public function testArrayHasOption()
    {
        $array = ["showErrors" => true];
        $result = array_has_option("showErrors", $array);
        $this->assertTrue($result);
    }
}