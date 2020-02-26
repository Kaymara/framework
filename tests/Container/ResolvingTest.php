<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
use Compose\Container\Exception\ArgumentResolutionException;
use Compose\Container\Exception\ContainerException;
 
class ResolvingTest extends TestCase
{
    public function testServiceResolution()
    {
        $container = new Container;

        $container->service(ServiceResolutionStub::class);

        $this->assertInstanceOf(ServiceResolutionStub::class, $container->make(ServiceResolutionStub::class));
    }

    public function testAutoServiceResolution()
    {
        $container = new Container;

        $this->assertInstanceOf(ServiceResolutionStub::class, $container->make(ServiceResolutionStub::class));
    }

    public function testServiceWithDependencyResolution()
    {
        $container = new Container;

        $class = $container->make(ServiceWithDependencyResolutionStub::class);

        $this->assertInstanceOf(ServiceWithDependencyResolutionStub::class, $class);

        $this->assertInstanceOf(SecondServiceResolutionStub::class, $class->service);
    }
    
    public function testServiceWithNestedDependenciesResolution()
    {
        $container = new Container;

        $class = $container->make(ServiceWithNestedDependencyResolutionStub::class);

        $this->assertInstanceOf(ServiceWithNestedDependencyResolutionStub::class, $class);

        $this->assertInstanceOf(ServiceWithDependencyResolutionStub::class, $class->nested);

        $this->assertInstanceOf(SecondServiceResolutionStub::class, $class->nested->service);
    }

    public function testSingletonResolution()
    {
        $container = new Container;

        $container->singleton(ServiceResolutionStub::class);

        $this->assertSame($container->make(ServiceResolutionStub::class), $container->make(ServiceResolutionStub::class));
    }

    public function testInstanceResolution()
    {
        $container = new Container;

        $class = new ServiceResolutionStub;

        $container->instance('foo', $class);

        $this->assertSame($class, $container->make('foo'));
    }

    public function testClosureResolution()
    {
        $container = new Container;

        $container->service('foo', function () {
            return new ServiceResolutionStub;
        });

        $this->assertInstanceOf(ServiceResolutionStub::class, $container->make('foo'));
    }

    public function testSharedClosureResolution()
    {
        $container = new Container;

        $container->singleton('foo', function () {
            return new ServiceResolutionStub;
        });

        $this->assertSame($container->make('foo'), $container->make('foo'));
    }

    public function testInterfaceResolution()
    {
        $container = new Container;

        $container->service(ServiceStubResolutionInterface::class, ServiceImplementationResolutionStub::class);

        $this->assertInstanceOf(ServiceImplementationResolutionStub::class, $container->make(ServiceStubResolutionInterface::class));
    }

    public function testNonExistentClassResolution()
    {
        $container = new Container;

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Class [dne] does not exist");

        $container->make('dne');
    }

    public function testInterfaceWithoutConcreteResolution()
    {
        $container = new Container;

        $container->service(ServiceStubResolutionInterface::class);

        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage("Class [Compose\Tests\Container\ServiceStubResolutionInterface] is not instantiable");

        $container->make(ServiceStubResolutionInterface::class);
    }

    public function testPrimitiveParamWithoutDefaultResolution()
    {
        $container = new Container;

        $this->expectException(ArgumentResolutionException::class);
        $this->expectExceptionMessage("Primitive type without default value cannot be resolved");

        $container->make(ServiceResolutionStubWithPrimitiveAndNoDefault::class);
    }

    public function testPrimitiveParamWithDefaultResolution()
    {
        $container = new Container;

        $class = $container->make(ServiceResolutionStubWithPrimitiveAndDefault::class);

        $this->assertInstanceOf(ServiceResolutionStubWithPrimitiveAndDefault::class, $class);

        $this->assertSame(5, $class->int);
    }

    public function testContainerIsPassedToResolvers()
    {
        $container = new Container;

        $container->service('foo', function ($c) {
            return $c;
        });

        $this->assertSame($container, $container->make('foo'));
    }
}

class ServiceResolutionStub
{
    //
}

class SecondServiceResolutionStub
{
    //
}

interface ServiceStubResolutionInterface
{
    //
}

class ServiceImplementationResolutionStub implements ServiceStubResolutionInterface
{
    //
}

class ServiceWithDependencyResolutionStub
{
    public $service;

    public function __construct(SecondServiceResolutionStub $secondService)
    {
        $this->service = $secondService;
    }
}

class ServiceWithNestedDependencyResolutionStub
{
    public $nested;

    public function __construct(ServiceWithDependencyResolutionStub $nested)
    {
        $this->nested = $nested;
    }
}

class ServiceResolutionStubWithPrimitiveAndNoDefault
{
    public $int;

    public function __construct(int $int)
    {
        $this->int = $int;
    }
}

class ServiceResolutionStubWithPrimitiveAndDefault
{
    public $int;

    public function __construct(int $int = 5)
    {
        $this->int = $int;
    }
}