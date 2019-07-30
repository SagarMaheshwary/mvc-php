<?php

/**
 * Here you can register all of your application routes.
 * 
 * Syntax GET: $router->get('uri','Controller@method');
 * Dynamic route: $router->method('uri/with/{dynamicvalue}','Controller@method');
 * Available router methods : get(), post(), put(), delete().
 */

$router->get('/','PagesController@index');
$router->get('/contact','PagesController@contact');
$router->get('/about','PagesController@about');