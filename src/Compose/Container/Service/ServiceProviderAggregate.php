<?php

namespace Compose\Container\Service;

use Compose\Container\Exception\ServiceProviderException;
use Compose\Container\Traits\ContainerAware;
use Compose\Contracts\Container\Service\ServiceProviderAggregateInterface;
use Compose\Contracts\Container\Service\ServiceProviderInterface;

class ServiceProviderAggregate implements ServiceProviderAggregateInterface
{
    use ContainerAware;

    /**
     * Array of providers
     *
     * @var ServiceProviderInterface[]
     */
    protected $providers = [];

    /**
     * Array of registered providers
     *
     * @var ServiceProviderInterface[]
     */
    protected $registered = [];

    /**
     * Array of booted providers
     *
     * @var ServiceProviderInterface[]
     */
    protected $booted = [];

    /**
     * Construct the aggregate
     *
     * @param ServiceProviderInterface[] $providers
     */
    public function __construct(array $providers = []) {
        $this->providers = array_filter($providers, fn($provider) => $provider instanceof ServiceProviderInterface);
    }

    /**
     * {@inheritDoc}
     */
    public function add($provider) {
        if (is_string($provider) && $this->getContainer()->has($provider)) {
            $provider = $this->getContainer()->get($provider);
        } elseif (is_string($provider) && class_exists($provider)) {
            $provider = new $provider;
        }

        if (in_array($this->providers, $provider, true)) {
            return $provider;
        }

        if ($provider instanceof ContainerAware) {
            $provider->setContainer($this->getContainer());
        }

        if ($provider instanceof ServiceProviderInterface) {
            $this->providers[] = $provider;

            return $this;
        }

        throw new ServiceProviderException(
            'A service must be a fully qualified class name or instance ' .
            'of {\Compose\Contracts\Container\Service\ServiceProviderInterface}'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function provides(string $service) {
        foreach ($this->providers as $provider) {
            if ($provider->provides($service)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function register() {
        foreach ($this->providers as $provider) {
            if (in_array($provider, $this->registered, true)) {
                continue;
            }

            $provider->register();
            $this->registered[] = $provider;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function boot() {
        foreach ($this->providers as $provider) {
            if (in_array($provider, $this->booted, true)) {
                continue;
            }

            if (! in_array($provider, $this->registered, true)) {
                $provider->register();
                $this->registered[] = $provider;
            }

            $provider->boot();
            $this->booted[] = $provider;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function flush() {
        $this->providers = [];
    }
}