<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class ExecStdoutTest extends TestCase
{
    /**
     * Runs exec_stdout while capturing lines instead of echoing them
     */
    private function capture(string $cmd) : array
    {
        $captured = [];
        exec_stdout($cmd, function($line) use (&$captured) {
            $captured[] = $line;
            return "";
        });
        return $captured;
    }

    public function testExecStdout()
    {
        $result = $this->capture("printf 'a\\nb\\n'");
        $this->assertSame(["a\n", "b\n"], $result);
    }

    public function testExecStdoutReturnsFormattedLines()
    {
        $result = exec_stdout("printf 'a\\n'", fn($line) => "");
        $this->assertSame([""], $result);
    }

    public function testExecStdoutCapturesStderr()
    {
        // Callback sees lines in arrival order, streams are read concurrently
        $result = $this->capture("printf 'out\\n'; printf 'err\\n' 1>&2");
        $this->assertEqualsCanonicalizing(["out\n", "err\n"], $result);
    }

    # @bugfix a last line "0" without newline was dropped
    public function testExecStdoutKeepsFalsyLastLine()
    {
        $result = $this->capture("printf 'a\\n0'");
        $this->assertSame(["a\n", "0"], $result);
    }

    # @bugfix reading stdout before stderr deadlocked when stderr output exceeded the pipe buffer
    public function testExecStdoutLargeStderrDoesNotDeadlock()
    {
        $result = $this->capture(escapeshellarg(PHP_BINARY) . " -r " . escapeshellarg('fwrite(STDERR, str_repeat("x\n", 100000)); echo "done";'));
        $this->assertCount(100001, $result);
        $this->assertContains("done", $result);
    }

    # @bugfix stdin stayed open, a command reading stdin blocked forever
    public function testExecStdoutClosesStdin()
    {
        $result = $this->capture("cat; echo end");
        $this->assertSame(["end\n"], $result);
    }
}
