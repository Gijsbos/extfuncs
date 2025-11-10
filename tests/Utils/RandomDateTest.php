<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use DateTime;
use PHPUnit\Framework\TestCase;

final class RandomDateTest extends TestCase 
{
    public function testRandomDate()
    {
        $result = random_date('now'); 
        $expectedResult = preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $result) == 1;
        $this->assertTrue($expectedResult);
    }

    public function testRandomDateBetween()
    {
        $result = random_date('+5 seconds', '+10 seconds'); 
        $expectedResult = (new DateTime())->getTimestamp() < (new DateTime($result))->getTimestamp()
                            && ((new DateTime())->modify("+16 seconds"))->getTimestamp() >= (new DateTime($result))->getTimestamp();
        $this->assertTrue($expectedResult);
    }
}