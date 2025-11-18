<?php

use gijsbos\ExtFuncs\Utils\TextParser;
use Ramsey\Uuid\Uuid;

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
    function include_recursive(string $input, string $extension = ".php")
    {
        if(is_file($input))
        {
            if(strlen($extension) > 0)
            {
                if(str_ends_with($input, $extension))
                    include_once $input;
            }
            else
                include_once $input;
        }
        else
        {
            foreach(scandir($input) as $item)
                if($item !== "." && $item !== "..")
                    include_recursive("$input/$item");
        }
    }
}

/**
 * rmdir_recursive
 * @uri: https://stackoverflow.com/questions/3338123/how-do-i-recursively-delete-a-directory-and-its-entire-contents-files-sub-dir
 */
if(!function_exists('rmdir_recursive'))
{
    function rmdir_recursive($dir)
    { 
        if (is_dir($dir))
        { 
            $objects = scandir($dir);
            foreach ($objects as $object)
            { 
                if ($object != "." && $object != "..")
                { 
                    if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                        rmdir_recursive($dir. DIRECTORY_SEPARATOR .$object);
                    else
                        unlink($dir. DIRECTORY_SEPARATOR .$object); 
                } 
            }
            rmdir($dir); 
        } 
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
 * @param bool $include - true = exclude keys (default), false = include keys
 * @return array filtered array
 */
if(!function_exists('array_filter_keys'))
{
    function array_filter_keys(array $array, array $keys, bool $asList = false, bool $include = true)
    {
        // Check if array is list
        if($asList && is_array_of_arrays($array))
        {
            return array_map(function($value) use ($keys, $asList, $include) {
                return array_filter_keys($value, $keys, $asList, $include);
            }, $array);
        }

        // Return result
        if($include)
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
 * @param bool $exclude - true = exclude keys (default), false = include keys
 * @return array filtered array
 */
if(!function_exists('array_filter_keys_recursive'))
{
    function array_filter_keys_recursive(array $array, array $keys, bool $asList = false, bool $include = true)
    {
        // Treat as list, sequential items with arrays as value
        if($asList && is_array_of_arrays($array))
        {
            // All values are arrays
            foreach($array as $key => $value)
                $array[$key] = array_filter_keys_recursive($value, $keys, $asList, $include);
            
            // Return result
            return $array;
        }
        
        // Regular assoc arrays
        foreach($array as $key => $value)
        {
            if(is_array($value) && is_array(@$keys[$key]))
            {
                $array[$key] = array_filter_keys_recursive($value, $keys[$key], $asList, $include);

                if(!$include && count($keys[$key]) !== 0)
                    unset($keys[$key]);
            }
        }

        // Return result
        return array_filter_keys($array, $keys, $asList, $include);
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
    function random_date($date = null, $to = null, $format = "Y-m-d H:i:s")
    {
        $getDate = function($date = null)
        {
            if(is_object($date) && $date instanceof DateTime)
                return $date;
            else if(is_int($date))
                return (new DateTime())->setTimestamp($date);
            else if(is_string($date) && strpos($date, "+") === false && strpos($date, "-") === false) // Date string
                return new DateTime($date);
            else if(is_string($date) && (strpos($date, "+") !== false || strpos($date, "-") !== false) ) // Modify
                return (new DateTime())->modify($date);
            else
                return null;
        };

        // Set default
        $date = $date ?? time();

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
 * random_firstname
 */
if(!function_exists('random_firstname'))
{
    function random_firstname()
    {
        return random_array_item(array("John", "James", "Clara", "Ben", "Clark", "Anouk", "Terry", "Abigail", "Linda", "Anna", "Josephine", "Jordy", "Eric", "Sebastian", "Petra"));
    }
}

/**
 * random_lastname
 */
if(!function_exists('random_lastname'))
{
    function random_lastname()
    {
        return random_array_item(array("Jameson", "Lundberg", "Bos", "West", "Rodgers", "Hamlin", "Brando", "Kidd", "Beller", "Bostwick", "Hodges", "McGillavry", "Dillon", "Herron"));
    }
}

/**
 * random_name
 */
if(!function_exists('random_name'))
{
    function random_name()
    {
        return random_firstname() . " " . random_lastname();
    }
}

/**
 * random_email
 */
if(!function_exists('random_email'))
{
    function random_email()
    {
        $firstName = str_replace(" ", "_", random_firstname());
        $lastName = str_replace(" ", "_", random_lastname());

        // Return
        return strtolower("$firstName.$lastName" . random_token(8) . "@example.com");
    }
}

/**
 * random_password
 */
if(!function_exists('random_password'))
{
    function random_password(int $length = 8)
    {
        if($length < 3)
            throw new InvalidArgumentException("Length must be equal or greater than 3");

        return random_string(1, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz") . random_string(1, "0123456789") . random_string(1, "!@#%^&*()\-_=+\[\]{}<>?\/~") . random_string($length - 3, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#%^&*()-_=+\[\]{}<>?/~");
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
 */
if(!function_exists('filter_vars'))
{
    function filter_vars(array $vars, $filter = null, bool $include = true)
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
        $vars = array_filter($vars, function($key) use ($filter, $include)
        {
            if($include)
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

/**
 * is_uuid4
 */
if(!function_exists('is_uuid4'))
{
    function is_uuid4(string $input) 
    {
        return preg_match('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', (string) $input) == 1;
    }
}

/**
 * uuid4
 */
if(!function_exists('uuid4'))
{
    function uuid4()
    {
        return Uuid::uuid4()->toString();
    }
}

/**
 * array_keys_exist
 */
if(!function_exists('array_keys_exist'))
{
    function array_keys_exist(array $keys, array $data, bool $asList = false) : bool
    {
        if($asList && is_array_of_arrays($data))
        {
            foreach($data as $value)
                if(array_keys_exist($keys, $value, $asList) === false)
                    return false;

            return true;
        }

        foreach($keys as $key => $value)
        {
            if(is_int($key) && is_string($value))
                $key = $value;

            if(!array_key_exists($key, $data))
                return false;
            else
            {
                $compareValue = $data[$key];

                if(is_array($value))
                {
                    if(!is_array($compareValue))
                        return false;
                    else
                    {
                        if(array_keys_exist($value, $compareValue, $asList) === false)
                            return false;
                    }
                }
            }
        }
        return true;
    }
}

/**
 * array_sort_keys
 * 
 *  Normalizes key order in array e.g.:
 *      [0] => 1,
 *      [assoc] => 'value',
 *      [2] => 2
 *  Becomes:
 *      [0] => 1,
 *      [assoc] => 'value',
 *      [1] => 2
 */
if(!function_exists('array_sort_keys'))
{
    function array_sort_keys(array $array) : array
    {
        $i = -1;
        ksort($array);
        return array_map_assoc(function($key, $value) use (&$i)
        {
            $i++;
            if(is_int($key))
            {
                if(is_array($value))
                    return [$i, array_sort_keys($value)];
                else
                    return [$i, $value];
            }
            else
            {
                if(is_array($value))
                    return [$key, array_sort_keys($value)];
                else
                    return [$key, $value];
            }
        }, $array);
    }
}

/**
 * array_equals
 */
if(!function_exists('array_equals'))
{
    function array_equals(array $array1, array $array2) : bool
    {
        // Check element length
        if(count($array1) !== count($array2))
            return false;

        // Resort keys
        $array1 = array_sort_keys($array1);
        $array2 = array_sort_keys($array2);

        // Check keys
        $keys1 = array_keys($array1);
        $keys2 = array_keys($array2);
        
        // Check keys
        if($keys1 != $keys2)
            return false;

        // Sort values (loses keys)
        $values1 = array_values($array1);
        $values2 = array_values($array2);
        sort($values1);
        sort($values2);
        
        // Check types
        foreach($values1 as $key => $value)
        {
            // Check if both items are arrays we need to compare
            if(is_array($values1[$key]) && is_array($values2[$key]))
            {
                // Check if array of arrays
                if(is_array_of_arrays($values1[$key]) && is_array_of_arrays($values2[$key]))
                {
                    foreach($values1[$key] as $k => $v)
                    {   
                        if(array_equals($values1[$key][$k], $values2[$key][$k]) === false)
                            return false;
                        else
                        {
                            unset($values1[$key][$k]);
                            unset($values2[$key][$k]);
                        }
                    }
                }
                else
                {
                    if(array_equals($values1[$key], $values2[$key]))
                    {
                        unset($values1[$key]);
                        unset($values2[$key]);
                    }
                    else
                        return false;
                }
            }
        }

        // Check result
        return $values1 == $values2;
    }
}

/**
 * array_diff_keys
 */
if(!function_exists('array_diff_keys'))
{
    function array_diff_keys(array $array1, array $array2, bool $asList = false) : array
    {
        if($asList && is_array_of_arrays($array1))
        {
            array_walk($array1, function($value, $key) use($array2, $asList) {
                $value = array_diff_keys($value, $array2, $asList);
            });
            return $array1;
        }

        $keys = array_get_keys($array1);
        $compare = array_get_keys($array2);
        $diff = [];

        foreach($keys as $key => $value)
        {
            if(is_int($key))
            {
                if(!array_key_exists($value, $compare) && !in_array($value, $compare))
                    $diff[$value] = $array1[$value];
            }
            else
            {
                if(!array_key_exists($key, $compare) && !in_array($key, $compare))
                {
                    $diff[$key] = $array1[$key];
                }
                else
                {
                    if(is_array($value))
                    {
                        if(array_key_exists($key, $array2) && is_array($array2[$key]))
                        {
                            if(count($subDiff = array_diff_keys($array1[$key], $array2[$key])) > 0)
                                $diff[$key] = $subDiff;
                        }
                        else
                            $diff[$key] = $array1[$key];
                    }
                }
            }
        }

        return $diff;
    }
}

/**
 * implode_key_value_array
 */
if(!function_exists('implode_key_value_array'))
{
    function implode_key_value_array(array $array, string $keyValueDelimiter = "=", string $itemDelimiter = ",", string $encloseStart = "", string $encloseEnd = "")
    {
        $string = [];

        foreach($array as $key => $value)
        {
            if(is_array($value))
                $string[] = "$key$keyValueDelimiter" . implode_key_value_array($value, $keyValueDelimiter, $itemDelimiter, $encloseStart, $encloseEnd);
            else
                $string[] = "$key$keyValueDelimiter$value";
        }

        return $encloseStart . implode($itemDelimiter, $string) . $encloseEnd;
    }
}

/**
 * array_has_keys
 *  USE FOR NON PRODUCTION ONLY
 * 
 * @param array $input - input array
 * @param array $keys - keys input e.g. ["key1", "key2" => ["key3" => "key4"]]
 * @param bool $asList - process sequential array of arrays matching keys against values
 * @param bool $strict - compares array keys literally, if exactly the same => true, else false
 */
if(!function_exists('array_has_keys'))
{
    function array_has_keys(array $array, array $keys, bool $asList = false, bool $strict = false, bool $throws = false)
    {   
        // Check if keys exist
        $arrayKeysExist = array_keys_exist($keys, $array, $asList);

        // Check 
        if(!$strict != 0 && $arrayKeysExist)
            return true;

        // Set arrayKeysExistRev
        $arrayKeysExistRev = true;

        // Check if STRICT
        if($strict)
        {
            $arrayKeys = array_get_keys($array, $asList);

            // Check if are same
            $arrayKeysExistRev = array_equals($arrayKeys, $keys);

            // Both are equal
            if($arrayKeysExist && $arrayKeysExistRev)
                return true;
        }

        // Check if throws
        if($throws)
        {
            $diff = [];
            $diff["missing"] = array_get_keys(array_diff_keys(keys_array_to_assoc($keys), keys_array_to_assoc(array_get_keys($array, $asList))));
            $diff["invalid"] = array_get_keys(array_diff_keys(keys_array_to_assoc(array_get_keys($array, $asList)), keys_array_to_assoc($keys)));
            throw new Exception(sprintf("%s failed: %s", __FUNCTION__, implode_key_value_array($diff, " => ", ", ", "[", "]")));            
        }
        else
            return false;
    }
}

/**
 * filename
 *  Returns the filename in path
 * 
 * @param string path - Filepath
 */
if(!function_exists('filename'))
{
    function filename(string $path) : string
    {
        if(preg_match("/([a-zA-Z0-9\_]+)(?=\.[a-zA-Z0-9]+$)/", $path, $matches) == 1)
        {
            return $matches[1];
        }
        return $path;
    }
}

/**
 * get_user_agent
 */
if(!function_exists('get_user_agent'))
{
    function get_user_agent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
    }
}

/**
 * get_referer
 */
if(!function_exists('get_referer'))
{
    function get_referer(bool $includeQuery = true)
    {
        $referer = @$_SERVER['HTTP_REFERER'];

        if($referer === null)
            return $referer;

        if(!$includeQuery)
        {
            $details = parse_url($referer);
            return sprintf("%s://%s%s", $details["scheme"], $details["host"], $details["path"]);
        }
        else
            return $referer;
    }
}

/**
 * get_base_uri
 */
if(!function_exists('get_base_uri'))
{
    function get_base_uri(bool $path = true, null|bool $useHTTPS = null) : string
    {
        $isUsingHTTPS = !empty($_SERVER['HTTPS']) || @$_SERVER['SERVER_PORT'] == 443 || @$_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || @$_SERVER['HTTP_X_FORWARDED_PORT'] == 443;

        $willUseHTTPS = is_bool($useHTTPS) ? $useHTTPS : $isUsingHTTPS;

        if(isset($_SERVER["HTTP_HOST"]))
            return ($willUseHTTPS ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . ($path ? $_SERVER["REQUEST_URI"] : "");
        else
            return "";
    }
}

/**
 * get_uri
 */
if(!function_exists('get_uri'))
{
    function get_uri(null|bool $useHTTPS = null) : string
    {
        return get_base_uri(true, $useHTTPS);
    }
}

/**
 * get_uri_part
 */
if(!function_exists('get_uri_part'))
{
    function get_uri_part(string $key)
    {
        $parts = parse_url(get_uri());

        if(!array_key_exists($key, $parts))
            throw new Error(__FUNCTION__ . " failed: unknown key $key");

        return $parts[$key];
    }
}

/**
 * useHTTPS
 */
if(!function_exists('useHTTPS'))
{
    function useHTTPS() : void
    {
        if(!isHTTPS())
        {
            header("location: " . get_uri(true));
            exit();
        }
    }
}

/**
 * exec_stdout
 */
if(!function_exists('exec_stdout'))
{
    function exec_stdout(string $cmd, null|string|callable $lineFormat = null)
    {
        $spec = array(
            0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
            1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
            2 => array("pipe", "w")  // stderr is a file to write to
        );
        
        $process = proc_open($cmd, $spec, $pipes);
        $lines = [];

        $print = function($input) use ($lineFormat)
        {
            return $lineFormat !== null ? $lineFormat($input) : $input;
        };

        while(($line = fgets($pipes[1])))
        {
            $line = $print($line);
            $lines[] = $line;
            fwrite(STDOUT, $line);
        }
        
        while(($line = fgets($pipes[2])))
        {
            $line = $print($line);
            $lines[] = $line;
            if(strlen($line))
                fwrite(STDERR, $line);
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        
        proc_close($process);

        return $lines;
    }
}