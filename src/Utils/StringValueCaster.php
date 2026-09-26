<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

/**
 * StringValueCaster
 */
class StringValueCaster
{
    /**
     * __construct
     */
    public function __construct()
    {
        
    }

    /**
     * isWrappedInQuotes
     */
    private function isWrappedInQuotes($input)
    {
        return is_string($input) && strlen($input) >= 2 &&
                (
                    ($input[0] == '"' && $input[strlen($input) - 1] == '"')
                    ||
                    ($input[0] == "'" && $input[strlen($input) - 1] == "'")
                );
    }

    /**
     * castValue
     */
    public function castValue(string $value)
    {
        // Strings
        if($this->isWrappedInQuotes($value))
        {
            return substr($value, 1, strlen($value) - 2);
        }

        // Numbers
        else if (is_numeric($value))
        {
            // Int or float following PHP rules, e.g. "10" => 10, "1.5" => 1.5, "1e3" => 1000.0
            $number = $value + 0;

            // Integers beyond PHP_INT_MAX would lose precision as float, keep them as exact string
            if(is_float($number) && preg_match('/[.eE]/', $value) !== 1)
                return $value;

            return $number;
        }

        // Booleans
        else if(strtolower($value) === "true" || strtolower($value) === "false")
        {
            return strtolower($value) === "true" ? true : false;
        }

        // Null
        else if($value === 'null')
        {
            return null;
        }

        // Constants
        else if(defined($value))
        {
            return constant($value);
        }

        // Default to string
        else
        {
            return $value;
        }
    }

    /**
     * cast
     */
    public static function cast(string $value)
    {
        return (new self())->castValue($value);
    }
}