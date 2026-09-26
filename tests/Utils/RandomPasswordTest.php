<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class RandomPasswordTest extends TestCase
{
    public function testRandomPasswordLength()
    {
        $this->assertSame(16, strlen(random_password(16)));
    }

    public function testRandomPasswordContainsRequiredCharacterClasses()
    {
        for($i = 0; $i < 50; $i++)
        {
            $result = random_password(3);
            $this->assertMatchesRegularExpression("/[a-zA-Z]/", $result);
            $this->assertMatchesRegularExpression("/[0-9]/", $result);
            $this->assertMatchesRegularExpression("/[^a-zA-Z0-9]/", $result);
        }
    }

    # @bugfix symbol pool contained literal backslashes from "\-", "\[" and "\/"
    public function testRandomPasswordHasNoBackslash()
    {
        for($i = 0; $i < 50; $i++)
            $this->assertStringNotContainsString("\\", random_password(32));
    }

    # @bugfix first three characters were always letter, digit, symbol
    public function testRandomPasswordIsShuffled()
    {
        $prefixes = [];
        for($i = 0; $i < 50; $i++)
            $prefixes[] = preg_match("/^[a-zA-Z][0-9][^a-zA-Z0-9]/", random_password(12));

        $this->assertContains(0, $prefixes);
    }

    public function testRandomPasswordTooShort()
    {
        $this->expectException(\InvalidArgumentException::class);
        random_password(2);
    }
}
