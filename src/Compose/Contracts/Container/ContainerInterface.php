<?php

namespace Compose\Contracts\Container;

use Closure;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Add a service to the container
     * 
     * @param string $alias
     * @param Closure|string|null $concrete
     * @param bool $singleton
     * 
     * @return ContainerInterface
     */
    public function service(string $alias, $concrete = null, $singleton = false);

    /**
     * Add a singleton to the container
     * 
     * @param string $alias
     * @param Closure|string|null $concrete
     * 
     * @return ContainerInterface
     */
    public function singleton(string $alias, $concrete = null);

    /**
     * Add an instance to the container
     * 
     * @param string $alias
     * @param mixed $instance
     * @param bool $singleton
     * 
     * @return ContainerInterface
     */
    public function instance(string $alias, $instance, $singleton = false);

    /**
     * Flush the container of all items
     */
    public function flush();

    /**
     * Resolve the given alias
     *
     * @param string $alias
     * @param array $paramOverrides
     *
     * @return mixed
     */
    public function make(string $alias, array $paramOverrides = []);
}