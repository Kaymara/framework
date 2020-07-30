<?php

namespace Compose\Contracts\Http;

use Compose\Contracts\Container\ContainerInterface;

interface Application extends ContainerInterface
{
    /**
     * Boot the application's service providers
     *
     * @return void
     */
    public function boot();

    /**
     * Register a service provider with the application
     *
     * @param $provider todo: create class and update this doc block
     *
     * @return void
     */
    public function register($provider);

    /**
     * Terminate the application
     *
     * @return void
     */
    public function terminate();

    /**
     * Has the application been bootstrapped?
     *
     * @return bool
     */
    public function bootstrapped() : bool;

    /**
     * Bootstrap the application with given bootstrappers
     *
     * @param array $bootstrappers
     *
     * @return void
     */
    public function bootstrap(array $bootstrappers);

    /**
     * Is the app configuration cached?
     *
     * @return bool
     */
    public function configCached() : bool;

    /**
     * Get the cached configuration file path
     *
     * @return string
     */
    public function cachedConfigPath();

    /**
     * Get the configuration file path
     *
     * @return string
     */
    public function configPath();

    /**
     * Set the application path
     *
     * @param string $path
     *
     * @return self
     */
    public function setAppPath(string $path);

    /**
     * Set the application database path
     *
     * @param string $path
     *
     * @return self
     */
    public function setDbPath(string $path);

    /**
     * Set the application routes path
     *
     * @param string $path
     *
     * @return self
     */
    public function setRoutesPath(string $path);

    /**
     * Set the application storage path
     *
     * @param string $path
     *
     * @return self
     */
    public function setStoragePath(string $path);

    /**
     * Get the environment file path
     *
     * @return string
     */
    public function envPath();

    /**
     * Get the environment file name
     *
     * @return string|null
     */
    public function envFile();

    /**
     * Set the environment file name
     *
     * @param string $file
     *
     * @return self
     */
    public function setEnvFile(string $file);

    /**
     * Set the environment path
     *
     * @param string $path
     *
     * @return self
     */
    public function setEnvPath(string $path);

    /**
     * Set the application's environment
     *
     * @param \Closure $callback
     *
     * @return self
     */
    public function setEnvironment($callback);

    /**
     * Get the current application environment
     *
     * @return string
     */
    public function environment();

    /**
     * Check the current environment against given one
     *
     * @param $environment
     *
     * @return bool
     */
    public function inEnvironment($environment);

    /**
     * Register service providers with the application
     *
     * @return void
     */
    public function registerProviders();

    /**
     * Skip the application's middleware stack
     *
     * @return self
     */
    public function skipMiddleware();

    /**
     * Should the application's middleware stack be skipped?
     *
     * @return bool
     */
    public function shouldSkipMiddleware();
}