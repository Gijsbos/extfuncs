<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Attributes;

use Attribute;

#[Attribute()]
class RegExp
{
    public function __construct(private string $regexp)
    {}

    public function getRegExp()
    {
        return $this->regexp;
    }

    public function validate(string $value)
    {
        return preg_match($this->regexp, $value) !== 0;
    }
}