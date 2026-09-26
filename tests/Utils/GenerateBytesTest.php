<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class GenerateBytesTest extends TestCase
{
    public function testGenerateBytes()
    {
        $this->assertSame(32, strlen(generate_bytes(32)));
        $this->assertNotSame(generate_bytes(32), generate_bytes(32));
    }

    public function testGenerateBytesZero()
    {
        $this->assertSame("", generate_bytes(0));
    }

    public function testRandomTokenLengths()
    {
        foreach([0, 1, 7, 8, 33] as $length)
            $this->assertMatchesRegularExpression("/^[a-f0-9]{{$length}}$/", random_token($length));
    }

    public function testRandomStringUsesPool()
    {
        $this->assertMatchesRegularExpression("/^[ab]{20}$/", random_string(20, "ab"));
        $this->assertSame("", random_string(0));
    }
}
