<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class MiscFunctionsTest extends TestCase
{
    public function testIsBinary()
    {
        $this->assertFalse(is_binary("plain text\twith\r\nwhitespace"));
        $this->assertTrue(is_binary("\x00\x01\x02"));
    }

    public function testIsSubsetOf()
    {
        $this->assertTrue(is_subset_of(["a", "b"], ["a", "b", "c"]));
        $this->assertTrue(is_subset_of([], ["a"]));
        $this->assertFalse(is_subset_of(["a", "d"], ["a", "b", "c"]));
    }

    public function testBench()
    {
        $start = bench_start();
        $this->assertIsFloat($start);

        $delta = bench_end($start, "test", false);
        $this->assertGreaterThanOrEqual(0, $delta);
    }

    public function testBenchPrints()
    {
        $this->expectOutputRegex("/^test time: [0-9.]+s\n$/");
        bench_end(bench_start(), "test");
    }

    public function testRandomFloatRange()
    {
        for($i = 0; $i < 50; $i++)
        {
            $result = random_float(1.5, 2.5);
            $this->assertGreaterThanOrEqual(1.5, $result);
            $this->assertLessThanOrEqual(2.5, $result);
        }
    }

    public function testRandomFloatReversedAndEqualBounds()
    {
        $result = random_float(3.0, 1.0);
        $this->assertGreaterThanOrEqual(1.0, $result);
        $this->assertLessThanOrEqual(3.0, $result);

        $this->assertSame(2.0, random_float(2.0, 2.0));
    }

    public function testRandomStringEmptyPool()
    {
        $this->expectException(\ValueError::class);
        random_string(3, "");
    }

    /**
     * Deprecated functions must keep matching their built-in replacements
     */
    public function testDeprecatedFunctionsMatchBuiltins()
    {
        foreach([[], [1, 2], ["a" => 1], [1 => "a"]] as $array)
            $this->assertSame(!array_is_list($array), array_is_assoc($array));

        foreach(['{"a":1}', "[1]", "1", "invalid", ""] as $json)
            $this->assertSame(json_validate($json), is_json($json));

        foreach(["/x/my-file.txt", "archive.tar.gz", "C:\\dir\\file.php"] as $path)
            $this->assertSame(pathinfo(str_replace("\\", "/", $path), PATHINFO_FILENAME), filename($path));

        $this->assertSame(gethostbyname(gethostname()), get_host_ip());
        $this->assertSame(16, strlen(generate_bytes(16)));
    }

    public function testRandomNames()
    {
        $this->assertMatchesRegularExpression("/^[A-Za-z]+ [A-Za-z]+$/", random_name());
        $this->assertMatchesRegularExpression("/^[a-z]+\.[a-z]+[a-f0-9]{8}@example\.com$/", random_email());
    }
}
