<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayPopAssocTest extends TestCase 
{
    public function testArrayPopAssoc()
    {
        $array = ["userId" => 1, "name" => "John"];
        $item = array_pop_assoc($array);
        $result = $item === ["name" => "John"] && $array === ["userId" => 1];
        $this->assertTrue($result);
    }

    public function testArrayPopAssocEmpty()
    {
        $array = [];
        $item = array_pop_assoc($array);
        $result = $item === null && $array === [];
        $this->assertTrue($result);
    }
}