<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use gijsbos\ExtFuncs\Exceptions\DotEnvException;

/**
 * DotEnv
 */
class DotEnv
{
    const FILE_NAME = ".env";

    /**
     * register
     *  Dont use shell_exec("export $key=$value") for linux, it is super slow
     */
    public static function register(string $key, $value)
    {
        putenv("$key=$value");
        $_ENV[$key] = $value;

        // Verify value exists
        if(getenv($key) === false)
            throw new DotEnvException("Register $key failed, env var not set");
    }

    /**
     * parseEnvText
     */
    public static function parseEnvText(string $text, bool $register = true)
    {
        $environment = [];
        $lines = explode("\n", $text);
        $count = count($lines);

        for($i = 0; $i < $count; $i++)
        {
            $line = ltrim($lines[$i]);
            $equalSignPos = strpos($line, "=");
            
            if(strlen($line) > 0 && $line[0] !== "#" && $equalSignPos !== false)
            {
                $name = trim(substr($line, 0, $equalSignPos));
                $value = trim(substr($line, $equalSignPos + 1));
                
                // Name is found, and value not yet set in ENV
                if(strlen($name))
                {
                    $value = strlen($value) ? StringValueCaster::cast($value) : "";

                    // Register
                    if($register)
                    {
                        self::register($name, $value);
                    }

                    // Add data
                    $environment[$name] = $value;
                }
            }
        }
        return $environment;
    }

    /**
     * parse
     */
    public static function parse(bool $register = true) : array
    {
        $filePath = self::FILE_NAME;

        if(!is_file($filePath))
            return [];

        $contents = file_get_contents($filePath);
        
        $result = self::parseEnvText($contents, $register);

        return $result;
    }

    /**
     * write
     *  The .env file must be available at all times, therefore changes are made into a clone which replaces the original file.
     */
    public static function write(string $key, $value, bool $register = true, bool $overwrite = false) : false | array
    {
        $filePath = "./" . self::FILE_NAME;

        // File not found, stop
        if(!is_file($filePath))
        {
            $result = fopen($filePath, "w");

            // No success
            if($result === false)
                throw new DotEnvException("Could not create new environment file '$filePath");
        }

        // Make sure file exists
        if(!is_file($filePath))
            return false;

        // Set lock file name
        $filePathTemp = "$filePath.temp";

        // If there is a lock file, prevent writing to file
        if(is_file($filePathTemp))
            return false;

        // Get file contents
        $fileContents = file_get_contents($filePath);

        // Copy file contents into a lock file
        file_put_contents($filePathTemp, $fileContents);

        // Key exists
        $keyExists = preg_match("/^$key=/m", $fileContents) == 1;

        // Read content
        if($keyExists && $overwrite)
            $fileContents = preg_replace("/^$key\s*=.+/m", "$key=$value", $fileContents);

        // No replacements, add to file
        if(!$keyExists)
            $fileContents .= strlen(trim($fileContents)) == 0 ? "$key=$value" : "\n$key=$value";

        // Register
        if($register)
        {
            try
            {
                self::register($key, $value);
            }
            catch(DotEnvException $ex)
            {
                if(is_file($filePathTemp))
                    unlink($filePathTemp);
                throw $ex;
            }
        }

        // Store new content in lock file 
        file_put_contents($filePathTemp, $fileContents);

        // Overwrite original env file
        $result = rename($filePathTemp, $filePath);

        // Failed
        if($result === false)
            throw new DotEnvException("Could not rename $filePathTemp to $filePath");

        // Return new env
        return self::parseEnvText($fileContents);
    }

    /**
     * unregister
     */
    public static function unregister(string $key)
    {
        putenv("$key");
        unset($_ENV[$key]);
    }

    /**
     * delete
     */
    public static function delete(string $key, bool $unregister = true)
    {
        $filePath = self::FILE_NAME;

        if(!is_file($filePath))
            return false;

        // Set lock file name
        $filePathTemp = "$filePath.temp";

        // If there is a lock file, prevent writing to file
        if(is_file($filePathTemp))
            return false;

        // Get file contents
        $fileContents = file_get_contents($filePath);
        
        // Read content
        if(strpos($fileContents, "$key=") !== false)
            $fileContents = preg_replace("/\n?$key\s*=.+/", "", $fileContents);

        // Register
        if($unregister)
            self::unregister($key);

        // Store new content in lock file 
        file_put_contents($filePathTemp, $fileContents);

        // Overwrite original env file
        rename($filePathTemp, $filePath);
    }
}