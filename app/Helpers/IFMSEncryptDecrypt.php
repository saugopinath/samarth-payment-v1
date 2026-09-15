<?php

namespace App\Helpers;

class IFMSEncryptDecrypt
{
    public static function generateAES256Key()
    {
        return random_bytes(32); // raw binary
    }

    public static function encryptSymmetricKey($symmetricKey, $publicKeyPath)
    {
        $publicKey = file_get_contents($publicKeyPath);
        openssl_public_encrypt($symmetricKey, $encrypted, $publicKey, OPENSSL_PKCS1_OAEP_PADDING);
        return $encrypted; // raw binary
    }

    public static function decrypt($encryptedSekBase64, $symmetricKey)
    {
        $encryptedSek = base64_decode($encryptedSekBase64, true);
        //  dump($symmetricKey);
        if ($encryptedSek === false) {
            throw new \Exception("Invalid base64 SEK");
        }

        // ❗ Debug key size
        // dump("Key length = ", strlen($symmetricKey)); // MUST be 32

        $decrypted = openssl_decrypt(
            $encryptedSek,
            'AES-256-ECB',
            $symmetricKey,
            OPENSSL_RAW_DATA
        );

        //dd($decrypted); // to see result

        if ($decrypted === false) {
            throw new \Exception("openssl_decrypt() failed — wrong AES key");
        }
        // return $decrypted;

        return base64_encode($decrypted);
    }
     public static function decrypt1($encryptedSekBase64, $symmetricKey)
    {
        $encryptedSek = base64_decode($encryptedSekBase64, true);
        //  dump($symmetricKey);
        if ($encryptedSek === false) {
            throw new \Exception("Invalid base64 SEK");
        }

        // ❗ Debug key size
        // dump("Key length = ", strlen($symmetricKey)); // MUST be 32

        $decrypted = openssl_decrypt(
            $encryptedSek,
            'AES-256-ECB',
            $symmetricKey,
            OPENSSL_RAW_DATA
        );

        //dd($decrypted); // to see result

        if ($decrypted === false) {
            throw new \Exception("openssl_decrypt() failed — wrong AES key");
        }
        // return $decrypted;

         return base64_encode($decrypted);
    }

      public static function decrypt2($encryptedSekBase64, $symmetricKey)
    {
         
         $encryptedSek = base64_decode($encryptedSekBase64, true);
        //  dump($symmetricKey);
        if ($encryptedSekBase64 === false) {
            throw new \Exception("Invalid base64 SEK");
        }

        // ❗ Debug key size
        // dump("Key length = ", strlen($symmetricKey)); // MUST be 32

        $decrypted = openssl_decrypt(
            $encryptedSek,
            'AES-256-ECB',
            $symmetricKey,
            OPENSSL_RAW_DATA
        );

        //dd($decrypted); // to see result

        if ($decrypted === false) {
            throw new \Exception("openssl_decrypt() failed — wrong AES key");
        }
         return $decrypted;

         //return base64_encode($decrypted);
    }
    // Encrypt payload using decrypted SEK
    public static function encryptAES256Base64($data, $base64Sek)
    {
        // dump($base64Sek);
        // $sek = base64_decode($base64Sek, true);
        //  $sek = $base64Sek;
        $sek = str_pad(base64_decode($base64Sek, true), 32, "\0");
        //  dd($sek);
        if (!$sek || strlen($sek) !== 32) {
            // dd('okk');
            throw new \Exception("Invalid SEK for encryption");
        }

        $encrypted = openssl_encrypt($data, 'AES-256-ECB', $sek, OPENSSL_RAW_DATA);
        return base64_encode($encrypted);
    }

    // Generate HMAC using decrypted SEK
    public static function generateHmacBase64($data, $Sek)
    {
        $sek = base64_decode($Sek, true);
        // $encryptedData = base64_decode($data);
        // var_dump($sek);var_dump($encryptedData);die;
        if (!$sek || strlen($sek) !== 32) {
            throw new \Exception("Invalid SEK for HMAC");
        }

        return base64_encode(hash_hmac('sha256', $data, $sek, true));
    }
}
