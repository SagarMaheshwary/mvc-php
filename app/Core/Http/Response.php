<?php

namespace App\Core\Http;

use App\Core\Support\Session;
use App\Core\Http\BaseController;

class Response
{

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
            $this->errors($url);
        }else{
            $this->header("location",$url);
        }
        return $this;
    }

    /**
     * return json response.
     * 
     * @param mixed|[] $data
     * @return mixed
     */
    public function json($data = [],$code = 200)
    {
        $this->header("Content-Type","application/json",$code);
        return json_encode($data);
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

}
