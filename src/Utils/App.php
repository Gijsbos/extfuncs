<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use DateTime;

/**
 * App
 */
final class App
{
    public static $SETTINGS = null;

    const DEFAULT_CHARACTER_ENCODING = "UTF-8";
    const DEFAULT_TIMEZONE = "Europe/Amsterdam";
    const DEFAULT_SESSION_NAME = "ssid";
    const DEFAULT_SESSION_PREFIX = "";
    const DEFAULT_COOKIE_EXPIRES = 604800;
    const DEFAULT_COOKIE_PATH = "/";
    const DEFAULT_COOKIE_SECURE = 1;
    const DEFAULT_COOKIE_HTTP_ONLY = 1;
    const DEFAULT_COOKIE_SAME_SITE = "Strict";

    private string $sessionName;
    private array $includes;
    private array $sessionSettings;
    private array $cookieSettings;
    private string $timezone;
    private string $characterEncoding;
    private bool $cliEnabled;
    private bool $startSession;

    /**
     * __construct
     */
    public function __construct(array $opts = [])
    {
        $this->sessionName = @$opts["sessionName"] ?? self::DEFAULT_SESSION_NAME;
        $this->includes = @$opts["includes"] ?? [];
        $this->sessionSettings = @$opts["sessionSettings"] ?? [];
        $this->cookieSettings = @$opts["cookieSettings"] ?? [];
        $this->timezone = @$opts["timezone"] ?? self::DEFAULT_TIMEZONE;
        $this->characterEncoding = @$opts["characterEncoding"] ?? self::DEFAULT_CHARACTER_ENCODING;
        $this->cliEnabled = @$opts["cliEnabled"] ?? false;
        $this->startSession = @$opts["startSession"] ?? false;

        $this->init();
    }

    /**
     * initTimezone
     */
    private function initTimezone()
    {
        if(is_string($this->timezone))
            date_default_timezone_set($this->timezone);
    }

    /**
     * initIncludes
     */
    private function initIncludes() : void
    {
        foreach($this->includes as $include)
            include_recursive($include);
    }

    /**
     * setErrorReporting
     * display_errors: 0 | 1
     * error_reporting: 0 | E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE
     */
    private function initErrorReporting() : void
    {
        if(($deployment = env("DEPLOYMENT")) !== false && str_starts_with(strtolower($deployment), "dev"))
        {
            ini_set("display_errors", "1");
            error_reporting(E_ALL);
        }
        else
        {
            ini_set("display_errors", "0");
            error_reporting(0);
        }
    }

    /**
     * initAppSettings
     */
    private function initAppSettings()
    {
        self::$SETTINGS = [
            "session" => [
                "name" => @$this->sessionSettings["name"] ?? self::DEFAULT_SESSION_NAME,
                "prefix" => @$this->sessionSettings["prefix"] ?? self::DEFAULT_SESSION_PREFIX,
            ],
            "cookies" => [
                "prefix" => @$this->cookieSettings["prefix"] ?? "",
                "expires" => @$this->cookieSettings["expires"] ?? self::DEFAULT_COOKIE_EXPIRES,
                "path" => is_string(@$this->cookieSettings["path"]) ? str_must_start_end_with($this->cookieSettings["path"], "/")  : self::DEFAULT_COOKIE_PATH,
                "domain" => @$this->cookieSettings["domain"],
                "secure" => @$this->cookieSettings["secure"] ?? self::DEFAULT_COOKIE_SECURE,
                "http-only" => @$this->cookieSettings["http-only"] ?? self::DEFAULT_COOKIE_HTTP_ONLY,
                "same-site" => @$this->cookieSettings["same-site"] ?? self::DEFAULT_COOKIE_SAME_SITE, // Strict or Lax
                "cookies-allowed-name" => @$this->cookieSettings["cookies-allowed-name"] ?? "cookies-allowed",
            ]
        ];
    }

    /**
     * printCLIWarning
     */
    private function printCLIWarning(string $message) : void
    {
        printf("\n%s %s %s", (new DateTime())->format("Y-m-d H:i:s"), cli_color("Warning", "yellow"), $message);
    }

    /**
     * checkHeadersSent
     */
    private function checkHeadersSent(string $message = "Headers have been sent")
    {
        if($this->cliEnabled && headers_sent())
            $this->printCLIWarning($message);
    }

