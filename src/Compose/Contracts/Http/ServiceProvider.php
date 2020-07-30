<?php

namespace Compose\Contracts\Http;

use Compose\Contracts\Bootable;

interface ServiceProvider extends Bootable
{
    /**
     * Register any service provider bindings to the application
     *
     * @return void
     */
    public function register();
}