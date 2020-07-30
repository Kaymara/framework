<?php

namespace Compose\Contracts\Routing;

use Compose\Contracts\Http\Middleware\Middleware;
use Compose\Routing\Parameter;

interface Route
{
    /**
     *  Get or set the route methods
     *
     * @param string|array|null $methods
     *
     * @return array|\Compose\Routing\Route
     */
    public function methods($methods = null);

    /**
     * Get or set the route uri
     *
     * @param string|null $uri
     *
     * @return string|self
     */
    public function uri(string $uri = null);

    /**
     * Get or set route action
     *
     * @param null $action
     *
     * @return string
     */
    public function action($action = null);

    /**
     * Get or set route's param stack
     *
     * @return Parameter[]
     */
    public function params();

    /**
     * Get parameter by name
     *
     * @param string $name
     *
     * @return Parameter|null
     */
    public function param(string $name);

    /**
     * Get route's param stack values
     *
     * @return Parameter[]
     */
    public function paramValues();

    /**
     * Get or set the controller instance
     *
     * @param mixed|null $controller
     *
     * @return mixed
     */
    public function controller($controller = null);

    /**
     * Get required param stack
     *
     * @return Parameter[]
     */
    public function requiredParams();

    /**
     * Get or set the route redirect
     *
     * @param Route|null $to
     *
     * @return Route|self
     */
    public function redirect(?Route $to = null);

    /**
     * Does the route have a param with the given name?
     *
     * @param $name
     *
     * @return bool
     */
    public function hasParam($name);

    /**
     * Get or set the pattern for given param name
     *
     * @param string $param
     * @param string|null $pattern
     *
     * @return self|string
     */
    public function pattern(string $param, string $pattern = null);

    /**
     * Get or set the route's name
     *
     * @param string|null $name
     *
     * @return string|self
     */
    public function name(string $name = null);

    /**
     * Does the route have the given alias?
     *
     * @param $name
     *
     * @return bool
     */
    public function hasName($name);

    /**
     * Bind middleware to route
     *
     * @param string|array|null $stack
     * @param array $params extra params that should be passed to middleware stack
     *
     * @return self|Middleware
     */
    public function middleware($stack = null, ...$params);

    /**
     * Get middleware params
     *
     * @return array
     */
    public function middlewareParams();
}