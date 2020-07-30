<?php declare(strict_types=1);

namespace Compose\Http;

use Compose\Container\Container;
use Compose\Contracts\Http\Application as ApplicationInterface;
use Compose\Http\Aggregates\ServiceProviders;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class Application extends Container implements ApplicationInterface, HttpKernelInterface
{
    /**
     * Is the application booted?
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The root path of the application
     *
     * @var string $root
     */
    protected $root;

    /**
     * The application path
     *
     * @var string|null $appPath
     */
    protected $appPath;

    /**
     * The application database path
     *
     * @var string|null
     */
    protected $dbPath;

    /**
     * The application routes path
     *
     * @var string|null $routesPath
     */
    protected $routesPath;

    /**
     * The application storage path
     *
     * @var string|null $routesPath
     */
    protected $storagePath;

    /**
     * The application environment path
     *
     * @var string|null
     */
    protected $envPath;

    /**
     * The application environment file
     *
     * @var string
     */
    protected $envFile = '.env';

    /**
     * Has the application been bootstrapped?
     *
     * @var bool
     */
    protected bool $bootstrapped = false;

    /**
     * The service provider aggregate instance
     *
     * @var \Compose\Http\Aggregates\ServiceProviders
     */
    protected $providers;

    /**
     * The application's core providers
     *
     * @var \Compose\Contracts\Http\ServiceProvider[]
     */
    protected $coreProviders = [
        \Compose\Events\EventServiceProvider::class,
        \Compose\Log\LogServiceProvider::class,
        \Compose\Routing\RoutingServiceProvider::class,
        \Compose\FileSystem\FileSystemServiceProvider::class,
    ];

    /**
     * The application's global middleware stack
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Skip the middleware stack?
     *
     * @var bool
     */
    protected $skipMiddleware = false;

    /**
     * Create a new application
     *
     * @param string $root
     * @param \Compose\Http\Aggregates\ServiceProviders $providers
     */
    public function __construct(string $root, ServiceProviders $providers)
    {
        $this->providers = $providers->app($this);

        $this->setRoot($root);
        $this->bindPaths();
        $this->bindCore();
//        $this->bindServiceProviders();
        $this->bindAliases();
    }

    /**
     * Set the application root path
     *
     * @param string $root
     *
     * @return void
     */
    protected function setRoot(string $root)
    {
        $this->root = rtrim($root, '\/');
    }

    /**
     * Register the core application paths with the container
     *
     * @return void
     */
    protected function bindPaths()
    {
        $this->instance('path.root', $this->rootPath());
        $this->instance('path.app', $this->appPath());
        $this->instance('path.cache', $this->cachePath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.database', $this->dbPath());
        $this->instance('path.db', $this->dbPath());
        $this->instance('path.env', $this->envPath());
        $this->instance('path.environment', $this->envPath());
        $this->instance('path.public', $this->publicPath());
        $this->instance('path.log', $this->logPath());
        $this->instance('path.resources', $this->resourcesPath());
        $this->instance('path.routes', $this->routesPath());
        $this->instance('path.storage', $this->storagePath());
        $this->instance('path.tests', $this->testsPath());
    }

    /**
     * Get the application root path
     *
     * @return string
     */
    protected function rootPath()
    {
        return $this->root;
    }

    /**
     * Get the application path
     *
     * @return string
     */
    protected function appPath()
    {
        return $this->appPath ?: $this->root . DIRECTORY_SEPARATOR . 'app';
    }

    /**
     * {@inheritDoc}
     */
    public function setAppPath(string $path)
    {
        $this->appPath = rtrim($path, '\/');

        $this->instance('app.path', $this->appPath);

        return $this;
    }

    /**
     * Get the application cache path
     *
     * @return string
     */
    protected function cachePath()
    {
        return $this->root . DIRECTORY_SEPARATOR . 'cache';
    }

    /**
     * {@inheritDoc}
     */
    public function configPath()
    {
        return $this->root . DIRECTORY_SEPARATOR . 'config';
    }

    /**
     * {@inheritDoc}
     */
    public function cachedConfigPath()
    {
        return $this->cachePath() . DIRECTORY_SEPARATOR . 'config.php';
    }

    /**
     * Get the application database path
     *
     * @return string
     */
    protected function dbPath()
    {
        return $this->dbPath ?: $this->root . DIRECTORY_SEPARATOR . 'database';
    }

    /**
     * {@inheritDoc}
     */
    public function setDbPath(string $path)
    {
        $this->dbPath = rtrim($path, '\/');

        $this->instance('app.database', $this->dbPath);
        $this->instance('app.db', $this->dbPath);

        return $this;
    }

    /**
     * Get the application public path
     *
     * @return string
     */
    protected function publicPath()
    {
        return $this->root . DIRECTORY_SEPARATOR . 'public';
    }

    /**
     * Get the application logging path
     *
     * @return string
     */
    protected function logPath()
    {
        return $this->root . DIRECTORY_SEPARATOR . 'logs';
    }

    /**
     * Get the application resource path
     *
     * @return string
     */
    protected function resourcesPath()
    {
        return $this->root . DIRECTORY_SEPARATOR . 'resources';
    }

    /**
     * Get the application routes path
     *
     * @return string
     */
    protected function routesPath()
    {
        return $this->routesPath ?: $this->root . DIRECTORY_SEPARATOR . 'routes';
    }

    /**
     * {@inheritDoc}
     */
    public function setRoutesPath(string $path)
    {
        $this->routesPath = rtrim($path, '\/');

        $this->instance('app.routes', $this->routesPath);

        return $this;
    }

    /**
     * Get the application storage path
     *
     * @return string
     */
    protected function storagePath()
    {
        return $this->storagePath ?: $this->root . DIRECTORY_SEPARATOR . 'storage';
    }

    /**
     * {@inheritDoc}
     */
    public function setStoragePath(string $path)
    {
        $this->storagePath = rtrim($path, '\/');

        $this->instance('app.storage', $this->storagePath);

        return $this;
    }

    /**
     * Get the application tests path
     *
     * @return string
     */
    protected function testsPath()
    {
        return $this->root . DIRECTORY_SEPARATOR . 'tests';
    }

    /**
     * Register any core bindings to the app container
     *
     * @return void
     */
    protected function bindCore()
    {
        self::setContainer($this);

        $this->instance(Container::class, $this);
        $this->alias(Container::class, [
            \Compose\Contracts\Container\ContainerInterface::class,
            \Psr\Container\ContainerInterface::class
        ]);
    }

    public function bindAliases()
    {
        // binding => [aliases]
        $aliasGroupings = [
            'app' => [self::class, Container::class, \Compose\Contracts\Container\ContainerInterface::class, \Psr\Container\ContainerInterface::class],
        ];

        foreach ($aliasGroupings as $binding => $aliases) {
            $this->alias($binding, $aliases);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, int $type = self::MASTER_REQUEST, bool $catch = true)
    {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function register($provider)
    {
        // TODO: Implement register() method.
    }

    /**
     * {@inheritDoc}
     */
    public function terminate()
    {
        // TODO: Implement terminate() method.
    }

    /**
     * Has the application been bootstrapped?
     *
     * @return bool
     */
    public function bootstrapped(): bool
    {
        return $this->bootstrapped;
    }

    /**
     * Bootstrap the application with given bootstrappers
     *
     * @param array $bootstrappers
     *
     * @return void
     */
    public function bootstrap(array $bootstrappers)
    {
        if ($this->bootstrapped) {
            return;
        }

        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }

        $this->bootstrapped = true;
    }

    /**
     * Is the app configuration cached?
     *
     * @return bool
     */
    public function configCached(): bool
    {
        return file_exists($this->cachedConfigPath());
    }

    /**
     * Get the environment file path
     *
     * @return string
     */
    public function envPath()
    {
        return $this->envPath ?: $this->root;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvPath(string $path)
    {
        $this->envPath = rtrim($path, '\/');

        $this->instance('app.environment', $this->envPath);
        $this->instance('app.env', $this->envPath);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function envFile()
    {
        return $this->envFile;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvFile(string $file)
    {
        $this->envFile = $file;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setEnvironment($callback)
    {
        // for the web, the current environment will be stored in app.php under the 'env' key (e.g. 'app.env')
        // todo: add console support
        // $args = $argv ?? null;

        $this['env'] = $callback();

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function inEnvironment($environment)
    {
        return mb_strtolower($this['env']) === mb_strtolower($environment);
    }

    /**
     * {@inheritDoc}
     */
    public function environment()
    {
        return $this['env'];
    }

    /**
     * {@inheritDoc}
     */
    public function registerProviders()
    {
        // start with the core providers
        $providers = $this->coreProviders;
        // todo: merge in the configured providers (and handle caching)
        $providers = array_filter($providers, fn($provider) => ! $this->providers->registered($provider));

        foreach ($providers as $provider) {
            if (is_string($provider)) {
                $provider = $this->providers->resolve($provider);
            }

            $this->providers->register($provider);
        }
    }

    /**
     * Boot the application
     *
     * @return void
     */
    public function boot()
    {
        if ($this->isBooted()) {
            return;
        }

        // todo: implement booting provider after application has been booted

        $this->providers->boot();

        $this->booted = true;
    }

    /**
     * Is the application booted?
     *
     * @return bool
     */
    public function isBooted()
    {
        return $this->booted;
    }

    /**
     * {@inheritDoc}
     */
    public function skipMiddleware()
    {
        $this->skipMiddleware = true;

        return $this;
    }

    /**
     * Should the application's middleware stack be skipped?
     *
     * @return bool
     */
    public function shouldSkipMiddleware()
    {
        return $this->skipMiddleware;
    }
}