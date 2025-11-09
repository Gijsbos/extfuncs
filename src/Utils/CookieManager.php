<?php
declare(strict_types=1);

namespace gijsbos\extfuncs\Utils;

/**
 * CookieManager
 */
abstract class CookieManager 
{
    /**
     * init
     *  Must be parsed BEFORE any headers are send in the document.
     *  Cookie is set when SESSION VALUE $_SESSION["cookies"] is set to true.
     */
    public static function init() : void 
    {
        # Check if the visitors decision regarding cookies is stored in session.
        if(SessionManager::has(App::getCookieAllowedName())) 
        {
            $cookies = strtolower(SessionManager::get(App::getCookieAllowedName()));

            if($cookies == "true") 
            {
                CookieManager::setCookie(App::getCookieAllowedName(), "true");
            }
        }
    }

    /**
     * setCookie
     * @param string $name
     * @param string $value
     * @param int $expires
     */
    public static function setCookie($name, $value, null|int $expires = null) : void
    {
        $expires = $expires === null ? time() + (int) App::getCookieExpires() : $expires;

        $cookieSecure = boolval(App::getCookieSecure());

        if(!isset($_SERVER['HTTPS']))
        {
            if($cookieSecure) 
            {
                $cookieSecure = false;
            }
        }

        if(!setcookie(App::getCookiePrefix() . $name, $value, [
            'expires' => $expires,
            'path' => App::getCookiePath(),
            'domain' => App::getCookieDomain(),
            'secure' => $cookieSecure,
            'httponly' => boolval(App::getCookieHTTPOnly()),
            'samesite' => App::getCookieSameSite()
        ])) {
            throw new \Exception("Could not initiate cookie");
        }
    }

    /**
     * hasCookie
     * @param string $name
     * @return bool
     */
    public static function hasCookie($name) : bool 
    {
        return isset($_COOKIE[App::getCookiePrefix() . $name]);
    }

    /**
     * removeCookie
     * @param string $name
     */
    public static function removeCookie($name) : void 
    {
        unset($_COOKIE[App::getCookiePrefix() . $name]);
        CookieManager::setCookie($name, "", time() - 3600);
    }

    /**
     * getCookie
     * @param string
     */
    public static function getCookie($name) 
    {
        if(!CookieManager::hasCookie($name)) 
        {
            return null;
        }
        return $_COOKIE[App::getCookiePrefix() . $name];
    }

    /**
     * setCookiesAllowed
     *  The SESSION value is set and the InitCookies function will turn this into a cookie on the next pageload
     * @param bool $value
     */
    public static function setCookiesAllowed(bool $value) : void 
    {
        if($value) 
        {
            SessionManager::set(App::getCookieAllowedName(), "true");
        }
        else 
        {
            SessionManager::set(App::getCookieAllowedName(), "false");
        }
    }

    /**
     * askForCookies
     *  Checks whether the visitor should be promted to accept cookies or not
     * @return bool
     */
    public static function askForCookies() : bool 
    {
        if(SessionManager::has(App::getCookieAllowedName())) { # Value is set in SESSION so a choice has been made.
            return false;
        }
        else if (CookieManager::hasCookie(App::getCookieAllowedName())) 
        {
            $cookies = CookieManager::getCookie(App::getCookieAllowedName());
            
            if($cookies == "true") 
            {
                return false;
            }
            else 
            {
                return true;
            }
        }
        else 
        {
            return true;
        }
    }

    /**
     * cookiesAreAllowed
     *  Returns if cookies are permitted on the website
     * @return bool
     */
    public static function cookiesAreAllowed() : bool 
    {
        if(SessionManager::has(App::getCookieAllowedName())) 
        {
            $cookies = SessionManager::get(App::getCookieAllowedName());
            if($cookies == "true") 
            {
                return true;
            }
        }
        else if (CookieManager::hasCookie(App::getCookieAllowedName())) 
        {
            $cookies = CookieManager::getCookie(App::getCookieAllowedName());
            if($cookies == "true") 
            {
                return true;
            }
        }
        return false;
    }
}