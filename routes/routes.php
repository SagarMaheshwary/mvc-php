<?php

/**
 * Here you can register both GET and POST routes.
 * 
 * Syntax GET: $router->get('uri','Controller@method');
 * Syntax POST: $router->post('uri','Controller@method');
 * Dynamic route: $router->method('uri/with/{dynamicvalue}','Controller@method');
 */

$router->get('/','HomeController@index');
$router->get('/user/{id}/name/{name}','HomeController@show');
$router->post('/hello','HomeController@hello');