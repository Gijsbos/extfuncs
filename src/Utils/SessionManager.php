<?php
declare(strict_types=1);

namespace gijsbos\extfuncs\Utils;

/**
 * SessionManager
 */
abstract class SessionManager 
{
    /**
     * set
     */
    public static function set($name, $value) 
    {
        return $_SESSION[App::getSessionPrefix() . $name] = $value;
    }

    /**
     * unset
     */
    public static function unset($name) 
    {
        if(SessionManager::has($name)) 
        {
            unset($_SESSION[App::getSessionPrefix() . $name]);
        }
    }

    /**
     * has
     */
    public static function has($name) : bool 
    {
        return isset($_SESSION[App::getSessionPrefix() . $name]);
    }

    /**
     * get
     */
    public static function get($name) 
    {
        if(SessionManager::has($name)) 
        {
            return $_SESSION[App::getSessionPrefix() . $name];
        }
        return null;
    }

    /**
     * add
     */
    public static function add($arrayName, $value) 
    {
        if(SessionManager::has($arrayName)) 
        {
            if(is_array(SessionManager::get($arrayName))) 
            {
                return $_SESSION[App::getSessionPrefix() . $arrayName][count($_SESSION[App::getSessionPrefix() . $arrayName])] = $value;
            }
            else 
            {
                return false;
            }
        }
        else 
        {
            $_SESSION[App::getSessionPrefix() . $arrayName] = array();
            return $_SESSION[App::getSessionPrefix() . $arrayName][count($_SESSION[App::getSessionPrefix() . $arrayName])] = $value;
        }
    }
}