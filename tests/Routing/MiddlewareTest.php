<?php

namespace Test\Routing;

use Compose\Http\Aggregates\ServiceProviders;
use Compose\Http\Application;
use Compose\Http\Kernel;
use Compose\Http\Middleware\Exceptions\MiddlewareException;
use Compose\Http\Middleware\Exceptions\MiddlewareNotCallableException;
use Compose\Http\Request;
use Compose\Http\Response;
use Compose\Routing\Router;
use Compose\Routing\RoutingServiceProvider;
use Compose\Utility\Hash;
use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public \Compose\Contracts\Http\Application $app;
    public Router $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__, ServiceProviders::create());
        $this->app->instance('kernel', Kernel::create($this->app));

        (new RoutingServiceProvider($this->app))->register();

        $this->router = $this->app['router'];
    }

    /**
     * @dataProvider middlewareProvider
     */
    public function testBindingMiddlewareToRoute($middleware)
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('Triggered from test :)');

        $this->router->get('foo/bar', fn() => 'amara')->middleware($middleware);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $request = new Request([], [], [], [], [], $_SERVER);

        $this->router->route($request);
    }

    public function middlewareProvider()
    {
        return [
            [MiddlewareStub::class],
            [[MiddlewareStub::class]],
            [new MiddlewareStub()]
        ];
    }

    public function testMiddlewareMustBeCallable()
    {
        $this->expectException(MiddlewareNotCallableException::class);

        $this->router->get('foo/bar', fn() => 'amara')->middleware(NotCallableMiddlewareStub::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $request = new Request([], [], [], [], [], $_SERVER);

        $this->router->route($request);
    }

    public function testRouteCollectionMiddleware()
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('Triggered from test :)');

        $this->router->collection()
                     ->middleware(MiddlewareStub::class)
                     ->apply(function () {
                         $this->router->get('foo/bar', fn () => 'baz');
                     });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->router->route($request);
    }

    public function testCollectionMiddlewareBeforeRouteMiddleware()
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('Called from collection');

        $this->router->collection()
                     ->middleware(CollectionMiddlewareStub::class)
                     ->apply(function () {
                         $this->router->get('foo/bar', fn () => 'baz')->middleware(MiddlewareStub::class);
                     });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->router->route($request);
    }

    /**
     * @dataProvider middlewareProvider
     */
    public function testExcludeCollectionMiddlewareFromRoute($middleware)
    {
        $this->expectNotToPerformAssertions();

        $this->router->collection()
                     ->middleware($middleware)
                     ->apply(function () use ($middleware) {
                         $this->router->get('foo/bar', fn () => 'baz')->middleware()->without($middleware);
                     });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->router->route($request);
    }

    public function testIndividualRouteMiddlewareCollection()
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('Triggered from test :)');

        $kernel = $this->app['kernel'];
        $reflected = new \ReflectionClass($kernel);

        $middlewareCollections = $reflected->getProperty('middlewareCollections');
        $middlewareCollections->setAccessible(true);
        $middlewareCollections->setValue($kernel, ['foo' => [MiddlewareStub::class]]);

        $this->router->get('foo/bar', fn() => 'amara')->middleware()->collection('foo');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        // make request to route and assert that middleware is called
        $request = new Request([], [], [], [], [], $_SERVER);

        $this->router->route($request);
    }

    public function testRouteCollectionMiddlewareCollection()
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('Triggered from test :)');

        $kernel = $this->app['kernel'];
        $reflected = new \ReflectionClass($kernel);

        $middlewareCollections = $reflected->getProperty('middlewareCollections');
        $middlewareCollections->setAccessible(true);
        $middlewareCollections->setValue($kernel, ['foo' => [MiddlewareStub::class]]);

        $this->router->collection()
                     ->middleware(MiddlewareStub::class)
                     ->middlewareCollection('foo')
                     ->apply(function () {
                         $this->router->get('foo/bar', fn () => 'baz');
                     });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        // make request to route and assert that middleware is called
        $request = new Request([], [], [], [], [], $_SERVER);

        $this->router->route($request);
    }

    public function testSkipMiddleware()
    {
        $this->expectNotToPerformAssertions();

        $this->app->skipMiddleware();

        $this->router->get('foo/bar', fn() => 'amara')->middleware(MiddlewareStub::class);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $request = new Request([], [], [], [], [], $_SERVER);

        $this->router->route($request);
    }

    public function testMiddlewareExtraParams()
    {
        $this->expectException(MiddlewareException::class);
        $this->expectExceptionMessage('baz');

        $this->router->get('foo/bar', fn() => 'amara')->middleware(ExtraParamsMiddlewareStub::class, 'baz');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $request = new Request([], [], [], [], [], $_SERVER);

        $this->router->route($request);
    }
}

class MiddlewareStub
{
    public function __invoke(Request $request, Response $response)
    {
        throw new MiddlewareException('Triggered from test :)');
    }
}

class CollectionMiddlewareStub
{
    public function __invoke(Request $request, Response $response)
    {
        throw new MiddlewareException('Called from collection');
    }
}

class NotCallableMiddlewareStub
{
    public function handle()
    {
        //
    }
}

class ExtraParamsMiddlewareStub
{
    public function __invoke(Request $request, Response $response, array $foo)
    {
        throw new MiddlewareException(Hash::first($foo));
    }
}