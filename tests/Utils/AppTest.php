<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class AppTest extends TestCase
{
    private array $state;

    /**
     * App changes global PHP settings; save and restore them so other tests are not affected
     */
    protected function setUp() : void
    {
        $this->state = [
            "timezone" => date_default_timezone_get(),
            "error_reporting" => error_reporting(),
            "display_errors" => ini_get("display_errors"),
            "encoding" => mb_internal_encoding(),
            "settings" => App::$SETTINGS,
            "server" => $_SERVER,
        ];

        foreach(["HTTP_HOSTNAME", "HTTP_PATHNAME", "ENV", "ENVIRONMENT"] as $key)
            putenv($key);

        unset($_SERVER["HTTP_HOST"], $_SERVER["SERVER_NAME"], $_SERVER["HTTPS"]);
    }

    protected function tearDown() : void
    {
        date_default_timezone_set($this->state["timezone"]);
        error_reporting($this->state["error_reporting"]);
        ini_set("display_errors", (string) $this->state["display_errors"]);
        mb_internal_encoding($this->state["encoding"]);
        App::$SETTINGS = $this->state["settings"];
        $_SERVER = $this->state["server"];

        foreach(["HTTP_HOSTNAME", "HTTP_PATHNAME", "ENV", "ENVIRONMENT"] as $key)
            putenv($key);
    }

    public function testDefaults()
    {
        $app = new App();

        $this->assertSame(App::DEFAULT_TIMEZONE, date_default_timezone_get());
        $this->assertSame("UTF-8", mb_internal_encoding());
        $this->assertTrue($app->cliEnabled);
        $this->assertFalse($app->startSession);

        $this->assertSame("", App::getSessionPrefix());
        $this->assertSame("", App::getCookiePrefix());
        $this->assertSame(App::DEFAULT_COOKIE_EXPIRES, App::getCookieExpires());
        $this->assertSame("/", App::getCookiePath());
        $this->assertNull(App::getCookieDomain());
        $this->assertSame(1, App::getCookieSecure());
        $this->assertSame(1, App::getCookieHTTPOnly());
        $this->assertSame("Strict", App::getCookieSameSite());
        $this->assertSame("cookies-allowed", App::getCookieAllowedName());
    }

    public function testOptions()
    {
        new App([
            "timezone" => "UTC",
            "sessionSettings" => ["prefix" => "s_"],
            "cookieSettings" => [
                "prefix" => "c_",
                "expires" => 60,
                "path" => "sub",
                "domain" => "example.com",
                "secure" => 0,
                "http-only" => 0,
                "same-site" => "Lax",
                "cookies-allowed-name" => "consent",
            ],
        ]);

        $this->assertSame("UTC", date_default_timezone_get());
        $this->assertSame("s_", App::getSessionPrefix());
        $this->assertSame("c_", App::getCookiePrefix());
        $this->assertSame(60, App::getCookieExpires());
        $this->assertSame("/sub/", App::getCookiePath());
        $this->assertSame("example.com", App::getCookieDomain());
        $this->assertSame(0, App::getCookieSecure());
        $this->assertSame(0, App::getCookieHTTPOnly());
        $this->assertSame("Lax", App::getCookieSameSite());
        $this->assertSame("consent", App::getCookieAllowedName());
    }

    public function testErrorReportingProduction()
    {
        putenv("ENV=production");
        new App();
        $this->assertSame(0, error_reporting());
        $this->assertSame("0", ini_get("display_errors"));
    }

    public function testErrorReportingDevelopment()
    {
        putenv("ENV=development");
        new App();
        $this->assertSame(E_ALL, error_reporting());
        $this->assertSame("1", ini_get("display_errors"));
    }

    # @bugfix only the legacy env var was checked, ENVIRONMENT (used by Environment) was ignored
    public function testErrorReportingDevelopmentEnvironment()
    {
        putenv("ENVIRONMENT=dev");
        new App();
        $this->assertSame(E_ALL, error_reporting());
    }

    public function testIncludes()
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . "extfuncs_app_" . random_token(12);
        mkdir($dir);
        file_put_contents("$dir/include.php", '<?php $GLOBALS["extfuncs_app_include"] = true;');

        new App(["includes" => [$dir]]);

        rmdir_recursive($dir);
        $this->assertTrue($GLOBALS["extfuncs_app_include"] ?? false);
    }

    public function testGetHttpHostnameFromEnv()
    {
        putenv("HTTP_HOSTNAME=https://example.com");
        $this->assertSame("example.com", App::getHttpHostname());
    }

    public function testGetHttpHostnameFromRequest()
    {
        $_SERVER["HTTP_HOST"] = "example.com";
        $this->assertSame("example.com", App::getHttpHostname());

        unset($_SERVER["HTTP_HOST"]);
        $_SERVER["SERVER_NAME"] = "server.example.com";
        $this->assertSame("server.example.com", App::getHttpHostname());
    }

    # @bugfix the Host header was used without validation
    public function testGetHttpHostnameIgnoresMalformedHost()
    {
        $_SERVER["HTTP_HOST"] = "evil.com/phish";
        $_SERVER["SERVER_NAME"] = "server.example.com";
        $this->assertSame("server.example.com", App::getHttpHostname());
    }

    public function testGetHttpPathname()
    {
        $this->assertSame("", App::getHttpPathname());

        putenv("HTTP_PATHNAME=/app");
        $this->assertSame("/app", App::getHttpPathname());
    }

    public function testGetBaseURI()
    {
        putenv("HTTP_HOSTNAME=example.com");
        putenv("HTTP_PATHNAME=/app/");

        $this->assertSame("http://example.com/app", App::getBaseURI());
        $this->assertSame("http://example.com/app/users", App::getBaseURI("users"));

        $_SERVER["HTTPS"] = "on";
        $this->assertSame("https://example.com/app/users", App::getBaseURI("users"));
    }

    /**
     * Whether headers are sent depends on PHPUnit output before this test (test order, runner),
     * so the expected output follows headers_sent()
     */
    private function expectHeadersSentWarning(string $message) : void
    {
        if(headers_sent())
            $this->expectOutputRegex("/Warning.*$message/");
        else
            $this->expectOutputString("");
    }

    public function testCheckHeadersSentPrintsWarningInCli()
    {
        $app = new App();

        $this->expectHeadersSentWarning("custom message");
        $app->checkHeadersSent("custom message");
    }

    /**
     * No output has been sent in a separate process
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCheckHeadersNotSentPrintsNothing()
    {
        $app = new App();

        $this->assertFalse(headers_sent());
        $this->expectOutputString("");
        $app->checkHeadersSent("custom message");
    }

    public function testInitSessionSkippedInCli()
    {
        $app = new App();

        $this->expectHeadersSentWarning("Could not start session");
        $app->initSession();
        $this->assertSame(PHP_SESSION_NONE, session_status());
    }
}
