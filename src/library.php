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

/**
 * array_in_array
 */
function array_in_array(array $haystack, $needle, string $needleDelimiter = ",") : bool
{
    // Turn string needle into array needle
    if(is_string($needle))
        $needle = array_filter(explode($needleDelimiter, $needle));

    // Verify that needle is not empty
    if(count($needle) === 0)
        return false;

    // Search needle in haystack
    foreach($needle as $input)
        if(!in_array($input, $haystack))
            return false;

    // Success
    return true;
}

/**
 * array_get_keys
 * @param array $array - input array
 * @param bool $asList - turns sequential array of arrays item into single key array
 */
function array_get_keys(array $array, bool $asList = false) : array
{
    if($asList && is_array_of_arrays($array))
    {
        $keys = [];

        foreach($array as $value)
        {
            foreach(array_get_keys($value, $asList) as $k => $v)
            {
                if(is_int($k))
                {
                    if(is_string($v))
                    {
                        if(!in_array($v, $keys))
                            $keys[] = $v;
                    }
                }
                else
                {
                    if(!array_key_exists($k, $keys))
                        $keys[$k] = $v;
                }
            }
        }

        return $keys;
    }

    $i = -1;
    $keys = [];
    foreach($array as $key => $value)
    {
        $i++;
        if(is_string($key) && is_array($value))
        {
            if(count($value) === 0)
                $keys[$i] = $key;
            else
                $keys[$key] = array_get_keys($value, $asList);
        }
        else
            $keys[$i] = $key;
    }
    return $keys;
}

/**
 * is_array_of_arrays
 */
function is_array_of_arrays(array $array) : bool
{
    return count($array) !== 0 && array_is_list($array) && count(array_filter($array, 'is_array')) === count($array);
}

/**
 * keys_array_to_assoc
 */
function keys_array_to_assoc(array $keys)
{
    return array_map_assoc(function($key, $value) {
        if(is_int($key))
            return [$value, $value];
        else if(is_array($value))
            return [$key, keys_array_to_assoc($value)];
        else
            return [$key, $value];
    }, $keys);
}

/**
 * array_filter_keys
 * 
 * @param array $array - array to filter
 * @param array $keys - keys to filter from array, e.g. ['key1', 'key2', 'key3' => ['sub-key1','sub-key2']]
 * @param bool $asList - apply filter to sequential lists or arrays
 * @param bool $includeKeys - true = include keys (default), false = exclude keys
 * @return array filtered array
 */
function array_filter_keys(array $array, array $keys, bool $asList = false, bool $includeKeys = true)
{
    // Check if array is list
    if($asList && is_array_of_arrays($array))
    {
        return array_map(function($value) use ($keys, $asList, $includeKeys) {
            return array_filter_keys($value, $keys, $asList, $includeKeys);
        }, $array);
    }

    // Return result
    if($includeKeys)
        return array_intersect_key($array, keys_array_to_assoc($keys));
    else
        return array_diff_key($array, keys_array_to_assoc($keys));
}

/**
 * array_filter_keys_recursive
 * 
 * @param array $array - array to filter
 * @param array $keys - keys to filter from array, e.g. ['key1', 'key2', 'key3' => ['sub-key1','sub-key2']]
 * @param bool $asList - apply filter to sequential lists or arrays
 * @param bool $includeKeys - true = include keys (default), false = exclude keys
 * @return array filtered array
 */
function array_filter_keys_recursive(array $array, array $keys, bool $asList = false, bool $includeKeys = true)
{
    // Treat as list, sequential items with arrays as value
    if($asList && is_array_of_arrays($array))
    {
        // All values are arrays
        foreach($array as $key => $value)
            $array[$key] = array_filter_keys_recursive($value, $keys, $asList, $includeKeys);
        
        // Return result
        return $array;
    }
    
    // Regular assoc arrays
    foreach($array as $key => $value)
    {
        if(is_array($value) && is_array(@$keys[$key]))
        {
            $array[$key] = array_filter_keys_recursive($value, $keys[$key], $asList, $includeKeys);

            if(!$includeKeys && count($keys[$key]) !== 0)
                unset($keys[$key]);
        }
    }

    // Return result
    return array_filter_keys($array, $keys, $asList, $includeKeys);
}