<?php declare(strict_types = 1);

namespace Compose\Tests\Container;

use PHPUnit\Framework\TestCase;
use Compose\Container\Container;
use LogicException;
 
class AliasingTest extends TestCase
{
    public function testBindAliasToItself()
    {
        $container = new Container;

        $this->expectException(LogicException::class);

        $container->alias('foo', ['foo', 'bar']);
    }

    public function testAlias()
    {
        $container = new Container;

        $container->alias('foo', ['bar', 'baz']);

        $this->assertTrue($container->aliased(['bar', 'baz']));

        $this->assertSame(['bar', 'baz'], $container->aliases('foo'));
    }
}