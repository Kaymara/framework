<?php

namespace Compose\Routing;

use Compose\Contracts\Http\Application;
use \Compose\Contracts\Routing\Route as RouteInterface;
use Compose\Contracts\Routing\Router as RouterInterface;
use Compose\Http\Middleware\Exceptions\MiddlewareException;
use Compose\Http\Middleware\Middleware;
use Compose\Http\Request;
use Compose\Http\Response;
use Compose\Routing\Aggregates\Routes;
use Compose\Routing\Exceptions\HttpMethodNotSupported;
use Compose\Routing\Exceptions\ParamMismatchException;
use Compose\Routing\Exceptions\ParamPatternException;
use Compose\Routing\Exceptions\RouteNotFoundException;
use Compose\Routing\Exceptions\RouterException;
use Compose\Traits\CreateStaticInstance;
use Compose\Utility\Hash;
use Compose\Utility\Str;

class Router implements RouterInterface
{
    use CreateStaticInstance;

    /**
     * The application instance
     *
     * @var \Compose\Contracts\Http\Application
     */
    protected $app;

    /**
     * The routes bound to the router
     *
     * @var Routes $routes
     */
    protected $routes;

    /**
     * Http methods that the router accepts
     *
     * @var array
     */
    protected static $httpMethods = ['get', 'post', 'patch', 'put', 'delete', 'options'];

    /**
     * Custom 404 handler
     *
     * @var callable $notFoundHandler
     */
    protected $notFoundHandler;

    /**
     * Route collection stack
     *
     * @var RouteCollection[]
     */
    protected $collections;

    /**
     * Fallback route
     *
     * @var callable
     */
    protected $fallback;

    /**
     * Create a router instance
     *
     * @param \Compose\Contracts\Http\Application|null $app
     */
    public function __construct(Application $app = null)
    {
        $this->app = $app;
        $this->routes = new Routes;
    }

    /**
     * Route the request to the matched action
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws ParamMismatchException
     * @throws ParamPatternException
     * @throws MiddlewareException
     * @throws \ReflectionException
     * @throws \Compose\Http\Middleware\Exceptions\MiddlewareNotCallableException
     */
    public function route(Request $request): Response
    {
        $route = $this->match($request);

        if (is_null($route)) {
            if ($fallback = $this->fallback) {
                return $this->app['response']->setContent($fallback($request));
            }

            return $this->notFound();
        }

        if (! empty($route->params())) {
            $this->matchParams($request, $route);
        }

        $response = $this->app['response'] ?? Response::create();

        if (! $this->app->shouldSkipMiddleware()) {
            $response = Middleware::create($this->app, $route->middleware()->stack())->handle($request, $response, $route->middlewareParams());
        }

        if (is_callable($action = $route->action())) {
            $content = $action(...$route->paramValues());

            return $response->setContent($content);
        }

        $controller = $this->app->make($route->controller());

        if (! method_exists($controller, $action)) {
            return $this->notFound();
        }

        $content = $controller->$action(...$route->paramValues());

        return $response->setContent($content);
    }

    /**
     * {@inheritDoc}
     */
    public function hasRoute(string $uri)
    {
        return $this->routes->has($uri);
    }

    /**
     * Add a GET route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return \Compose\Contracts\Routing\Route
     *
     * @throws RouterException
     */
    public function get(string $uri, $action)
    {
        return $this->addRoute(__FUNCTION__, $uri, $action);
    }

    /**
     * Add a POST route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return \Compose\Contracts\Routing\Route
     *
     * @throws RouterException
     */
    public function post(string $uri, $action)
    {
        return $this->addRoute(__FUNCTION__, $uri, $action);
    }

    /**
     * Add a PUT route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return \Compose\Contracts\Routing\Route
     *
     * @throws RouterException
     */
    public function put(string $uri, $action)
    {
        return $this->addRoute(__FUNCTION__, $uri, $action);
    }

    /**
     * Add a PATCH route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return \Compose\Contracts\Routing\Route
     *
     * @throws RouterException
     */
    public function patch(string $uri, $action)
    {
        return $this->addRoute(__FUNCTION__, $uri, $action);
    }

    /**
     * Add a DELETE route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return \Compose\Contracts\Routing\Route
     *
     * @throws RouterException
     * @throws HttpMethodNotSupported
     */
    public function delete(string $uri, $action)
    {
        return $this->addRoute(__FUNCTION__, $uri, $action);
    }

