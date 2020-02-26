<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
 
class BindingTest extends TestCase
{
    public function testBindService()
    {
        $container = new Container;

        $container->service(ServiceBindingStub::class);

        $this->assertTrue($container->has(ServiceBindingStub::class));

        $this->assertNotSame($container->make(ServiceBindingStub::class), $container->make(ServiceBindingStub::class));
    }

    public function testBindSingleton()
    {
        $container = new Container;

        $container->singleton(ServiceBindingStub::class);

        $this->assertTrue($container->has(ServiceBindingStub::class));

        $this->assertSame($container->make(ServiceBindingStub::class), $container->make(ServiceBindingStub::class));
    }

    public function testBindInstance()
    {
        $container = new Container;
    
        $class = new ServiceBindingStub;

        $container->instance('stub', $class);

        $this->assertTrue($container->has('stub'));

        $this->assertSame($class, $container->make('stub'));
    }
}

class ServiceBindingStub
{
    //
}