<?php

namespace Compose\Contracts\Container\Service;

use Compose\Contracts\Container\ContainerAwareInterface;
use Compose\Contracts\Container\Service\ServiceInterface;

interface SingletonAggregateInterface extends ContainerAwareInterface
{
    /**
     * Add a singleton to the aggregate
     *
     * @param string $alias
     * @param mixed $service
     * 
     * @return ServiceInterface
     */
    public function add(string $alias, $service) : ServiceInterface;

    /**
     * Does a singleton with the given alias exist in the aggregate?
     *
     * @param string $alias
     * 
     * @return boolean
     */
    public function exists(string $alias) : bool;

    /**
     * Are there singletons with the given tag in the aggregate?
     *
     * @param string $tag
     * 
     * @return boolean
     */
    public function taggedWith(string $tag) : bool;

    /**
     * Retrieves singleton with given alias from the aggregate
     *
     * @param string $alias
     * 
     * @return ServiceInterface
     */
    public function service(string $alias) : ServiceInterface;

    /**
     * Resolve singleton with given alias from the aggregaate
     *
     * @param string $alias
     * 
     * @return mixed
     */
    public function make(string $alias);

    /**
     * Resolve array of tagged singletons from the aggregate
     *
     * @param string $tag
     * 
     * @return mixed
     */
    public function makeTagged(string $tag);

    /**
     * Flush singletons
     *
     * @return self
     */
    public function flush() : self;
}