<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class HasherTest extends TestCase 
{
    public function testSha512() 
    {
        $hash1 = Hasher::sha512("test");
        $hash2 = Hasher::sha512("test");
        $this->assertEquals($hash1, $hash2);
    }

    public function testSha256() 
    {
        $hash1 = Hasher::sha256("test");
        $hash2 = Hasher::sha256("test");
        $this->assertEquals($hash1, $hash2);
    }
}