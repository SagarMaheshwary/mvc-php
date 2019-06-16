<?php

namespace App\Core\Http;

use App\Core\Support\Session;
use App\Core\Http\Request;

class Response
{
    /**
     * Current request object.
     * @var Request
     */
    protected $request;

    /**
     * Base Controller object.
     * @var BaseController
     */
    protected $controller;

    /**
     * Magic method called when the instance
     * is created.
     * 
     * @return void
     */
    public function __construct()
    {
        $this->setRequest(new Request);
        $this->setController(new BaseController);
    }

    /**
     * Set a header.
     * 
     * @param string $key
     * @param string $value
     * @param int|200 $statusCode
     * @param bool|true $replace
     * @return Response
     */
    public function header($key,$value,$statusCode = 200,$replace = true)
    {
        header("{$key}: {$value}",$replace,$statusCode);
        return $this;
    }

    /**
     * set a response code.
     * 
     * @param int $code
     * @return Response
     */
    public function statusCode($code)
    {
        http_response_code($code);
        return $this;
    }

    /**
     * Redirect to a specific url or pass
     * a status code to generate an error.
     * 
     * @param string|int $url (url or status code)
     * @return Response
     */
    public function redirect($url)
    {
        if(is_int($url)){
            $this->httpError($url);
        }else{
            $this->header("Location",$url,302);
        }
        return $this;
    }

    /**
     * Redirect to the previous url.
     * 
     * @return void
     */
    public function redirectBack()
    {
        $this->redirect($this->getRequest()->previousUrl());
    }

    /**
     * return json response.
     * 
     * @param mixed|[] $data
     * @param int $code
     * @return mixed
     */
    public function json($data = [],$code = 200)
    {
        $this->header("Content-Type","application/json",$code);
        return json_encode($data);
    }

    /**
     * Store a session value for the next
     * request (flash message).
     * 
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function with($key, $value)
    {
        Session::flash($key,$value);
    }

    /**
     * Force an http error and display an error
     * view.
     * 
     * @param int $code
     * @return void
     */
    protected function httpError($code)
    {
        switch ($code) {
            case 403:
                //403 forbidden!
                $this->sendErrorResponse($code,'errors.403');
            break;
            case 404:
                //404 not found!
                $this->sendErrorResponse($code,'errors.404');
            break;
            case 503:
                //503 service unavailable!
                $this->sendErrorResponse($code,'errors.503');
            break;
            case 500:
            default:
                //500 internal server error!
                $this->sendErrorResponse($code,'errors.500');
            break;
        }
    }

    /**
     * Send the error response code if requests json or
     * else just return the appropriate view.
     * 
     * @param int $code
     * @param string $view
     * @return void
     */
    protected function sendErrorResponse($code, $view)
    {
        if($this->getRequest()->isJsonRequest()){
            $this->statusCode($code);
        }else{
            $this->getController()->view($view);
        }
    }

    /**
     * Set current request object.
     * 
     * @param Request $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get current request object.
     * 
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the controller object.
     * 
     * @param BaseController $controller
     * @return void
     */
    public function setController(BaseController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Get controller object.
     * 
     * @return BaseController
     */
    public function getController()
    {
        return $this->controller;
    }

}
