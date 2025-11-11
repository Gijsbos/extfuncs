<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

/**
 * Environment
 */
abstract class Environment 
{
    /**
     * getEnvironment
     */
    public static function getEnvironment() : false | string
    {
        return ($env = env("ENVIRONMENT")) !== false ? $env : env("DEPLOYMENT");
    }

    /**
     * isDevelopment
     */
    public static function isDevelopment() : bool
    {
        $env = self::getEnvironment();

        if($env === false) 
            return false;

        return str_starts_with(strtolower(trim($env)), "dev");
    }

    /**
     * isProduction
     */
    public static function isProduction() : bool
    {
        $env = self::getEnvironment();

        if($env === false) 
            return false;

        return str_starts_with(strtolower(trim($env)), "prod");
    }

    /**
     * isTest
     */
    public static function isTest() : bool
    {
        $env = self::getEnvironment();

        if($env === false) 
            return false;

        return str_starts_with(strtolower(trim($env)), "test");
    }
}