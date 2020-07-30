<?php

namespace Compose\Events;

use Compose\Contracts\Http\ServiceProvider;

class EventServiceProvider implements ServiceProvider
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
        $this->app->singleton('eventBus', fn() => EventBus::create($this->app));
    }
}