<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use gijsbos\ExtFuncs\Exceptions\DotEnvException;
use PHPUnit\Framework\TestCase;

final class DotEnvTest extends TestCase
{
    private string $cwd;
    private string $dir;

    /**
     * DotEnv works on ./.env; run in a temp dir so the project .env is never touched
     */
    protected function setUp() : void
    {
        $this->cwd = getcwd();
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "extfuncs_dotenv_" . random_token(12);
        mkdir($this->dir);
        chdir($this->dir);
    }

    protected function tearDown() : void
    {
        chdir($this->cwd);
        rmdir_recursive($this->dir);

        foreach(["DOTENV_TEST_A", "DOTENV_TEST_B", "DOTENV_TEST_KEY", "MY_DOTENV_TEST_KEY"] as $key)
            DotEnv::unregister($key);
    }

    public function testParseEnvTextCastsValues()
    {
        $result = DotEnv::parseEnvText("VERSION=1.5\nPASSWORD=\"\"\nID=99999999999999999999\nPORT=8080\nNAME=app", false);
        $expectedResult = [
            "VERSION" => 1.5,
            "PASSWORD" => "",
            "ID" => "99999999999999999999",
            "PORT" => 8080,
            "NAME" => "app",
        ];
        $this->assertSame($expectedResult, $result);
    }

    public function testParseEnvTextSkipsCommentsAndInvalidLines()
    {
        $result = DotEnv::parseEnvText("# comment\n\n  A = 1 \nno equals sign\n=novalue\nB=\nC=x=y", false);
        $this->assertSame(["A" => 1, "B" => "", "C" => "x=y"], $result);
    }

    public function testParseEnvTextRegisters()
    {
        DotEnv::parseEnvText("DOTENV_TEST_A=hello", true);
        $this->assertSame("hello", getenv("DOTENV_TEST_A"));
        $this->assertSame("hello", $_ENV["DOTENV_TEST_A"]);
    }

    public function testParse()
    {
        file_put_contents(".env", "DOTENV_TEST_A=1\nDOTENV_TEST_B=b");
        $this->assertSame(["DOTENV_TEST_A" => 1, "DOTENV_TEST_B" => "b"], DotEnv::parse(false));
        $this->assertFalse(getenv("DOTENV_TEST_A"));
    }

    public function testParseMissingFile()
    {
        $this->assertSame([], DotEnv::parse());
    }

    public function testRegisterAndUnregister()
    {
        DotEnv::register("DOTENV_TEST_A", "value");
        $this->assertSame("value", env("DOTENV_TEST_A"));

        DotEnv::unregister("DOTENV_TEST_A");
        $this->assertFalse(env("DOTENV_TEST_A"));
    }

    public function testWriteCreatesFile()
    {
        DotEnv::write("DOTENV_TEST_A", "value");
        $this->assertSame("DOTENV_TEST_A=value", file_get_contents(".env"));
        $this->assertSame("value", getenv("DOTENV_TEST_A"));
        $this->assertFileDoesNotExist(".env.temp");
    }

    public function testWriteAppends()
    {
        file_put_contents(".env", "DOTENV_TEST_A=a");
        $result = DotEnv::write("DOTENV_TEST_B", "b", false);
        $this->assertSame("DOTENV_TEST_A=a\nDOTENV_TEST_B=b", file_get_contents(".env"));
        $this->assertSame(["DOTENV_TEST_A" => "a", "DOTENV_TEST_B" => "b"], $result);
    }

    public function testWriteDoesNotOverwriteByDefault()
    {
        file_put_contents(".env", "DOTENV_TEST_A=old");
        DotEnv::write("DOTENV_TEST_A", "new", false);
        $this->assertSame("DOTENV_TEST_A=old", file_get_contents(".env"));
    }

    public function testWriteOverwrite()
    {
        file_put_contents(".env", "DOTENV_TEST_A=old\nDOTENV_TEST_B=b");
        DotEnv::write("DOTENV_TEST_A", "new", false, true);
        $this->assertSame("DOTENV_TEST_A=new\nDOTENV_TEST_B=b", file_get_contents(".env"));
    }

    # @bugfix "$1" in a value was treated as a regex backreference, "pa$1ss" was written as "pass"
    public function testWriteOverwriteValueWithDollar()
    {
        file_put_contents(".env", "DOTENV_TEST_A=old");
        DotEnv::write("DOTENV_TEST_A", 'pa$1ss', false, true);
        $this->assertSame('DOTENV_TEST_A=pa$1ss', file_get_contents(".env"));
    }

    # @bugfix keys with an empty value were never overwritten
    public function testWriteOverwriteEmptyValue()
    {
        file_put_contents(".env", "DOTENV_TEST_A=\nDOTENV_TEST_B=b");
        DotEnv::write("DOTENV_TEST_A", "filled", false, true);
        $this->assertSame("DOTENV_TEST_A=filled\nDOTENV_TEST_B=b", file_get_contents(".env"));
    }

    # @bugfix all file entries were registered even with register=false
    public function testWriteWithoutRegister()
    {
        file_put_contents(".env", "DOTENV_TEST_A=a");
        DotEnv::write("DOTENV_TEST_B", "b", false);
        $this->assertFalse(getenv("DOTENV_TEST_A"));
        $this->assertFalse(getenv("DOTENV_TEST_B"));
    }

    public function testWriteRejectsNewlineInValue()
    {
        $this->expectException(DotEnvException::class);
        DotEnv::write("DOTENV_TEST_A", "x\nINJECTED=1", false);
    }

    public function testWriteRejectsInvalidKey()
    {
        $this->expectException(DotEnvException::class);
        DotEnv::write("A.*", "x", false);
    }

    public function testWriteLockedReturnsFalse()
    {
        file_put_contents(".env", "DOTENV_TEST_A=a");
        file_put_contents(".env.temp", "");
        $this->assertFalse(DotEnv::write("DOTENV_TEST_B", "b", false));
        $this->assertSame("DOTENV_TEST_A=a", file_get_contents(".env"));
    }

    public function testDelete()
    {
        file_put_contents(".env", "DOTENV_TEST_A=a\nDOTENV_TEST_KEY=k\nDOTENV_TEST_B=b");
        DotEnv::delete("DOTENV_TEST_KEY", false);
        $this->assertSame("DOTENV_TEST_A=a\nDOTENV_TEST_B=b", file_get_contents(".env"));
    }

    # @bugfix deleting KEY also cut MY_KEY down to "MY_"
    public function testDeleteLeavesKeysWithSameSuffix()
    {
        file_put_contents(".env", "MY_DOTENV_TEST_KEY=keep\nDOTENV_TEST_KEY=remove");
        DotEnv::delete("DOTENV_TEST_KEY", false);
        $this->assertSame("MY_DOTENV_TEST_KEY=keep\n", file_get_contents(".env"));
    }

    public function testDeleteUnregisters()
    {
        file_put_contents(".env", "DOTENV_TEST_A=a");
        DotEnv::register("DOTENV_TEST_A", "a");
        DotEnv::delete("DOTENV_TEST_A");
        $this->assertFalse(getenv("DOTENV_TEST_A"));
        $this->assertSame("", file_get_contents(".env"));
    }

    public function testDeleteMissingFile()
    {
        $this->assertFalse(DotEnv::delete("DOTENV_TEST_A"));
    }
}
