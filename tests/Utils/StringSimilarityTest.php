<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;
use PHPUnit\Framework\TestCase;

final class StringSimilarityTest extends TestCase
{
    public function testJaroWinklerKnownValues()
    {
        $compare = new StringCompareJaroWinkler();

        // Reference values from the Jaro-Winkler literature
        $this->assertEqualsWithDelta(0.9611, $compare->compare("MARTHA", "MARHTA"), 0.0001);
        $this->assertEqualsWithDelta(0.8400, $compare->compare("DWAYNE", "DUANE"), 0.0001);
        $this->assertEqualsWithDelta(0.8133, $compare->compare("DIXON", "DICKSONX"), 0.0001);
    }

    public function testJaroWinklerBounds()
    {
        $compare = new StringCompareJaroWinkler();

        $this->assertSame(1.0, $compare->compare("same", "same"));
        $this->assertSame(0.0, (float) $compare->compare("abc", "xyz"));
        $this->assertSame(0.0, (float) $compare->compare("abc", ""));
    }

    # @bugfix identical empty strings returned 0
    public function testJaroWinklerEmpty()
    {
        $this->assertSame(1.0, (new StringCompareJaroWinkler())->compare("", ""));
    }

    # @bugfix multibyte strings were compared per byte, identical strings scored 0.92
    public function testJaroWinklerMultibyte()
    {
        $compare = new StringCompareJaroWinkler();

        $this->assertSame(1.0, $compare->compare("héllo", "héllo"));
        $this->assertEqualsWithDelta($compare->compare("hallo", "hbllo"), $compare->compare("héllo", "hëllo"), 0.0001);
    }

    public function testSmithWatermanGotoh()
    {
        $compare = new SmithWatermanGotoh();

        $this->assertSame(1.0, $compare->compare("abc", "abc"));
        $this->assertSame(1.0, $compare->compare("abc", "xxabcxx"));
        $this->assertSame(0.75, $compare->compare("café", "cafe"));
        $this->assertSame(0.0, (float) $compare->compare("abc", "xyz"));
    }

    public function testSmithWatermanGotohEmpty()
    {
        $compare = new SmithWatermanGotoh();

        $this->assertSame(1.0, $compare->compare("", ""));
        $this->assertSame(0.0, $compare->compare("abc", ""));
    }

    # @bugfix "0" was treated as empty by empty()
    public function testSmithWatermanGotohZeroString()
    {
        $this->assertSame(1.0, (new SmithWatermanGotoh())->compare("0", "x0"));
    }

    # @bugfix character count and byte indexes were mixed, multibyte characters shifted the comparison
    public function testSmithWatermanGotohMultibyte()
    {
        $this->assertSame(0.5, (new SmithWatermanGotoh())->compare("éa", "ea"));
    }

    public function testSmithWatermanGotohCustomSubstitution()
    {
        $compare = new SmithWatermanGotoh(-1.0, new SmithWatermanMatchMismatch(2.0, -1.0));
        $this->assertSame(1.0, $compare->compare("abc", "abc"));
    }

    public function testSmithWatermanGotohInvalidGap()
    {
        $this->expectException(Exception::class);
        new SmithWatermanGotoh(0.5);
    }

    public function testSmithWatermanMatchMismatchInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("matchValue must be > mismatchValue");
        new SmithWatermanMatchMismatch(1.0, 1.0);
    }
}
