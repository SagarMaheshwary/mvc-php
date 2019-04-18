<?php

namespace App\Core\Security;

class Hash
{
    /**
     * Create a hash from string.
     * 
     * @param string $str
     * @return string
     */
    public static function make($str)
    {
        return password_hash($str,PASSWORD_DEFAULT);
    }

    /**
     * Verify a hash against a string.
     * 
     * @param string $str
     * @param string $hash
     * @return bool
     */
    public static function match($str,$hash)
    {
        return password_verify($str,$hash);
    }

    /**
     * Create a unique hash.
     * 
     * @param string $len
     * @return string
     */
    public static function unique($len = 32)
    {
        return bin2hex(random_bytes($len));
    }
    
}
