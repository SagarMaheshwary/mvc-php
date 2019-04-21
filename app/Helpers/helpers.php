<?php

/**
 * Global helpers.
 */
use App\Core\Support\Session;
use App\Core\Security\{Hash,CSRF};
use App\Core\Http\Request;

/**
 * dump the data and kill the page.
 * 
 * @param array $data
 * @return void
 */
function dd($data = [])
{
    var_dump($data);
    die();
}

/**
 * Create a url from a given uri.
 * 
 * @param string $uri
 * @return string
 */
function url($uri = '')
{
    $uri = sanitizeUri($uri);
    return "http://{$_SERVER['HTTP_HOST']}/{$uri}";
}

/**
 * Get the current url.
 * 
 * @return string
 */
function currentUrl()
{
    return url(Request::uri());
}

/**
 * Sanitize the given uri.
 * 
 * @param string $uri
 * @return string
 */
function sanitizeUri($uri)
{
    if(strpos($uri,'/') == 0) $uri = ltrim($uri,'/');
    
    return filter_var(
        $uri, FILTER_SANITIZE_URL
    );
}

/**
 * get the csrf token.
 */
function token()
{
    return CSRF::generate();
}

/**
 * get the csrf hidden field
 */
function csrfField()
{
    return CSRF::csrfField();
}
