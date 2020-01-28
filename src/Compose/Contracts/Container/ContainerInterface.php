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
     * @param string $id
     * @param Closure|null $service
     * @param bool $shared
     * 
     * @return ServiceInterface
     */
    public function add(string $id, $service = null, $shared = false) : ServiceInterface;

    /**
     * Add a singleton to the container
     * 
     * @param string $id
     * @param Closure|null $service
     * 
     * @return ServiceInterface
     */
    public function singleton(string $id, $service = null) : ServiceInterface;

    /**
     * Add an instance to the container
     * 
     * @param string $id
     * @param mixed $instance
     * 
     * @return mixed
     */
    public function instance(string $id, $instance);

    /**
     * Clear the container of all services
     * 
     * @return void
     */
    public function clear() : void;
}