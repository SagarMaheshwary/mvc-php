<?php

namespace App\Core\Support;

/**
 * App Container.
 */
class App
{
    /**
     * All registered keys.
     * 
     * @var array
     */
    protected static $registry = [];

    /**
     * Get a value from the registry.
     * 
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::has($key) ? self::$registry[$key] : false;
    }

    /**
     * Check if a value exists in the registry.
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset(self::$registry[$key]) ? true : false;
    }

    /**
     * Register a value into the App container.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function register($key,$value)
    {
        self::$registry[$key] = $value;
    }
}
