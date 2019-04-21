<?php

namespace App\Core\Http;

use Exception;

/**
 * Router for our MVC Application.
 * This router support both static routes as
 * well as routes with dynamic parameters.
 */
class Router
{

    /**
     * All routes.
     * 
     * @var array
     */
    private $routes = [
        'GET'  => [],
        'POST' => [],
    ];

    /**
     * Current request uri.
     * 
     * @var string
     */
    private $uri;

    /**
     * Current request method.
     * 
     * @var string
     */
    private $method;

    /**
     * Route uri.
     * 
     * @var string
     */
    private $routeUri;

    /**
     * Route action
     * 
     * @var string
     */
    private $routeAction;

    /**
     * Matched route parameters
     * 
     * @var string
     */
    private $routeParams = [];

    /**
     * Did we find a route matching the uri?
     * 
     * @var string
     */
    private $matchedRoute = false;

    /**
     * Regex pattern for dynamic routes.
     * 
     * @var string
     */
    private $regex = '/\{(.*?)\}/';

    /**
     * Add a route for "GET" method.
     * 
     * @param string $uri
     * @param string controller
     * @return void
     */
    public function get($uri,$controller)
    {
        $this->routes['GET'][$uri] = $controller;
    }

    /**
     * Add a route for "POST" method.
     * 
     * @param string $uri
     * @param string controller
     * @return void
     */
    public function post($uri,$controller)
    {
        $this->routes['POST'][$uri] = $controller;
    }

    /**
     * Create the router instance and load
     * routes from the given file.
     * 
     * @param string $routes
     * @return App\Core\Http\Router
     */
    public static function load($routes)
    {
        $router = new self;
        require $routes;
        return $router;
    }

    /**
     * Direct the router to call a controller
     * method.
     * 
     * @param string $uri
     * @param string $requestType
     * @return void
     */
    public function direct($uri,$method)
    {
        $this->setUri($uri);
        $this->setMethod($method);

        $this->matchRoute();

        if($this->getMatchedRoute()){
            //We got a matching route.
            $this->callAction(
                ...explode('@',$this->getRouteAction())
            );
        }else{
            //no route registered with the uri.
            throw new Exception("Route not Found!");
        }
    }

    /**
     * Create a controller instance and
     * call the method.
     * 
     * @param string $controller
     * @param string $action
     * @return void
     */
    protected function callAction($controller,$action)
    {
        $controller = "App\\Controllers\\{$controller}";

        if(!$controller = new $controller)
            throw new Exception("Controller Not Found");
        
        if(!method_exists($controller,$action))
            throw new Exception("Controller Method not Found");
        
        if($this->hasRouteParams()){
            $controller->$action(...$this->getRouteParams());
        }else{
            $controller->$action();
        }
    }

    /**
     * Match the current uri with registered the routes.
     * 
     * @return void
     */
    protected function matchRoute()
    {
        $routes = $this->getRoutes()[$this->getMethod()];

        foreach ($routes as $routeUri => $routeAction) {
            
            $routeUri = sanitizeUri($routeUri);
            $this->setRouteUri($routeUri);
            $this->setRouteAction($routeAction);

            $this->resetDynamicRouteUri();

            if($this->getRouteUri() != $this->getUri()){
                //didn't match the uri, onto the next index.
                continue;
            }else{
                $this->setDynamicParams($routeUri);
            }
            
            //found a matching route.
            $this->setMatchedRoute(true);
            
            if($this->getMatchedRoute()){
                //found the route so stop the loop.
                break;
            }
        }
    }

    /**
     * Set the dynamic parameters if any.
     * 
     * @param string $routeUri
     * @return void
     */
    protected function setDynamicParams($routeUri)
    {
        //pull out parts containing dynamic params with
        //same index.
        $routeUriParams = preg_grep(
            $this->getRegex(),$this->chunkUri($routeUri)
        );

        //no params so break the execution of this function.
        if(count($routeUriParams) <= 0) return;

        $currentUri = $this->chunkUri($this->getUri());

        $params = [];

        //set those param values to a new array,
        foreach($routeUriParams as $i => $param){
            $params[] = $currentUri[$i];
        }

        $this->setRouteParams($params);
    }

