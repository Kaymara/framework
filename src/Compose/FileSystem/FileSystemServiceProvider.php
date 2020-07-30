<?php

namespace Compose\FileSystem;

use Compose\Contracts\Http\ServiceProvider;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class FileSystemServiceProvider implements ServiceProvider
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
        $this->app->instance('files', new Filesystem(new Local($this->app['path.root'])));
    }
}