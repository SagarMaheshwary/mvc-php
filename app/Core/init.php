<?php

//Starting the session will be the first we do.
session_start();

/* ----------------------------- Default settings START -------------------------------- */

//prettify the errors.
ini_set("html_errors", 1);
ini_set("error_prepend_string", "<pre style='color: #333; font-face:monospace; font-size:14px;'>");
ini_set("error_append_string ", "</pre>");

/* ----------------------------- Default settings END -------------------------------- */

/**
 * Bootstrap the Application
 */
use App\Core\Support\{App,Session};
use App\Core\Database\{Connection,Model};
use App\Core\Http\{Router,Request};

//register configuration to the app.
App::register('config',require '../config/app.php');

//Call the appropriate route.
$output = Router::load('../routes/routes.php')
    ->direct(Request::uri(),Request::method());

//For requests that expect json results.
if(Request::isJsonRequest() && is_string($output)){
    echo $output;
}

/**
 * We need to call this method after we return the output
 * and that way we can save the current uri and use it in
 * the next request as the previous uri.
 */
Session::setPreviousUri($_SERVER['REQUEST_URI']);