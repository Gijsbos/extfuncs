<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Exceptions;

use Exception;

/**
 * FileNotFoundException
 */
class FileNotFoundException extends Exception 
{
    public function __construct() 
    {
        parent::__construct(call_user_func_array('sprintf', func_get_args()));
    }
}