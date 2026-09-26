<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class JsonDecodePreserveEmptyObjectsTest extends TestCase
{
    public function testDecodesObjectsToArrays()
    {
        $result = json_decode_preserve_empty_objects('{"a":1,"b":{"c":[1,2]}}');
        $expectedResult = ["a" => 1, "b" => ["c" => [1, 2]]];
        $this->assertSame($expectedResult, $result);
    }

    public function testPreservesEmptyObjects()
    {
        $result = json_decode_preserve_empty_objects('{"a":{},"b":[]}');
        $this->assertInstanceOf(\stdClass::class, $result["a"]);
        $this->assertSame([], $result["b"]);
        $this->assertSame('{"a":{},"b":[]}', json_encode($result));
    }

    # @bugfix nested string values were decoded again, e.g. "123" became 123
    public function testStringValuesAreNotDecodedAgain()
    {
        $result = json_decode_preserve_empty_objects('{"a":"123","b":"null","c":"{\"d\":1}"}');
        $expectedResult = ["a" => "123", "b" => "null", "c" => '{"d":1}'];
        $this->assertSame($expectedResult, $result);
    }

    public function testInvalidJsonIsReturnedUntouched()
    {
        $this->assertSame("not json", json_decode_preserve_empty_objects("not json"));
    }

    public function testNonStringInputIsConverted()
    {
        $input = (object) ["a" => (object) [], "b" => (object) ["c" => 1]];
        $result = json_decode_preserve_empty_objects($input);
        $this->assertInstanceOf(\stdClass::class, $result["a"]);
        $this->assertSame(["c" => 1], $result["b"]);
    }

    # @bugfix depth argument was ignored when validating, deep documents were returned as raw string
    public function testCustomDepthIsRespected()
    {
        $json = str_repeat("[", 600) . str_repeat("]", 600);
        $this->assertIsArray(json_decode_preserve_empty_objects($json, 1000));
        $this->assertSame($json, json_decode_preserve_empty_objects($json, 100));
    }

    public function testObjectAsArrayFlagIsIgnored()
    {
        $result = json_decode_preserve_empty_objects('{"a":{}}', 512, JSON_OBJECT_AS_ARRAY);
        $this->assertInstanceOf(\stdClass::class, $result["a"]);
    }
}
