<?php

namespace Compose\Contracts\Container\Service;

use Compose\Contracts\Container\ContainerAwareInterface;
use IteratorAggregate;

interface ServiceProviderAggregateInterface extends ContainerAwareInterface, IteratorAggregate
{
    /**
     * Add a service provider to the aggregate
     *
     * @param string $alias
     * @param string|ServiceProviderInterface $service
     * 
     * @return self
     */
    public function add($provider) : ServiceProviderAggregateInterface;

    /**
     * Is the given service provided by the aggregate?
     *
     * @param string $service
     * 
     * @return boolean
     */
    public function provides(string $service) : bool;

    /**
     * Registers all service providers in the aggregate
     *
     * @param string $provider
     * 
     * @return void
     */
    public function register();

    /**
     * Boots all service providers in the aggregate
     *
     * @param string $provider
     * 
     * @return void
     */
    public function boot();

    /**
     * Flush providers
     *
     * @return self
     */
    public function flush() : self;
}