<?php
declare(strict_types=1);

namespace gijsbos\extfuncs\Utils;

use PHPUnit\Framework\TestCase;

final class SessionManagerTest extends TestCase 
{
    public function testSessionManagerSet() 
    {
        SessionManager::set("test", "value");
        $this->assertEquals("value", $_SESSION[App::getSessionPrefix() . "test"]);
    }

    public function testSessionManagerSetArray() 
    {
        SessionManager::add("test", "Apple");
        $this->assertEquals("Apple",  SessionManager::get("test")[0]);
    }

    public function testSessionManagerSetArray2() 
    {
        SessionManager::add("test", "Apple");
        $this->assertEquals(1, count(SessionManager::get("test")));
    }

    public function testSessionManagerUnset() 
    {
        SessionManager::set("test", "value");
        SessionManager::unset("test");
        $result = isset($_SESSION[App::getSessionPrefix() . "test"]);

        $this->assertFalse($result);
    }

    public function testSessionManagerHasSuccess() 
    {
        SessionManager::set("test", "value");
        $result = SessionManager::has("test");
        $this->assertTrue($result);
    }

    public function testSessionManagerHasFailure() 
    {
        SessionManager::set("test", "value");
        $result = SessionManager::has("other_test");
        $this->assertFalse($result);
    }

    public function testSessionManagerGet() 
    {
        SessionManager::set("test", "value");
        $this->assertEquals("value", SessionManager::get("test"));
    }

    protected function tearDown() : void 
    {
        if(isset($_SESSION[App::getSessionPrefix() . "test"])) unset($_SESSION[App::getSessionPrefix() . "test"]);
    }
}