<?php declare(strict_types=1);

namespace Test\Routing;

use Compose\Http\Aggregates\ServiceProviders;
use Compose\Http\Application;
use Compose\Http\Kernel;
use Compose\Http\Middleware\Exceptions\MiddlewareException;
use Compose\Http\Middleware\Exceptions\MiddlewareNotCallableException;
use Compose\Http\Request;
use Compose\Http\Response;
use Compose\Routing\Exceptions\HttpMethodNotSupported;
use Compose\Routing\Exceptions\ParamMismatchException;
use Compose\Routing\Exceptions\ParamPatternException;
use Compose\Routing\Exceptions\RouteNotFoundException;
use Compose\Routing\Router;
use Compose\Routing\Exceptions\RouterException;
use Compose\Routing\RoutingServiceProvider;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public \Compose\Contracts\Http\Application $app;
    public Router $router;

    public function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(__DIR__, ServiceProviders::create());
        (new RoutingServiceProvider($this->app))->register();

        $this->router = $this->app['router'];
    }

    /**
     * @param $method
     *
     * @dataProvider routeProvider
     */
    public function testRegisterRoutes($method)
    {
        $this->router->$method('/foo/bar', fn() => 'baz');

        $this->assertTrue($this->router->hasRoute('foo/bar'));
    }

    public function routeProvider()
    {
        return [
            'get'    => ['get'],
            'post'   => ['post'],
            'patch'  => ['patch'],
            'put'    => ['put'],
            'delete' => ['delete']
        ];
    }

    public function testFormatRoute()
    {
        $route = $this->router->get('/foo/bar/', fn() => 'baz');

        $this->assertSame('foo/bar', $route->uri());
    }

    /**
     * @param $method
     *
     * @dataProvider routeProvider
     */
    public function testCanPassControllerAndActionToRouter($method)
    {
        $route = $this->router->$method('foo/bar', ControllerStub::class . '@show');

        $this->assertSame(ControllerStub::class, $route->controller());
        $this->assertSame('show', $route->action());
    }

    public function testControllerActionMustBeCorrectFormat()
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessage('Binding a route to a controller action must be in the form {controller@action}');

        $this->router->get('foo/bar', ControllerStub::class);
    }

    public function testRequiredParameterBoundToRoute()
    {
        $route = $this->router->get('foo/bar/{baz}/{derek}', fn($id) => $id);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/1';

        $this->assertCount(2, $route->params());
        $this->assertSame('baz', $route->params()[0]->name());
        $this->assertSame('derek', $route->params()[1]->name());
    }

    public function testRequiredRouteParamsWithCallable()
    {
        $this->router->get('foo/bar/{id}/{testId}', fn($id, $testId) => $id + $testId);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/1/3';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals(4, $response->getContent());
    }

    public function testExceptionThrownIfRouteParamMismatch()
    {
        $this->expectException(ParamMismatchException::class);

        $this->router->get('foo/bar/{id}/{testId}', fn($id, $testId) => $id + $testId);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/1';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->router->route($request);
    }

    public function testRequiredRouteParamsWithController()
    {
        $this->router->delete('foo/bar/{id}/{testId}', ControllerStub::class . '@delete');

        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_SERVER['REQUEST_URI'] = '/foo/bar/1/3';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals('resources 1 and 3 deleted', $response->getContent());
    }

    /**
     * @param $route
     * @param $given
     * @param $action
     *
     * @dataProvider notFoundProvider
     */
    public function testNotFound($route, $given, $action)
    {
        $this->router->get($route, $action);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $given;

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertTrue($response->isNotFound());
    }

    public function notFoundProvider()
    {
        return [
            'route not found'             => ['/bar/foo', '/foo/bar', fn() => 'baz'],
            'controller action not found' => ['/foo/bar', '/foo/bar', ControllerStub::class . '@index']
        ];
    }

    /**
     * @dataProvider optionalParamProvider
     *
     * @param $given
     * @param $expected
     */
    public function testOptionalParams($given, $expected)
    {
        $this->router->get('foo/bar/{id?}', ControllerStub::class . '@create');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $given;

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals($expected, $response->getContent());
    }

    public function optionalParamProvider()
    {
        return [
            'without param' => ['/foo/bar', 'bar'],
            'with param'    => ['/foo/bar/derek', 'derek']
        ];
    }

    public function testRedirect()
    {
        $route = $this->router->get('foo/bar/', ControllerStub::class . '@foo');
        $this->router->get('bar/foo', ControllerStub::class . '@bar');
        $this->router->redirect('/foo/bar/', '/bar/foo');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertSame('bar/foo', $route->redirect()->uri());
        $this->assertSame('bar', $response->getContent());
    }

    public function testToRouteMustExistWhenRedirecting()
    {
        $this->expectException(RouteNotFoundException::class);

        $this->router->get('bar/foo', ControllerStub::class . '@bar');
        $this->router->redirect('/foo/bar/', '/bar/foo');
    }

    public function testFromRouteMustExistWhenRedirecting()
    {
        $this->expectException(RouteNotFoundException::class);

        $this->router->get('foo/bar', ControllerStub::class . '@bar');
        $this->router->redirect('/foo/bar/', '/bar/foo');
    }

    public function testBindingMultipleMethodsToRoute()
    {
        $methods = ['get', 'post'];
        $this->router->methods($methods, 'foo/bar/', ControllerStub::class . '@foo');

        foreach ($methods as $method) {
            $_SERVER['REQUEST_METHOD'] = $method;
            $_SERVER['REQUEST_URI'] = '/foo/bar/';

            $request = new Request([], [], [], [], [], $_SERVER);
            $response = $this->router->route($request);

            $this->assertSame('foo', $response->getContent());
        }
    }

    public function testCannotBindUnsupportedMethodWhenBindingMultiple()
    {
        $this->expectException(HttpMethodNotSupported::class);

        $this->router->methods(['get', 'head'], 'foo/bar/', ControllerStub::class . '@foo');
    }

    public function testBindingAllSupportedMethodsToRoute()
    {
        $this->router->any('foo/bar/', fn() => 'foo');

        foreach ($this->router->httpMethods() as $method) {
            $_SERVER['REQUEST_METHOD'] = $method;
            $_SERVER['REQUEST_URI'] = '/foo/bar/';

            $request = new Request([], [], [], [], [], $_SERVER);
            $response = $this->router->route($request);

            $this->assertSame('foo', $response->getContent());
        }
    }

    public function testSettingNotFoundHandler()
    {
        $this->router->get('foo/bar/', fn() => 'foo');
        $this->router->notFound(fn(Response $response) => $response->setContent('foo'));

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bar/foo/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('foo', $response->getContent());
    }

    public function testGlobalParamPattern()
    {
        $this->router->get('foo/bar/{id}', fn($id) => $id);
        $this->router->pattern('id', '[0-9]+');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/1';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals(1, $response->getContent());
    }

    public function testParamMustMatchGlobalParamPattern()
    {
        $this->expectException(ParamPatternException::class);

        $this->router->get('foo/bar/{id}', fn($id) => $id);
        $this->router->pattern('id', '[0-9]+');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/derek';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->router->route($request);
    }

    public function testLocalParamPattern()
    {
        $this->router->get('foo/bar/{id}', fn($id) => $id)->pattern('id', '[0-9]+');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/1';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals(1, $response->getContent());
    }

    public function testParamMustMatchLocalParamPattern()
    {
        $this->expectException(ParamPatternException::class);

        $this->router->get('foo/bar/{id}', fn($id) => $id)->pattern('id', '[0-9]+');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/derek';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->router->route($request);
    }

    /**
     * @param $name
     * @param $expected
     * @param $comparison
     *
     * @dataProvider namedRouteProvider
     */
    public function testNamedRoute($name, $expected, $comparison)
    {
        $this->router->get('foo/bar', fn() => 'baz')->name($name);

        $route = $this->router->named('foo');

        $this->assertEquals($expected, $comparison($route));
    }

    public function namedRouteProvider()
    {
        return [
            'route has name'          => ['foo', 'foo/bar', fn($route) => $route->uri()],
            'route doesn\'t has name' => ['bar', null, fn() => null]
        ];
    }

    public function testCanRegisterRoutesInCollection()
    {
        $this->router->collection()->apply(function() {
            $this->router->get('foo/bar', fn() => 'baz');
            $this->router->get('bar/baz', fn() => 'amara');
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals('baz', $response->getContent());

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/bar/baz/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals('amara', $response->getContent());
    }

    public function testRouteCollectionPrefix()
    {
        $this->router->collection()->prefix('admin')->apply(function() {
            $this->router->get('foo/bar', fn() => 'baz');
        });

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/admin/foo/bar/';

        $request = new Request([], [], [], [], [], $_SERVER);
        $response = $this->router->route($request);

        $this->assertEquals('baz', $response->getContent());
    }

    public function testFallbackRoute()
    {
        $this->router->fallback(fn() => 'fallin\' back');

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $request = new Request([], [], [], [], [], $_SERVER);
        $this->app['response'] = Response::class;

        $this->assertEquals('fallin\' back', $this->router->route($request)->getContent());
    }

    public function testHttpMethodOverride()
    {
        $this->router->put('foo/bar', fn() => 'amara');

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/foo/bar';

        $request = new Request([], ['_method' => 'PUT'], [], [], [], $_SERVER);
        $request::enableHttpMethodParameterOverride();

        $this->assertEquals('amara', $this->router->route($request)->getContent());
    }
}

class ControllerStub
{
    public function show($id)
    {
        return $id;
    }

    public function delete($foo, $bar)
    {
        return sprintf('resources %s and %s deleted', $foo, $bar);
    }

    public function create($foo = null)
    {
        return $foo ?? 'bar';
    }

    public function foo()
    {
        return 'foo';
    }

    public function bar()
    {
        return 'bar';
    }
}