<?php

namespace Compose\Contracts\Http;

interface Bootstrappable
{
    /**
     * Bootstrap the application
     *
     * @param Application $app
     *
     * @return void
     */
    public function bootstrap(Application $app);
}