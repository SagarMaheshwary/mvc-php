<?php

namespace App\Core\Http;

use App\Core\Support\Session;
use App\Core\Http\{BaseController,Request};

class Response extends BaseController
{
    /**
     * Current request object.
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        $this->request = new Request;
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
                $this->statusCode($code);
                $this->view('errors.403');
            break;
            case 404:
                $this->statusCode($code);
                //404 not found!
                $this->view('errors.404');
            break;
            case 503:
                //503 service unavailable!
                $this->statusCode($code);
                $this->view('errors.503');
            break;
            case 500:
            default:
                //500 internal server error!
                $this->statusCode($code);
                $this->view('errors.500');
            break;
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

}
