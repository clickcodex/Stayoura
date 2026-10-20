<?php

namespace App\Helpers;

class CryptoHelper
{
    private static $method = "AES-256-CBC";
    private static $secret_key = "craftstroke_secret_key";
    private static $secret_iv = "craftstroke_secret_iv";

    private static function getKey()
    {
        return hash('sha256', self::$secret_key);
    }

    private static function getIV()
    { return substr(hash('sha256', self::$secret_iv), 0, 16);}

    public static function encrypt($data)
    {
        if ($data === null || $data === '') {
            return '';
        }

        $output = openssl_encrypt(
            (string)$data,
            self::$method,
            self::getKey(),
            0,
            self::getIV()
        );

        return base64_encode($output);
    }

    public static function decrypt($data)
    {
        if ($data === null || $data === '') {
            return '';
        }

        $str = (string)$data;

        // If it's already a plain small integer, return directly
        if (is_numeric($str) && (int)$str > 0 && strlen($str) < 8) {
            return $str;
        }

        // Standard incoming from $_GET might have '+' replaced by space ' '
        $clean = str_replace(' ', '+', trim($str));

        // Attempt 1: double base64 decode (since encrypt() does openssl (which outputs base64) + base64_encode)
        $firstPass = base64_decode($clean, true);
        if ($firstPass !== false) {
            $output = openssl_decrypt(
                $firstPass,
                self::$method,
                self::getKey(),
                0,
                self::getIV()
            );
            if ($output !== false && $output !== '') {
                return $output;
            }
        }

        // Attempt 2: single base64 decode / direct openssl_decrypt
        $outputDirect = openssl_decrypt(
            $clean,
            self::$method,
            self::getKey(),
            0,
            self::getIV()
        );
        if ($outputDirect !== false && $outputDirect !== '') {
            return $outputDirect;
        }

        // Fallback: If numeric
        if (is_numeric($str)) {
            return $str;
        }

        return '';
    }

    /**
     * Safely encrypt integer ID for URL query strings or path parameters
     */
    public static function encryptId($id): string
    {
        if (empty($id)) return '';
        return urlencode(self::encrypt((string)$id));
    }

    /**
     * Safely decrypt an encrypted string or raw ID into an integer
     */
    public static function decryptId($data): int
    {
        if (empty($data)) return 0;
        $dec = self::decrypt(urldecode((string)$data));
        return is_numeric($dec) ? (int)$dec : 0;
    }
}