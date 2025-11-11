<?php
declare(strict_types=1);

use gijsbos\ExtFuncs\Utils\DotEnv;
use gijsbos\ExtFuncs\Utils\Encrypter;

# Source
include_once "vendor/autoload.php";

# Check .env file exists
if(!is_file(".env"))
{
    $data = [
        "ENC_KEY" => Encrypter::generateKey(),
    ];

    file_put_contents(".env", implode("\n", array_map_assoc(function($k, $v)
    {
        return [$k, implode("=",[$k,$v])];
    }, $data)));
}

# Include files
DotEnv::parse();