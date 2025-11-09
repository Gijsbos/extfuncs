<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

define("INLINE",    flag_id('WDS\TextParser'));
define("BLOCK",     flag_id('WDS\TextParser'));
define("COLON",     flag_id('WDS\TextParser'));
define("SEMICOLON", flag_id('WDS\TextParser'));
define("HASHTAG",   flag_id('WDS\TextParser'));
define("HYPHEN",    flag_id('WDS\TextParser'));

/**
 * TextFormat
 */
abstract class TextParser
{
    const UNICODE_MAP = array(
        COLON => array("symbol" => ":", "unicode" => "U+003A"),
        SEMICOLON => array("symbol" => ";", "unicode" => "U+003B"),
        HASHTAG => array("symbol" => "#", "unicode" => "U+0023"),
        HYPHEN => array("symbol" => "-", "unicode" => "U+002D"),
    );

    /**
     * escape
     */
    public static function escape($input, int $flag)
    {
        if(!is_string($input)) return $input;

        foreach(self::UNICODE_MAP as $key => $value)
            if($key & $flag)
                $input = str_replace(self::UNICODE_MAP[$key]["symbol"], self::UNICODE_MAP[$key]["unicode"], $input);
        
        return $input;
    }

    /**
     * unescape
     */
    public static function unescape($input, int $flag)
    {
        if(!is_string($input)) return $input;

        foreach(self::UNICODE_MAP as $key => $value)
        if($key & $flag)
            $input = str_replace(self::UNICODE_MAP[$key]["unicode"], self::UNICODE_MAP[$key]["symbol"], $input);
    
        return $input;
    }

    /**
     * applyToOpenCloseChar
     */
    public static function applyToOpenCloseChar($input, string $openChar, string $closeChar, callable $function)
    {
        if(!is_string($input)) return $input;

        // Create regexp
        $regexp = '/\\' . $openChar . '.+\\' . $closeChar . "/U";

        // Match
        preg_match_all($regexp, $input, $matches);
        foreach($matches[0] as $match)
        {
            $replace = $function($match);
            $input = str_replace($match, is_numeric($replace) ? strval($replace) : $replace, $input);
        }

        //
        return $input;
    }
    
    /**
     * applyToInnerQuotes
     */
    public static function applyToInnerQuotes($input, callable $function)
    {
        $input = self::applyToOpenCloseChar($input, '"', '"', $function);
        $input = self::applyToOpenCloseChar($input, "'", "'", $function);
        $input = self::applyToOpenCloseChar($input, "`", "`", $function);
        return $input;
    }

    /**
     * getInnerQuote
     */
    public static function getInnerQuote(string $line) : string
    {
        if(preg_match("/\"(.*)\"/", $line, $matches))
            return $matches[1];

        if(preg_match("/\'(.*)\'/", $line, $matches))
            return $matches[1];

        if(preg_match("/\`(.*)\`/", $line, $matches))
            return $matches[1];
        
        return $line;
    }
    
    /**
     * removeComments
     * 
     *  Removes inline and block comments from an input string.
     *  
     *  flags:
     *   - INLINE: (default) Removes hash, slash and block comments defined on a single line
     *   - BLOCK: (default) Remove block comments spanning multiple lines
     */
    public static function removeComments(string $string, null|int $flags = null, bool $multiByteSafe = false) : string
    {
        // Set functions
        $strlen = $multiByteSafe ? 'mb_strlen' : 'strlen';
        $substr = $multiByteSafe ? 'mb_substr' : 'substr';

        // Check flags
        $flags = $flags === null ? INLINE | BLOCK : $flags;

        // Escape escaped quotes with UTF-8 encoding
        $string = preg_replace("/(?<!\\\)\\\[\"]/", "0x5C0x22", $string);
        $string = preg_replace("/(?<!\\\)\\\[\']/", "0x5C0x27", $string);
        $string = preg_replace("/(?<!\\\)\\\[\`]/", "0x5C0x60", $string);

        // Match
        preg_match_all("/\"(\\[\s\S]|[^\"])*\"|'(\\[\s\S]|[^'])*'|`(\\[\s\S]|[^`])*`|(\/\/.*|#.*|\/\*[\s\S]*?\*\/)/", $string, $matches, PREG_OFFSET_CAPTURE);

        // Iterate
        $offsetDelta = 0;

        // Iterate over matches
        foreach($matches[0] as $i => $details)
        {
            $match = $details[0];
            $matchLength = $strlen($match);
            $offset = $details[1] + $offsetDelta;
            $stringLength = $strlen($string);
        
            if( (str_starts_with($match, "//") || str_starts_with($match, "#")) && ($flags & INLINE))
            {
                // Perform replace
                $string = \substr_replace($string, "", $offset, $matchLength);

                // Add to delta
                $offsetDelta += ($strlen($string) - $stringLength);
            }

            else if( str_starts_with($match, "/*") && str_ends_with($match, "*/") && ($flags & BLOCK))
            {
                // Count number of newlines
                $newlines = substr_count($substr($string, $offset, $matchLength), "\n");
                
                // Replacement
                $replacement = \str_repeat("\n", $newlines);

                // Replace in string
                $string = \substr_replace($string, $replacement, $offset, $matchLength);

                // Add to delta
                $offsetDelta += ($strlen($string) - $stringLength);
            }
        }

        // Replace escaped chars
        return str_replace("0x5C0x22", '\"', str_replace("0x5C0x27", "\'", str_replace("0x5C0x60", "\`", $string)));
    }

