<?php

namespace Insitaction\Service;

use Exception;

class EncryptService
{
    public const METHOD = 'aes-256-cbc';

    public function __construct(private string $encryptKey)
    {
    }

    /**
     * @throws Exception
     */
    public function encrypt(string $data): string
    {
        if (SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES !== mb_strlen($this->encryptKey, '8bit')) {
            throw new Exception('Wrong secret format', 500);
        }

        $ivsize = openssl_cipher_iv_length(self::METHOD);

        if (false === $ivsize) {
            throw new Exception('openssl_cipher_iv_length error', 500);
        }

        $iv = openssl_random_pseudo_bytes($ivsize);
        $ciphertext = openssl_encrypt($data, self::METHOD, $this->encryptKey, OPENSSL_RAW_DATA, $iv);

        if (false === $ciphertext) {
            throw new Exception('Encryption Error', 500);
        }

        return $iv . $ciphertext;
    }

    /**
     * @param resource $ressource
     *
     * @throws Exception
     */
    public function decrypt($ressource): string
    {
        $data = stream_get_contents($ressource);

        if (false === $data) {
            throw new Exception('Can\'t reads remainder of a stream into a string', 500);
        }

        if (SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES !== mb_strlen($this->encryptKey, '8bit')) {
            throw new Exception('Wrong secret format', 500);
        }

        $ivsize = openssl_cipher_iv_length(self::METHOD);

        if (false === $ivsize) {
            throw new Exception('openssl_cipher_iv_length error', 500);
        }

        $iv = mb_substr($data, 0, $ivsize, '8bit');

        if (SODIUM_CRYPTO_AEAD_AES256GCM_ABYTES != strlen($iv)) {
            throw new Exception('Wrong data encryption', 500);
        }

        $decrypted = openssl_decrypt(
            mb_substr($data, $ivsize, null, '8bit'),
            self::METHOD,
            $this->encryptKey,
            OPENSSL_RAW_DATA,
            $iv
        );

        if (false === $decrypted) {
            throw new Exception('Decrypt Error', 500);
        }

        return $decrypted;
    }
}
