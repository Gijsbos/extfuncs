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

    # @bugfix uppercase UUIDs were rejected
    public function testIsUuid4Uppercase()
    {
        $this->assertTrue(\is_uuid4("FD056932-63E0-4BE5-BEF0-7217D4B0F6E0"));
    }

    public function testIsUuid4Invalid()
    {
        $this->assertFalse(\is_uuid4("fd056932-63e0-1be5-bef0-7217d4b0f6e0")); // version 1
        $this->assertFalse(\is_uuid4("fd056932-63e0-4be5-cef0-7217d4b0f6e0")); // invalid variant
        $this->assertFalse(\is_uuid4("fd056932-63e0-4be5-bef0-7217d4b0f6e0\n")); // trailing newline
        $this->assertFalse(\is_uuid4(""));
    }
}