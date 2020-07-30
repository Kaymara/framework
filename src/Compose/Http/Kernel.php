<?php declare(strict_types=1);

namespace Compose\Http;

use Compose\Contracts\Http\Application;
use Compose\Contracts\Http\Debug\ExceptionHandler;
use Compose\Contracts\Http\Kernel as KernelInterface;
use Compose\Contracts\Http\Middleware\Middleware;
use Compose\Contracts\Routing\Router;
use Compose\Events\Events\RequestHandled;
use Compose\Traits\CreateStaticInstance;
use Symfony\Component\Debug\Exception\FatalThrowableError;

class Kernel implements KernelInterface
{
    use CreateStaticInstance;

    /**
     * List of responsibilities for the Kernel
     *
     * Bootstrap application with what it needs to handle Http request
     * Middleware management
     *      priority?
     *      routing groups?
     * Pass request through middleware
     * Exception / Throwable handling
     *      reporting
     *      rendering
     *  Send request to router
     */

    /**
     * The application instance
     *
     * @var Application
     */
    protected Application $app;

    /**
     * The router instance
     *
     * @var Router
     */
    protected Router $router;

    /**
     * The application's middleware handler
     *
     * @var Middleware
     */
    protected Middleware $middleware;

    /**
     * Collections of middleware
     *
     * @var array
     */
    protected $middlewareCollections = [];

    /**
     * The application's bootstrap classes
     *
     * @var array
     */
    protected array $bootstrappers = [
        \Compose\Http\Bootstrap\EnvironmentVariables::class,
        \Compose\Http\Bootstrap\Configuration::class,
        \Compose\Http\Bootstrap\ErrorHandling::class,
        \Compose\Http\Bootstrap\Providers::class,
    ];

    /**
     * The application's error handler
     *
     * @var
     */
    protected $errorHandler;

    /**
     * Create a Kernel instance
     *
     * @param Application $app
     * @param Router|null $router
     * @param Middleware|null $middleware
     */
    public function __construct(Application $app, ?Router $router = null, ?Middleware $middleware = null)
    {
        $this->app = $app;
        $this->router = $router ?: \Compose\Routing\Router::create($this->app);
        $this->middleware = $middleware ?: \Compose\Http\Middleware\Middleware::create($this->app);
    }

    /**
     * {@inheritDoc}
     */
    public function bootstrap()
    {
        $this->app->bootstrapped() ?: $this->app->bootstrap($this->bootstrappers);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request)
    {
        try {
            $request::enableHttpMethodParameterOverride();

            $this->app->instance('request', $request);
            $this->app->instance('response', Response::create());

            $this->bootstrap();

            $this->middleware->handle($request, $this->app['response']);

            $response = $this->router->route($request);
        } catch (\Exception $e) {
            $this->reportException($e);

            $this->renderException($request, $e);
        } catch (\Throwable $e) {
            $this->reportException(new FatalThrowableError($e));

            $this->renderException($request, $e);
        }

        $this->app['eventBus']->dispatch(new RequestHandled($request, $response));

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function terminate(Request $request, Response $response)
    {
        $this->middleware->terminate();

        $this->app->terminate();
    }

    /**
     *{@inheritDoc}
     */
    public function middlewareCollection(string $collection)
    {
        return $this->middlewareCollections[$collection] ?? null;
    }

    protected function reportException(\Exception $e)
    {
        $this->app[ExceptionHandler::class]->report($e); // todo: define concrete exception handler (or use Symfony's implementation)
    }

    protected function renderException(Request $request, \Exception $e)
    {
        $this->app[ExceptionHandler::class]->render($e);
    }
}