    /**
     * setSessionSettings
     * Change session name to OWASP specs "id", prevent identification of system used.
     * URI: https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html
     * 
     * Increase the SESSIONID identifier to increase security
     * URI: https://www.owasp.org/index.php/Insufficient_Session-ID_Length
     * URI: https://www.php.net/manual/en/session.security.ini.php
     */
    private function initSessionSettings() : void
    {
        // Check headers
        $this->checkHeadersSent("Could not set session name/sid");

        // Verify session not started
        if(!$this->cliEnabled && !headers_sent() && \session_status() == PHP_SESSION_NONE)
        {
            // Get session params
            $sessionParameters = array(
                "lifetime" => self::getCookieExpires(),         # In seconds
                "path" => self::getCookiePath(),                # e.g. www.example.com/path/ => '/path/'
                "domain" => self::getCookieDomain(),            # e.g. www.example.com => '.example.com'
                "secure" => !empty($_SERVER["HTTPS"]),          # Secure when HTTPS is enabled
                "httponly" => self::getCookieHTTPOnly(),        # Http only => true is not accessible by javascript
                "samesite" => self::getCookieSameSite(),        # Only allow cookie to be accessed on the same site
            );

            // Ini settings
            ini_set("session.cookie_lifetime", $sessionParameters["lifetime"] );
            ini_set("session.gc_maxlifetime", $sessionParameters["lifetime"] );
            ini_set("session.gc_probability", 1 );
            ini_set("session.gc_divisor", 3 );
            ini_set("session.cookie_samesite", $sessionParameters["samesite"] );

            // Init session/cookie params
            $result = session_set_cookie_params($sessionParameters);

            // Verify result
            if($result === false)
                throw new \RuntimeException(__METHOD__ . " failed: Could not initialize session cookie parameters, please check your cookie configuration");
        }
    }

    /**
     * initSession
     */
    private function initSession() : void
    {
        // Check headers
        $this->checkHeadersSent("Could not start session");

        // Start session
        if($this->startSession && !$this->cliEnabled && !headers_sent() && session_status() == PHP_SESSION_NONE) 
        {
            session_name($this->sessionName);
            session_start();
        }
    }

    /**
     * setCharacterEncoding
     */
    private function setCharacterEncoding() : void
    {
        mb_internal_encoding($this->characterEncoding);
        mb_http_output($this->characterEncoding);
    }

    /**
     * init
     */
    private function init()
    {
        $this->initTimezone();
        $this->initIncludes();
        $this->initErrorReporting();
        $this->initAppSettings();
        $this->initSessionSettings();
        $this->initSession();
        $this->setCharacterEncoding();
    }

    /**
     * getSessionPrefix
     */
    public static function getSessionPrefix()
    {
        return @App::$SETTINGS["session"]["prefix"] ?? "";
    }

    /**
     * getCookiePrefix
     */
    public static function getCookiePrefix()
    {
        return @App::$SETTINGS["cookies"]["prefix"] ?? "";
    }

    /**
     * getCookieExpires
     */
    public static function getCookieExpires()
    {
        return @App::$SETTINGS["cookies"]["expires"] ?? 604800;
    }

    /**
     * getCookiePath
     */
    public static function getCookiePath()
    {
        return @App::$SETTINGS["cookies"]["path"] ?? "/";
    }

    /**
     * getCookieDomain
     */
    public static function getCookieDomain()
    {
        return @App::$SETTINGS["cookies"]["domain"] ?? null;
    }

    /**
     * getCookieSecure
     */
    public static function getCookieSecure()
    {
        return @App::$SETTINGS["cookies"]["secure"] ?? 1;
    }

    /**
     * getCookieHTTPOnly
     */
    public static function getCookieHTTPOnly()
    {
        return @App::$SETTINGS["cookies"]["http-only"] ?? 1;
    }

    /**
     * getCookieSameSite
     */
    public static function getCookieSameSite()
    {
        return @App::$SETTINGS["cookies"]["same-site"] ?? "Strict";
    }

    /**
     * getCookieAllowedName
     */
    public static function getCookieAllowedName()
    {
        return @App::$SETTINGS["cookies"]["cookies-allowed-name"] ?? "cookies-allowed";
    }
}