    /**
     * replaceInComments
     */
    public static function replaceInComments(string $string, string $search, string $replace, null|int $flags = null, bool $multiByteSafe = false) : string
    {
        // Set functions
        $strlen = $multiByteSafe ? 'mb_strlen' : 'strlen';
        $substr = $multiByteSafe ? 'mb_substr' : 'substr';

        // Check flags
        $flags = $flags === null ? INLINE | BLOCK : $flags;

        // Escape escaped quotes with UTF-8 encoding
        $string = preg_replace("/(?<!\\\)\\\[\"]/", "0x5C0x22", $string);
        $string = preg_replace("/(?<!\\\)\\\[\']/", "0x5C0x27", $string);
        $string = preg_replace("/(?<!\\\)\\\[\`]/", "0x5C0x60", $string);

        // Match
        preg_match_all("/\"(\\[\s\S]|[^\"])*\"|'(\\[\s\S]|[^'])*'|`(\\[\s\S]|[^`])*`|(\/\/.*|#.*|\/\*[\s\S]*?\*\/)/", $string, $matches, PREG_OFFSET_CAPTURE);

        // Iterate
        $offsetDelta = 0;

        // Iterate over matches
        foreach($matches[0] as $i => $details)
        {
            $match = $details[0];
            $matchLength = $strlen($match);
            $offset = $details[1] + $offsetDelta;
            $stringLength = $strlen($string);
        
            if( (str_starts_with($match, "//") || str_starts_with($match, "#")) && ($flags & INLINE))
            {
                // Get current section
                $section = substr($string, $offset, $matchLength);

                // Create replacements
                $replacement = str_replace($search, $replace, $section);

                // Perform replace
                $string = \substr_replace($string, $replacement, $offset, $matchLength);

                // Add to delta
                $offsetDelta += ($strlen($string) - $stringLength);
            }

            else if( str_starts_with($match, "/*") && str_ends_with($match, "*/") && ($flags & BLOCK))
            {
                // Get current section
                $section = $substr($string, $offset, $matchLength);

                // Create replacements
                $replacement = str_replace($search, $replace, $section);

                // Replace in string
                $string = \substr_replace($string, $replacement, $offset, $matchLength);

                // Add to delta
                $offsetDelta += ($strlen($string) - $stringLength);
            }
        }

        // Replace escaped chars
        return str_replace("0x5C0x22", '\"', str_replace("0x5C0x27", "\'", str_replace("0x5C0x60", "\`", $string)));
    }

    /**
     * isComment
     */
    public static function isComment(string $match) : bool
    {
        return str_starts_with($match, "//") || str_starts_with($match, "#") || (str_starts_with($match, "/*") && str_ends_with($match, "*/"));
    }

    /**
     * isQuote
     */
    public static function isQuote(string $match) : bool
    {
        return str_starts_ends_with($match, '"') || str_starts_ends_with($match, "'") || str_starts_ends_with($match, "`");
    }

    /**
     * replaceInQuote
     */
    public static function replaceInQuote(string $match, string $search, string $replace) : string
    {
        if(self::isQuote(($match)))
        {
            // Get quote
            $quote = $match[0];

            // Replace
            return $quote . str_replace($search, $replace, substr($match, 1, strlen($match) - 2)) . $quote;
        }

        return $match;
    }

    /**
     * replaceCommentQuote
     */
    public static function replaceCommentQuote(string $text, callable $function)
    {
        // Escape escaped quotes with UTF-8 encoding
        $text = preg_replace("/(?<!\\\)\\\[\"]/", "0x5C0x22", $text);
        $text = preg_replace("/(?<!\\\)\\\[\']/", "0x5C0x27", $text);
        $text = preg_replace("/(?<!\\\)\\\[\`]/", "0x5C0x60", $text);

        // Match
        preg_match_all("/\"(\\[\s\S]|[^\"])*\"|'(\\[\s\S]|[^'])*'|`(\\[\s\S]|[^`])*`|(\/\/.*|#.*|\/\*[\s\S]*?\*\/)/", $text, $matches, PREG_OFFSET_CAPTURE);

        // Iterate
        $offsetDelta = 0;

        // Iterate over matches
        foreach($matches[0] as $i => $details)
        {
            // Extract details
            $match = $details[0];
            $matchLength = strlen($match);
            $offset = $details[1] + $offsetDelta;
            $stringLength = strlen($text);

            // Create replacements
            $replacement = $function($match, $offset);

            // Perform replace
            $text = \substr_replace($text, $replacement, $offset, $matchLength);

            // Add to delta
            $offsetDelta += (strlen($text) - $stringLength);
        }

        // Replace escaped chars
        return str_replace("0x5C0x22", '\"', str_replace("0x5C0x27", "\'", str_replace("0x5C0x60", "\`", $text)));
    }
}