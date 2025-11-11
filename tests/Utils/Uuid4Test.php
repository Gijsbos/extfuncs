<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class Uuid4Test extends TestCase 
{
    public function testUuid4()
    {
        $uuid4 = uuid4();
        $result = \is_uuid4($uuid4);
        $this->assertTrue($result);
    }
}