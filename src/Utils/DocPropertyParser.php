<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use InvalidArgumentException;
use ReflectionClass;

/**
 * DocPropertyParser
 */
class DocPropertyParser
{
    /**
     * __construct
     */
    public function __construct()
    {
        
    }

    /**
     * getObject
     */
    private function getObject($object)
    {
        if(is_string($object))
        {
            if(!class_exists($object))
                throw new InvalidArgumentException("Invalid argument #1 string expecting class name");

            // Get reflection
            $object = new ReflectionClass($object);
        }

        return $object;
    }

    /**
     * parseDocCommentProperty
     */
    private function parseDocCommentProperty($reflection, string $key, string $value, null|array $customParsers = null)
    {
        if(is_array($customParsers) && array_key_exists($key, $customParsers))
        {
            $customParser = $customParsers[$key];

            // Return value
            return $customParser($key, $value, $reflection);
        }
        
        // Default
        return StringValueCaster::cast($value);
    }

    /**
     * parseDocComment
     */
    private function parseDocComment(object $object, string $docComment, null|array $customParsers = null)
    {
        // Store result
        $docProperties = [];

        // Remove comment symbols
        $lines = explode("\n", $docComment);

        // Parse lines
        foreach($lines as $line)
        {
            // Trim
            $line = ltrim($line);

            // Remove comment symbols from start
            while(strlen($line) && ($line[0] == "*" || $line[0] == "/"))
                $line = substr($line, 1);

            // Trim
            $line = ltrim($line);

            // Parse line
            if(strlen($line))
            {
                // Parse property
                if(preg_match("/@(\w+)(.*)/", $line, $matches))
                {
                    $key = $matches[1];
                    $value = trim($matches[2]);

                    // Parse key
                    $propertyValue = self::parseDocCommentProperty($object, $key, $value, $customParsers);

                    // If the key has been defined more than once, we turn the docPropertyValue into an array
                    if(array_key_exists($key, $docProperties))
                    {
                        // Get existing value
                        $docPropertyValue = $docProperties[$key];

                        // If array, we add the new value to the array
                        if(is_array($docPropertyValue))
                            $docProperties[$key][] = $propertyValue;

                        // If anything else, we turn the value into an array
                        else
                            $docProperties[$key] = [$docPropertyValue, $propertyValue];
                    }
                    else
                        $docProperties[$key] = $propertyValue;
                }
            }
        }
        return $docProperties;
    }

    /**
     * parseDocProperties
     * 
     * @param object $object
     */
    public function parseDocProperties($object, null|array $customParsers = null) : array
    {
        $object = $this->getObject($object);

        // Get doc comment
        $docComment = $object->getDocComment();

        // Not found
        if($docComment === false)
            return [];
        
        // Parse
        return $this->parseDocComment($object, $docComment, $customParsers);
    }

    /**
     * parse
     */
    public static function parse($class, null|array $customParsers = null) : array
    {
        return (new self())->parseDocProperties($class, $customParsers);
    }
}