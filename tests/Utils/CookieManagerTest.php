<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class CookieManagerTest extends TestCase
{
    private $settings;

    protected function setUp() : void
    {
        $this->settings = App::$SETTINGS;
        App::$SETTINGS = ["cookies" => ["prefix" => "t_"]];
        $_COOKIE = [];
        $_SESSION = [];
    }

    protected function tearDown() : void
    {
        App::$SETTINGS = $this->settings;
        $_COOKIE = [];
        $_SESSION = [];
    }

    public function testHasAndGetCookieUsePrefix()
    {
        $_COOKIE["t_name"] = "value";

        $this->assertTrue(CookieManager::hasCookie("name"));
        $this->assertSame("value", CookieManager::getCookie("name"));

        $this->assertFalse(CookieManager::hasCookie("other"));
        $this->assertNull(CookieManager::getCookie("other"));
    }

    public function testSetCookiesAllowed()
    {
        CookieManager::setCookiesAllowed(true);
        $this->assertSame("true", SessionManager::get("cookies-allowed"));

        CookieManager::setCookiesAllowed(false);
        $this->assertSame("false", SessionManager::get("cookies-allowed"));
    }

    public function testAskForCookiesWithoutDecision()
    {
        $this->assertTrue(CookieManager::askForCookies());
        $this->assertFalse(CookieManager::cookiesAreAllowed());
    }

    public function testCookiesAllowedInSession()
    {
        CookieManager::setCookiesAllowed(true);
        $this->assertFalse(CookieManager::askForCookies());
        $this->assertTrue(CookieManager::cookiesAreAllowed());
    }

    public function testCookiesDeclinedInSession()
    {
        CookieManager::setCookiesAllowed(false);
        $this->assertFalse(CookieManager::askForCookies());
        $this->assertFalse(CookieManager::cookiesAreAllowed());
    }

    public function testCookiesAllowedInCookie()
    {
        $_COOKIE["t_cookies-allowed"] = "true";
        $this->assertFalse(CookieManager::askForCookies());
        $this->assertTrue(CookieManager::cookiesAreAllowed());
    }

    public function testCookiesDeclinedInCookie()
    {
        $_COOKIE["t_cookies-allowed"] = "false";
        $this->assertTrue(CookieManager::askForCookies());
        $this->assertFalse(CookieManager::cookiesAreAllowed());
    }

    /**
     * setcookie() fails once output has been sent, so run without PHPUnit's output
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testSetAndRemoveCookie()
    {
        CookieManager::setCookie("name", "value");
        $_COOKIE["t_name"] = "value";

        CookieManager::removeCookie("name");
        $this->assertFalse(CookieManager::hasCookie("name"));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testInitSetsConsentCookie()
    {
        CookieManager::setCookiesAllowed(true);
        CookieManager::init();
        $this->assertTrue(CookieManager::cookiesAreAllowed());
    }
}
