<?php

namespace Compose\Routing\Aggregates;

use Compose\Contracts\Routing\Route;
use Compose\Utility\Hash;

class Routes
{
    /**
     * Routes bound to the aggregate partitioned by method
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Flattened array of routes
     *
     * @var array
     */
    protected $flattened = [];

    /**
     * Is the given route bound to the aggregate?
     *
     * @param string $route
     *
     * @return bool
     */
    public function has(string $route)
    {
        $this->formatRoute($route);

        return strpos(implode('', array_keys($this->flattened)), $route) !== false;
    }

    protected function formatRoute(string $route)
    {
        //
    }

    /**
     * Add route to aggregate
     *
     * @param Route $route
     */
    public function add(Route $route)
    {
        foreach ($route->methods() as $method) {
            $this->routes[$method][$route->uri()] = $route;
            $this->flattened[$method . ':' . $route->uri()] = $route;
        }
    }

    /**
     * Get a route by the given method and uri
     *
     * @param string $method
     * @param string $uri
     *
     * @return Route|null
     */
    public function get(string $method, string $uri)
    {
        $routes = array_filter(Hash::get($this->routes, strtolower($method)) ?? [], fn($route) => strpos($uri, $route->uri()) === 0);

        return Hash::first($routes);
    }

    /**
     * Get routes that are bound to param with given name
     *
     * @param string $paramName
     *
     * @return Route[]
     */
    public function getByParamName(string $paramName)
    {
        return array_filter($this->flattened, fn(Route $route) => $route->hasParam($paramName));
    }

    /**
     * Get the route with given name
     *
     * @param string $name
     *
     * @return Route|null
     */
    public function getByName(string $name)
    {
        $namedRoutes = array_filter($this->flattened, fn(Route $route) => $route->hasName($name));

        return Hash::first($namedRoutes);
    }
}