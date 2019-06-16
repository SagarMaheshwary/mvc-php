<?php

namespace App\Core\Http;

use Exception;
use App\Core\Security\CSRF;
use App\Core\Support\Session;
use App\Core\Http\{Request,Response};

class BaseController
{
    /**
     * Path to views directory.
     * 
     * @var string
     */
    protected $viewPath = __DIR__.'/../../../views/';

    /**
     * Display a view.
     * 
     * @param string $view
     * @param array|[] $data
     * @return void
     */
    public function view($view,$data = [])
    {
        if(!$this->exists($view))
            throw new Exception("View not Found");

        extract($data);
        require $this->name($view);
        return $this;
    }

    /**
     * Include a view from a view.
     * 
     * @param string $view
     * @return void
     */
    public function include($view)
    {
        if(!$this->exists($view))
            throw new Exception("Include not found");
        
        include $this->name($view);
    }

    /**
     * Check if a view exists.
     * 
     * @param string $view
     * @return bool
     */
    protected function exists($view)
    {
        return file_exists($this->name($view)) ? true : false;
    }

    /**
     * Get the view name.
     * 
     * @param string $view
     * @return string
     */
    protected function name($view)
    {
        $view = str_replace('.','/',$view);
        return $this->viewPath.$view.'.php';
    }

    /**
     * Set a flash message to session.
     * 
     * @param string $key
     * @param string $value
     * @return void
     */
    public function with($key,$value)
    {
        Session::flash($key,$value);
        return $this;
    }

    /**
     * Check for csrf token.
     * 
     * @param string $method
     * @return false
     * @throws \Exception
     */
    protected function csrf($method = 'post')
    {
        $requestMethod = Request::method();

        //first check if we have PUT or DELETE request.
        if (!Request::has('_method') 
        || !in_array(strtoupper($method),['PUT','DELETE'])) {
            if (!Request::isMethod($method)) {
                //request method doesn't match.
                return false;
            }
        }
        
        //check for the csrf token.
        if (!CSRF::match(Request::input('csrf_token'))) {
            $this->response()->statusCode(419);
            throw new Exception("CSRF token not found");
            exit();
        }
    }

    /**
     * Get the response object.
     * 
     * @return \App\Core\Http\Response
     */
    protected function response()
    {
        return new Response();
    }

}