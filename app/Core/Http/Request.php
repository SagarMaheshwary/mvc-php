<?php

namespace App\Core\Http;

use Exception;
use App\Core\Support\Session;
use App\Core\Validation\Validator;

class Request
{
    /**
     * Current request cookies.
     * 
     * @var array
     */
    public $cookies = [];

    /**
     * Server details from current request.
     * 
     * @var array
     */
    public $server = [];

    /**
     * Current request files.
     * 
     * @var array
     */
    public $files = [];

    /**
     * Current request headers.
     * 
     * @var array
     */
    public $headers = [];

    /**
     * Query String values from current request.
     * 
     * @var array
     */
    public $query = [];

    /**
     * Request data for dynamic access.
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Set all the request properties with the
     * class is instantiated.
     */
    public function __construct()
    {
        $this->attributes();
        $this->cookies();
        $this->server();
        $this->query();
        $this->files();
        $this->headers();
    }

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

        if(!in_array($method,$methods)){
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
        return self::has($key) ? e($_REQUEST[$key]) : '';
    }

    /**
     * check if an input value exists.
     * 
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return (!isset($_REQUEST[$key]) || $_REQUEST[$key] == '') ? false : true;
    }

    /**
     * get an input from "GET" global.
     * 
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return self::isMethod("GET") && self::has($key) ? e(self::input($key)) : '';
    }

    /**
     * get an input from "POST" global.
     * 
     * @param string $key
     * @return mixed
     */
    public static function post($key)
    {
        return self::isMethod("POST") && self::has($key) ? e(self::input($key)) : '';
    }

    /**
     * Check if a file was uploaded file.
     * 
     * @return
     */
    public static function hasFile($key)
    {
        return isset($_FILES[$key]);
    }

    /**
     * retrieve the uploaded file.
     * 
     * @return 
     */
    public static function file($key)
    {
        return self::hasFile($key) ? $_FILES[$key] : false;
    }

    /**
     * Check whether the request accepts json.
     * 
     * @return bool
     */
    public static function isJsonRequest()
    {
        return (strtolower($_SERVER['HTTP_ACCEPT']) == 'application/json')
        ? true : false;
    }

    /**
     * Validate the current Request.
     * 
     * @param array $rules
     * @return void
     */
    public function validate($rules)
    {
        $validator = new Validator();
        $validator->validate($this,$rules);
    }

    /**
     * Get the previous request url.
     * 
     * @return string
     */
    public static function previousUrl()
    {
        return url(Session::getPreviousUri());
    }

    /**
     * Set all the cookies
     * 
     * @return void
     */
    protected function cookies()
    {
        $this->cookies = $_COOKIE;
    }

    /**
     * Set the query string values as an
     * associative array.
     * 
     * @return void
     */
    protected function query()
    {
        if(!$_SERVER['QUERY_STRING']) return;
        
        $strings = explode('&',$_SERVER['QUERY_STRING']);
        $query = [];
        
        foreach($strings as $string){
            $val = explode('=',e($string));
            $query[$val[0]] = $val[1];
        }

        $this->query = $query;
    }

    /**
     * Set all the server global values.
     * 
     * @return void
     */
    protected function server()
    {
        $this->server = [
            'DOCUMENT_ROOT'        => $_SERVER['DOCUMENT_ROOT'],
            'SERVER_NAME'          => $_SERVER['SERVER_NAME'],
            'SERVER_PORT'          => $_SERVER['SERVER_PORT'],
            'SERVER_SOFTWARE'      => $_SERVER['SERVER_SOFTWARE'],
            'SERVER_PROTOCOL'      => $_SERVER['SERVER_PROTOCOL'],
            'HTTP_HOST'            => $_SERVER['HTTP_HOST'],
            'HTTP_ACCEPT'          => $_SERVER['HTTP_ACCEPT'],
            'HTTP_USER_AGENT'      => $_SERVER['HTTP_USER_AGENT'],
            'HTTP_ACCEPT_ENCODING' => $_SERVER['HTTP_ACCEPT_ENCODING'],
            'QUERY_STRING'         => $_SERVER['QUERY_STRING'],
            'PHP_SELF'             => $_SERVER['PHP_SELF'],
            'SCRIPT_NAME'          => $_SERVER['SCRIPT_NAME'],
            'SCRIPT_FILENAME'      => $_SERVER['SCRIPT_FILENAME'],
            'REMOTE_ADDR'          => $_SERVER['REMOTE_ADDR'],
            'REMOTE_PORT'          => $_SERVER['REMOTE_PORT'],
            'REQUEST_URI'          => $_SERVER['REQUEST_URI'],
            'REQUEST_METHOD'       => $_SERVER['REQUEST_METHOD'],
            'REQUEST_TIME'         => $_SERVER['REQUEST_TIME'],
            'REQUEST_TIME_FLOAT'   => $_SERVER['REQUEST_TIME_FLOAT'],
        ];
    }

    /**
     * Set all the headers
     * 
     * @return void
     */
    protected function headers()
    {
        $this->headers = getallheaders();
    }

    /**
     * Set all the uploaded files.
     * 
     * @return void
     */
    protected function files()
    {
        $this->files = $_FILES;
    }

    /**
     * Set all the $_REQUEST and $_FILES to 
     * $attributes property for dynamic
     * access
     * 
     * @return void
     */
    protected function attributes()
    {
        $request = [];

        foreach ($_REQUEST as $key => $value) {
            $request[e($key)] = e($value);
        }

        $this->attributes = array_merge($request,$_FILES);
    }

    /**
     * Dynamically get request attributes.
     * 
     * @return mixed
     */
    public function __get($key){
        return e($this->attributes[$key]);
    }

    /**
     * Dynamically get request attributes.
     */
    public function __set($key,$value){
        $this->attributes[e($key)] = e($value);
    }

    /**
     * Dynamically check for request attributes.
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset request attributes.
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

}
