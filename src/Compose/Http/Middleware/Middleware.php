<?php

namespace Compose\Http\Middleware;

use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\Middleware\Middleware as MiddlewareInterface;
use Compose\Http\Middleware\Exceptions\MiddlewareException;
use Compose\Http\Middleware\Exceptions\MiddlewareNotCallableException;
use Compose\Http\Request;
use Compose\Http\Response;
use Compose\Traits\CreateStaticInstance;
use Compose\Utility\Hash;

class Middleware implements MiddlewareInterface
{
    use CreateStaticInstance;

    /**
     * The application instance
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Items in the middleware stack
     *
     * @var array
     */
    protected array $items = [];

    /**
     * Should middleware be skipped?
     *
     * @var bool
     */
    protected bool $skip = false;

    /**
     * The current middleware stack index
     *
     * @var int
     */
    protected int $index = 0;

    /**
     * Middleware collections
     *
     * @var array
     */
    private $collections = [];

    /**
     * Create middleware stack
     *
     * @param Application $app
     * @param array $items
     */
    public function __construct(Application $app, $items = [])
    {
        $this->app = $app;

        $this->append($items);
    }

    /**
     * {@inheritDoc}
     */
    public function append($middleware)
    {
        if (is_object($middleware)) {
            $middleware = [$middleware];
        }

        foreach ((array) $middleware as $singleMiddleware) {
            if (array_search($singleMiddleware, $this->items) === false) {
                $this->items[] = $singleMiddleware;
            }
        }


        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function prepend($middleware)
    {
        if (array_search($middleware, $this->items) === false) {
            array_unshift($this->items, $middleware);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function terminate()
    {
        foreach ($this->items as $middleware) {
            $instance = $this->app->make($middleware);

            if (method_exists($instance, 'terminate')) {
                $instance->terminate();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $middleware): bool
    {
        return in_array($middleware, $this->items);
    }

    /**
     * {@inheritDoc}
     */
    public function get($index)
    {
        return Hash::get($this->items, $index);
    }

    /**
     * {@inheritDoc}
     *
     * @throws MiddlewareException
     * @throws \ReflectionException
     * @throws MiddlewareNotCallableException
     */
    public function handle(Request $request, Response $response, $params = [])
    {
        if ($this->shouldSkip()) {
            return $response;
        }

        foreach ($this->items as $middleware) {
            return $this->__invoke($request, $response, $params);
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function skip($skip = true)
    {
        $this->skip = $skip;
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkip()
    {
        return $this->skip;
    }

    /**
     * Resolve middleware
     *
     * @param $middleware
     *
     * @return mixed
     * @throws MiddlewareException
     */
    protected function resolve($middleware)
    {
        if (is_object($middleware)) {
            return $middleware;
        }

        if (is_string($middleware)) {
            return $this->app[$middleware];
        }

        throw new MiddlewareException(sprintf('Middleware must be class name or object. {%s} given'), gettype($middleware));
    }

    /**
     * Get middleware stack
     *
     * @return array
     */
    public function stack()
    {
        $collectionItems = [];
        $kernel = $this->app['kernel'];

        foreach ($this->collections as $collection) {
            if (! is_null($middlewareCollection = $kernel->middlewareCollection($collection))) {
                $collectionItems[] = $middlewareCollection;
            }
        }

        return Hash::flatten(array_merge($this->items, $collectionItems));
    }

    /**
     * Remove middleware from stack
     *
     * @param mixed
     *
     * @return self
     */
    public function without($middleware)
    {
        if (is_object($middleware)) {
            $middleware = [$middleware];
        }

        foreach ((array) $middleware as $singleMiddleware) {
            $key = array_search($singleMiddleware, $this->items);

            if ($key !== false) {
                unset($this->items[$key]);
            }
        }
    }

    /**
     * Apply middleware collection(s)
     *
     * @param string|array $middleware
     *
     * @return self
     */
    public function collection($middleware)
    {
        if (! is_string($middleware) && ! is_array($middleware)) {
            throw new \InvalidArgumentException(sprintf('Middleware must be a string or array. {%s} provided', gettype($middleware)));
        }

        $this->collections = (array) $middleware;
    }

    public function __invoke(Request $request, Response $response, $params)
    {
        $next = $this->get($this->index);

        if (! $next) {
            return $response;
        }

        $next = $this->resolve($next);

        if (! is_callable($next)) {
            throw new MiddlewareNotCallableException(sprintf("{%s} must be callable", (new \ReflectionClass($next))->getName()));
        }

        $this->index++;

        return $next($request, $response, $params, $this);
    }
}