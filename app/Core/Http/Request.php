<?php

namespace App\Core\Http;

use Exception;

class Request
{

    /**
     * Fetch the request URI.
     *
     * @return string
     */
    public static function uri()
    {
        return trim(
            parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'
        );
    }

    /**
     * Fetch the request method.
     *
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check for the request method.
     *
     * @param string $method
     * @return string
     */
    public static function isMethod($method)
    {
        $methods = ["GET","POST"];

        if(in_array($method,$methods)){
            throw new Exception("Invalid Method!");
        }
        
        return self::method() == $method ? true : false;
    }

    /**
     * get an input value.
     * 
     * @param string $key
     * @return mixed
     */
    public static function input($key)
    {
        return self::has($key) ? $_REQUEST[$key] : '';
    }

    /**
     * check if an input value exists.
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset($_REQUEST[$key]);
    }

    /**
     * get an input from "GET" global.
     * 
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : '';
    }

    /**
     * get an input from "POST" global.
     * 
     * @param string $key
     * @return mixed
     */
    public static function post($key)
    {
        return isset($_POST[$key]) ? $_POST[$key] : '';
    }
}
