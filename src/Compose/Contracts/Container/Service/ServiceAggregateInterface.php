<?php

namespace Compose\Contracts\Container\Service;

interface ServiceAggregateInterface
{
    /**
     * Add a service to the aggregate
     *
     * @param string $id
     * @param ServiceInterface $service
     * 
     * @return ServiceInterface
     */
    public function add(string $id, $service) : ServiceInterface;

    /**
     * Does the given alias exist in the aggregate?
     *
     * @param string $id
     * 
     * @return boolean
     */
    public function has(string $id) : bool;

    /**
     * Does the given tag exist in the aggregate?
     *
     * @param string $tag
     * @return boolean
     */
    public function tagged(string $tag) : bool;

    /**
     * Resolve service from the aggregate
     * 
     * @param string $id
     *
     * @return ServiceInterface
     */
    public function make(string $id) : ServiceInterface;

    /**
     * Resolve services with given tag from the aggregate
     *
     * @param string $tag
     * 
     * @return mixed
     */
    public function makeTagged(string $tag) : ServiceAggregateInterface;
}