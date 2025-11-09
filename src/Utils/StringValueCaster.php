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
        return is_string($input) && strlen($input) > 2 &&
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
            return strpos(".", $value) !== false ? floatval($value) : intval($value);
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