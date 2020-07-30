<?php declare(strict_types=1);

namespace Compose\Routing;

use Compose\Contracts\Container\ContainerInterface;
use Compose\Contracts\Routing\RouteCollection as RouteCollectionInterface;
use Compose\Http\Middleware\Exceptions\MiddlewareNotCallableException;
use Compose\Http\Middleware\Middleware;
use Compose\Traits\CreateStaticInstance;

class RouteCollection implements RouteCollectionInterface
{
    use CreateStaticInstance;

    /**
     * The router instance
     *
     * @var Router
     */
    protected $router;

    /**
     * The container instance
     *
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * The route collection prefix
     *
     * @var string
     */
    protected $prefix;

    /**
     * The route collection middleware stack
     *
     * @var Middleware $middleware
     */
    protected $middleware;

    /**
     * Middleware collection stack
     *
     * @var array
     */
    protected $middlewareCollections = [];

    /**
     * Create a route collection
     *
     * @param Router $router
     * @param ContainerInterface $container
     * @param Middleware|null $middleware
     */
    public function __construct(Router $router, ContainerInterface $container, ?Middleware $middleware = null)
    {
        $this->router = $router;
        $this->container = $container;
        $this->middleware = $middleware ?? Middleware::create($this->container);
    }

    /**
     * Apply attributes to routes defined in callable
     *
     * @param callable $callable
     *
     * @return void
     */
    public function apply(callable $callable)
    {
        // We will invoke the callable which will apply all of the collection
        // attributes to the defined routes
        $callable();

        $this->router->popCollectionStack();
    }

    /**
     * Get or set the route collection prefix
     *
     * @param string|null $prefix
     *
     * @return self|string
     */
    public function prefix(string $prefix = null)
    {
        if (is_null($prefix)) {
            return $this->prefix;
        }

        $this->prefix = $this->parsePrefix($prefix);

        return $this;
    }

    /**
     * Parse the given prefix
     *
     * @param string $prefix
     *
     * @return string
     */
    protected function parsePrefix(string $prefix)
    {
        return trim($prefix, '/');
    }

    /**
     * Bind middleware to route collection
     *
     * @param null $stack
     *
     * @return self|Middleware
     *
     * @throws MiddlewareNotCallableException
     * @throws \ReflectionException
     */
    public function middleware($stack = null)
    {
        if (is_null($stack)) {
            return $this->middleware;
        }

        if (is_object($stack)) {
            $stack = [$stack];
        }

        $this->middleware->append((array) $stack);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function middlewareCollection($collection)
    {
        if (! is_string($collection) && ! is_array($collection)) {
            throw new \InvalidArgumentException(sprintf('Middleware must be string or array. {%s} given.', gettype($collection)));
        }

        $this->middleware->collection((array) $collection);

        return $this;
    }
}