<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class EnvTest extends TestCase
{
    public function testEnvFromGetenv()
    {
        putenv("EXTFUNCS_TEST_GETENV=value");
        $this->assertSame("value", env("EXTFUNCS_TEST_GETENV"));
        putenv("EXTFUNCS_TEST_GETENV");
    }

    public function testEnvFromEnvArray()
    {
        $_ENV["EXTFUNCS_TEST_ENV"] = "value";
        $this->assertSame("value", env("EXTFUNCS_TEST_ENV"));
        unset($_ENV["EXTFUNCS_TEST_ENV"]);
    }

    public function testEnvMissing()
    {
        $this->assertFalse(env("EXTFUNCS_TEST_MISSING"));
        $this->assertFalse(env("EXTFUNCS_TEST_MISSING", false));
    }

    public function testEnvMissingThrows()
    {
        $this->expectException(\Exception::class);
        env("EXTFUNCS_TEST_MISSING", true);
    }

    public function testEnvMissingThrowsCustom()
    {
        $this->expectException(\RuntimeException::class);
        env("EXTFUNCS_TEST_MISSING", new \RuntimeException());
    }

    public function testFlagId()
    {
        $domain = "extfuncs_test_" . random_token(8);
        $this->assertSame(1, flag_id($domain));
        $this->assertSame(2, flag_id($domain));
        $this->assertSame(4, flag_id($domain));
    }

    public function testFlagIdRangeExceeded()
    {
        $domain = "extfuncs_test_%s_" . random_token(8);

        for($i = 0; $i < PHP_INT_SIZE * 8 - 1; $i++)
            flag_id($domain);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Flag range exceeded for domain '$domain'");
        flag_id($domain);
    }
}
