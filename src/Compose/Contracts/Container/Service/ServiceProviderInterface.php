<?php

namespace Compose\Contracts\Container\Service;

use Compose\Contracts\Container\ContainerAwareInterface;

interface ServiceProviderInterface extends ContainerAwareInterface
{
    /**
     * Either returns a boolean if checking whether the given service is provided
     * or return an array of provided services if no argument is passed
     * 
     * @param string|null $service
     *
     * @return void
     */
    public function provides(string $service = null);

    /**
     * Register services with the container
     *
     * @return void
     */
    public function register();

    /**
     * Boot services
     *
     * @return void
     */
    public function boot();
}