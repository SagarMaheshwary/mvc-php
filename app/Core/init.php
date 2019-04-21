<?php

session_start();

/**
 * Bootstrap the Application
 */
use App\Core\Http\{Router,Request};

//Call the appropriate route.
Router::load('../routes/routes.php')
    ->direct(Request::uri(),Request::method());