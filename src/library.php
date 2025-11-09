<?php

/**
 * flag_id
 *  Returns a unique flag id 
 */
function flag_id($domain = 0)
{
    global $SDK_FLAG;

    if(!$SDK_FLAG)
        $SDK_FLAG = [];

    if(!array_key_exists($domain, $SDK_FLAG))
        $SDK_FLAG[$domain] = 1;
    else
        $SDK_FLAG[$domain] *= 2;

    // If float values are reached, the domain has too many values
    if(is_float($SDK_FLAG[$domain]))    
        throw new Exception(sprintf("Flag range exceeded for domain '$domain' on line %s", debug_backtrace()[0]["file"] . ":" . debug_backtrace()[0]["line"]));

    return $SDK_FLAG[$domain];
}

/**
 * env
 */
function env(string $key, null|bool|Exception $throws = null) : false | string
{
    if(getenv($key) !== false)
        return getenv($key);
    else if(isset($_ENV) && is_array($_ENV) && array_key_exists($key, $_ENV))
        return $_ENV[$key];
    else
    {
        if($throws !== null && $throws !== false)
            throw is_object($throws) ? $throws : new Exception("env var $key not found");

        return false;
    }
}

/**
 * include_recursive
 */
function include_recursive(string $input)
{
    if(is_file($input))
        include_once $input;
    else
        foreach(scandir($input) as $item)
            if($item !== "." && $item !== "..")
                include_recursive("$input/$item");
}

/**
 * Courtesey of: Nicholas Shanks
 * https://stackoverflow.com/questions/13036160/phps-array-map-including-keys
 */
function array_map_assoc(callable $function, array $array)
{
    return array_column(array_map($function, array_keys($array), $array), 1, 0);
}

/**
 * array_is_assoc
 */
function array_is_assoc(array $array)
{
    return !array_is_list($array);
}

/**
 * array_option
 */
function array_option(string $key, null|array $array = null, $defaultValue = false, $throws = null)
{
    if($array === null)
    {
        if($throws !== null)
            throw new $throws;

        return $defaultValue;
    }

    if(!array_key_exists($key, $array))
    {
        if($throws !== null)
            throw new $throws;

        return $defaultValue;
    }

    return $array[$key];
}

/**
 * array_has_option
 */
function array_has_option(string $key, null|array $array = null, bool $default = false) : bool
{
    if($array === null)
        return $default;

    if(array_key_exists($key, $array))
    {
        if(is_bool($array[$key]))
            return $array[$key];
        else
            return true;
    }
    else if(in_array($key, $array, true))
        return true;
    else
        return $default;
}


/**
 * array_get_key_value
 */
if(!function_exists('array_get_key_value'))
{
    function array_get_key_value(string $path, array $array, string $delimiter = ".", bool|Exception $throws = false)
    {
        // Explode path using delimiter
        $remaining = explode($delimiter, $path);

        // Get first key
        $key = array_shift($remaining);

        // Key not found
        if(!array_key_exists($key, $array))
        {
            if($throws !== false)
                throw is_object($throws) ? $throws : new Exception("Key '$key' not found"); 

            return null;
        }

        // Return key
        if(count($remaining) === 0)
            return $array[$key];

        // Continue search
        else if(is_array($array[$key]))
            return array_get_key_value(implode($delimiter, $remaining), $array[$key], $delimiter, $throws);

        // Not found
        else
        {
            if($throws !== false)
                throw is_object($throws) ? $throws : new Exception("Key '$key' not found"); 

            return null;
        }
    }
}

/**
 * get_client_ip
 * @param bool reliable - true (default) uses the only reliable $_SERVER[REMOTE_ADDR], false uses "HTTP_..." which can be set by client.
 */
function get_client_ip(bool $reliable = true) : string
{
    $ipaddress = 'UNKNOWN';

    if($reliable)
    {
        if(!empty($_SERVER["REMOTE_ADDR"]))
            $ipaddress = $_SERVER["REMOTE_ADDR"];
    }
    else
    {
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
            $ipaddress = $_SERVER["HTTP_CLIENT_IP"];

        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
            $ipaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];

        else if(!empty($_SERVER["REMOTE_ADDR"]))
            $ipaddress = $_SERVER["REMOTE_ADDR"];
    }
        
    return ($ipaddress == "::1" || $ipaddress == "UNKNOWN") ? getHostByName(getHostName()) : $ipaddress;
}

/**
 * get_host_ip
 */
function get_host_ip() : string
{       
    return getHostByName(getHostName());
}

/**
 * is_json
 */
function is_json($input) : bool 
{
    if(!is_string($input)) return false;
    if(strcmp($input, "null") == 0) return false;
    json_decode($input);
    return (json_last_error() == JSON_ERROR_NONE);
}

/**
 * is_binary
 */
function is_binary($input) : bool 
{
    return preg_match('~[^\x20-\x7E\t\r\n]~', $input) > 0;
}

/**
 * startb
 */
function startb() : float
{
    return microtime(true);
}

/**
 * endb
 */
function endb(float $startb, string $name = "benchmark", bool $print = true) : float
{
    $delta = microtime(true) - $startb;   
    if($print) printf("%s time: %fs\n", $name, $delta);
    return $delta;
}

/**
 * isHTTPS
 */
function isHTTPS() : bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || !empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443;
}