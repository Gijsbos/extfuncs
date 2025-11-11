<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use ReflectionClass;
use ReflectionFunction;

/**
 * Functions
 */
class Functions
{
    /**
     * executable
     */
    public static function executable(string $classMethod, null|string $method = null) : bool
    {
        if($method === null)
        {
            if(strpos(($classMethod = str_replace("::", "->", $classMethod)), "->"))
            {
                $classMethod = explode("->", $classMethod);
                $method = $classMethod[1];
                $classMethod = $classMethod[0];
            }
            else
                return is_callable($classMethod);
        }

        return method_exists($classMethod, $method) || function_exists($classMethod);
    }

    /**
     * execute
     */
    public static function execute(string $classMethod, array $constructorMethodArgs = [], array $methodArgs = [])
    {
        $isStatic = strpos($classMethod, "::");
        
        // Split the atom
        $classMethod = explode("->", str_replace("::", "->", $classMethod));

        // Set class and method
        $class = $classMethod[0];
        $method = @$classMethod[1];

        // Check if $method is set
        if($method === null)
        {
            if(!is_callable($class))
                throw new \Exception("Invalid method '$class'");

            return (new ReflectionFunction($class))->invoke(...$constructorMethodArgs);
        }

        // Create ReflectionClass
        if(!($reflection = new ReflectionClass($class))->hasMethod($method))
            throw new \Exception("Invalid class method '$class->$method'");

        // Execute
        if($isStatic)
            return call_user_func_array($class."::".$method, $constructorMethodArgs);
        else
            return call_user_func_array(array($reflection->newInstanceArgs($constructorMethodArgs), $method), $methodArgs);
    }
}