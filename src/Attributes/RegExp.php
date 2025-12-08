<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Attributes;

use Attribute;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;

#[Attribute()]
class RegExp
{
    public function __construct(private string $regexp)
    {}

    public function getRegExp()
    {
        return $this->regexp;
    }

    public function isValid(string $value, &$matches = null)
    {
        return preg_match($this->regexp, $value, $matches) !== 0;
    }

    public function validate(string $value, ?string $argumentName = null, &$matches = null)
    {
        if(!$this->isValid($value, $matches))
        {
            throw new InvalidArgumentException("Argument" . (is_string($argumentName) ? " $argumentName " : " ") . "is invalid");
        }
    }

    public static function extractFrom($className, $propertyOrMethod) : RegExp
    {
        if(!class_exists($className))
        {
            throw new LogicException("Class '$className' does not exist");
        }
        
        $reflection = new ReflectionClass($className);
        $target = $reflection;

        if(is_string($propertyOrMethod) && strlen($propertyOrMethod) > 0)
        {
            if($reflection->hasProperty($propertyOrMethod))
            {
                $target = $reflection->getProperty($propertyOrMethod);
            }
            else if ($reflection->hasMethod($propertyOrMethod))
            {
                $target = $reflection->getProperty($propertyOrMethod);
            }
            else
                throw new LogicException("RegExp pattern attribute not found for class '$className/$propertyOrMethod'");
        }

        $attributes = array_filter($target->getAttributes(), fn($a) => $a->getName() == __CLASS__ || is_subclass_of($a->getName(), __CLASS__));

        if(count($attributes) > 0)
            return reset($attributes)->newInstance();
        else
            throw new LogicException("RegExp pattern attribute not found in class '$className'");
    }
}