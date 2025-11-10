<?php

/**
 * flag_id
 *  Returns a unique flag id 
 */
if(!function_exists('flag_id'))
{
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
}

/**
 * env
 */
if(!function_exists('env'))
{
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
}

/**
 * include_recursive
 */
if(!function_exists('include_recursive'))
{
    function include_recursive(string $input)
    {
        if(is_file($input))
            include_once $input;
        else
            foreach(scandir($input) as $item)
                if($item !== "." && $item !== "..")
                    include_recursive("$input/$item");
    }
}

/**
 * Courtesey of: Nicholas Shanks
 * https://stackoverflow.com/questions/13036160/phps-array-map-including-keys
 */
if(!function_exists('array_map_assoc'))
{
    function array_map_assoc(callable $function, array $array)
    {
        return array_column(array_map($function, array_keys($array), $array), 1, 0);
    }
}

/**
 * array_is_assoc
 */
if(!function_exists('array_is_assoc'))
{
    function array_is_assoc(array $array)
    {
        return !array_is_list($array);
    }
}


/**
 * array_option
 */
if(!function_exists('array_option'))
{
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
}

/**
 * array_has_option
 */
if(!function_exists('array_has_option'))
{
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
if(!function_exists('get_client_ip'))
{
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
}

/**
 * get_host_ip
 */
if(!function_exists('get_host_ip'))
{
    function get_host_ip() : string
    {       
        return getHostByName(getHostName());
    }
}

/**
 * is_json
 */
if(!function_exists('is_json'))
{
    function is_json($input) : bool 
    {
        if(!is_string($input)) return false;
        if(strcmp($input, "null") == 0) return false;
        json_decode($input);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

/**
 * is_binary
 */
if(!function_exists('is_binary'))
{
    function is_binary($input) : bool 
    {
        return preg_match('~[^\x20-\x7E\t\r\n]~', $input) > 0;
    }
}

/**
 * bench_start
 */
if(!function_exists('bench_start'))
{
    function bench_start() : float
    {
        return microtime(true);
    }
}

/**
 * bench_end
 */
if(!function_exists('bench_end'))
{
    function bench_end(float $startb, string $name = "benchmark", bool $print = true) : float
    {
        $delta = microtime(true) - $startb;   
        if($print) printf("%s time: %fs\n", $name, $delta);
        return $delta;
    }
}

/**
 * isHTTPS
 */
if(!function_exists('isHTTPS'))
{
    function isHTTPS() : bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || !empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443;
    }
}

/**
 * array_in_array
 */
if(!function_exists('array_in_array'))
{
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
}

/**
 * array_get_keys
 * @param array $array - input array
 * @param bool $asList - turns sequential array of arrays item into single key array
 */
if(!function_exists('array_get_keys'))
{
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
}


/**
 * is_array_of_arrays
 */
if(!function_exists('is_array_of_arrays'))
{
    function is_array_of_arrays(array $array) : bool
    {
        return count($array) !== 0 && array_is_list($array) && count(array_filter($array, 'is_array')) === count($array);
    }
}

/**
 * keys_array_to_assoc
 */
if(!function_exists('keys_array_to_assoc'))
{
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
if(!function_exists('array_filter_keys'))
{
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
if(!function_exists('array_filter_keys_recursive'))
{
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
}

/**
 * random_float
 */
if(!function_exists('random_float'))
{
    function random_float(int $min, int $max)
    {
        return ($min + ($max - $min) * (mt_rand() / mt_getrandmax()));
    }
}

/**
 * random_array_item
 */
if(!function_exists('random_array_item'))
{
    function random_array_item(array $array)
    {
        return $array[array_rand($array, 1)];
    }
}

/**
 * random_string
 */
if(!function_exists('random_string'))
{
    function random_string(int $length, string $characterPool = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ")
    {
        $string = array();
        $alphaLength = strlen($characterPool) - 1; //put the length - 1 in cache
        for ($i = 0; $i < $length; $i++) 
        {
            $n = rand(0, $alphaLength);
            $string[] = $characterPool[$n];
        }
        return implode($string);
    }
}

/**
 * generate_bytes
 */
if(!function_exists('generate_bytes'))
{
    function generate_bytes(int $length) : string 
    {
        if ((function_exists('random_bytes'))) 
        {
            return random_bytes($length);
        }
        $bytes = '';
        for ($i = 1; $i <= $length; $i++) 
        {
            $bytes = chr(mt_rand(0, 255)) . $bytes;
        }
        return $bytes;
    }
}

/**
 * random_token
 */
if(!function_exists('random_token'))
{
    function random_token(int $length)
    {
        $isOddNumber = $length%2 == 1;
        $length = $isOddNumber ? $length + 1 : $length;
        $token = bin2hex(generate_bytes($length/2));
        return $isOddNumber ? substr($token, 0, strlen($token) - 1) : $token;
    }
}

/**
 * random_date
 */
if(!function_exists('random_date'))
{
    function random_date($date, $to = null, $format = "Y-m-d H:i:s")
    {
        $getDate = function($date = null)
        {
            if(is_object($date) && $date instanceof DateTime)
                return $date;
            else if(is_int($date))
                return new DateTime($date);
            else if(is_string($date) && strpos($date, "+") === false && strpos($date, "-") === false) // Date string
                return new DateTime($date);
            else if(is_string($date) && (strpos($date, "+") !== false || strpos($date, "-") !== false) ) // Modify
                return (new DateTime())->modify($date);
            else
                return null;
        };

        // Get dates
        $date = $getDate($date);
        $to = $getDate($to);

        // Check if both dates are set
        if($date instanceof DateTime && $to instanceof DateTime)
        {
            $timestamp = null;
            if($date->getTimestamp() > $to->getTimestamp())
                $timestamp = mt_rand($to->getTimestamp(), $date->getTimestamp());
            else
                $timestamp = mt_rand($date->getTimestamp(), $to->getTimestamp());

            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp);
            return $dateTime->format($format);
        }
        else if($date instanceof DateTime && $to === null)
            return $date->format($format);
        else if($date === null && $to === null)
            throw new InvalidArgumentException("random(DATE, date, to?, format?) could not create date using values 'NULL', 'NULL'");
    }
}

/**
 * random_ip
 */
if(!function_exists('random_ip'))
{
    function random_ip($version = "v4")
    {
        $generateIP6Block = function()
        {
            $seed = str_split("1234567890abcdef");
            shuffle($seed);
            $block = join("", $seed); // Symbol array to string
            $block = mb_substr($block, 0, 4);
            return $block;
        };

        if($version == "v6")
        {
            $prefix = "2a04:5200:8";
            $a = $generateIP6Block();
            $b = $generateIP6Block();
            $c = $generateIP6Block();
            $d = $generateIP6Block();
            $e = $generateIP6Block();
            return "{$prefix}:{$a}:{$b}:{$c}:{$d}:{$e}";
        }
        else
        {
            return mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255).".".mt_rand(0,255);
        }
    }
}