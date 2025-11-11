<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ArrayShiftAssocTest extends TestCase 
{
    public function testArrayShiftAssoc()
    {
        $array = ["userId" => 1, "name" => "John"];
        $item = array_shift_assoc($array);
        $result = $item === ["userId" => 1] && $array === ["name" => "John"];
        $this->assertTrue($result);
    }

    public function testArrayShiftAssocEmpty()
    {
        $array = [];
        $item = array_shift_assoc($array);
        $result = $item === null && $array === [];
        $this->assertTrue($result);
    }
}