    /**
     * Add a OPTIONS route to the router
     *
     * @param string $uri
     * @param $action
     *
     * @return RouteInterface
     *
     * @throws HttpMethodNotSupported
     * @throws RouterException
     */
    public function options(string $uri, $action)
    {
        return $this->addRoute(__FUNCTION__, $uri, $action);
    }

    /**
     * Add a route to the router
     *
     * @param string $method
     * @param string $uri
     * @param string|callable $action
     *
     * @return \Compose\Contracts\Routing\Route
     *
     * @throws HttpMethodNotSupported
     * @throws RouterException
     */
    protected function addRoute(string $method, string $uri, $action)
    {
        if (! in_array(strtolower($method), static::$httpMethods)) {
            $this->notSupported();
        }

        $action = $this->parseControllerAction($action);

        $route = $this->createRoute($method, $uri, $action);

        $this->applyCollectionAttributes($route);

        $this->routes->add($route);

        return $route;
    }

    /**
     * Apply route collection attributes to given route
     *
     * @param RouteInterface $route
     *
     * @return void
     */
    protected function applyCollectionAttributes(RouteInterface $route)
    {
        if (empty($this->collections)) {
            return;
        }

        $collection = Hash::last($this->collections);

        if ($prefix = $collection->prefix()) {
            $route->uri($prefix . '/' . $route->uri());
        }

        $route->middleware(array_merge($collection->middleware()->stack(), $route->middleware()->stack()));
    }

    /**
     * Format a URI
     *
     * @param string $uri
     *
     * @return string
     */
    protected function formatUri(string $uri)
    {
        return trim($uri, '/');
    }

    /**
     * Create a route instance
     *
     * @param string $method
     * @param string $uri
     * @param array $action
     *
     * @return \Compose\Contracts\Routing\Route
     */
    protected function createRoute(string $method, string $uri, $action)
    {
        $params = $this->parseParams($uri);

        $route = Route::create($this, $this->app)
                      ->methods($method)
                      ->uri($this->formatUri($uri))
                      ->action($action['call'])
                      ->setParams($params);

        if (array_key_exists('controller', $action)) {
            $route->controller($action['controller']);
        }

        return $route;
    }

    /**
     * Parse controller and action
     *
     * @param mixed $action
     *
     * @return array
     *
     * @throws RouterException
     */
    protected function parseControllerAction($action)
    {
        if ($action instanceof \Closure) {
            return ['call' => $action];
        }

        if (strpos($action, '@') === false) {
            throw new RouterException('Binding a route to a controller action must be in the form {controller@action}');
        }

        [$controller, $call] = explode('@', $action);

        return compact('controller', 'call');
    }

    /**
     * Match a route to the request uri
     *
     * @param Request $request
     *
     * @return RouteInterface|null
     */
    protected function match(Request $request)
    {
        $route = $this->routes->get($request->getMethod(), $this->formatUri($request->getRequestUri()));

        if (is_null($route) || is_null($redirect = $route->redirect())) {
            return $route;
        }

        return $redirect;
    }

    /**
     * Set 404 handler or send response
     *
     * @param callable|null $handler
     *
     * @return Response
     */
    public function notFound(callable $handler = null)
    {
        $response = Response::create('', 404);

        if (is_null($handler)) {
            $handler = $this->notFoundHandler;

            return $handler ? $handler($response) : $response;
        }

        $this->notFoundHandler = $handler;
    }

    /**
     * @param string $uri
     *
     * @return array|Parameter[]
     */
    protected function parseParams(string &$uri)
    {
        // parse any parameters within curly braces off the route in order to store them separately on the route instance
        // e.g. foo/bar/{id} becomes: route - foo/bar parameter - id

        // at least one param found in route. Need to parse
        if (! preg_match_all('/\/{(.*?)}/', $uri, $matches)) {
            return [];
        }

        foreach ($matches[0] as $match) {
            $uri = str_replace($match, '', $uri);
        }

        $closure = function ($value) {
            if (Str::endsIn($value, '?')) {
                return Parameter::create(Str::removeEnd($value))->required(false);
            }

            return Parameter::create($value);
        };

        return array_map($closure, $matches[1]);
    }

    /**
     * Match request params to route params
     *
     * @param Request $request
     * @param RouteInterface $route
     *
     * @return void
     *
     * @throws ParamMismatchException
     * @throws ParamPatternException
     */
    protected function matchParams(Request $request, RouteInterface $route)
    {
        $paramString = $this->getParamString($request, $route);

        $params = empty($paramString) ? [] : explode('/', $paramString);

        if ($required = count($route->requiredParams()) > $given = count($params)) {
            throw new ParamMismatchException(
                sprintf('There are %d required route param(s). %d were provided', $required, $given)
            );
        }

        $this->checkParamPatterns($route, $params);

        foreach ($params as $key => $param) {
            $route->params()[$key]->value($param);
        }
    }

