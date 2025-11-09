<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Exceptions;

/**
 * FileUploadHandlerException
 */
class FileUploadHandlerException extends \Exception
{
    public $error;

    /**
     * __construct
     */
    public function __construct(string $error, ...$args)
    {
        $this->error = $error;

        // Run parent
        parent::__construct(call_user_func_array('sprintf', $args));
    }
}