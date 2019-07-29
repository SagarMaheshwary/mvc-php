<?php

namespace App\Core\Http;

use Exception;

/**
 * Router for our MVC Application.
 * This router supports both static routes as
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
        'GET'    => [],
        'POST'   => [],
        'PUT'    => [],
        'DELETE' => [],
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
    private $regex = '/\{(\w+)\}/';

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
     * Add a route for "PUT" method.
     * 
     * @param string $uri
     * @param string controller
     * @return void
     */
    public function put($uri,$controller)
    {
        $this->routes['PUT'][$uri] = $controller;
    }

    /**
     * Add a route for "DELETE" method.
     * 
     * @param string $uri
     * @param string controller
     * @return void
     */
    public function delete($uri,$controller)
    {
        $this->routes['DELETE'][$uri] = $controller;
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
        $router = new static;
        require $routes;
        return $router;
    }

    /**
     * Dispatch the router to call a controller
     * method.
     * 
     * @param string $uri
     * @param string $requestType
     * @return mixed
     */
    public function dispatch($uri,$method)
    {
        $this->setUri($uri);
        $this->setMethod($method);

        $this->matchRoute();

        if($this->getMatchedRoute()){
            //We got a matching route.
            return $this->callAction(
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
     * @return mixed
     */
    protected function callAction($controller,$action)
    {
        $controller = "App\\Controllers\\{$controller}";

        $controller = $this->actionExists($controller,$action);
        
        $params = $this->hasRouteParams()
            ? $this->getRouteParams()
            : $this->getDefaultRouteParams();

        return $controller->$action(...$params);
    }

    /**
     * Check if both controller and it's method exist.
     * 
     * @param string $controller
     * @param string $action
     * @return mixed|object
     */
    protected function actionExists($controller,$action)
    {
        if(!$controller = new $controller)
            throw new Exception("Controller Not Found");
        
        if(!method_exists($controller,$action))
            throw new Exception("Controller Method not Found");

        return $controller;
    }

    /**
     * Match the current uri with registered routes.
     * 
     * @return void
     */
    protected function matchRoute()
    {
        $routes = $this->getRoutes()[$this->getMethod()];

        foreach ($routes as $routeUri => $routeAction) {
            
            $this->setRouteUri(sanitizeUri($routeUri));
            $this->setRouteAction($routeAction);

            $this->setDynamicRoute();

            //didn't match the uri, onto the next index.
            if($this->getRouteUri() != $this->getUri()) continue;

            //found a matching route.
            $this->setMatchedRoute(true);
            
            break; //break the loop statement.
        }
    }

    /**
     * Set the dynamic parameters and reset route uri with
     * the current uri if matches.
     * 
     * @return void
     */
    protected function setDynamicRoute()
    {
        if(!$this->isDynamicRouteUri()) return;

        $routeUriChunks = $this->chunkUri($this->getRouteUri());

        $uriChunks = $this->chunkUri($this->getUri());

        //pull out parts containing dynamic params with same array indexes.
        $routeUriParams = preg_grep(
            $this->getRegex(),$routeUriChunks
        );

        //no params/uri doesn't match so break the execution of this function.
        if(count($routeUriParams) == 0 
        || count($uriChunks) != count($routeUriChunks)) return;

        $params = $this->getDefaultRouteParams();

        foreach($routeUriParams as $i => $param){

            //reset the placeholder with the uri value.
            $routeUriChunks[$i] = $uriChunks[$i];

            //set those param values to a new array,
            $params[] = $uriChunks[$i];

        }

        $this->setRouteUri(implode('/',$routeUriChunks));

        $this->setRouteParams($params);
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
     * Request and Response objects
     * for incoming requests.
     * 
     * @param array $params
     * @return void
     */
    protected function getDefaultRouteParams()
    {
        return [new Request(),new Response()];
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
