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
    private $cookies = [];

    /**
     * Server details from current request.
     * 
     * @var array
     */
    private $server = [];

    /**
     * Current request files.
     * 
     * @var array
     */
    private $files = [];

    /**
     * Current request headers.
     * 
     * @var array
     */
    private $headers = [];

    /**
     * Query String values from current request.
     * 
     * @var array
     */
    private $query = [];

    /**
     * Request data for dynamic access.
     * 
     * @var array
     */
    protected $attributes = [];

    /**
     * Set all the request properties with the
     * class is instantiated.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->setAttributes();
        $this->setCookies();
        $this->setServer();
        $this->setQuery();
        $this->setFiles();
        $this->setHeaders();
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
     * Get all the cookies.
     * 
     * @return array
     */
    public function cookies()
    {
        return $this->cookies;
    }

    /**
     * Set all the cookies.
     * 
     * @return void
     */
    protected function setCookies()
    {
        $this->cookies = $_COOKIE;
    }

    /**
     * Get the query string.
     * 
     * @return array key/value pairs of query string
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * Set the query string values as an
     * associative array.
     * 
     * @return void
     */
    protected function setQuery()
    {
        if(!$_SERVER['QUERY_STRING']) return;
        
        $strings = explode('&',$_SERVER['QUERY_STRING']);
        $query = [];
        
        foreach($strings as $string){
            $val = explode('=',e($string));
            
            //we will check if we don't have something like
            //url/key instead of url/key=value or else we will
            //get an error.
            $query[$val[0]] = isset($val[1]) ? $val[1] : '';
        }

        $this->query = $query;
    }

    /**
     * Get the $_SERVER values.
     * 
     * @return array
     */
    public function server()
    {
        return $this->server;
    }

    /**
     * Set the $_SERVER global values.
     * 
     * @return void
     */
    protected function setServer()
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
     * Get all the headers
     * 
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Set all the headers
     * 
     * @return void
     */
    protected function setHeaders()
    {
        $this->headers = getallheaders();
    }

    /**
     * Get all the uploaded files.
     * 
     * @return void
     */
    public function files()
    {
        return $this->files;
    }

    /**
     * Set all the uploaded files.
     * 
     * @return void
     */
    protected function setFiles()
    {
        $this->files = $_FILES;
    }

    /**
     * Set all the $_REQUEST and $_FILES to 
     * $attributes property for dynamic
     * access.
     * 
     * @return void
     */
    protected function setAttributes()
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
        return $this->attributes[$key];
    }

    /**
     * Dynamically get request attributes.
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set($key,$value){
        $this->attributes[e($key)] = e($value);
    }

    /**
     * Dynamically check for request attributes.
     * 
     * @param string $key
     * @return mixed
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset request attributes.
     * 
     * @param string $key
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

}
