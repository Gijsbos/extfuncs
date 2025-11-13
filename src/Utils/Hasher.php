<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

/**
 * Hasher
 */
abstract class Hasher
{
    /**
     * Hash (length 128)
     * @param string $input
     * @return string
     */
    public static function hash($algo, $input) : string 
    {
        return hash_hmac($algo, (string) $input, env("HASH_KEY") !== false ? env("HASH_KEY") : '');
    }

    /**
     * Hash (length 128)
     * @param string $input
     * @return string
     */
    public static function sha512($input) : string 
    {
        return self::hash("sha512", $input);
    }

    /**
     * Hash (length 128)
     * @param string $input
     * @return string
     */
    public static function sha256($input) : string 
    {
        return self::hash("sha256", $input);
    }
}