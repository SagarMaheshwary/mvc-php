<?php

session_start();

/**
 * Bootstrap the Application
 */
use App\Core\Support\App;
use App\Core\Database\{Connection,Model};
use App\Core\Http\{Router,Request};

//register configuration to the app.
App::register('config',require '../config/app.php');

//Call the appropriate route.
echo Router::load('../routes/routes.php')
    ->direct(Request::uri(),Request::method());