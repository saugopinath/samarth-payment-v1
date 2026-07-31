<?php

namespace App\Helpers;

class SBIEncryptDecrypt
{
    public static function file_encrypt($fileContent, $publicKey)
    {
        $data = $fileContent;

        // Generate AES key and IV
        $aesKey = openssl_random_pseudo_bytes(32); // 256-bit AES key
        $iv = openssl_random_pseudo_bytes(16);     // 16 bytes IV

        // Encrypt data with AES/GCM
        $ciphertext = openssl_encrypt($data, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        if (empty($publicKey)) {
            throw new \Exception("Public key is empty in file_encrypt.");
        }
        $parsedKey = openssl_get_publickey($publicKey);
        if ($parsedKey === false) {
            throw new \Exception("Public key could not be parsed by openssl_get_publickey: " . openssl_error_string());
        }

        // Encrypt AES key with RSA
        $success = openssl_public_encrypt($aesKey, $encryptedAesKey, $parsedKey, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$success) {
            throw new \Exception("openssl_public_encrypt failed: " . openssl_error_string());
        }

        // Concatenate encrypted AES key, IV, ciphertext, and tag
        $encryptedOutput = $encryptedAesKey . $iv . $ciphertext . $tag;

        // Encode with Base64
        $encodedOutput = base64_encode($encryptedOutput);

        return $encodedOutput;
    }
    public static function file_decrypt($fileContent, $privateKey)
    {
        $encodedEncryptedData = $fileContent;

        // Decode Base64
        $encryptedData = base64_decode($encodedEncryptedData);

        // Extract encrypted AES key, IV, ciphertext, and tag
        $encryptedAesKey = substr($encryptedData, 0, 256);
        $iv = substr($encryptedData, 256, 16);
        $ciphertext = substr($encryptedData, 272, -16);
        $tag = substr($encryptedData, -16);

        // Decrypt AES key with RSA
        openssl_private_decrypt($encryptedAesKey, $aesKey, $privateKey, OPENSSL_PKCS1_OAEP_PADDING);

        // Decrypt data with AES/GCM
        $decryptedData = openssl_decrypt($ciphertext, 'aes-256-gcm', $aesKey, OPENSSL_RAW_DATA, $iv, $tag);

        return $decryptedData;
    }
}
