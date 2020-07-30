<?php

namespace Compose\Contracts\Container\Service;

use Compose\Contracts\Container\ContainerAwareInterface;
use IteratorAggregate;

interface ServiceProviderAggregateInterface extends ContainerAwareInterface
{
    /**
     * Add a service provider to the aggregate
     *
     * @param string $alias
     * @param string|ServiceProviderInterface $service
     * 
     * @return self
     */
    public function add($provider);

    /**
     * Is the given service provided by the aggregate?
     *
     * @param string $service
     * 
     * @return boolean
     */
    public function provides(string $service) : bool;

    /**
     * Register service provider that provides given service
     *
     * @param string|null $service
     * 
     * @return void
     */
    public function register(string $service);

    /**
     * Registers all service providers in the aggregate
     *
     * @return void
     */
    public function registerAll();

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