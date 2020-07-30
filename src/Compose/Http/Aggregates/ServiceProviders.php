<?php

namespace Compose\Http\Aggregates;

use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\ServiceProvider;

class ServiceProviders
{
    /**
     * The application instance
     *
     * @var \Compose\Contracts\Http\Application
     */
    protected $app;

    /**
     * The application's registered providers
     *
     * @var \Compose\Contracts\Http\ServiceProvider[]
     */
    protected $registered = [];

    /**
     * The application's booted providers
     *
     * @var array
     */
    protected $booted = [];

    public static function create(Application $app = null)
    {
        $self = new static;

        if ($app) {
            $self->app($app);
        }

        return $self;
    }

    /**
     * Inject the application instance
     *
     * @param \Compose\Contracts\Http\Application $app
     *
     * @return self
     */
    public function app($app)
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Is the given service provider registered with the aggregate?
     *
     * @param mixed $provider
     *
     * @return bool
     */
    public function registered($provider)
    {
        return in_array($provider, $this->registered);
    }

    /**
     * Resolve provider
     *
     * @param string $provider
     *
     * @return mixed
     */
    public function resolve(string $provider)
    {
        return new $provider($this->app);
    }

    /**
     * Register a service provider
     *
     * @param ServiceProvider $provider
     *
     * @return void
     */
    public function register(ServiceProvider $provider)
    {
        $provider->register();

        $this->registered[] = $provider;
    }

    /**
     * Boot the registered service providers
     *
     * @return void
     */
    public function boot()
    {
        foreach ($this->registered as $provider) {
            $provider->boot();

            $this->booted[] = $provider;
        }
    }
}