<?php

namespace App\Lib;

class Strings
{
    const ENCRYPTION_SALT = 'obedient-hence-known';

    /**
     * toTitleCase
     *
     * Converts string to title case & handles hyphens
     *
     * @param string $strData The string to convert
     * @return string The converted string
     */
    public static function toTitleCase($strData)
    {
        return mb_convert_case(mb_strtolower($strData), MB_CASE_TITLE, "UTF-8");
    }

    /**
     * Runs a simple AES 256 Encryption algorithm on supplied text.
     *
     * @param        $text
     * @param string $salt
     * @return string
     */
    public static function simple_encrypt($text, $salt = self::ENCRYPTION_SALT)
    {
        // For MySQL compatibility, Salts are limited to 16 characters
        $salt = substr(hash('sha256', $salt), 0, 16);

        $str = trim(openssl_encrypt($text, 'aes-128-ecb', $salt)); // OpenSSL returns string in base64 format by default
        // Making the base64 URL friendly, in addition a few random bits to make it not so obvious it's a base64 string (base64 decode will fail)
        return substr(bin2hex(openssl_random_pseudo_bytes(10)), 0, 5) . rtrim(strtr($str, '+/', '-_'), '=');
    }

    /**
     * Decrypts string encrypted by self::simple_encrypt
     *
     * @param        $token
     * @param string $salt
     * @return string
     */
    public static function simple_decrypt($token, $salt = self::ENCRYPTION_SALT)
    {
        // For MySQL compatibility, Salts are limited to 16 characters
        $salt = substr(hash('sha256', $salt), 0, 16);

        $token = substr($token, 5); // Remove random appended bits
        $token = strtr($token, '-_', '+/') . str_repeat('=', strlen($token) % 4);  // Reconstructing Base64 String
        return trim(openssl_decrypt($token, 'aes-128-ecb', $salt));
    }

}
