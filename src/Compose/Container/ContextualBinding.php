<?php

namespace Compose\Container;

use Compose\Contracts\Container\ContainerInterface;
use Compose\Contracts\Container\ContextualBindingInterface;

class ContextualBinding implements ContextualBindingInterface
{
    /**
     * Container instance
     *
     * @var Container
     */
    protected $container;

    /**
     * The context
     *
     * @var array|string $when
     */
    protected $when;

    /**
     * Contextual binding implementation
     *
     * @var \Closure|string $give
     */
    protected $give;


    /**
     * ContextualBinding constructor.
     *
     * @param \Closure|string $implementation
     * @param ContainerInterface $container
     */
    public function __construct($implementation, ContainerInterface $container)
    {
        $this->give = $implementation;
        $this->container = $container;
    }

    /**
     * Sets the binding context
     *
     * @param array|string $context
     *
     * @return self
     */
    public function when($context)
    {
        // e.g. ['UsersController', 'CompaniesController', 'AccountsController'] or 'UsersController'
        $this->when = $context;

        return $this;
    }

    /**
     * Sets the target abstract
     *
     * @param $abstract
     *
     * @return void
     */
    public function gets($abstract)
    {
        foreach ((array) $this->when as $concrete) {
            $this->container->addContextualBinding($concrete, $abstract, $this->give);
        }
    }
}