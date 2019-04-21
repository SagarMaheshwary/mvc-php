<?php

namespace App\Controllers;

class PagesController extends Controller
{

    /**
     * Show the home page.
     * 
     * @return void
     */
    public function index()
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
