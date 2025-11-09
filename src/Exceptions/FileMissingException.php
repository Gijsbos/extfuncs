<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Exceptions;

use Exception;

class FileMissingException extends Exception 
{
    public function __construct($message) 
    {
        parent::__construct($message);
    }
}