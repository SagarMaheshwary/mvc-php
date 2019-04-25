<?php

namespace App\Core\Http;

use Exception;

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
    protected function view($view,$data = [])
    {
        if(!$this->exists($view))
            throw new Exception("View not Found");

        extract($data);
        require $this->name($view);
    }

    /**
     * Include a view from a view.
     * 
     * @param string $view
     * @return void
     */
    protected function include($view)
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
}