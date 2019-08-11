<?php

namespace App\Service\Crypt;

class CryptService {
    private static $salt = "jfaj03fjq0x9q0w9";

    public static function hashPassword(string $password)
    {
        return md5(self::$salt.$password);
    }
}