    /**
     * Get the parameter string from request
     * e.g. 1/2 from /foo/bar/1/2
     *
     * @param Request $request
     * @param RouteInterface $route
     *
     * @return string
     */
    protected function getParamString(Request $request, RouteInterface $route)
    {
        return trim(str_replace(trim($route->uri(), '/'), '', trim($request->getRequestUri(), '/')), '/');
    }

    /**
     * Redirect route
     *
     * @param string $from
     * @param string $to
     *
     * @return self
     *
     * @throws RouteNotFoundException
     */
    public function redirect(string $from, string $to)
    {
        if (! $fromRoute = $this->routes->get('get', $this->formatUri($from))) {
            throw new RouteNotFoundException(sprintf('The route {%s} has not been bound to the router.',
                $this->formatUri($from)));
        }

        if (! $toRoute = $this->routes->get('get', $this->formatUri($to))) {
            throw new RouteNotFoundException(sprintf('The route {%s} has not been bound to the router.',
                $this->formatUri($to)));
        }

        $fromRoute->redirect($toRoute);

        return $this;
    }

    /**
     * Bind http methods to router
     *
     * @param $methods
     * @param string $uri
     * @param $action
     *
     * @return RouteInterface[]
     *
     * @throws HttpMethodNotSupported
     * @throws RouterException
     */
    public function methods($methods, string $uri, $action)
    {
        if (! empty(array_diff((array) $methods, static::$httpMethods))) {
            $this->notSupported();
        }

        $routes = [];

        foreach ($methods as $method) {
            $routes[] = $this->addRoute($method, $uri, $action);
        }

        return $routes;
    }

    /**
     * Bind route and action to all supported http methods
     *
     * @param string $uri
     * @param $action
     *
     * @return RouteInterface[]
     *
     * @throws HttpMethodNotSupported
     * @throws RouterException
     */
    public function any(string $uri, $action)
    {
        return $this->methods(static::$httpMethods, $uri, $action);
    }

    /**
     * @throws HttpMethodNotSupported
     */
    protected function notSupported()
    {
        throw new HttpMethodNotSupported(
            sprintf(
                'The router only supports the following methods: {%s}',
                implode(', ', static::$httpMethods)
            )
        );
    }

    /**
     * Get the router's supported Http methods
     *
     * @return array
     */
    public function httpMethods()
    {
        return static::$httpMethods;
    }

    /**
     * Bind a pcre to a route param
     *
     * @param string $param
     * @param string $pattern
     *
     * @return void
     */
    public function pattern(string $param, string $pattern)
    {
        foreach ($this->routes->getByParamName($param) as $route) {
            $route->pattern($param, $pattern);
        }
    }

    /**
     * Check param values against bound patterns
     *
     * @param RouteInterface $route
     * @param $params
     *
     * @return void
     *
     * @throws ParamPatternException
     */
    protected function checkParamPatterns(RouteInterface $route, $params)
    {
        foreach ($params as $key => $value) {
            $param = $route->params()[$key];

            if (is_null($pattern = $param->pattern())) {
                continue;
            }

            if (! preg_match('~' . $pattern . '~', $value)) {
                throw new ParamPatternException(
                    sprintf(
                        'Parameter {%s} must match pattern {%s}',
                        $param->name(),
                        $pattern
                    )
                );
            }
        }

        // make sure param value passes bound pcre

        // throw exception if not
    }

    /**
     * Get route with given name
     *
     * @param string $name
     *
     * @return RouteInterface|null
     */
    public function named(string $name)
    {
        return $this->routes->getByName($name);
    }

    /**
     * Create a route collection
     *
     * @return RouteCollection
     */
    public function collection()
    {
        $this->addCollection($collection = RouteCollection::create($this, $this->app));

        return $collection;
    }

    /**
     * Add route collection to stack
     *
     * @param RouteCollection $collection
     *
     * @return void
     */
    protected function addCollection(RouteCollection $collection)
    {
        $this->collections[] = $collection;
    }

    /**
     * Pop a collection off the stack
     *
     * @return RouteCollection|null
     */
    public function popCollectionStack()
    {
        return array_pop($this->collections);
    }

    /**
     * Bind a fallback route to the router
     *
     * @param callable $callable
     *
     * @return void
     */
    public function fallback(callable $callable)
    {
        $this->fallback = $callable;
    }
}