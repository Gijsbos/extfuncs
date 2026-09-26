<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class SelectObjectsFromListTest extends TestCase
{
    private function list() : array
    {
        return [
            (object) ["id" => 1, "type" => "a", "meta" => (object) ["color" => "red", "size" => 1]],
            (object) ["id" => 2, "type" => "b", "meta" => (object) ["color" => "red", "size" => 2]],
            (object) ["id" => 3, "type" => "a", "meta" => (object) ["color" => "blue", "size" => 1]],
            (object) ["id" => 4, "type" => "a", "meta" => null],
        ];
    }

    public function testSelectByValue()
    {
        $result = select_objects_from_list($this->list(), ["type" => "a"]);
        $this->assertSame([1, 3, 4], array_column($result, "id"));
    }

    public function testSelectByMultipleValues()
    {
        $result = select_objects_from_list($this->list(), ["type" => "a", "id" => 3]);
        $this->assertSame([3], array_column($result, "id"));
    }

    public function testSelectByNestedValues()
    {
        $result = select_objects_from_list($this->list(), ["meta" => ["color" => "red"]]);
        $this->assertSame([1, 2], array_column($result, "id"));
    }

    public function testSelectByNestedAndValue()
    {
        $result = select_objects_from_list($this->list(), ["meta" => ["color" => "red", "size" => 1], "type" => "a"]);
        $this->assertSame([1], array_column($result, "id"));
    }

    public function testSelectIsStrict()
    {
        $result = select_objects_from_list($this->list(), ["id" => "1"]);
        $this->assertSame([], $result);
    }

    public function testSelectNoParams()
    {
        $this->assertCount(4, select_objects_from_list($this->list(), []));
    }
}
