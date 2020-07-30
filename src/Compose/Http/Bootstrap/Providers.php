<?php

namespace Compose\Http\Bootstrap;

use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\Bootstrappable;

class Providers implements Bootstrappable
{
    /**
     * The application instance
     *
     * @var Application
     */
    public $app;

    /**
     * Bootstrap the application
     *
     * @param Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->registerProviders();

        $app->boot();
    }
}