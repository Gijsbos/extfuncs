<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;

/**
 * SmithWatermanGotoh
 * Courtesey of: Joshweir @ https://stackoverflow.com/questions/4898705/smith-waterman-for-string-in-php
 */
class SmithWatermanGotoh 
{
    private $gapValue;
    private $substitution;

    /**
     * Constructs a new Smith Waterman metric.
     * 
     * @param gapValue
     *            a non-positive gap penalty
     * @param substitution
     *            a substitution function
     */
    public function __construct($gapValue=-0.5, $substitution=null)
    {
        if($gapValue > 0.0) throw new Exception("gapValue must be <= 0");

        if (empty($substitution)) $this->substitution = new SmithWatermanMatchMismatch(1.0, -2.0);
        else $this->substitution = $substitution;
        $this->gapValue = $gapValue;
    }

    /**
     * smithWatermanGotoh
     *  $s and $t are arrays of characters so multibyte strings are compared per character
     */
    private function smithWatermanGotoh(array $s, array $t) 
    {   
        $v0 = [];
        $v1 = [];
        $s_len = count($s);
        $t_len = count($t);
        $max = $v0[0] = max(0, $this->gapValue, $this->substitution->compare($s, 0, $t, 0));

        for ($j = 1; $j < $t_len; $j++) {
            $v0[$j] = max(0, $v0[$j - 1] + $this->gapValue,
                    $this->substitution->compare($s, 0, $t, $j));

            $max = max($max, $v0[$j]);
        }

        // Find max
        for ($i = 1; $i < $s_len; $i++) {
            $v1[0] = max(0, $v0[0] + $this->gapValue, $this->substitution->compare($s, $i, $t, 0));

            $max = max($max, $v1[0]);

            for ($j = 1; $j < $t_len; $j++) {
                $v1[$j] = max(0, $v0[$j] + $this->gapValue, $v1[$j - 1] + $this->gapValue,
                        $v0[$j - 1] + $this->substitution->compare($s, $i, $t, $j));

                $max = max($max, $v1[$j]);
            }

            $v0 = $v1;
        }

        return $max;
    }

    public function compare($a, $b) 
    {
        $a = (string) $a;
        $b = (string) $b;

        // Not empty(): "0" is a valid string
        if ($a === "" && $b === "") {
            return 1.0;
        }

        if ($a === "" || $b === "") {
            return 0.0;
        }

        $a = mb_str_split($a);
        $b = mb_str_split($b);

        $maxDistance = min(count($a), count($b))
                * max($this->substitution->max(), $this->gapValue);
        return $this->smithWatermanGotoh($a, $b) / $maxDistance;
    }
}
