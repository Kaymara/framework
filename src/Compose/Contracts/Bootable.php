<?php

namespace Compose\Contracts;

interface Bootable
{
    /**
     * Handle booting the instance
     *
     * @return void
     */
    public function boot();
}