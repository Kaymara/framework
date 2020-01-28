<?php

namespace Compose\Contracts\Container;

interface ContainerAwareInterface
{
    /**
     * Get the container instance
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