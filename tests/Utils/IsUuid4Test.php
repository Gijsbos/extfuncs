<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class IsUuid4Test extends TestCase 
{
    public function testIsUuid4()
    {
        $uuid4 = "fd056932-63e0-4be5-bef0-7217d4b0f6e0";
        $result = \is_uuid4($uuid4);
        $this->assertTrue($result);
    }

    public function testUuid4()
    {
        $uuid4 = uuid4();
        $result = \is_uuid4($uuid4);
        $this->assertTrue($result);
    }
}