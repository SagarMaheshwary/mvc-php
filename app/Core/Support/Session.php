<?php

namespace App\Core\Support;

/**
 * Handle all the stuff related to session.
 */
class Session
{
    
    /**
     * Get a value.
     * 
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::has($key) == true ? $_SESSION[$key] : '';
    }

    /**
     * Set a value.
     * 
     * @param string $key
     * @param string $value
     * @return bool
     */
    public static function set($key,$value)
    {
        return (bool)($_SESSION[$key] = $value);
    }

    /**
     * Determine if a value exists.
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_SESSION[$key]) ? true : false;
    }

    /**
     * Unset/Remove a value.
     * 
     * @param string $key
     * @return void
     */
    public static function unset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * Completely destroy the session.
     * 
     * @return void
     */
    public static function destroy()
    {
        session_unset();
        session_destroy();
    }

    /**
     * Make the value available for the next request.
     * (Flash message)
     * 
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public static function flash($key,$value)
    {
        if(self::has($key)){
            //value exists so return and unset it.
            $flash =self::get($key);
            self::unset($key);
            return $flash;
        }else{
            //value doesn't exists so set it.
            self::set($key,$value);
        }
    }

}
