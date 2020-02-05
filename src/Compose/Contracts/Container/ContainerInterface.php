<?php

namespace Compose\Contracts\Container;

use Closure;
use Compose\Contracts\Container\Service\ServiceInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

interface ContainerInterface extends PsrContainerInterface
{
    /**
     * Add a service to the container
     * 
     * @param string $alias
     * @param Closure|string|null $concrete
     * 
     * @return ServiceInterface
     */
    public function add(string $alias, $concrete = null) : ServiceInterface;

    /**
     * Add a singleton to the container
     * 
     * @param string $alias
     * @param Closure|string|null $concrete
     * 
     * @return ServiceInterface
     */
    public function singleton(string $alias, $concrete = null) : ServiceInterface;

    /**
     * Add an instance to the container
     * 
     * @param string $alias
     * @param mixed $instance
     * 
     * @return mixed
     */
    public function instance(string $alias, $instance);

    /**
     * Clear the container of all services
     * 
     * @return void
     */
    public function clear() : void;
}