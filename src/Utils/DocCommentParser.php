<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Exception;
use ReflectionMethod;

/**
 * DocCommentParser
 * 
 *  Parses Doc Comment Blocks.
 * 
 *  Define a property:
 *      @<key>: <value>
 * 
 *  Refer to property in same doc comment:
 *      @<key>: {<key>}
 *  Refer to property in same class
 *      @<key>: {<methodName>::<key>}
 *  Refer to property in third party class
 *      @<key>: {<className>::<methodName>::<key>}
 *  Refer to class constant
 *      @<key>: {<className>::<constantName>}
 * 
 *  Merge array values:
 *      @Array1: ['value1'];
 *      @Array2: ['value2'];
 *      @Merged: {Array1} + {Array2}
 * 
 */
class DocCommentParser
{
    const ESCAPE_SYMBOLS = [
        "@" => "0x40",
    ];

    public $reflection;
    public $propertyPrefix;
    public $propertyAppendix;
    public $skipProperties;
    
    /**
     * __construct
     */
    public function __construct($reflection, string $propertyPrefix = '@', string $propertyAppendix = ':', array $skipProperties = array())
    {
        $this->reflection = $reflection;
        $this->propertyPrefix = $propertyPrefix;
        $this->propertyAppendix = $propertyAppendix;
        $this->skipProperties = $skipProperties;
    }

    /**
     * clearDocCommentSymbols
     */
    private static function clearDocCommentSymbols(string $comment)
    {
        return trim(preg_replace("/^\/?[\t ]*\*+[\t ]*\/?/m", "", $comment));
    }

    /**
     * parseProperties
     */
    private function parseProperties(string $comment) : array
    {
        $propertyAppendix = $this->propertyAppendix;
        
        return array_map_assoc(function($key, $value) use ($propertyAppendix)
        {
            // Remove abundant spacing
            $value = preg_replace("/\s+/", " ", $value);

            // Parse
            if(preg_match("/([\w\-\[\]]+)$propertyAppendix(.*)/", $value, $matches))
            {
                $k = trim($matches[1]);
                $v = trim($matches[2]);
                return [$k, $v];
            }
            else
                return [$value, ""];
        }, array_slice(explode($this->propertyPrefix, $comment), 1));
    }

    /**
     * inArray
     */
    private static function inArray($needle, array $array)
    {
        return in_array(strtolower($needle), array_map('strtolower', $array));
    }

    /**
     * isDocPropReference
     */
    private static function isDocPropReference(string $input)
    {
        return str_starts_with($input, "{") && str_ends_with($input, "}");
    }

    /**
     * parseDocPropReference
     *  Parses doc property references indicated by placeholders {<name>}.
     *  Example:
     *  {Prop1} => looks up Prop1 value in current property list
     *  {Method::Prop1}
     *  {\NAMESPACE\ClassName::Method::Prop1} => looks up Prop1 value in Method in \NAMESPACE\ClassName
     */
    private function parseDocPropReference(string $input, array $properties)
    {
        // Replace
        $input = str_replace("->", "::", $input);

        // Count ::
        $input = substr($input, 1, strlen($input) - 2);
        $explode = explode("::", $input);
        $count = count($explode);
        
        // Lookup prop in same doc
        if($count === 1)
        {
            // Check if property is found
            if(!self::inArray($input, array_keys($properties)))
                throw new Exception(sprintf("%s failed, doc property reference '%s' does not exist", __METHOD__, $input));

            // Check if property is array
            return $properties[$input];
        }

        // Class::Constant or Class::Method or Class::Method:DocCommentPropertySelector
        else if($count === 2 || $count === 3)
        {
            $class = $explode[0];
            $property = $explode[1];
            $docCommentPropertySelector = @$explode[2];

            // Correct class
            if(!class_exists($class))
            {
                if(method_exists($this->reflection->class, $class))
                {
                    $docCommentPropertySelector = $property;
                    $property = $class;
                    $class = $this->reflection->class;
                }
            }

            // Determine contents
            $isMethod = method_exists($class, $property);
            $isConstant = defined($constant = "$class::$property");

            // Return constant
            if($isConstant)
                return constant($constant);

            // Return method
            else if($isMethod)
            {
                $method = $property;

                // Parse properties
                $docProperties = self::parse(new ReflectionMethod($class, $method));

                // Check if property is found in docProperties
                if(!array_key_exists($docCommentPropertySelector, $docProperties))
                    throw new Exception(sprintf("%s failed, doc property reference '%s::%s::%s' does not exist", __METHOD__, $class, $method, $docCommentPropertySelector));

                // Return value
                return $docProperties[$docCommentPropertySelector];
            }
            else
                throw new Exception("Unknown reference found '$input'");
        }
        
        // Throw exception
        throw new Exception(sprintf("%s failed, invalid placeholder format '%s'", __METHOD__, $input));
    }

    /**
     * typeCastProperty
     */
    private function typeCastProperty($value, array $properties)
    {
        return StringValueParser::parse($value,
        [
            // Parse doc placeholders
            function(string &$input) use ($properties)
            {
                // Check if properties have been set
                if($properties !== null && $this->isDocPropReference($input))
                {
                    // Parse
                    $input = $this->parseDocPropReference($input, $properties);

                    // True will submit the changes
                    return true;
                }
                // Return false
                return false;
            }
        ], self::ESCAPE_SYMBOLS);
    }

    /**
     * typeCastProperties
     */
    private function typeCastProperties(array $properties) : array
    {
        foreach($properties as $key => $value)
            if(!self::inArray($key, $this->skipProperties))
                $properties[$key] = $this->typeCastProperty($value, $properties);

        return $properties;
    }

    /**
     * unescape
     */
    private function unescape(array $properties)
    {
        foreach($properties as $key => $value)
        {
            if(is_string($value))
            {
                $properties[$key] = StringValueParser::unescape($properties[$key], self::ESCAPE_SYMBOLS);

                foreach(self::ESCAPE_SYMBOLS as $search => $replacement)
                {    
                    $properties[$key] = str_replace($replacement, $search, $properties[$key]);
                }
            }
        }

        return $properties;
    }

    /**
     * parseComment
     */
    public function parseComment() : array
    {
        // Get comment
        $comment = $this->reflection->getDocComment();

        // Check if comment was provided
        if($comment === false)
            return [];

        // Remove doc comment symbols
        $comment = self::clearDocCommentSymbols($comment);
        
        // escape
        $comment = StringValueParser::escape($comment, self::ESCAPE_SYMBOLS);
        
        // Parse properties
        $properties = $this->parseProperties($comment);

        // Typecast
        $properties = $this->typeCastProperties($properties);

        // Unescape
        $properties = $this->unescape($properties);

        // Return properties
        return $properties;
    }

    /**
     * parse
     * 
     * @param ReflectionProperty|ReflectionMethod $reflection
     * @return array
     */
    public static function parse($reflection, array $options = array()) : array
    {
        // Set parse options
        $propertyPrefix = array_key_exists("propertyPrefix", $options) ? $options["propertyPrefix"] : '@';
        $propertyAppendix = array_key_exists("propertyAppendix", $options) ? $options["propertyAppendix"] : ':';
        $skipProperties = array_key_exists("skipProperties", $options) ? $options["skipProperties"] : [];

        // Return result
        return (new DocCommentParser($reflection, $propertyPrefix, $propertyAppendix, $skipProperties))->parseComment();
    }
}