    /**
     * Reset the dynamic route uri with the
     * current uri.
     * 
     * @return void
     */
    protected function resetDynamicRouteUri()
    {
        if(!$this->isDynamicRouteUri()) return;

        //uri chunk length and route uri chunk length
        //didn't match.
        if(count($this->chunkUri($this->getRouteUri())) 
        != count($this->chunkUri($this->getUri()))) return;

        //replace the route param placeholders with the
        //uri values.
        $this->setRouteUri(
            preg_replace($this->getRegex(),$this->getRouteUri(),$this->getUri())
        );
    }

    /**
     * Check if the route has dynamic params.
     * 
     * @return bool
     */
    protected function isDynamicRouteUri()
    {
        return preg_match("({|})",$this->getRouteUri());
    }

    /**
     * split the uri by '/' into an array.
     * 
     * @param string $uri
     * @return array
     */
    protected function chunkUri($uri)
    {
        return explode('/',$uri);
    }

    /**
     * Set a route.
     * 
     * @param string $method
     * @param string $uri
     * @param string $action
     * @return void
     */
    protected function setRoute($method,$uri,$action)
    {
        $this->routes[$method][$uri] = $action;
    }

    /**
     * Get a specified route.
     * 
     * @param string $method
     * @param string $uri
     * @return string
     */
    protected function getRoute($method,$uri)
    {
        return $this->routes[$method][$uri];
    }

    /**
     * Get all routes.
     * 
     * @param string $method
     * @param string $uri
     * @return string
     */
    protected function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Set current request uri.
     * 
     * @param string $uri
     * @return void
     */
    protected function setUri($uri)
    {
        $this->uri = sanitizeUri($uri);
    }

    /**
     * Get current request uri.
     * 
     * @return string
     */
    protected function getUri()
    {
        return $this->uri;
    }

    /**
     * Set current request method.
     * 
     * @param string $method
     * @return void
     */
    protected function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * Get current request method.
     * 
     * @return string
     */
    protected function getMethod()
    {
        return $this->method;
    }

    /**
     * Set matched route uri.
     * 
     * @param string $uri
     * @return void
     */
    protected function setRouteUri($uri)
    {
        $this->routeUri = $uri;
    }

    /**
     * Get matched route uri.
     * 
     * @return string
     */
    protected function getRouteUri()
    {
        return $this->routeUri;
    }

    /**
     * Set matched route action.
     * 
     * @param string $action
     * @return void
     */
    protected function setRouteAction($action)
    {
        $this->routeAction = $action;
    }

    /**
     * Get matched route action.
     * 
     * @return string
     */
    protected function getRouteAction()
    {
        return $this->routeAction;
    }

    /**
     * Set route params.
     * 
     * @param array $params
     * @return void
     */
    protected function setRouteParams($params = [])
    {
        $this->routeParams = $params;
    }

    /**
     * Get route params.
     * 
     * @return string
     */
    protected function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * Does this route has any dynamic params.
     * 
     * @return string
     */
    protected function hasRouteParams()
    {
        return !$this->routeParams ? false : true;
    }

    /**
     * Set matched route.
     * 
     * @param bool $matched
     * @return void
     */
    protected function setMatchedRoute($matched)
    {
        $this->matchedRoute = $matched;
    }

    /**
     * Get matched route.
     * 
     * @param bool $matched
     * @return string
     */
    protected function getMatchedRoute()
    {
        return $this->matchedRoute;
    }

    /**
     * Get regex pattern for dynamic routes.
     * 
     * @return string
     */
    protected function getRegex()
    {
        return $this->regex;
    }
}
