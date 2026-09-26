<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class HttpFunctionsTest extends TestCase
{
    private array $server;

    protected function setUp() : void
    {
        $this->server = $_SERVER;

        foreach(["HTTPS", "SERVER_PORT", "HTTP_X_FORWARDED_PROTO", "HTTP_X_FORWARDED_PORT", "HTTP_HOST", "REQUEST_URI", "HTTP_CLIENT_IP", "HTTP_X_FORWARDED_FOR", "REMOTE_ADDR", "HTTP_REFERER"] as $key)
            unset($_SERVER[$key]);

        putenv("ALLOWED_HOSTS");
        unset($_ENV["ALLOWED_HOSTS"]);
    }

    protected function tearDown() : void
    {
        $_SERVER = $this->server;
        putenv("ALLOWED_HOSTS");
    }

    public function testGetHost()
    {
        $this->assertNull(get_host());

        foreach(["example.com", "example.com:8080", "localhost", "my_service", "127.0.0.1:80", "[::1]:8080"] as $host)
        {
            $_SERVER["HTTP_HOST"] = $host;
            $this->assertSame($host, get_host());
        }
    }

    public function testGetHostRejectsMalformed()
    {
        foreach(["evil.com/path", "user@evil.com", "evil.com\r\nX-Injected: 1", "evil.com\n", "evil.com:port", "", "exa mple.com"] as $host)
        {
            $_SERVER["HTTP_HOST"] = $host;
            $this->assertNull(get_host(), "Host '$host' should be rejected");
        }
    }

    public function testGetHostAllowed()
    {
        putenv("ALLOWED_HOSTS=example.com, www.example.com");

        $_SERVER["HTTP_HOST"] = "www.example.com";
        $this->assertSame("www.example.com", get_host());

        $_SERVER["HTTP_HOST"] = "EXAMPLE.com:8443";
        $this->assertSame("EXAMPLE.com:8443", get_host());
    }

    public function testGetHostNotAllowedFallsBack()
    {
        putenv("ALLOWED_HOSTS=example.com,www.example.com");

        $_SERVER["HTTP_HOST"] = "evil.com";
        $this->assertSame("example.com", get_host());

        $_SERVER["HTTP_HOST"] = "evil.com/path";
        $this->assertSame("example.com", get_host());

        unset($_SERVER["HTTP_HOST"]);
        $this->assertSame("example.com", get_host());
    }

    public function testGetHostAllowedWithPort()
    {
        putenv("ALLOWED_HOSTS=example.com:8080");

        $_SERVER["HTTP_HOST"] = "example.com:8080";
        $this->assertSame("example.com:8080", get_host());

        $_SERVER["HTTP_HOST"] = "example.com:9090";
        $this->assertSame("example.com:8080", get_host());
    }

    public function testGetBaseUriRejectsMalformedHost()
    {
        $_SERVER["HTTP_HOST"] = "evil.com/phish?";
        $_SERVER["REQUEST_URI"] = "/";
        $this->assertSame("", get_base_uri());
    }

    public function testGetBaseUriUsesAllowedHost()
    {
        putenv("ALLOWED_HOSTS=example.com");
        $_SERVER["HTTP_HOST"] = "evil.com";
        $_SERVER["REQUEST_URI"] = "/reset?token=abc";

        $this->assertSame("https://example.com/reset?token=abc", get_base_uri(true, true));
    }

    public function testIsHTTPS()
    {
        $this->assertFalse(isHTTPS());

        $_SERVER["HTTPS"] = "on";
        $this->assertTrue(isHTTPS());

        $_SERVER["HTTPS"] = "off";
        $this->assertFalse(isHTTPS());

        $_SERVER["SERVER_PORT"] = "443";
        $this->assertTrue(isHTTPS());
    }

    public function testIsHTTPSForwarded()
    {
        $_SERVER["HTTP_X_FORWARDED_PROTO"] = "https";
        $this->assertTrue(isHTTPS());

        unset($_SERVER["HTTP_X_FORWARDED_PROTO"]);
        $_SERVER["HTTP_X_FORWARDED_PORT"] = "443";
        $this->assertTrue(isHTTPS());
    }

    public function testGetBaseUri()
    {
        $_SERVER["HTTP_HOST"] = "example.com";
        $_SERVER["REQUEST_URI"] = "/path?q=1";

        $this->assertSame("http://example.com/path?q=1", get_base_uri());
        $this->assertSame("http://example.com", get_base_uri(false));
        $this->assertSame("https://example.com/path?q=1", get_base_uri(true, true));
    }

    # @bugfix HTTPS=off (set by IIS) was treated as HTTPS
    public function testGetBaseUriHttpsOff()
    {
        $_SERVER["HTTP_HOST"] = "example.com";
        $_SERVER["REQUEST_URI"] = "/";
        $_SERVER["HTTPS"] = "off";

        $this->assertSame("http://example.com/", get_base_uri());
    }

    public function testGetBaseUriWithoutHost()
    {
        $this->assertSame("", get_base_uri());
    }

    # @bugfix missing REQUEST_URI raised a warning
    public function testGetBaseUriWithoutRequestUri()
    {
        $_SERVER["HTTP_HOST"] = "example.com";
        $this->assertSame("http://example.com", get_base_uri());
    }

    public function testGetUriPart()
    {
        $_SERVER["HTTP_HOST"] = "example.com";
        $_SERVER["REQUEST_URI"] = "/path?q=1";

        $this->assertSame("example.com", get_uri_part("host"));
        $this->assertSame("/path", get_uri_part("path"));
        $this->assertSame("q=1", get_uri_part("query"));
    }

    public function testGetUriPartUnknown()
    {
        $_SERVER["HTTP_HOST"] = "example.com";
        $_SERVER["REQUEST_URI"] = "/path";

        $this->expectException(\Error::class);
        get_uri_part("query");
    }

    public function testGetClientIpReliableIgnoresHeaders()
    {
        $_SERVER["REMOTE_ADDR"] = "10.0.0.1";
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "1.2.3.4";

        $this->assertSame("10.0.0.1", get_client_ip());
    }

    # @bugfix X-Forwarded-For lists were returned as a whole, e.g. "1.2.3.4, 10.0.0.2"
    public function testGetClientIpForwardedForList()
    {
        $_SERVER["REMOTE_ADDR"] = "10.0.0.1";
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "1.2.3.4, 10.0.0.2";

        $this->assertSame("1.2.3.4", get_client_ip(false));
    }

    # @bugfix client supplied headers were returned without validation
    public function testGetClientIpInvalidHeaderFallsBack()
    {
        $_SERVER["REMOTE_ADDR"] = "10.0.0.1";
        $_SERVER["HTTP_CLIENT_IP"] = "<script>";
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "not-an-ip";

        $this->assertSame("10.0.0.1", get_client_ip(false));
    }

    # @bugfix referer without path raised an undefined key warning
    public function testGetRefererWithoutPath()
    {
        $_SERVER["HTTP_REFERER"] = "https://example.com?q=1";
        $this->assertSame("https://example.com", get_referer(false));
    }

    public function testGetRefererKeepsPort()
    {
        $_SERVER["HTTP_REFERER"] = "http://example.com:8080/a?q=1";
        $this->assertSame("http://example.com:8080/a", get_referer(false));
    }

    public function testGetRefererInvalid()
    {
        $_SERVER["HTTP_REFERER"] = "/relative/path";
        $this->assertNull(get_referer(false));
    }

    public function testGetRefererMissing()
    {
        $this->assertNull(get_referer());
    }
}
