<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use InvalidArgumentException;

/**
 * StringValueParser
 */
class StringValueParser
{
    private $inputHandlers;
    private $stringEscapeSymbols;

    /**
     * __construct
     * 
     * @param array $stringEscapeSymbols - Assoc array of symbols that need to be escaped in quotes string [symbol => replacement]
     * @param array $inputHandlers - Callable array for handling values. Callsign: function($input) : bool
     */
    public function __construct(array $inputHandlers = [], array $stringEscapeSymbols = [])
    {
        $this->inputHandlers = $inputHandlers;
        $this->stringEscapeSymbols = $stringEscapeSymbols;
    }

    /**
     * replace
     */
    private static function replace(string $pattern, string $search, string $replace, string $subject, int $group = 1)
    {
        $offset = 0;
        while(preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE, $offset))
        {
            $startPos = $matches[0][1];
            $length = strlen($matches[0][0]);

            // Get replacement
            $replacement = str_replace($search, $replace, $matches[$group][0]);
            $subject = substr_replace($subject, $replacement, (int) $matches[$group][1], strlen($matches[$group][0]));

            // Calculate delta because the replacement text might not match the initial match text
            $delta = strlen($matches[$group][0]) - strlen($replacement);

            // Set offset
            $offset = $startPos + $length - $delta;
        }
        return $subject;
    }

    /**
     * escape
     */
    public static function escape(string $input, array $stringEscapeSymbols)
    {
        // Replace
        foreach($stringEscapeSymbols as $symbol => $replacement)
            $input = self::replace("/('|\"|\`)(.+?)(\\1)/", $symbol, $replacement, $input, 2);

        // Return
        return $input;
    }

    /**
     * unescape
     */
    public static function unescape(string $input, array $stringEscapeSymbols)
    {
        // Replace
        foreach($stringEscapeSymbols as $symbol => $replacement)
            $input = self::replace("/('|\"|\`)(.+?)(\\1)/", $replacement, $symbol, $input, 2);

        // Return
        return $input;
    }

    /**
     * propertyEscape
     */
    private function propertyEscape(string $value)
    {
        // Unescape array symbols
        $value = str_replace("0x3D0x3E", "=>", $value);
        $value = str_replace("0x2C", ",", $value);

        // Unescape symbols
        foreach($this->stringEscapeSymbols as $symbol => $replacement)
            $value = str_replace($replacement, $symbol, $value);
        
        // Return value
        return $value;
    }

    /**
     * isArrayString
     */
    private static function isArrayString(string &$input) : bool
    {
        if((str_starts_with($input, "array(")))
            return true;
        else
            return str_starts_with($input, "[") && str_ends_with($input, "]");
    }

    /**
     * prepareArrayString
     */
    private function prepareArrayString(string $input)
    {
        $input = trim($input);

        // Escape
        $input = replace_enclosed("'", "'", $input, "(", "0x28");
        $input = replace_enclosed("'", "'", $input, ")", "0x29");
        
        // Change array( .. ) => [ .. ]
        $input = replace_enclosed_function("array(", ")", $input, function($v) {
            return "[" . substr($v, $s = strlen("array("), strlen($v) - $s - 1) . "]"; 
        }, true);

        // Unescape
        $input = replace_enclosed("'", "'", $input, "0x28", "(");
        $input = replace_enclosed("'", "'", $input, "0x29", ")");

        // Unescape
        return $input;
    }

    /**
     * parseArrayString
     */
    private function parseArrayString(string $arrayString)
    {
        $array = [];

        // Replace comma's
        $arrayString = replace_enclosed("'", "'", $arrayString, ",", "0x2C");
        $arrayString = replace_enclosed('"', '"', $arrayString, ",", "0x2C");
        
        $arrayString = replace_enclosed("'", "'", $arrayString, "=>", "0x3D0x3E");
        $arrayString = replace_enclosed('"', '"', $arrayString, "0x3D0x3E", "=>");
        
        $arrayString = replace_enclosed("[", "]", $arrayString, ",", "0x2C");
        $arrayString = replace_enclosed("[", "]", $arrayString, "=>", "0x3D0x3E");

        // Empty
        if(strlen($arrayString) == 0)
            return [];

        // Explode
        foreach(explode(",", $arrayString) as $value)
        {
            $value = trim($value);

            // Empty string
            if(strlen($value) == 0)
                continue;

            // Parse
            $explode = explode("=>", $value);
            $key = $this->parseInput($explode[0]);
            $value = @$explode[1];

            // Sequential
            if(count($explode) == 1)
            {
                $array[] = $key;
            }

            // Assoc
            else if(count($explode) === 2)
            {
                $value = str_replace("0x2C", ",", $value);
                $value = str_replace("0x3D0x3E", "=>", $value);

                // Parse value
                $array[$key] = $this->parseInput($value);
            }
        }

        // Fix escaped
        array_walk_recursive($array, function(&$value) {
            if(is_string($value))
                $value = $this->propertyEscape($value);
        });

        return $array;
    }

    /**
     * handleArrayOperation
     */
    private function handleArrayOperation(array $array, string $operator, null|array &$result = null)
    {
        if($result === null)
            $result = $array;
        else
        {
            if($operator == "+")
                $result = $result + $array;
            else if($operator == "|")
                $result = array_merge($result, $array);
            else if($operator == "*")
                $result = array_merge_recursive($result, $array);
            else
                throw new InvalidArgumentException("Invalid operator '$operator'");
        }
    }

    /**
     * parseApex
     */
    public function parseApex(string $input)
    {   
        $result = null;

        // Set values
        $arrayValues = [];

        // Parse stuff
        $replacement = replace_enclosed_function("[", "]", $input, function($arrayString) use (&$arrayValues)
        {
            $parsed = $this->parseArrayString($arrayString);

            $i = count($arrayValues);

            $arrayValues["[$i]"] = $parsed;

            return "$i";
        });
        
        // Remove spaces
        $replacement = preg_replace("/\s+/", "", $replacement);

        // Perform addition
        $offset = 0;
        $operator = "";

        // Perform operations
        while(preg_match("/\[\d+\]|.{1}/", $replacement, $matches, 0, $offset))
        {
            $match = $matches[0];
            $offset += strlen($match);

            if(str_starts_with($match, "[") && str_ends_with($match, "]"))
            {
                $this->handleArrayOperation($arrayValues[$match], $operator, $result);   
            }
            else
            {
                $operator = $match;
            }
        }

        return $result;
    }

    /**
     * isSingleQuoteFragment
     */
    private function isSingleQuoteFragment(string $input, string $quote)
    {
        $input = str_replace("\\".$quote, "", $input);
        return substr_count($input, $quote) === 2;
    }

    /**
     * parseInput
     */
    public function parseInput(string $input)
    {
        $input = trim($input);

        // Custom handlers
        foreach($this->inputHandlers as $handler)
            if($handler($input))
                return $input;

        // Boolean
        if($input == "true" || $input == "false")
            return $input == "true" ? true : false;

        // Null
        else if($input == "null")
            return null;

        // Numeric int or float
        else if(is_numeric($input))
            return typecast($input);

        // Constants
        else if(is_string($input) && defined($input))
            return \constant($input);

        // String domain
        else
        {
            // Escape
            $input = self::escape($input, $this->stringEscapeSymbols);

            // String enclosed in quotes
            if(preg_match("/^('|\"|\`)(.+?)(\\1)$/", $input, $matches) == 1 && $this->isSingleQuoteFragment($input, $matches[1]))
                return $matches[2];

            else
            {
                // Array string
                if($this->isArrayString($input))
                    return $this->parseApex($this->prepareArrayString($input));

                // String as default
                else
                    return $input;
            }
        }
    }

    /**
     * parse
     */
    public static function parse(string $input, array $inputHandlers = [], array $stringEscapeSymbols = [])
    {
        return (new StringValueParser($inputHandlers, $stringEscapeSymbols))->parseInput($input);
    }
}