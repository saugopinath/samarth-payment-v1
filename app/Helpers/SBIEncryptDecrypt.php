<?php

namespace App\Helpers;

class SBIEncryptDecrypt
{
    public static function file_encrypt($file_content, $publickey = null)
    {
        // Stub for encrypting file
        // For testing, just return the content as-is or base64 encoded
        return base64_encode($file_content);
    }
}
