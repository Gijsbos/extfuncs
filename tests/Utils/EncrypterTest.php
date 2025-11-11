<?php
declare(strict_types=1);

namespace gijsbos\ExtFuncs\Utils;

use PHPUnit\Framework\TestCase;

final class EncrypterTest extends TestCase 
{
    public function testEncryptDecrypt()
    {
        $plaintext = "Encrypt this";
        $ciphertext = Encrypter::Encrypt($plaintext);
        $decrypttext = Encrypter::Decrypt($ciphertext);
        $this->assertEquals($plaintext, $decrypttext);
    }

    public function testDecryptFailureReturnsFalse()
    {
        $ciphertext = "cannot decrypt";
        $decrypttext = Encrypter::Decrypt($ciphertext);
        $this->assertEquals($ciphertext, $decrypttext);
    }
}