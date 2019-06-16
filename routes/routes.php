<?php

/**
 * Here you can register both GET and POST routes.
 * 
 * Syntax GET: $router->get('uri','Controller@method');
 * Syntax POST: $router->post('uri','Controller@method');
 * Dynamic route: $router->method('uri/with/{dynamicvalue}','Controller@method');
 */

$router->get('/','PagesController@index');
$router->get('/contact','PagesController@contact');
$router->get('/about','PagesController@about');
$router->put('/csrf','PagesController@about');