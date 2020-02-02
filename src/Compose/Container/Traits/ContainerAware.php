<?php

namespace Compose\Container\Traits;

use Compose\Container\Exception\ContainerException;
use Psr\Container\ContainerInterface;

trait ContainerAware
{

    /**
     * Container instance
     *
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Get the container instance
     * 
     * @throws ContainerException
     * 
     * @return ContainerInterface
     */
    public function getContainer() {
        if (! $this->container instanceof ContainerInterface) {
            throw new ContainerException('No container instance has been set.');
        }

        return $this->container;
    }

    /**
     * Set the container instance
     *
     * @param ContainerInterface $container
     * 
     * @return self
     */
    public function setContainer(ContainerInterface $container) {
        if (! $this->container instanceof ContainerInterface) {
            $this->container = $container;
        }

        return $this;
    }
}