<?php

namespace Compose\Contracts\Container;

use Compose\Container\Exception\ContainerException;

interface ContainerAwareInterface
{
    /**
     * Get the container instance
     * 
     * @throws ContainerException
     * 
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface;

    /**
     * Set the container instance
     * 
     * @return self
     */
    public function setContainer(ContainerInterface $container) : self;
}