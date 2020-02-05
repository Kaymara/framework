<?php

namespace Compose\Contracts\Container\Service;

use Compose\Contracts\Container\ContainerAwareInterface;
use Compose\Contracts\Container\Service\ServiceInterface;

interface ServiceAggregateInterface extends ContainerAwareInterface
{
    /**
     * Add a service to the aggregate
     *
     * @param string $alias
     * @param mixed $service
     * 
     * @return ServiceInterface
     */
    public function add(string $alias, $service) : ServiceInterface;

    /**
     * Does a service with the given alias exist in the aggregate?
     *
     * @param string $alias
     * 
     * @return boolean
     */
    public function exists(string $alias) : bool;

    /**
     * Are there services with the given tag in the aggregate?
     *
     * @param string $tag
     * 
     * @return boolean
     */
    public function taggedWith(string $tag) : bool;

    /**
     * Retrieves service with given alias from the aggregate
     *
     * @param string $alias
     * 
     * @return ServiceInterface
     */
    public function service(string $alias) : ServiceInterface;

    /**
     * Resolve service with given alias from the aggregaate
     *
     * @param string $alias
     * 
     * @return mixed
     */
    public function resolve(string $alias);

    /**
     * Resolve array of tagged services from the aggregate
     *
     * @param string $tag
     * 
     * @return mixed
     */
    public function resolveTagged(string $tag);

    /**
     * Flush services
     *
     * @return self
     */
    public function flush() : self;
}