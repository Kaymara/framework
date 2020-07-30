<?php

namespace Compose\Contracts\Routing;

use Compose\Contracts\Routing\Route as RouteInterface;
use Compose\Http\Request;
use Compose\Http\Response;

interface Router
{
    /**
     * Route the request to the appropriate controller::method
     *
     * @param Request $request
     *
     * @return Response
     */
    public function route(Request $request) : Response;

    /**
     * Is the given route bound to the router?
     *
     * @param string $route
     *
     * @return bool
     */
    public function hasRoute(string $route);

    /**
     * Add a GET route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return Route
     */
    public function get(string $uri, $action);

    /**
     * Add a POST route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return Route
     */
    public function post(string $uri, $action);

    /**
     * Add a PUT route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return Route
     */
    public function put(string $uri, $action);

    /**
     * Add a PATCH route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return Route
     */
    public function patch(string $uri, $action);

    /**
     * Add a DELETE route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return Route
     */
    public function delete(string $uri, $action);

    /**
     * Add an OPTIONS route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return Route
     */
    public function options(string $uri, $action);

    /**
     * Redirect route
     *
     * @param string $from
     * @param string $to
     *
     * @return self
     */
    public function redirect(string $from, string $to);

    /**
     * Bind http methods to router
     *
     * @param $methods
     * @param string $uri
     * @param $action
     *
     * @return Route[]
     */
    public function methods($methods, string $uri, $action);

    /**
     * Bind route and action to all supported http methods
     *
     * @param string $uri
     * @param $action
     *
     * @return RouteInterface[]
     */
    public function any(string $uri, $action);

    /**
     * Set 404 handler or send response
     *
     * @param callable|null $handler
     *
     * @return mixed
     */
    public function notFound(callable $handler = null);

    /**
     * Bind a pcre to a route param
     *
     * @param string $param
     * @param string $pattern
     *
     * @return void
     */
    public function pattern(string $param, string $pattern);

    /**
     * Get route with given name
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function named(string $name);

    /**
     * Create a route collection
     *
     * @return RouteCollection
     */
    public function collection();

    /**
     * Pop a collection off the stack
     *
     * @return self|null
     */
    public function popCollectionStack();

    /**
     * Bind a fallback route to the router
     *
     * @param callable $callable
     *
     * @return void
     */
    public function fallback(callable $callable);
}