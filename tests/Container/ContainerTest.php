<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
use Compose\Contracts\Container\ContainerInterface;
 
class ContainerTest extends TestCase
{
    public function testContainerInstanceIsSingleton()
    {
        $container = Container::setContainer(new Container);

        $this->assertSame($container, Container::getContainer());
    }

    public function testGetContainerWillReturnNewInstanceIfCurrentInstanceNull()
    {
        $container = Container::setContainer(null);

        $this->assertNull($container);

        $container2 = Container::getContainer();

        $this->assertInstanceOf(ContainerInterface::class, $container2);
        $this->assertNotSame($container, $container2);
    }

    public function testArrayAccess()
    {
        $container = new Container;

        $container['foo'] = fn() => 'bar';

        $this->assertTrue($container->offsetExists('foo'));

        $this->assertSame($container['foo'], 'bar');
    }
}