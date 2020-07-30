<?php

namespace Compose\Routing;

use Compose\Container\Container;
use Compose\Contracts\Container\ContainerInterface;
use \Compose\Contracts\Routing\Route as RouteInterface;
use \Compose\Contracts\Routing\Router as RouterInterface;
use Compose\Http\Middleware\Exceptions\MiddlewareNotCallableException;
use Compose\Http\Middleware\Middleware;
use Compose\Routing\Exceptions\ParamNotFoundException;
use Compose\Traits\CreateStaticInstance;
use Compose\Utility\Hash;

class Route implements RouteInterface
{
    use CreateStaticInstance;

    /**
     * The router instance
     *
     * @var Router
     */
    protected $router;

    /**
     * The container interface
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * The methods bound to the router
     *
     * @var array
     */
    protected $methods = [];

    /**
     * The route URI
     *
     * @var string
     */
    protected $uri;

    /**
     * The route name
     *
     * @var string
     */
    protected $name;

    /**
     * The bound controller instance
     *
     * @var mixed
     */
    protected $controller;

    /**
     * The route's parameter stack
     *
     * @var Parameter[]
     */
    protected $params = [];

    /**
     * The bound controller action
     *
     * @var string
     */
    protected $action;

    /**
     * The redirect Route
     *
     * @var Route
     */
    protected $redirect;

    /**
     * The route's middleware stack
     *
     * @var Middleware $middleware
     */
    protected $middleware;

    /**
     * The route's middleware params
     *
     * @var array $middlewareParams
     */
    protected $middlewareParams;

    /**
     * Create a route instance
     *
     * @param \Compose\Contracts\Routing\Router $router
     * @param ContainerInterface|null $container
     * @param Middleware|null $middleware
     */
    public function __construct(RouterInterface $router, ?ContainerInterface $container = null, ?Middleware $middleware = null)
    {
        $this->router = $router;
        $this->container = $container ?? new Container;
        $this->middleware = $middleware ?? Middleware::create($this->container);
    }

    /**
     * Get the router instance
     *
     * @return Router
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Get the container instance
     *
     * @return ContainerInterface
     */
    public function container()
    {
        return $this->container;
    }

    /**
     *  Get or set the route methods
     *
     * @param string|array|null $methods
     *
     * @return array|self
     */
    public function methods($methods = null)
    {
        if (is_null($methods)) {
            return $this->methods;
        }

        if (is_string($methods)) {
            $methods = [$methods];
        }

        $this->methods = $methods;

        return $this;
    }

    /**
     * Get or set the route uri
     *
     * @param string|null $uri
     *
     * @return string|self
     */
    public function uri(string $uri = null)
    {
        if (is_null($uri)) {
            return $this->uri;
        }

        $this->uri = $uri;

        return $this;
    }

    /**
     * Get or set the controller instance
     *
     * @param mixed|null $controller
     *
     * @return mixed
     */
    public function controller($controller = null)
    {
        if (is_null($controller)) {
            return $this->controller;
        }

        $this->controller = $controller;
    }

    /**
     * Get or set route action
     *
     * @param null $action
     *
     * @return string|self
     */
    public function action($action = null)
    {
        if (is_null($action)) {
            return $this->action;
        }

        $this->action = $action;

        return $this;
    }

    /**
     * Get the route's param stack
     *
     *
     * @return Parameter[]
     */
    public function params()
    {
        return $this->params;
    }

    /**
     * Set the route's params
     *
     * @param array $params
     *
     * @return self
     */
    public function setParams(array $params)
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Get route's param stack values
     *
     * @return array
     */
    public function paramValues()
    {
        return array_map(fn (Parameter $param) => $param->value(), $this->params);
    }

    /**
     * Get required param stack
     *
     * @return Parameter[]
     */
    public function requiredParams()
    {
        return array_filter($this->params, fn ($param) => $param->required());
    }

    /**
     * Get or set the route redirect
     *
     * @param RouteInterface|null $to
     *
     * @return RouteInterface|self
     */
    public function redirect(?RouteInterface $to = null)
    {
        if (is_null($to)) {
            return $this->redirect;
        }

        $this->redirect = $to;

        return $this;
    }

    /**
     * Does the route have a param with the given name?
     *
     * @param $name
     *
     * @return bool
     */
    public function hasParam($name)
    {
        return $this->param($name) instanceof Parameter;
    }

    /**
     * Get or set the pattern for given param name
     *
     * @param string $param
     * @param string|null $pattern
     *
     * @return self|string
     *
     * @throws ParamNotFoundException
     */
    public function pattern(string $param, string $pattern = null)
    {
        if (is_null($parameter = $this->param($param))) {
            throw new ParamNotFoundException(sprintf('Parameter {%s} is not bound to route {%s}', $param, $this->uri));
        }

        $parameter->pattern($pattern);
    }

    /**
     * Get parameter by name
     *
     * @param string $name
     *
     * @return Parameter|null
     */
    public function param(string $name)
    {
        $params = array_filter($this->params, fn (Parameter $param) => $param->name() === $name);

        return Hash::first($params);
    }

    /**
     * Get or set the route's name
     *
     * @param string|null $name
     *
     * @return string|self
     */
    public function name(string $name = null)
    {
        if (is_null($name)) {
            return $this->name;
        }

        $this->name = $name;

        return $this;
    }

    /**
     * Does the route have the given alias?
     *
     * @param $name
     *
     * @return bool
     */
    public function hasName($name)
    {
        return $this->name === $name;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     * @throws MiddlewareNotCallableException
     */
    public function middleware($stack = null, ...$params)
    {
        if (is_null($stack)) {
            return $this->middleware;
        }

        $this->middlewareParams = $params ?? [];

        if (is_object($stack)) {
            $stack = [$stack];
        }

        $this->middleware->append((array) $stack);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function middlewareParams()
    {
        return $this->middlewareParams;
    }
}