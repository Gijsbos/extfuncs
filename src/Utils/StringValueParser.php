<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;

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
        if((str_starts_with($input, "array(") && str_ends_with($input, ")")))
        {
            $input = sprintf("[%s]", substr($input, strlen("array("), strlen($input) - strlen("array(") - 1));
            return true;
        }
        else
            return str_starts_with($input, "[") && str_ends_with($input, "]");
    }

    /**
     * parseArray
     */
    private function parseArray(string $arrayString) : array
    {
        $array = [];

        // Unescape array string symbols for recursive parsing
        $arrayString = $this->propertyEscape($arrayString);
        
        // Extract
        if(!$this->isArrayString($arrayString))
            throw new Exception(sprintf("%s failed, invalid start/end symbol for array parsing"));

        // If the array is empty, return an empty array.
        // Prevents further processing of the empty array that results in an array with an empty string
        if(str_replace(" ", "", $arrayString) == "[]")
            return [];

        // Get inner content
        $arrayString = trim(substr($arrayString, 1, strlen($arrayString) - 2));
        
        // Replace trailing comma in array definition e.g. [key => [value], key2,] (trailing comma behind key2)
        $arrayString = preg_replace("/,(\s+)\]/", "\\1]", $arrayString);
        
        // Escape comma's in brackets
        $arrayString = replace_enclosed("[", "]", $arrayString, ",", "0x2C");
        
        // Escape inner bracket '=>'
        $arrayString = replace_enclosed("[", "]", $arrayString, "=>", "0x3D0x3E");
        
        // Escape inner string '=>'
        $arrayString = self::replace("/('|\"|\`)(.+?)(\\1)/", "=>", "0x3D0x3E", $arrayString, 2);
        
        // Escape symbols in strings
        $arrayString = self::replace("/('|\"|\`)(.+?)(\\1)/", ",", "0x2C", $arrayString, 2);
        
        // Parse array values separated by comma
        foreach(explode(",", $arrayString) as $value)
        {
            $explode = explode("=>", $value);
            $key = $this->parseInput($explode[0]);

            // Sequential
            if(count($explode) == 1)
                $array[] = $key;

            // Assoc
            else if(count($explode) === 2)
                $array[$key] = $this->parseInput($explode[1]);
        }

        // Fix escaped
        array_walk_recursive($array, function(&$value) {
            if(is_string($value))
                $value = $this->propertyEscape($value);
        });

        // Return array
        return $array;
    }

    /**
     * isMergeString
     */
    private function isMergeString(string $input) : bool
    {
        return strpos(self::escape($input, ["+" => "0x2B"]), "+") !== false;
    }

    /**
     * parseMergeString
     */
    private function parseMergeString(string $input)
    {
        $escaped = self::escape($input, ["+" => "0x2B"]);

        // Current value
        $value = null;

        // Iterate over values
        foreach(explode("+", $escaped) as $input)
        {
            $parsed = $this->parseInput($input);

            if($value === null)
                $value = $parsed;
            else
            {
                if(is_string($value) && is_string($parsed))
                    $value .= $parsed;
                else if(is_array($value) && is_array($parsed))
                    $value = array_merge($value, $parsed);
            }
        }

        // Return value
        return $value;
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

            // Array string
            else if($this->isArrayString($input))
                return $this->parseArray($input);

            // Array merge string
            else if($this->isMergeString($input))
                return $this->parseMergeString($input);

            // String as default
            else
                return $input;
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