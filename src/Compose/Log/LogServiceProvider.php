<?php

namespace Compose\Log;

use Compose\Contracts\Http\ServiceProvider;

class LogServiceProvider implements ServiceProvider
{
    /**
     * The application instance
     *
     * @var \Compose\Contracts\Http\Application
     */
    protected $app;

    /**
     * @param \Compose\Contracts\Http\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Handle booting the instance
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any service provider bindings to the application
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('log', fn() => new LogManager($this->app));
    }
}