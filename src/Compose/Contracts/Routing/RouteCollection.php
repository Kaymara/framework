<?php declare(strict_types=1);

namespace Compose\Contracts\Routing;

use Compose\Contracts\Http\Middleware\Middleware;

interface RouteCollection
{
    /**
     * Apply attributes to routes defined in callable
     *
     * @param callable $callable
     *
     * @return void
     */
    public function apply(callable $callable);

    /**
     * Get or set the route collection prefix
     *
     * @param string|null $prefix
     *
     * @return self|string
     */
    public function prefix(string $prefix = null);

    /**
     * Bind middleware to route collection
     *
     * @param null $stack
     *
     * @return self|Middleware
     */
    public function middleware($stack = null);

    /**
     * Bind middleware collection
     *
     * @param string|array $collection
     *
     * @return self
     */
    public function middlewareCollection($collection);
}