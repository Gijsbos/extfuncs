<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase 
{
    public function testEnvironmentisTest() 
    {
        DotEnv::register("DEPLOYMENT", "test");
        $result = Environment::isTest();
        $this->assertTrue($result);
    }

    public function testEnvironmentisProduction() 
    {
        DotEnv::register("DEPLOYMENT", "prod");
        $result = Environment::isProduction();
        $this->assertTrue($result);
    }
    
    public function testEnvironmentisDevelopment() 
    {
        DotEnv::register("DEPLOYMENT", "dev");
        $result = Environment::isDevelopment();
        $this->assertTrue($result);
    }

    protected function tearDown() : void 
    {
        $_ENV["DEPLOYMENT"] = "test";
    }
}