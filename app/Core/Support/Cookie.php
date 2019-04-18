<?php

namespace App\Core\Support;

/**
 * Handle all the stuff related to cookies.
 */
class Cookie
{
    
    /**
     * Get a value.
     * 
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::has($key) == true ? $_COOKIE[$key] : '';
    }

    /**
     * Set a value.
     * 
     * @param string $key
     * @param string $value
     * @return bool
     */
    public static function set($key, $value, $expires = 0, $httpOnly = false, $path = '/',$domain = null,$secure = false)
    {
        setcookie($key,$value,$expires,$path,$domain,$secure,$httpOnly);
    }

    /**
     * Determine if a value exists.
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_COOKIE[$key]) ? true : false;
    }

    /**
     * Unset/Remove a value.
     * 
     * @param string $key
     * @return void
     */
    public static function unset($key)
    {
        self::set($key,'');
    }

}
