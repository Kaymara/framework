<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
 
class ExtendingTest extends TestCase
{
    public function testExtendService()
    {
        $container = new Container;

        $container->service('foo', ServiceExtendingStub::class);
        
        $container->extend('foo', function ($oldService, $container) {
            return $oldService->int = 25;
        });

        $service = $container->make('foo');

        $this->assertInstanceOf(ServiceExtendingStub::class, $service);
        $this->assertSame(25, $service->int);
    }

    public function testExtendSingleton()
    {
        $container = new Container;

        $container->singleton('foo', ServiceExtendingStub::class);
        
        $container->extend('foo', function ($oldService, $container) {
            return $oldService->int = 25;
        });

        $service = $container->make('foo');

        $this->assertInstanceOf(ServiceExtendingStub::class, $service);
        $this->assertSame(25, $service->int);
        $this->assertSame($service, $container->make('foo'));
    }

    public function testExtendInstance()
    {
        $container = new Container;

        $service = new ServiceExtendingStub;

        $container->instance('foo', $service);
        
        $container->extend('foo', function ($oldService, $container) {
            $oldService->int = 25;

            return $oldService;
        });

        $service1 = $container->make('foo');

        $this->assertSame(25, $service->int);
        $this->assertSame($service, $service1);
    }

    public function testMultipleExtensions()
    {
        $container = new Container;

        $container->service('foo', ServiceExtendingStub1::class);
        
        $container->extend('foo', function ($oldService, $container) {
            $oldService->a = 1;

            return $oldService;
        });

        $container->extend('foo', function ($oldService, $container) {
            $oldService->b = 2;

            return $oldService;
        });

        $container->extend('foo', function ($oldService, $container) {
            $oldService->c = 3;

            return $oldService;
        });

        $service = $container->make('foo');

        $this->assertInstanceOf(ServiceExtendingStub1::class, $service);
        $this->assertSame(1, $service->a);
        $this->assertSame(2, $service->b);
        $this->assertSame(3, $service->c);
    }

    public function testExtendAlias()
    {
        $container = new Container;

        $container->service('foo', ServiceExtendingStub::class);

        $container->alias('foo', 'bar');

        $container->extend('bar', function ($oldService, $container) {
            $oldService->int = 25;

            return $oldService;
        });

        $service = $container->make('foo');

        $this->assertInstanceOf(ServiceExtendingStub::class, $service);
        $this->assertSame(25, $service->int);
    }

    public function testLazyExtension()
    {
        $container = new Container;

        $container->service('foo', ServiceLazyExtendingStub::class);

        $container->extend('foo', function ($oldService, $container) {
            $oldService::$int = 25;

            return $oldService;
        });

        $this->assertSame(10, ServiceLazyExtendingStub::$int);

        $container->make('foo');

        $this->assertSame(25, ServiceLazyExtendingStub::$int);
    }

    public function testExtendBeforeBind()
    {
        $container = new Container;

        $container->extend('foo', function ($oldService, $container) {
            $oldService->int = 25;

            return $oldService;
        });

        $container->service('foo', ServiceExtendingStub::class);

        $service = $container->make('foo');

        $this->assertInstanceOf(ServiceExtendingStub::class, $service);
        $this->assertSame(25, $service->int);
    }
}

class ServiceExtendingStub
{
    public $int;
}

class ServiceExtendingStub1
{
    public $a;
    public $b;
    public $c;
}

class ServiceLazyExtendingStub
{
    public static $int = 10;
}