<?php

namespace Compose\Container\Service;

use Compose\Container\Exception\ServiceNotFoundException;
use Compose\Container\Traits\ContainerAware;
use Compose\Contracts\Container\Service\SingletonAggregateInterface;
use Compose\Contracts\Container\Service\ServiceInterface;

class SingletonAggregate implements SingletonAggregateInterface
{
    use ContainerAware;

    /**
     * Array of services
     *
     * @var ServiceInterface[]
     */
    protected $services = [];

    /**
     * Construct the aggregate
     *
     * @param ServiceInterface[] $services
     */
    public function __construct(array $services = []) {
        $this->services = array_filter($services, fn($service) => $service instanceof ServiceInterface);
    }

    /**
     * {@inheritDoc}
     */
    public function add(string $alias, $service) {
        if (! $service instanceof ServiceInterface) {
            $service = new Service($alias, $service);
        }

        $this->services[] = $service->singleton();

        return $service;
    }

    /**
     * {@inheritDoc}
     */
    public function exists(string $alias) {
        foreach ($this->services as $service) {
            if ($service->aliasedWith($alias)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function taggedWith(string $tag) {
        foreach ($this->services as $service) {
            if ($service->taggedWith($tag)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function service(string $alias) {
        if (! $this->exists($alias)) {
            throw new ServiceNotFoundException("Alias {$alias} is not known to a service."); 
        }

        foreach ($this->services as $service) {
            if ($service->aliasedWith($alias)) {
                return $service->setContainer($this->getContainer());
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(string $alias) {
        return $this->service($alias)->resolve();
    }

    /**
     * Resolve array of tagged services from the aggregate
     *
     * @param string $tag
     * 
     * @return mixed
     */
    public function resolveTagged(string $tag) {
        $resolved = [];

        foreach ($this->services as $service) {
            if ($service->taggedWith($tag)) {
                $resolved[] = $service->resolve();
            }
        }

        return $resolved;
    }

    /**
     * {@inheritDoc}
     */
    public function flush() {
        $this->singletons = [];
    }
}