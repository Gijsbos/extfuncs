<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    protected function setUp() : void
    {
        DotEnv::unregister("ENVIRONMENT");
        DotEnv::unregister("ENV");
    }

    protected function tearDown() : void
    {
        DotEnv::unregister("ENVIRONMENT");
        DotEnv::unregister("ENV");
    }

    public function testEnvironmentisTest()
    {
        DotEnv::register("ENV", "test");
        $this->assertTrue(Environment::isTest());
        $this->assertFalse(Environment::isProduction());
        $this->assertFalse(Environment::isDevelopment());
    }

    public function testEnvironmentisProduction()
    {
        DotEnv::register("ENV", "prod");
        $this->assertTrue(Environment::isProduction());
    }

    public function testEnvironmentisDevelopment()
    {
        DotEnv::register("ENV", "dev");
        $this->assertTrue(Environment::isDevelopment());
    }

    public function testEnvironmentVariable()
    {
        DotEnv::register("ENVIRONMENT", "Development");
        $this->assertSame("Development", Environment::getEnvironment());
        $this->assertTrue(Environment::isDevelopment());
    }

    public function testEnvironmentTakesPrecedenceOverEnv()
    {
        DotEnv::register("ENVIRONMENT", "production");
        DotEnv::register("ENV", "development");
        $this->assertSame("production", Environment::getEnvironment());
        $this->assertTrue(Environment::isProduction());
    }

    public function testEnvironmentNotSet()
    {
        $this->assertFalse(Environment::getEnvironment());
        $this->assertFalse(Environment::isTest());
        $this->assertFalse(Environment::isProduction());
        $this->assertFalse(Environment::isDevelopment());
    }

    public function testDeploymentIsNoLongerRead()
    {
        DotEnv::register("DEPLOYMENT", "dev");
        $this->assertFalse(Environment::getEnvironment());
        DotEnv::unregister("DEPLOYMENT");
    }
}
