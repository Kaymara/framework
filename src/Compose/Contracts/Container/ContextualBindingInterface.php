<?php

namespace Compose\Contracts\Container;

interface ContextualBindingInterface
{
    /**
     * Sets the binding context
     *
     * @param array|string $context
     *
     * @return $this
     */
    public function when($context);

    /**
     * Sets the target abstract
     *
     * @param $abstract
     *
     * @return void
     */
    public function gets($abstract);
}