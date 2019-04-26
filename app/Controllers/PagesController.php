<?php

namespace App\Controllers;

use App\Core\Http\{Request,Response};

class PagesController extends Controller
{

    /**
     * Show the home page.
     * 
     * @param App\Core\Http\Request $request
     * @param App\Core\Http\Response $response
     * @return void
     */
    public function index(Request $request,Response $response)
    {
        $this->view('home');
    }

    /**
     * Show the home page.
     * 
     * @return void
     */
    public function contact()
    {
        $this->view('contact');
    }

    /**
     * Show the home page.
     * 
     * @return void
     */
    public function about()
    {
        $this->view('about');
    }

}
