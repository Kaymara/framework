<?php

namespace Compose\Contracts\Container;

use Closure;
use Compose\Contracts\Container\Service\ServiceInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;
use Compose\Contracts\Container\Service\ServiceProviderInterface;

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
    public function service(string $alias, $concrete = null) : ServiceInterface;

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
     * @param bool $singleton
     * 
     * @return mixed
     */
    public function instance(string $alias, $instance, $singleton = false);

    /**
     * Flush the container of all items
     * 
     * @return self
     */
    public function flush() : self;

    /**
     * Add a provider to the container
     *
     * @param ServiceProviderInterface|string $provider
     * 
     * @return self
     */
    public function addProvider($provider) : self;
}