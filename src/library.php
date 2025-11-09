<?php

/**
 * env
 */
function env(string $key, null|bool|Exception $throws = null) : false | string
{
    if(getenv($key) !== false)
        return getenv($key);
    else if(isset($_ENV) && is_array($_ENV) && array_key_exists($key, $_ENV))
        return $_ENV[$key];
    else
    {
        if($throws !== null && $throws !== false)
            throw is_object($throws) ? $throws : new Exception("env var $key not found");

        return false;
    }
}

/**
 * include_recursive
 */
function include_recursive(string $input)
{
    if(is_file($input))
        include_once $input;
    else
        foreach(scandir($input) as $item)
            if($item !== "." && $item !== "..")
                include_recursive("$input/$item");
}