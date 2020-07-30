<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use Compose\Container\ContextualBinding;
use Compose\Container\Exception\ContainerException;
use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
 
class ContextualBindingTest extends TestCase
{
    public function testContextualBindingMustYieldSomething()
    {
        $container = new Container;

        $this->expectException(\LogicException::class);

        $container->yield(null);
    }

    public function testYieldReturnsContextualBinding()
    {
        $container = new Container;

        $this->assertInstanceOf(ContextualBinding::class, $container->yield('foo'));
    }

    public function testContextuallyBindClass()
    {
        $container = new Container;

        $container
            ->yield(ContextualBindingStub::class)
            ->when(get_class($this))
            ->gets(ContextualBindingInterface::class);

        $this->assertInstanceOf(ContextualBindingStub::class, $container->make(ContextualBindingInterface::class));
    }

    public function testExceptionThrownWhenCallingClassIsNotContextuallyBound()
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Class [Compose\Tests\Container\ContextualBindingInterface] is not instantiable.');

        $container = new Container;

        $container
            ->yield(ContextualBindingStub::class)
            ->when(ContextualBindingCallingClassStub::class)
            ->gets(ContextualBindingInterface::class);

        $container->make(ContextualBindingInterface::class);
    }

    public function testContextuallyBindMultipleClasses()
    {
        $container = new Container;

        $container
            ->yield(ContextualBindingStub::class)
            ->when([
                ContextualBindingCallingClassStub::class,
                get_class($this)
            ])
            ->gets(ContextualBindingInterface::class);

        $this->assertInstanceOf(ContextualBindingStub::class, $container->make(ContextualBindingInterface::class));
    }

    public function testContextuallyBindClosure()
    {
        $container = new Container;

        $container
            ->yield(fn($container) => $container->make(ContextualBindingStub::class))
            ->when(get_class($this))
            ->gets(ContextualBindingInterface::class);

        $this->assertInstanceOf(ContextualBindingStub::class, $container->make(ContextualBindingInterface::class));
    }
}

interface ContextualBindingInterface
{
    //
}

class ContextualBindingStub implements ContextualBindingInterface
{
    //
}

class ContextualBindingCallingClassStub
{
    //
}
