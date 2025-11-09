<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Interfaces;

/**
 * EncrypterInterface
 *  Interface for encrypter
 */
interface EncrypterInterface
{
    public static function generateKey() : string;
    public static function encrypt($plaintext);
    public static function decrypt($ciphertext);
}