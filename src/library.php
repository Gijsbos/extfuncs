<?php

/**
 * flag_id
 *  Returns a unique flag id 
 */

use gijsbos\ExtFuncs\Utils\TextParser;

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
    function array_is_assoc(array $array) : bool
    {
        return (array_keys($array) !== range(0, count($array) - 1));
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
    function random_float(float $min, float $max)
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

/**
 * resolve_class
 */
if(!function_exists('resolve_class'))
{
    function resolve_class(string $className, null|string $namespace = null, bool | Exception $throws = true)
    {
        if(class_exists($className))
            return $className;

        // No namespace, no resolve
        if($namespace == null)
        {
            if($throws === true)
                throw new Exception("Could not resolve class '$className'");
            else if($throws instanceof Exception)
                throw $throws;

            return false;
        }

        // Format namespace
        $namespace = !str_ends_with($namespace, "\\") ? "$namespace\\" : $namespace;

        // Try again
        return resolve_class("$namespace$className", null, $throws);
    }
}

/**
 * resolve_callable
 * 
 * @param string callableString String e.g. <function> or <className>::<method>
 * @param string namespace Used to resolve a class executable
 * @return false|string|array false or callable string|array
 */
if(!function_exists('resolve_callable'))
{
    function resolve_callable(string $callableString, null|string $className = null, null|string $namespace = null, bool | Exception $throws = true) : false | array
    {
        if(
            ($pos = strpos($callableString, "::")) !== false
            ||
            ($pos = strpos($callableString, "->")) !== false
        )
        {
            $className = substr($callableString, 0, $pos); // Extract class
            $callableString = substr($callableString, $pos + 2); // Contains method
        }

        if($className !== null)
            $className = resolve_class($className, $namespace, $throws);

        if($className === null)
        {
            if(!function_exists($callableString))
            {
                if($throws === true)
                    throw new Exception("Could not resolve callable '$callableString', function '$callableString' does not exist");
                else if($throws instanceof Exception)
                    throw $throws;

                return false;
            }

            return $callableString;
        }
        else
        {
            if(!method_exists($className, $callableString))
            {
                if($throws === true)
                    throw new Exception("Could not resolve callable '$className->$callableString', method '$callableString' does not exist");
                else if($throws instanceof Exception)
                    throw $throws;

                return false;
            }

            return [$className, $callableString];
        }
    }
}

/**
 * constant_parse
 */
if(!function_exists('constant_parse'))
{
    function constant_parse(string $input)
    {
        $input = trim($input);

        // Handle parenthesis first
        $explode = explode_enclosed("(", ")", $input, 0, true);
        
        // Resolve parentheses
        if(count($explode))
        {
            foreach($explode as $startPos => $value)
            {
                $input = substr_replace($input, constant_parse($value), $startPos, strlen($value) + 2);
            }
        }

        // Resolve string without parentheses
        $explode = array_filter(explode(" ", $input));
        $nextOperator = null;
        $result = null;

        // Iterate over items
        foreach($explode as $item)
        {
            if($item == "&")
                $nextOperator = "&";
            else if($item == "|")
                $nextOperator = "|";
            else
            {
                // Validate item; assume that a number is a value resolved previously
                if(!is_numeric($item) && !defined($item))
                    throw new Exception("Undefined constant $item");

                // Get value
                $constantValue = is_numeric($item) ? intval($item) : constant($item);

                // No operator, assume first value
                if($nextOperator === null)
                    $result = $constantValue;
                else
                {
                    switch($nextOperator)
                    {
                        case "&":
                            $result = $result & $constantValue;
                        break;
                        case "|":
                            $result = $result | $constantValue;
                        break;
                    }
                }
                $nextOperator = null;
            }
        }

        // 
        return $result;
    }
}

/**
 * parse_string_value
 * @param string $string input string
 * @param string $fileReference file where function is called
 *  Parses input value from string representation
 *  e.g.    "true" =>       (string) "true"
 *          true =>         (boolean) true
 *          1 =>            (integer) 1
 *          1.1 =>          (float) 1
 *          array('value')  (array) ['value']
 * 
 */
if(!function_exists('parse_string_value'))
{
    function parse_string_value(null|string $string = null, array $options = [])
    {
        // Null no parsing
        if($string === null)
            return null;

        // Options
        $fileReference = array_option("fileReference", $options, null);
        $parseConstants = array_option("parseConstants", $options, true);
        
        // Trim string
        $string = trim($string);

        // Check if inline array was used, turn into array e.g. ['item','item'] => array('item','item')
        $string = str_starts_with($string, "[") && str_ends_with($string, "]") ? "array(".explode_enclosed("[","]", $string)[0].")" : $string;

        // Quote value
        if(is_wrapped_in_quotes($string))
            return unwrap_quotes($string);

        // Constant value
        else if($parseConstants && defined($string))
            return constant_parse($string);

        // Class constant/static property
        else if(preg_match("/^([\w\\\]+\\\)?(\w+)::(\w+)$/", $string, $matches) === 1)
        {
            // Get namespace and class info
            $className = $matchedClassName = $matches[1] . $matches[2];
            $property = $matches[3];

            // Check if class exists
            if(is_null($className) || !class_exists($className))
                throw new \Exception(sprintf("Could not parse class constant/static property '%s', class '%s' does not exist", $string, $matchedClassName));
            else
            {
                // Check if property is constant or static
                $reflectionClass = new \ReflectionClass($className);

                // Check property
                if($reflectionClass->hasConstant($property))
                    return $reflectionClass->getConstant($property);
                else if($reflectionClass->hasProperty($property) && $reflectionClass->getProperty($property)->isStatic())
                    return $className::$property;
                else
                    throw new \Exception(sprintf("Could not parse class constant/static property '%s', constant/property '%s' does not exist", $string, $property));
            }
        }
        // Variable reference
        else if(preg_match("/^\\$|@\\$/", $string) === 1)
            return $string;

        // Number
        else if (is_numeric($string))
            return typecast($string);

        // Boolean
        else if($string === "true")
            return true;
        else if($string === "false")
            return false;

        // Null
        else if($string === "null")
            return null;

        // Array
        else if(str_starts_with($string, "array(") && str_ends_with($string, ")"))
        {
            // Get args string
            $string = explode_enclosed("(", ")", $string)[0];
            
            // Return result
            return strlen($string) > 0 ? parse_array_string($string, $options) : array();
        }
        else // We expect "'1'" to be a string as input and "1" an int, but if nothing matches, we assume string anyway
            return $string;
    }
}

/**
 * parse_array_string
 *  Parses a comma separated args string e.g. 'argument', 'argument 2' => array("argument", "argument 2")
 */
if(!function_exists('parse_array_string'))
{
    function parse_array_string(string $args, array $options = []) : array
    {
        // Check if args is empty
        if(strlen($args) === 0)
            return array();

        // Escape args
        $args = TextParser::replaceCommentQuote($args, function($match){
            return TextParser::replaceInQuote($match, ",", "U+002C");
        });

        // Replace enclosed characters
        $args = replace_enclosed("(", ")", $args, ",", "U+002C");
        $args = replace_enclosed("[", "]", $args, ",", "U+002C");

        // Explode
        $explode = array_map(function($item){ return str_replace("U+002C", ',', trim($item)); }, explode(",", $args));

        // Create array from string
        return array_map_assoc(function($i, $item) use ($options)
        {
            // Check if array encountered
            if(preg_match("/^array\((.*)\)$/i", $item, $matches) === 1 || preg_match("/^\[(.*)\]$/i", $item, $matches) === 1)
            {
                return array($i, parse_string_value($matches[0], $options));
            }
            else if(str_contains($item, "=>"))
            {
                // Split
                $split = explode("=>", $item);

                // Key
                $key = trim($split[0]);
                $value = trim($split[1]);

                // Return assoc
                return array(parse_string_value($key, $options), parse_string_value($value, $options));
            }
            else
            {
                return array($i, parse_string_value($item, $options));
            }
        }, $explode);
    }
}

/**
 * array_shift_assoc
 */
if(!function_exists('array_shift_assoc'))
{
    function array_shift_assoc(array &$array)
    {
        if(!count($array))
            return null;

        return array_splice($array, 0, 1);
    }
}

/**
 * array_pop_assoc
 */
if(!function_exists('array_pop_assoc'))
{
    function array_pop_assoc(array &$array)
    {
        if(($count = count($array)) === 0)
            return null;

        return array_splice($array, $count - 1, 1);
    }
}

/**
 * sort_list_array
 * Sorts a list string based on item length using any delimiter and ASC or DESC flags.
 */
if(!function_exists('sort_list_array'))
{
    function sort_list_array(array $array, bool $descending = true) : array
    {
        usort($array, function($a, $b) use ($descending)
        {
            if($descending)
            {
                return strlen($b) - strlen($a);
            }
            else
            {
                return strlen($a) - strlen($b);
            }
        });

        return $array;
    }
}

/**
 * sort_string_list
 * Sorts a list string based on item length using any delimiter and ASC or DESC flags.
 */
if(!function_exists('sort_list_string'))
{
    function sort_list_string(string $array, string $delimiter, bool $descending = true) : string
    {
        $array = explode($delimiter, trim($array));
        $sortedArray = sort_list_array($array, $descending);
        return implode($delimiter, $sortedArray);
    }
}

/**
 * filter_vars
 * flags: INCLUDE_KEYS, EXCLUDE_KEYS
 */
if(!function_exists('filter_vars'))
{
    function filter_vars(array $vars, $filter = null, bool $exclude = true)
    {
        // Check if filter is set
        if($filter === null)
        {
            return $vars;
        }

        // Check if filter has the correct input
        if((!is_string($filter) && !is_array($filter) && !is_int($filter)))
        {
            return $vars;
        }

        // Check if filter is int
        if(is_int($filter)) $filter = (string) $filter;

        // Check if filter is string
        if(is_string($filter))
        {
            // Turn string to array
            $filter = explode(",", $filter);
        }
        
        // Filter out the keys
        $vars = array_filter($vars, function($key) use ($filter, $exclude)
        {
            if(!$exclude)
            {
                return in_array($key, $filter);
            }
            else
            {
                return !in_array($key, $filter);
            }
        }, ARRAY_FILTER_USE_KEY);

        // Make array start at 0
        array_unshift($vars);

        return $vars;
    }
}