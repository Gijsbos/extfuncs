<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use Defuse\Crypto\Key;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use gijsbos\ExtFuncs\Interfaces\EncrypterInterface;

/**
 * Encrypter
 * Encrypts and decrypts data using ENV:ENC_KEY and Encrypter::CIPHER
 */
final class Encrypter implements EncrypterInterface
{
    /**
     * generateKey
     */
    public static function generateKey() : string
    {
        return (string) Key::createNewRandomKey()->saveToAsciiSafeString();
    }

    /**
     * encrypt
     */
    public static function encrypt($plaintext)
    {
        return Crypto::encrypt($plaintext, Key::loadFromAsciiSafeString(env("ENC_KEY", true)));
    }

    /**
     * decrypt
     */
    public static function decrypt($ciphertext, bool $strict = false)
    {
        try
        {
            return Crypto::decrypt($ciphertext, Key::loadFromAsciiSafeString(env("ENC_KEY", true)));
        }
        catch(WrongKeyOrModifiedCiphertextException $ex)
        {
            if($strict)
                throw $ex;

            return $ciphertext;
        }
    }
}