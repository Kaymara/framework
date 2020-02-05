<?php

namespace Compose\Container;

use Compose\Container\Service\ServiceAggregate;
use Compose\Container\Service\ServiceProviderAggregate;
use Compose\Container\Service\SingletonAggregate;
use Compose\Contracts\Container\ContainerAwareInterface;
use Compose\Contracts\Container\ContainerInterface;
use Compose\Contracts\Container\Service\ServiceAggregateInterface;
use Compose\Contracts\Container\Service\ServiceProviderAggregateInterface;
use Compose\Contracts\Container\Service\SingletonAggregateInterface;

class Container implements ContainerInterface 
{
    /**
     * Aggregate of services
     *
     * @var ServiceAggregateInterface
     */
    protected $services;

    /**
     * Aggregate of singletons
     *
     * @var SingletonAggregateInterface
     */
    protected $singletons;

    /**
     * Aggregate of service providers
     *
     * @var ServiceProviderAggregateInterface
     */
    protected $providers;

    /**
     * Construct the container.
     *
     * @param ServiceAggregateInterface $services
     * @param SingletonAggregateInterface $singletons
     * @param ServiceProviderAggregateInterface $providers
     */
    public function __construct(
        ServiceAggregateInterface $services = null, 
        SingletonAggregateInterface $singletons = null, 
        ServiceProviderAggregateInterface $providers = null
    ) {
        $this->services = $services ?? new ServiceAggregate();
        $this->singletons = $singletons ?? new SingletonAggregate();
        $this->providers = $providers ?? new ServiceProviderAggregate();

        if ($this->services instanceof ContainerAwareInterface) {
            $this->services->setContainer($this);
        }

        if ($this->singletons instanceof ContainerAwareInterface) {
            $this->singletons->setContainer($this);
        }

        if ($this->providers instanceof ContainerAwareInterface) {
            $this->providers->setContainer($this);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function service(string $alias, $concrete = null) {
        $concrete ??= $alias;

        $this->services->add($alias, $concrete);
    }

    /**
     * {@inheritDoc}
     */
    public function singleton(string $alias, $concrete = null) {
        $concrete ??= $alias;

        $this->singletons->add($alias, $concrete);
    }

    /**
     * {@inheritDoc}
     */
    public function instance(string $alias, $instance, $singleton = false) {
        $aggregate = $singleton ? $this->singletons : $this->services;

        $aggregate->add($alias, $instance);
    }

    /**
     * {@inheritDoc}
     */
    public function flush() {
        $this->services->flush();
        $this->singletons->flush();
        $this->providers->flush();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function get() {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function has($alias) {
        if ($this->services->exists($alias) || $this->services->taggedWith($alias)) {
            return true;
        }

        if ($this->singletons->exists($alias) || $this->singletons->taggedWith($alias)) {
            return true;
        }

        if ($this->providers->provides($alias)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function addProvider($provider) {
        $this->providers->add($provider);

        return $this;
    }
}